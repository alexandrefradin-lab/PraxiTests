<?php

namespace Praxis\Core\Mailing;

class NeuromarketingOptimizer
{
    /**
     * Génère N variantes de subject avec des biais cognitifs différents.
     * Retourne ['variant_key' => 'subject string', ...]
     */
    public function subjectVariants(string $base, array $biases = ['scarcity', 'urgency', 'social_proof']): array
    {
        $variants = ['control' => $base];

        foreach ($biases as $bias) {
            $variants[$bias] = match ($bias) {
                'scarcity'    => "Dernières places — {$base}",
                'urgency'     => "Aujourd'hui seulement : {$base}",
                'social_proof' => "Ils l'ont fait — {$base}",
                'curiosity'   => "Un détail qui change tout : {$base}",
                'reciprocity' => "Cadeau pour toi : {$base}",
                'authority'   => "Recommandé par nos experts : {$base}",
                'anchoring'   => "Avant 199 € — aujourd'hui {$base}",
                default       => $base,
            };
        }
        return $variants;
    }

    /** Choisit aléatoirement (ou via stats si dispo) une variante */
    public function pickVariant(array $variants, array $performance = []): string
    {
        if (!$performance) {
            $key = array_rand($variants);
            return $variants[$key];
        }
        // Greedy : pick highest open rate
        arsort($performance);
        $best = array_key_first($performance);
        return $variants[$best] ?? reset($variants);
    }

    /**
     * Injecte des éléments neuromarketing dans le HTML d'un email :
     *  - Indicateur de progression (Zeigarnik)
     *  - Social proof
     *  - Compte à rebours / urgence
     *  - Personnalisation prénom
     */
    public function enhanceHtml(string $html, array $context): string
    {
        $progressBar = '';
        if (isset($context['progress_percent'])) {
            $p = (int) $context['progress_percent'];
            $progressBar = "<div style=\"background:#eef2ff;border-radius:6px;overflow:hidden;height:8px;margin:16px 0\">
                <div style=\"width:{$p}%;background:linear-gradient(90deg,#6366f1,#10b981);height:8px\"></div>
            </div>
            <p style=\"font-size:12px;color:#6b7280;margin:0\">Tu es à {$p}% — il ne te reste que quelques étapes.</p>";
        }

        $socialProof = '';
        if (isset($context['users_count'])) {
            $n = number_format($context['users_count'], 0, ',', ' ');
            $socialProof = "<p style=\"font-size:13px;color:#374151\"><strong>{$n} personnes</strong> ont déjà fait le test cette semaine.</p>";
        }

        return str_replace(
            ['{{ NEURO_PROGRESS }}', '{{ NEURO_SOCIAL_PROOF }}'],
            [$progressBar, $socialProof],
            $html
        );
    }

    /** Recommande le meilleur moment d'envoi à partir d'un fuseau et d'une heuristique simple */
    public function bestSendTime(string $timezone = 'Europe/Paris'): \DateTimeImmutable
    {
        $tz = new \DateTimeZone($timezone);
        $now = new \DateTimeImmutable('now', $tz);
        $target = $now->setTime(10, 30);
        if ($target < $now) $target = $target->modify('+1 day');

        $weekday = (int) $target->format('N');
        if (in_array($weekday, [6, 7])) {
            $target = $target->modify("+" . (8 - $weekday) . " day");
        }
        return $target;
    }
}
