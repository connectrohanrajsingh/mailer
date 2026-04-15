<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class FetchedEmailOverview extends Model
{
    protected $fillable = [
        'folder',
        'uid',
        'message_id',
        'message_id_hashed',
        'subject',
        'sender_name',
        'sender_email',
        'priority',
        'size',
        'seen',
        'answered',
        'flagged',
        'date',
        'received_date',
        'thread_id',
        'in_reply_to',
        'have_attachments',
        'processed'
    ];

    protected $casts = [
        'seen'             => 'boolean',
        'answered'         => 'boolean',
        'flagged'          => 'boolean',
        'have_attachments' => 'boolean',
        'processed'        => 'boolean',
        'date'             => 'datetime',
        'received_date'    => 'datetime',
    ];


    public function body()
    {
        return $this->hasOne(FetchedEmailBody::class, 'email_id');
    }

    public function addresses()
    {
        return $this->hasMany(FetchedEmailAddresses::class, 'email_id');
    }

    public function attachments()
    {
        return $this->hasMany(FetchedEmailAttachment::class, 'email_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!empty($model->message_id)) {
                $model->message_id_hashed = self::normalizedHashedMessageId($model->message_id);
            }

            if (!empty($model->sender_email)) {
                $model->sender_email = strtolower(trim($model->sender_email));
            }
        });
    }

    public static function existsByMessageId(string $messageId, string $folder): bool
    {
        return self::where(['folder' => $folder, 'message_id_hashed' => self::normalizedHashedMessageId($messageId)])->exists();
    }

    public static function normalizedHashedMessageId(string $messageId): string
    {
        $normalized = strtolower(trim($messageId));
        $hash       = hash('sha256', $normalized);
        return $hash;
    }

}
