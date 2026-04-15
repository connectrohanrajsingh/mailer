<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FetchedEmailTracker extends Model
{
    protected $fillable = [
        'fetch_date',
        'folder',
        'total_emails',
        'processed_emails',
        'total_page',
        'processed_pages',
        'start_uid',
        'last_uid',
        'status',
    ];

    protected $casts = [
        'fetch_date'       => 'date',
        'total_emails'     => 'integer',
        'processed_emails' => 'integer',
        'total_page'       => 'integer',
        'processed_pages'  => 'integer',
        'start_uid'        => 'integer',
        'last_uid'         => 'integer',
    ];
}