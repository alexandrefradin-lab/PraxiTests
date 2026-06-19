<?php

namespace Praxis\Core\AI\Services;

use App\Models\Profile;
use Illuminate\Support\Facades\Storage;
use Praxis\Core\AI\AIManager;
use Praxis\Core\AI\PromptBuilder;
use Smalot\PdfParser\Parser as PdfParser;

class CvExtractionService
{
    public function __construct(
        protected AIManager $ai,
        protected PromptBuilder $prompts,
    ) {}

    public function extractFromFile(string $absolutePath): string
    {
        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        return match ($ext) {
            'pdf'  => $this->extractFromPdf($absolutePath),
            'txt'  => file_get_contents($absolutePath),
            default => '', // doc/docx : prévoir conversion via libreoffice ou phpword en plugin
        };
    }

    protected function extractFromPdf(string $path): string
    {
        try {
            $parser = new PdfParser();
            return $parser->parseFile($path)->getText();
        } catch (\Throwable $e) {
            logger()->warning("CV PDF parse failed: {$e->getMessage()}");
            return '';
        }
    }

    public function structureProfile(Profile $profile): array
    {
        // A5 — Vérifier l'existence du fichier avant tout accès
        if (!$profile->cv_extracted_text) {
            if (!$profile->cv_path || !Storage::disk('local')->exists($profile->cv_path)) {
                logger()->warning("CvExtractionService: fichier CV introuvable pour le profil {$profile->id}", [
                    'cv_path' => $profile->cv_path,
                ]);
                return [];
            }
        }

        $text = $profile->cv_extracted_text ?: $this->extractFromFile(storage_path('app/' . $profile->cv_path));
        if (!$text) return [];

        $messages = $this->prompts->cvExtraction($text);

        try {
            $driver = $this->ai->forTask('cv_extract');
            $raw = $driver->chat($messages, ['temperature' => 0.1, 'max_tokens' => 2000]);
        } catch (\Throwable $e) {
            logger()->warning("CV structureProfile AI failed: {$e->getMessage()}");
            return [];
        }

        $first = strpos($raw, '{'); $last = strrpos($raw, '}');
        $structured = ($first !== false && $last !== false)
            ? json_decode(substr($raw, $first, $last - $first + 1), true)
            : null;

        if (is_array($structured)) {
            $profile->update([
                'cv_extracted_text' => $text,
                'cv_structured'     => $structured,
            ]);
            return $structured;
        }
        return [];
    }
}
