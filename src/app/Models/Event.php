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

    public function subscriptions() {
        return $this->hasMany(Subscription::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Situação
    |--------------------------------------------------------------------------
    | Deduzida das datas, sem coluna própria (decisão do dono em 2026-08-29):
    | com poucos eventos, uma coluna a mais seria só mais um lugar para
    | desatualizar. A contrapartida é que não dá para cancelar nem reabrir um
    | evento na mão — se isso for preciso, vira coluna.
    */

    public function jaAconteceu(): bool
    {
        return $this->event_date !== null && $this->event_date->isPast();
    }

    public function inscricoesAbertas(): bool
    {
        return $this->active
            && !$this->jaAconteceu()
            && $this->registration_deadline !== null
            && $this->registration_deadline->isFuture();
    }

    /** Rótulo curto para as telas. */
    public function situacao(): string
    {
        if (!$this->active) {
            return 'Inativo';
        }

        if ($this->jaAconteceu()) {
            return 'Realizado';
        }

        return $this->inscricoesAbertas() ? 'Inscrições abertas' : 'Inscrições encerradas';
    }

    /** Cor da etiqueta, seguindo as classes do template do painel. */
    public function corDaSituacao(): string
    {
        return match ($this->situacao()) {
            'Inscrições abertas' => 'success',
            'Inscrições encerradas' => 'warning',
            'Realizado' => 'secondary',
            default => 'danger',
        };
    }
}