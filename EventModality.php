<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventModality extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',             // Ex: 5km, 10km, Caminhada
        'distance_km',      // Ex: 5, 10, 0
        'max_participants', // Controle de vagas por modalidade
        'active',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}