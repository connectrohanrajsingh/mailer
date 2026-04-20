<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FetchedEmailAttachment extends Model
{
    protected $fillable = [
        'email_id',
        'email',
        'name',
        'name_uuid',
        'mime_type',
        'size',
        'disposition',
        'inline',
        'content_id',
        'checksum',
        'storage_disk',
        'storage_path',
    ];

    protected $casts = [
        'inline' => 'boolean',
    ];

    public function overview()
    {
        return $this->belongsTo(FetchedEmailOverview::class, 'email_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->email = strtolower(trim($model->email));
        });
    }

    public function getUrl()
    {
        return route('attachment.show', ['inbox', $this->id]);
    }

    public function getDownloadUrl()
    {
        return route('attachment.download', ['inbox', $this->id]);
    }


}
