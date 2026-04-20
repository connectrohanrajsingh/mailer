<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SendEmailAttachment extends Model
{
    protected $fillable = [
        'sent_email_id',
        'email',
        'name',
        'name_uuid',
        'mime_type',
        'size',
        'checksum',
        'storage_disk',
        'storage_path',
    ];


}
