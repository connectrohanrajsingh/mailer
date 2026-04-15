<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FetchedEmailBody extends Model
{
    protected $fillable = [
        'email_id',
        'body_text',
        'body_html',
        'raw_body',
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

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function getHeader($key)
    {
        return $this->headers[$key] ?? null;
    }
}
