<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FetchedEmailAddresses extends Model
{
    protected $fillable = [
        'email_id',
        'type',
        'name',
        'email',
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
}
