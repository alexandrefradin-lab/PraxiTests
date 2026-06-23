<?php

namespace App\Http\Controllers\Concerns;

/**
 * Options de personnalisation des rapports PDF (branding + organisation + sections).
 * Partagé par ResultController (synthèse par test) et GrimoireController (relecture globale).
 * Ordre de priorité : config par défaut → surcharge tenant (settings group 'pdf').
 */
trait BuildsBrandedPdf
{
    /**
     * Génère et télécharge un PDF à partir d'une vue Blade.
     *
     * Tente d'abord d'embarquer les polices Lora/Lato (embedFonts=true). Si le
     * cache storage/fonts est inaccessible sur l'hébergeur (ex. OVH cluster121),
     * dompdf lève une exception — on retente avec embedFonts=false pour tomber
     * sur les polices DejaVu déjà mises en cache, garantissant un PDF sans 500.
     *
     * @param  string  $view      Nom de la vue Blade (ex: 'pdf.results')
     * @param  array   $data      Variables transmises à la vue
     * @param  string  $filename  Nom du fichier téléchargé
     */
    protected function downloadBrandedPdf(string $view, array $data, string $filename): \Symfony\Component\HttpFoundation\Response
    {
        $render = function (bool $embedFonts) use ($view, $data): string {
            $html = view($view, array_merge($data, ['embedFonts' => $embedFonts]))->render();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                ->setPaper('A4', 'portrait');

            // Accès au dompdf sous-jacent pour activer les scripts PHP (numérotation).
            $dompdf = $pdf->getDomPDF();
            $options = $dompdf->getOptions();
            $options->setIsPhpEnabled(true);
            $dompdf->setOptions($options);

            $pdf->render();
            return $pdf->output();
        };

        try {
            $output = $render(true);
        } catch (\Throwable $e) {
            // Polices inaccessibles (storage/fonts vide ou permissions OVH) → repli DejaVu.
            \Illuminate\Support\Facades\Log::warning('PDF: repli DejaVu (embedFonts=false)', [
                'error' => $e->getMessage(),
            ]);
            $output = $render(false);
        }

        return response($output, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function pdfOptions(): array
    {
        $s = \App\Models\Setting::getGroup('pdf');   // surcharges éventuelles par tenant

        $brand = [
            'name'      => $s['brand_name']      ?? config('praxiquest.branding.name'),
            'tagline'   => $s['brand_tagline']   ?? config('praxiquest.branding.tagline'),
            'logo'      => $s['brand_logo']      ?? config('praxiquest.branding.logo'),
            'primary'   => $s['color_primary']   ?? config('praxiquest.branding.primary_color', '#4F46E5'),
            'secondary' => $s['color_secondary'] ?? config('praxiquest.branding.secondary_color', '#10B981'),
            'accent'    => $s['color_accent']    ?? '#0F172A',
        ];

        $org = [
            'name'    => $brand['name'],
            'advisor' => $s['advisor'] ?? config('praxiquest.pdf.footer.advisor'),
            'email'   => $s['email']   ?? config('praxiquest.pdf.footer.email'),
            'phone'   => $s['phone']   ?? config('praxiquest.pdf.footer.phone'),
            'website' => $s['website'] ?? config('praxiquest.pdf.footer.website'),
            'address' => $s['address'] ?? config('praxiquest.pdf.footer.address'),
            'legal'   => $s['legal']   ?? config('praxiquest.pdf.footer.legal'),
        ];

        // Sections : un réglage tenant 'section_<clé>' = '0' désactive le bloc.
        $sections = config('praxiquest.pdf.sections', []);
        foreach ($sections as $key => $default) {
            if (array_key_exists("section_{$key}", $s)) {
                $sections[$key] = filter_var($s["section_{$key}"], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return compact('brand', 'org', 'sections');
    }
}
