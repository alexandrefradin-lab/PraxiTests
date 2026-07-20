<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * Validation centralisée de l'upload de CV.
 *
 * SEC : double validation
 *  1. Laravel (extension + MIME déclaré par le navigateur)
 *  2. finfo (magic bytes réels du fichier) — résiste au spoofing
 */
class CvUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $maxKb = config('praxiquest.profile.cv_max_size_kb', 5120);
        $mimes = implode(',', config('praxiquest.profile.cv_allowed_mimes', ['pdf', 'doc', 'docx']));

        return [
            'cv' => [
                'required',
                'file',
                "mimes:{$mimes}",
                "max:{$maxKb}",
            ],
        ];
    }

    public function messages(): array
    {
        $maxMb = round(config('praxiquest.profile.cv_max_size_kb', 5120) / 1024, 0);
        $mimes = implode(', ', config('praxiquest.profile.cv_allowed_mimes', ['pdf', 'doc', 'docx']));

        return [
            'cv.required' => 'Votre CV est requis pour continuer.',
            'cv.file'     => 'Le fichier CV n\'est pas valide.',
            'cv.mimes'    => "Le CV doit être au format : {$mimes}.",
            'cv.max'      => "Le CV ne doit pas dépasser {$maxMb} Mo.",
        ];
    }

    /**
     * SEC : vérification magic bytes après validation Laravel.
     * Appelé manuellement depuis le controller.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateMagicBytes(): void
    {
        $file = $this->file('cv');
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return;
        }

        $realMime = $this->detectRealMime($file->getRealPath());

        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        if (!in_array($realMime, $allowedMimes, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cv' => ['Le contenu du fichier CV n\'est pas valide (format non autorisé).'],
            ]);
        }
    }

    /**
     * Détecte le vrai type MIME via finfo (magic bytes).
     * Fallback sur mime_content_type si finfo absent.
     */
    protected function detectRealMime(string $path): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $path);
            finfo_close($finfo);
            return (string) $mime;
        }

        if (function_exists('mime_content_type')) {
            return (string) mime_content_type($path);
        }

        // Fallback ultime : lecture des magic bytes manuellement
        return $this->readMagicBytes($path);
    }

    /**
     * Lecture manuelle des magic bytes pour les formats courants.
     */
    protected function readMagicBytes(string $path): string
    {
        $handle = fopen($path, 'rb');
        if (!$handle) {
            return 'application/octet-stream';
        }
        $bytes = bin2hex(fread($handle, 8));
        fclose($handle);

        return match (true) {
            str_starts_with($bytes, '25504446')             => 'application/pdf',          // %PDF
            str_starts_with($bytes, 'd0cf11e0a1b11ae1')     => 'application/msword',       // DOC (OLE)
            str_starts_with($bytes, '504b0304')             => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX (ZIP)
            default                                         => 'application/octet-stream',
        };
    }
}
