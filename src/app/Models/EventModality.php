<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventModality extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'distance_km',
        'max_participants',
        'active',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}