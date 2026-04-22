<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentEmailAttachment extends Model
{
    protected $fillable = [
        'email_id',
        'name',
        'name_uuid',
        'mime_type',
        'size',
        'checksum',
        'storage_disk',
        'storage_path',
    ];

    public function getUrl()
    {
        return route('attachment.show', ['outbox', $this->id]);
    }

    public function getDownloadUrl()
    {
        return route('attachment.download', ['outbox', $this->id]);
    }


}
