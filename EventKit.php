<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventKit extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',         // Ex: Kit Básico, Kit Premium
        'description',  // Ex: Camiseta + Medalha
        'price',        // Onde o valor "chumbado" vai morar no banco
        'stock',        // Controle de estoque do kit
        'active',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}