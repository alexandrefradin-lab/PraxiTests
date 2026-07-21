<?php

namespace App\Support;

/**
 * ChartRenderer — graphiques « cabinet d'audit » pour le PDF (moteur dompdf).
 *
 * dompdf ne rend pas le SVG inline de façon fiable : on génère donc des PNG
 * haute résolution côté serveur (GD, suréchantillonnés ×3 puis réduits pour un
 * anti-crénelage propre) renvoyés en data-URI base64, que dompdf intègre comme
 * une simple image.
 *
 * Palette « éditorial clair » (alignée sur results.blade.php) : papier nu,
 * filets fins, un seul accent employé en trait et en aplat très dilué.
 *   or #A67520 · encre #1C1408 · label #8A7C64 · filet ink @ alpha 105-116.
 *
 * Toutes les méthodes sont défensives : si GD/FreeType/police manquent, elles
 * renvoient null — le template retombe alors sur ses barres HTML (aucune
 * régression). Jamais d'exception propagée.
 */
class ChartRenderer
{
    /** Facteur de suréchantillonnage (rendu net après réduction bicubique). */
    private const SS = 3;

    private static bool $fontsResolved = false;
    private static ?string $fontRegular = null;
    private static ?string $fontBold    = null;

    /* ════════════════════════════ RADAR ════════════════════════════════ */

    /**
     * Toile d'araignée des dimensions.
     *
     * @param array $axes  [ ['label'=>string,'value'=>0..max,'color'=>?'#hex'], … ] (3 à 12)
     * @param array $o     ['max'=>100,'w'=>360,'h'=>330,'accent'=>'#A67520','rings'=>4]
     */
    public static function radar(array $axes, array $o = []): ?string
    {
        try {
            if (! self::ready()) {
                return null;
            }
            $axes = array_values(array_filter($axes, fn ($a) => isset($a['label']) && is_numeric($a['value'] ?? null)));
            $n = count($axes);
            if ($n < 3) {
                return null;
            }
            $axes = array_slice($axes, 0, 12);
            $n = count($axes);

            // Canvas plus large pour éviter la troncature des longs labels (ex. "Investigative")
            $W = (int) ($o['w'] ?? 480);
            $H = (int) ($o['h'] ?? 420);
            $max   = (float) ($o['max']   ?? 100);
            $rings = (int)   ($o['rings'] ?? 4);
            $accent = self::rgb($o['accent'] ?? '#A67520');
            $ss = self::SS;
            $cw = $W * $ss;
            $ch = $H * $ss;

            $im = self::canvas($cw, $ch);
            $cx = $cw / 2;
            $cy = $ch / 2 + 4 * $ss;
            // Marge plus généreuse (68 vs 58) pour les libellés longs
            $R  = min($cw, $ch) / 2 - 70 * $ss;

            $font = self::font();

            // Palette « éditorial clair » : papier nu, filets, un seul accent.
            $ringOut = imagecolorallocatealpha($im, 42,  30,  8,  95);   // anneau extérieur
            $ringIn  = imagecolorallocatealpha($im, 42,  30,  8, 112);   // anneaux intérieurs
            $spoke   = imagecolorallocatealpha($im, 42,  30,  8, 116);   // rayons
            $fill    = imagecolorallocatealpha($im, $accent[0], $accent[1], $accent[2], 104); // aplat très léger
            $line    = imagecolorallocate($im, $accent[0], $accent[1], $accent[2]);
            $dot     = imagecolorallocate($im, $accent[0], $accent[1], $accent[2]);
            $dotE    = imagecolorallocate($im, 255, 255, 255);
            $txt     = imagecolorallocate($im, 28, 20, 8);      // encre — nom de dimension
            $val     = imagecolorallocate($im, 138, 124, 100);  // gris chaud — valeur
            $ringLbl = imagecolorallocatealpha($im, 42, 30, 8, 105);  // graduations 25/50/75/100

            // Pré-calcul des sommets
            $pts = [];
            foreach ($axes as $i => $a) {
                $ang = -M_PI / 2 + $i * 2 * M_PI / $n;
                $pts[] = [
                    'cos'   => cos($ang),
                    'sin'   => sin($ang),
                    'ratio' => max(0.0, min(1.0, ((float) $a['value']) / ($max ?: 100))),
                    'label' => (string) $a['label'],
                    'value' => (float) $a['value'],
                ];
            }

            // Contour de la zone radar (aucun fond : le papier reste nu)
            $bgPoly = [];
            foreach ($pts as $p) {
                $bgPoly[] = $cx + $p['cos'] * $R;
                $bgPoly[] = $cy + $p['sin'] * $R;
            }

            // Anneaux intérieurs
            imagesetthickness($im, max(1, (int) round($ss * 0.7)));
            for ($r = 1; $r < $rings; $r++) {
                $f = $r / $rings;
                $poly = [];
                foreach ($pts as $p) {
                    $poly[] = $cx + $p['cos'] * $R * $f;
                    $poly[] = $cy + $p['sin'] * $R * $f;
                }
                self::closedPoly($im, $poly, $ringIn);
            }
            // Anneau extérieur — à peine plus marqué que les anneaux internes
            imagesetthickness($im, max(1, (int) round($ss * 0.9)));
            self::closedPoly($im, $bgPoly, $ringOut);
            imagesetthickness($im, max(1, (int) round($ss * 0.7)));

            // Rayons
            foreach ($pts as $p) {
                imageline($im, (int) $cx, (int) $cy,
                    (int) ($cx + $p['cos'] * $R), (int) ($cy + $p['sin'] * $R), $spoke);
            }

            // Labels des anneaux (axe vertical, côté droit du centre)
            $fs = (float) ($o['label_size'] ?? 10) * $ss;
            for ($r = 1; $r <= $rings; $r++) {
                $f = $r / $rings;
                $rly = $cy - $R * $f + 3 * $ss;
                self::text($im, $font, $fs * 0.62, $cx + 5 * $ss, $rly,
                    (string) (int) round($max * $f), $ringLbl, 'start');
            }

            // Polygone de données — remplissage + contour
            $poly = [];
            foreach ($pts as $p) {
                $poly[] = $cx + $p['cos'] * $R * $p['ratio'];
                $poly[] = $cy + $p['sin'] * $R * $p['ratio'];
            }
            self::filledPoly($im, $poly, $fill);
            imagesetthickness($im, max(1, (int) round($ss * 1.2)));
            self::closedPoly($im, $poly, $line);

            // Sommets : disque or plein, cerné de blanc. Aucun halo.
            imagesetthickness($im, 1);
            foreach ($pts as $p) {
                $dx = $cx + $p['cos'] * $R * $p['ratio'];
                $dy = $cy + $p['sin'] * $R * $p['ratio'];
                self::disc($im, $dx, $dy, 3.8 * $ss, $dot, $dotE);
            }

            // Labels d'axes — offset 20*$ss pour éviter la troncature
            foreach ($pts as $p) {
                $lx = $cx + $p['cos'] * ($R + 20 * $ss);
                $ly = $cy + $p['sin'] * ($R + 20 * $ss);
                $anchor = $p['cos'] > 0.30 ? 'start' : ($p['cos'] < -0.30 ? 'end' : 'mid');
                self::text($im, $font, $fs * 0.92, $lx, $ly - 4 * $ss, $p['label'], $txt, $anchor);
                self::text($im, $font, $fs * 0.80, $lx, $ly + 9 * $ss,
                    (string) round($p['value']), $val, $anchor);
            }

            return self::down($im, $W, $H);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /* ═══════════════════════ QUADRANT KARASEK ══════════════════════════ */

    /**
     * Quadrant tension/contrôle (demandes × latitude) avec position du candidat.
     *
     * @param array  $k         scoring['karasek'] (demandes, latitude, *_max)
     * @param array  $profiles  scoring['meta_profiles'] (key => ['color'=>, 'label'=>])
     * @param string $profile   clé du profil courant
     */
    public static function karasekQuadrant(array $k, array $profiles = [], ?string $profile = null, array $o = []): ?string
    {
        try {
            if (! self::ready()) {
                return null;
            }
            if (! isset($k['demandes'], $k['latitude'])) {
                return null;
            }
            $scale  = 36.0;
            $thDem  = 22.0;
            $thLat  = 21.0;
            $dem = max(0, min($scale, (float) $k['demandes']));
            $lat = max(0, min($scale, (float) $k['latitude']));

            $W = (int) ($o['w'] ?? 360);
            $H = (int) ($o['h'] ?? 320);
            $ss = self::SS;
            $cw = $W * $ss;
            $ch = $H * $ss;
            $im = self::canvas($cw, $ch);

            // Zone de tracé (marges pour libellés d'axes).
            $mL = 54 * $ss;
            $mR = 22 * $ss;
            $mT = 22 * $ss;
            $mB = 40 * $ss;
            $x0 = $mL;
            $y0 = $mT;
            $pw = $cw - $mL - $mR;
            $ph = $ch - $mT - $mB;

            $font = self::font();

            $px = fn ($v) => $x0 + ($v / $scale) * $pw;            // latitude → x
            $py = fn ($v) => $y0 + $ph - ($v / $scale) * $ph;       // demandes → y (haut = élevé)

            $col = function (string $key) use ($profiles) {
                $hex = $profiles[$key]['color'] ?? '#999999';
                return self::rgb($hex);
            };
            $tx = $px($thLat);
            $ty = $py($thDem);

            // Cadrans teintés.
            $quad = [
                ['tendu',   $x0,  $y0,  $tx,        $ty],
                ['actif',   $tx,  $y0,  $x0 + $pw,  $ty],
                ['passif',  $x0,  $ty,  $tx,        $y0 + $ph],
                ['detendu', $tx,  $ty,  $x0 + $pw,  $y0 + $ph],
            ];
            /* Cadrans : teinte de la config, très diluée — elle situe sans
               dominer. Seul le cadran du candidat garde un libellé en encre. */
            foreach ($quad as [$key, $ax, $ay, $bx, $by]) {
                $c = $col($key);
                $fillc = imagecolorallocatealpha($im, $c[0], $c[1], $c[2], 118);
                imagefilledrectangle($im, (int) $ax, (int) $ay, (int) $bx, (int) $by, $fillc);
                // Étiquette du cadran (centrée).
                $label = $profiles[$key]['label'] ?? ucfirst($key);
                $lblC  = $profile === $key
                    ? imagecolorallocate($im, 28, 20, 8)              // encre
                    : imagecolorallocate($im, 154, 142, 120);         // gris chaud
                self::text($im, $font, 10 * $ss, ($ax + $bx) / 2, ($ay + $by) / 2, $label, $lblC, 'mid');
            }

            // Seuils — filets discrets.
            $gold = imagecolorallocatealpha($im, 166, 117, 32, 88);
            imagesetthickness($im, max(1, (int) round($ss * 0.7)));
            imageline($im, (int) $tx, (int) $y0, (int) $tx, (int) ($y0 + $ph), $gold);
            imageline($im, (int) $x0, (int) $ty, (int) ($x0 + $pw), (int) $ty, $gold);

            // Cadre — hairline.
            $frame = imagecolorallocatealpha($im, 42, 30, 8, 108);
            imagerectangle($im, (int) $x0, (int) $y0, (int) ($x0 + $pw), (int) ($y0 + $ph), $frame);

            // Point candidat : disque or cerné de blanc, sans halo.
            $core = imagecolorallocate($im, 166, 117, 32);
            $edge = imagecolorallocate($im, 255, 255, 255);
            $cxp = $px($lat);
            $cyp = $py($dem);
            self::disc($im, $cxp, $cyp, 5.5 * $ss, $core, $edge);

            // Libellés d'axes.
            $axc = imagecolorallocate($im, 138, 124, 100);
            self::text($im, $font, 9.5 * $ss, $x0 + $pw / 2, $ch - 14 * $ss, 'LATITUDE DÉCISIONNELLE →', $axc, 'mid');
            self::vtext($im, $font, 9.5 * $ss, 16 * $ss, $y0 + $ph / 2, 'DEMANDES PSYCHO →', $axc, 'mid');

            return self::down($im, $W, $H);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /* ═══════════════════════════ Primitives ════════════════════════════ */

    private static function canvas(int $w, int $h)
    {
        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);
        imagealphablending($im, false);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        imagealphablending($im, true);

        return $im;
    }

    /** Réduction bicubique + sortie data-URI. */
    private static function down($im, int $w, int $h): ?string
    {
        $out = imagescale($im, $w, $h, IMG_BICUBIC);
        imagedestroy($im);
        if ($out === false) {
            return null;
        }
        imagesavealpha($out, true);
        ob_start();
        imagepng($out);
        $data = ob_get_clean();
        imagedestroy($out);

        return 'data:image/png;base64,' . base64_encode($data);
    }

    private static function closedPoly($im, array $poly, $color): void
    {
        $n = (int) (count($poly) / 2);
        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            imageline(
                $im,
                (int) round($poly[$i * 2]), (int) round($poly[$i * 2 + 1]),
                (int) round($poly[$j * 2]), (int) round($poly[$j * 2 + 1]),
                $color
            );
        }
    }

    private static function filledPoly($im, array $poly, $color): void
    {
        $ints = array_map(fn ($v) => (int) round($v), $poly);
        // Signature PHP 8.1+ : (image, points, color).
        imagefilledpolygon($im, $ints, $color);
    }

    private static function disc($im, float $x, float $y, float $r, $fill, $edge): void
    {
        $d = (int) round($r * 2);
        imagefilledellipse($im, (int) round($x), (int) round($y), $d + 4, $d + 4, $edge);
        imagefilledellipse($im, (int) round($x), (int) round($y), $d, $d, $fill);
    }

    /** Texte ancré (start|mid|end), centré verticalement sur $y. */
    private static function text($im, ?string $font, float $size, float $x, float $y, string $text, $color, string $anchor = 'start'): void
    {
        if (! $font || $text === '') {
            return;
        }
        $bbox = imagettfbbox($size, 0, $font, $text);
        $w = abs($bbox[2] - $bbox[0]);
        $h = abs($bbox[7] - $bbox[1]);
        $tx = $x;
        if ($anchor === 'mid') {
            $tx = $x - $w / 2;
        } elseif ($anchor === 'end') {
            $tx = $x - $w;
        }
        $ty = $y + $h / 2;
        imagettftext($im, $size, 0, (int) round($tx), (int) round($ty), $color, $font, $text);
    }

    /** Texte vertical (rotation 90°), centré sur $y. */
    private static function vtext($im, ?string $font, float $size, float $x, float $y, string $text, $color, string $anchor = 'mid'): void
    {
        if (! $font || $text === '') {
            return;
        }
        $bbox = imagettfbbox($size, 90, $font, $text);
        $h = abs($bbox[5] - $bbox[1]);   // hauteur visuelle = longueur du texte
        $ty = $y + $h / 2;
        if ($anchor === 'start') {
            $ty = $y;
        } elseif ($anchor === 'end') {
            $ty = $y + $h;
        }
        imagettftext($im, $size, 90, (int) round($x), (int) round($ty), $color, $font, $text);
    }

    /* ═══════════════════════ Disponibilité & polices ═══════════════════ */

    private static function ready(): bool
    {
        return function_exists('imagecreatetruecolor')
            && function_exists('imagettftext')
            && function_exists('imagescale')
            && self::font() !== null;
    }

    private static function font(bool $bold = false): ?string
    {
        if (! self::$fontsResolved) {
            self::resolveFonts();
            self::$fontsResolved = true;
        }

        return $bold ? (self::$fontBold ?? self::$fontRegular) : self::$fontRegular;
    }

    private static function resolveFonts(): void
    {
        $base = function (string $p): string {
            return function_exists('base_path') ? base_path($p) : $p;
        };
        $regularCandidates = [
            $base('resources/fonts/Lato-Regular.ttf'),
            $base('resources/fonts/Lora-Regular.ttf'),
            $base('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/lato/Lato-Regular.ttf',
        ];
        $boldCandidates = [
            $base('resources/fonts/Lato-Bold.ttf'),
            $base('resources/fonts/Lora-Bold.ttf'),
            $base('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        ];
        foreach ($regularCandidates as $f) {
            if (is_string($f) && @is_file($f)) {
                self::$fontRegular = $f;
                break;
            }
        }
        foreach ($boldCandidates as $f) {
            if (is_string($f) && @is_file($f)) {
                self::$fontBold = $f;
                break;
            }
        }
    }

    /** '#A67520' → [166,117,32]. Tolérant (#abc, sans #). */
    private static function rgb(string $hex): array
    {
        $h = ltrim($hex, '#');
        if (strlen($h) === 3) {
            $h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
        }
        if (strlen($h) < 6 || ! ctype_xdigit($h)) {
            return [166, 117, 32];
        }

        return [hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2))];
    }
}
