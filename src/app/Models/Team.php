<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'name',
        'slug',
        'description',
        'is_public',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    /**
     * As equipes que o atleta pode escolher sozinho: do organizador atual,
     * abertas e ativas. As três condições juntas, sempre — é o filtro que a
     * inscrição vai usar quando o vínculo do atleta for implementado.
     */
    public function scopeEscolhivelPeloAtleta($query, int $organizerId)
    {
        return $query->where('organizer_id', $organizerId)
            ->where('is_public', true)
            ->where('active', true);
    }
}
