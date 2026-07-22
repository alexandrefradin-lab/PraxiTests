<?php

namespace App\Console\Commands;

use App\Models\ProtectionAlert;
use App\Models\UserDevice;
use Illuminate\Console\Command;
use Praxis\Core\Protection\LicenseService;

/**
 * Revue des anomalies détectées par le dispositif anti-copie.
 *
 * Sans surface de lecture, les alertes ne servent à rien : cette commande est
 * le rendez-vous hebdomadaire avec ce que le dispositif a vu.
 *
 *   php artisan praxiquest:protection:report --days=7
 */
class ProtectionReport extends Command
{
    protected $signature = 'praxiquest:protection:report
                            {--days=7 : Fenêtre d\'analyse en jours}
                            {--type= : Filtre sur un type (scraping, sharing, license, pdf_leak)}';

    protected $description = 'Récapitule les anomalies anti-copie détectées sur la période';

    public function handle(LicenseService $license): int
    {
        $days  = max(1, (int) $this->option('days'));
        $since = now()->subDays($days);

        $this->newLine();
        $this->info("Protection PraxiQuest — {$days} derniers jours");

        // ── État des volets ──────────────────────────────────────────────
        $this->table(['Volet', 'Actif', 'Mode'], [
            ['Licence', $this->yn(config('protection.license.enabled')), config('protection.license.mode')],
            ['Anti-scraping', $this->yn(config('protection.scraping.enabled')), config('protection.scraping.mode')],
            ['Partage de comptes', $this->yn(config('protection.sharing.enabled')), config('protection.sharing.mode')],
            ['Traçage PDF', $this->yn(config('protection.watermark.enabled')), '—'],
        ]);

        if (config('protection.license.enabled') && ! $license->passes()) {
            $this->error('Licence : ' . ($license->status()->reason ?? 'non valide'));
        }

        // ── Anomalies ────────────────────────────────────────────────────
        $query = ProtectionAlert::where('created_at', '>=', $since);

        if ($type = $this->option('type')) {
            $query->where('type', $type);
        }

        $counts = (clone $query)
            ->selectRaw('type, severity, count(*) as total')
            ->groupBy('type', 'severity')
            ->get();

        if ($counts->isEmpty()) {
            $this->newLine();
            $this->info('Aucune anomalie sur la période.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->table(
            ['Type', 'Gravité', 'Nombre'],
            $counts->map(fn ($r) => [$r->type, $r->severity, $r->total])->all()
        );

        // ── Détail des alertes non traitées ──────────────────────────────
        $open = (clone $query)->unresolved()->with('user:id,name,email')->latest()->limit(20)->get();

        if ($open->isNotEmpty()) {
            $this->newLine();
            $this->warn("Alertes non traitées ({$open->count()} affichées) :");
            $this->table(
                ['Date', 'Type', 'Compte', 'Résumé'],
                $open->map(fn ($a) => [
                    $a->created_at->format('d/m H:i'),
                    $a->type,
                    $a->user?->email ?? ($a->ip_address ?: '—'),
                    \Illuminate\Support\Str::limit($a->summary, 70),
                ])->all()
            );
            $this->line('Marquer comme traité : ProtectionAlert::find(<id>)->update([\'resolved_at\' => now()]);');
        }

        // ── Comptes aux appareils les plus dispersés ─────────────────────
        $spread = UserDevice::selectRaw('user_id, count(*) as devices, count(distinct last_network) as networks')
            ->where('last_seen_at', '>=', $since)
            ->groupBy('user_id')
            ->havingRaw('count(*) > ?', [(int) config('protection.sharing.max_devices', 5)])
            ->orderByDesc('devices')
            ->limit(10)
            ->get();

        if ($spread->isNotEmpty()) {
            $this->newLine();
            $this->warn('Comptes au-dessus du plafond d\'appareils :');
            $this->table(
                ['Compte', 'Appareils', 'Réseaux'],
                $spread->map(fn ($r) => ['#' . $r->user_id, $r->devices, $r->networks])->all()
            );
        }

        return self::SUCCESS;
    }

    private function yn(mixed $value): string
    {
        return $value ? 'oui' : 'non';
    }
}
