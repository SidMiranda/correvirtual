<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organizer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'cnpj',
        'email',
        'slug',
        'active',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    /**
     * O endereço do site público deste organizador.
     *
     * Não dá para usar `url('/')` dentro do painel: em
     * admin.correvirtual.com.br isso devolve a raiz do próprio domínio do
     * painel, que o nginx redireciona de volta para /admin — o link "ver o
     * site" acabava no lugar de onde saiu. O site de cada organizador mora no
     * domínio dele (ADR 0002), então é de lá que a URL sai.
     */
    public function siteUrl(): string
    {
        // Em desenvolvimento tudo mora no mesmo host (localhost) e o domínio
        // gravado no banco é o de PRODUÇÃO — mandar quem está testando para lá
        // é convite a mexer no site errado achando que é o local.
        if (app()->environment('local')) {
            return url('/');
        }

        return "https://{$this->domain}";
    }
}
