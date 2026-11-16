<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'company', 'email', 'phone', 'solution_type', 'service_id', 'message', 'source_url', 'utm_source', 'utm_medium', 'utm_campaign', 'status', 'internal_notes', 'privacy_accepted_at'])]
class Lead extends Model
{
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    protected function casts(): array
    {
        return ['status' => LeadStatus::class, 'privacy_accepted_at' => 'datetime'];
    }
}
