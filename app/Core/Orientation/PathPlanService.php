<?php

namespace Praxis\Core\Orientation;

use App\Models\CareerPath;
use App\Models\CareerPathPlan;
use App\Models\Profile;
use Illuminate\Support\Facades\Log;
use Praxis\Core\AI\AIManager;
use Praxis\Core\AI\Concerns\ParsesAiJson;

/**
 * Génère un plan d'action personnalisé pour une piste métier donnée.
 *
 * Modèle : Haiku (structuré, compact, ~0,01 €/plan).
 * Stocké dans career_path_plans — généré une seule fois par (profil × piste),
 * réutilisé ensuite sans coût.
 *
 * Format plan_json :
 * {
 *   "premier_pas": "...",
 *   "etapes": [
 *     { "num": 1, "titre": "...", "description": "...", "duree": "..." },
 *     ...
 *   ],
 *   "ressources": [
 *     { "titre": "...", "url": "...", "type": "financement|emploi|reseau|formation|info" },
 *     ...
 *   ],
 *   "conseil": "..."
 * }
 */
class PathPlanService
{
    use ParsesAiJson;

    public function __construct(protected AIManager $ai) {}

    /**
     * Retourne le plan existant ou en génère un nouveau.
     *
     * @return array<string, mixed>  Le plan_json ou tableau vide si échec.
     */
    public function getOrGenerate(Profile $profile, CareerPath $path): array
    {
        $existing = CareerPathPlan::where('profile_id', $profile->id)
            ->where('career_path_id', $path->id)
            ->first();

        if ($existing && !empty($existing->plan_json)) {
            return $existing->plan_json;
        }

        $plan = $this->generate($profile, $path);

        if (!empty($plan)) {
            CareerPathPlan::updateOrCreate(
                ['profile_id' => $profile->id, 'career_path_id' => $path->id],
                ['plan_json' => $plan, 'generated_at' => now()]
            );
        }

        return $plan;
    }

    /**
     * Génère le plan via Haiku (pas de cache — forcer la regénération).
     *
     * @return array<string, mixed>
     */
    public function generate(Profile $profile, CareerPath $path): array
    {
        $messages = $this->buildMessages($profile, $path);

        try {
            $driver = $this->ai->forTask('path_plan');
            $raw    = (string) $driver->chat($messages, ['temperature' => 0.5, 'max_tokens' => 1200]);
        } catch (\Throwable $e) {
            Log::warning('PathPlanService: AI call failed', [
                'profile_id'     => $profile->id,
                'career_path_id' => $path->id,
                'error'          => $e->getMessage(),
            ]);
            return [];
        }

        $json = $this->parseJson($raw);

        // Validation minimale : au moins premier_pas et une étape.
        if (empty($json['premier_pas']) || empty($json['etapes'])) {
            Log::warning('PathPlanService: JSON invalide ou incomplet', [
                'profile_id'     => $profile->id,
                'career_path_id' => $path->id,
                'raw_preview'    => mb_substr($raw, 0, 300),
            ]);
            return [];
        }

        return $json;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Prompt
    // ─────────────────────────────────────────────────────────────────────────

    /** @return array<int, array<string, string>> */
    protected function buildMessages(Profile $profile, CareerPath $path): array
    {
        $salary = $path->salary_indicative;
        $salaryStr = ($salary && isset($salary['median']))
            ? number_format($salary['median'], 0, ',', ' ') . ' €/an (médiane)'
            : 'non communiqué';

        $gapStr = ($path->formation_months ?? 0) <= 0
            ? 'Aucune formation requise'
            : "{$path->formation_months} mois de formation";

        $statusStr = match ($profile->status ?? '') {
            'salarie'           => 'Salarié(e)',
            'demandeur_emploi'  => 'Demandeur/demandeuse d\'emploi',
            'entrepreneur'      => 'Entrepreneur/entrepreneuse',
            default             => 'Non précisé',
        };

        $cvExtract = mb_substr($profile->cv_extracted_text ?? '', 0, 800);
        $cvBlock   = $cvExtract ? "\nExtrait CV : {$cvExtract}" : '';

        $system = <<<SYS
Tu es un conseiller en évolution professionnelle (CEP) expert en PTP (Projet de Transition Professionnelle).
Tu génères des plans d'action concrets, réalistes et motivants en JSON pur (sans balises markdown).
Réponds UNIQUEMENT avec un objet JSON valide. Pas de texte avant ni après.
SYS;

        $user = <<<USR
Génère un plan d'action personnalisé pour ce candidat qui envisage la piste métier suivante.

=== PISTE MÉTIER ===
Titre : {$path->title}
Famille : {$path->family}
Formation requise : {$gapStr}
Salaire indicatif : {$salaryStr}
Marché : demande {$path->market_demand}, tendance {$path->market_trend}

=== PROFIL CANDIDAT ===
Statut : {$statusStr}
Rôle actuel : {$profile->current_role}{$cvBlock}

=== FORMAT JSON ATTENDU ===
{
  "premier_pas": "Une action immédiate, concrète, gratuite à faire cette semaine (1 phrase)",
  "etapes": [
    { "num": 1, "titre": "Titre court", "description": "Description concrète (2-3 phrases)", "duree": "Ex. : 2 semaines" },
    { "num": 2, ... },
    { "num": 3, ... },
    { "num": 4, ... }
  ],
  "ressources": [
    { "titre": "Nom de la ressource", "url": "URL réelle", "type": "financement|emploi|reseau|formation|info" }
  ],
  "conseil": "Conseil personnalisé basé sur le profil (2-3 phrases, ton du coach bienveillant)"
}

Règles :
- 4 étapes (pas plus, pas moins), ordonnées chronologiquement
- Pour un salarié : intégrer le PTP (Transitions Pro, financement employeur)
- Pour un demandeur d'emploi : mentionner l'AIF France Travail / CPF
- 3 à 5 ressources avec des URLs réelles (moncompteformation.gouv.fr, transitionspro.fr, francetravail.fr, etc.)
- Durées réalistes cohérentes avec les {$path->formation_months} mois de formation
- Conseil basé sur le statut et le parcours du candidat, pas générique
USR;

        return [
            ['role' => 'user', 'content' => $system . "\n\n" . $user],
        ];
    }
}
