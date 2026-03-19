<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'title',
        'slug',
        'description',
        'location',
        'event_date',
        'registration_deadline',
        'banner_url',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'registration_deadline' => 'datetime',
        ];
    }

    public function kits() {
        return $this->hasMany(EventKit::class);
    }

    public function modalities() {
        return $this->hasMany(EventModality::class);
    }

    public function organizer() {
        return $this->belongsTo(Organizer::class);
    }
}