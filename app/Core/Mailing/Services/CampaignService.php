<?php

namespace Praxis\Core\Mailing\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Praxis\Core\Mailing\NeuromarketingOptimizer;
use Praxis\Core\Mailing\Mail\CampaignMail;
use Praxis\Core\Plugins\PluginHooks;

class CampaignService
{
    public function __construct(protected NeuromarketingOptimizer $neuro) {}

    public function sendCampaign(int $campaignId): array
    {
        $campaign = DB::table('email_campaigns')->find($campaignId);
        if (!$campaign) throw new \RuntimeException("Campaign not found");

        $audience = $this->resolveAudience(json_decode($campaign->audience_filter ?? '[]', true));
        $variants = json_decode($campaign->variants ?? '[]', true) ?: ['control' => $campaign->subject];

        $stats = ['queued' => 0, 'failed' => 0];
        $logBatch = [];

        foreach ($audience as $user) {
            try {
                $variant = array_rand($variants);
                $subject = $variants[$variant];
                $html = $this->neuro->enhanceHtml(
                    $campaign->body_html,
                    PluginHooks::applyFilters('email.context', ['user' => $user], $user)
                );

                Mail::to($user->email)->queue(new CampaignMail($subject, $html, $campaign->body_text));

                $logBatch[] = [
                    'user_id'     => $user->id,
                    'campaign_id' => $campaign->id,
                    'to_email'    => $user->email,
                    'subject'     => $subject,
                    'variant'     => $variant,
                    'status'      => 'queued',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                if (count($logBatch) >= 500) {
                    DB::table('email_logs')->insert($logBatch);
                    $logBatch = [];
                }

                $stats['queued']++;
            } catch (\Throwable $e) {
                $stats['failed']++;
                logger()->error("Campaign send failed for {$user->email}: {$e->getMessage()}");
            }
        }

        if (!empty($logBatch)) {
            DB::table('email_logs')->insert($logBatch);
        }

        DB::table('email_campaigns')->where('id', $campaign->id)->update([
            'status'  => 'sending',
            'sent_at' => now(),
            'stats'   => json_encode($stats),
        ]);

        PluginHooks::doAction('campaign.sent', $campaign, $stats);
        return $stats;
    }

    protected function resolveAudience(array $filter)
    {
        $q = User::query();
        if (!empty($filter['status']))      $q->whereHas('profile', fn ($p) => $p->where('status', $filter['status']));
        if (!empty($filter['has_completed_test'])) {
            $q->whereHas('attempts', fn ($a) => $a->where('status', 'completed'));
        }
        if (!empty($filter['inactive_days'])) {
            $q->where('last_login_at', '<', now()->subDays((int) $filter['inactive_days']));
        }
        // P-08 : curseur paresseux pour ne pas charger toute la table en mémoire
        return $q->select(['id', 'email'])->cursor();
    }
}
