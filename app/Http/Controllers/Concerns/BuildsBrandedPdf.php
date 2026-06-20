<?php

namespace App\Http\Controllers\Concerns;

/**
 * Options de personnalisation des rapports PDF (branding + organisation + sections).
 * Partagé par ResultController (synthèse par test) et GrimoireController (relecture globale).
 * Ordre de priorité : config par défaut → surcharge tenant (settings group 'pdf').
 */
trait BuildsBrandedPdf
{
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
