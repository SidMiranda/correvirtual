<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;


    protected $fillable = [
        'subscription_id',
        'provider',
        'transaction_id',
        'payment_method',
        'status',
        'qr_code',
        'qr_code_base64',
        'ticket_url',
        'expires_at',
        'paid_at',
        'payload'
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

}
