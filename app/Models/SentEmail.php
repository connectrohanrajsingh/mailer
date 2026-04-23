<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SentEmail extends Model
{
    protected $fillable = ["from_email", "from_name", "to_name", "subject", "to_emails", "cc_emails", "bcc_emails", "reply_to", "status", "remark", "sent_at", "body"];

    protected $casts = [
        'to_emails'  => 'json',
        'cc_emails'  => 'json',
        'bcc_emails' => 'json',
        'reply_to'   => 'integer',
        'sent_at'    => 'datetime',
    ];

    public function attachments()
    {
        return $this->hasMany(SentEmailAttachment::class, 'email_id');
    }

    protected static function booted()
    {
        static::updating(function ($model) {
            if (!empty($model->sender_email)) {
                $model->sender_email = strtolower(trim($model->sender_email));
            }
        });
    }

}
