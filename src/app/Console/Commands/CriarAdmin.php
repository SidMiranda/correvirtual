<?php

namespace App\Console\Commands;

use App\Models\Organizer;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Cria (ou promove) o administrador de um organizador.
 *
 * Existe porque não há tela de cadastro de admin nem papel `super_admin`
 * implementado — o primeiro acesso ao painel tem que nascer por fora.
 * Ver docs/specs/painel-admin.md e docs/runbook.md.
 */
class CriarAdmin extends Command
{
    protected $signature = 'admin:criar
                            {email : E-mail do administrador}
                            {--organizador= : ID ou slug do organizador (pergunta se houver mais de um)}
                            {--nome= : Nome, se o usuário ainda não existir}
                            {--senha= : Senha, se o usuário ainda não existir (pergunta se omitida)}';

    protected $description = 'Cria um administrador de organizador, ou promove um usuário existente a administrador';

    public function handle(): int
    {
        $email = $this->argument('email');

        $organizer = $this->resolverOrganizador();
        if (!$organizer) {
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            // Promover um atleta já existente é o caso comum: o organizador
            // costuma já ter se cadastrado pelo site antes de pedir acesso.
            $this->warn("Usuário {$email} já existe (papel atual: {$user->role}).");

            if (!$this->confirm("Promover a administrador de \"{$organizer->name}\"?", true)) {
                $this->info('Nada foi alterado.');
                return self::SUCCESS;
            }

            $user->role = 'organizer_admin';
            $user->organizer_id = $organizer->id;
            $user->active = true;

            // Sem e-mail confirmado o login barra a entrada (LoginController).
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
                $this->line('E-mail marcado como confirmado (era necessário para o login).');
            }

            $user->save();

            $this->info("Pronto: {$email} agora administra \"{$organizer->name}\".");
            return self::SUCCESS;
        }

        $nome = $this->option('nome') ?: $this->ask('Nome do administrador');
        $senha = $this->option('senha') ?: $this->secret('Senha (mínimo 8 caracteres)');

        if (strlen((string) $senha) < 8) {
            $this->error('A senha precisa ter pelo menos 8 caracteres.');
            return self::FAILURE;
        }

        $novo = new User([
            'name' => $nome,
            'email' => $email,
            'password' => Hash::make($senha),
            'role' => 'organizer_admin',
            'organizer_id' => $organizer->id,
            'active' => true,
        ]);

        // Fora do array acima de propósito: `email_verified_at` não está no
        // $fillable do User, então mass assignment descarta o valor em silêncio —
        // e o admin nasceria sem e-mail confirmado, travado no login.
        $novo->email_verified_at = now();
        $novo->save();

        $this->info("Administrador {$email} criado para \"{$organizer->name}\".");
        $this->line('Acesse o painel em /admin.');

        return self::SUCCESS;
    }

    private function resolverOrganizador(): ?Organizer
    {
        $informado = $this->option('organizador');

        if ($informado) {
            $organizer = Organizer::where('id', $informado)
                ->orWhere('slug', $informado)
                ->first();

            if (!$organizer) {
                $this->error("Organizador \"{$informado}\" não encontrado (busquei por ID e por slug).");
            }

            return $organizer;
        }

        $organizers = Organizer::orderBy('id')->get();

        if ($organizers->isEmpty()) {
            $this->error('Não há nenhum organizador cadastrado. Crie um antes (ver docs/runbook.md).');
            return null;
        }

        if ($organizers->count() === 1) {
            return $organizers->first();
        }

        $escolha = $this->choice(
            'Para qual organizador?',
            $organizers->mapWithKeys(fn ($o) => [$o->id => "{$o->name} ({$o->domain})"])->all()
        );

        // O choice devolve o rótulo; recupera o ID pela chave correspondente.
        return $organizers->first(fn ($o) => "{$o->name} ({$o->domain})" === $escolha)
            ?? $organizers->find($escolha);
    }
}
