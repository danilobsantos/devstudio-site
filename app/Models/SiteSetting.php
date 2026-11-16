<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['display_name', 'email', 'phone', 'whatsapp', 'address', 'meta_title', 'meta_description', 'canonical_url', 'social_links', 'institutional_links'])]
class SiteSetting extends Model
{
    protected function casts(): array
    {
        return ['social_links' => 'array', 'institutional_links' => 'array'];
    }
}
