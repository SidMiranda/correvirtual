<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'name',
        'site_url',
        'description',
        'has_logo',
        'position',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'has_logo' => 'boolean',
            'active' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    /**
     * Os que aparecem no site: do organizador atual, ativos, na ordem que o
     * organizador definiu — e o nome só desempata quem tem a mesma posição.
     */
    public function scopeNaVitrine($query, int $organizerId)
    {
        return $query->where('organizer_id', $organizerId)
            ->where('active', true)
            ->orderBy('position')
            ->orderBy('name');
    }
}
