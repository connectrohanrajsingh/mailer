<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FetchedEmailBody extends Model
{
    protected $fillable = [
        'email_id',
        'body_text',
        'body_html',
        'body_raw',
        'flags',
        'headers',
    ];

    protected $casts = [
        'flags'   => 'array',
        'headers' => 'array',
    ];

    public function overview()
    {
        return $this->belongsTo(FetchedEmailOverview::class, 'email_id');
    }

}
