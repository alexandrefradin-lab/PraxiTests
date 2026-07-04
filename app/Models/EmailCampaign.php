<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailCampaign extends Model
{
    use SoftDeletes;

    protected $table = 'email_campaigns';

    protected $fillable = [
        'professional_account_id',
        'name',
        'subject',
        'preheader',
        'body_html',
        'body_text',
        'variants',
        'audience_filter',
        'neuromarketing',
        'status',
        'scheduled_at',
        'sent_at',
        'stats',
    ];

    protected $casts = [
        'variants'        => 'array',
        'audience_filter' => 'array',
        'neuromarketing'  => 'array',
        'stats'           => 'array',
        'scheduled_at'    => 'datetime',
        'sent_at'         => 'datetime',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(EmailLog::class, 'campaign_id');
    }

    public function professionalAccount(): BelongsTo
    {
        return $this->belongsTo(ProfessionalAccount::class);
    }
}
