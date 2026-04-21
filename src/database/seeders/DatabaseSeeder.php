<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventKit;
use App\Models\EventModality;
use App\Models\Organizer;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criar um Organizador Padrão
        $organizer1 = Organizer::create([
            'name' => 'Corre Virtual Eventos',
            'domain' => 'eventos.correvirtual.com.br',
            'email' => 'falecom@correvirtual.com.br',
            'slug'  => 'corre-virtual-organizadora',
            'cnpj' => '12.345.678/0001-90',
        ]);

        $organizer2 = Organizer::create([
            'name' => 'Borafitness Eventos',
            'domain' => 'borafitness.mobspot.com.br',
            'email' => 'falecom@borafitness.com.br',
            'slug'  => 'borafitness-organizadora',
            'cnpj' => '98.765.432/0001-09',
        ]);

        // --- EVENTO 1: CarnaRun do Quarteto - 2025 ---
        $event1 = Event::create([
            'organizer_id' => $organizer1->id,
            'title' => 'CarnaRun do Quarteto - 2025',
            'slug' => 'carnarun-do-quarteto-2025',
            'description' => 'Treinão 5k, 10k e Caminhada. Ação solidária: doe 1kg de alimento não perecível para ajudar famílias necessitadas. Os participantes podem ir fantasiados (não obrigatório). O evento contará com pós-treino com café comunitário e linda medalha de participação em MDF.',
            'location' => 'Pastelícia Campo da Brahma, Mogi Guaçu, SP',
            'event_date' => '2025-03-02 07:00:00',
            'registration_deadline' => '2025-02-28 23:59:59',
            'banner_url' => 'carnarun-2025.jpg',
            'active' => true,
            'created_at' => '2024-10-24 10:00:00',
            'updated_at' => '2024-10-24 10:00:00',
        ]);

        EventModality::factory()->create(['event_id' => $event1->id, 'name' => '5km', 'distance_km' => 5]);
        EventModality::factory()->create(['event_id' => $event1->id, 'name' => '10km', 'distance_km' => 10]);
        EventModality::factory()->create(['event_id' => $event1->id, 'name' => 'Caminhada', 'distance_km' => 3]);

        EventKit::factory()->create([
            'event_id' => $event1->id,
            'name' => 'Kit Treinão',
            'price' => 39.90,
            'description' => 'Medalha em MDF + Café Comunitário pós-treino'
        ]);

        EventKit::factory()->create([
            'event_id' => $event1->id,
            'name' => 'Kit Bebaço',
            'price' => 59.90,
            'description' => 'Medalha em MDF + Caneca de Chopp + Café Comunitário pós-treino'
        ]);

        EventKit::factory()->create([
            'event_id' => $event1->id,
            'name' => 'Kit Nóis Capota Mas Não Breca',
            'price' => 79.90,
            'description' => 'Medalha em MDF + Camiseta Exclusiva + Café Comunitário pós-treino'
        ]);

        // --- EVENTO 2: 1º Corre que a bruxa vem ai - 30 de outubro as 8h ---
        $event2 = Event::create([
            'organizer_id' => $organizer1->id,
            'title' => '1º Corre que a bruxa vem ai',
            'slug' => 'primeira-corre-que-a-bruxa-vem-ai',
            'description' => 'Treinão 5k, 10k e Caminhada com largada às 7h. Ação solidária: doe 1kg de alimento não perecível para ajudar famílias necessitadas. Os participantes podem ir fantasiados (não obrigatório). O evento contará com pós-treino com café comunitário e linda medalha de participação em MDF.',
            'location' => 'Saindo do Pastelícia Campo da Brahma, Mogi Guaçu, SP',
            'event_date' => '2025-10-30 08:00:00',
            'registration_deadline' => '2025-10-28 23:59:59',
            'banner_url' => 'halloween-2025.jpg',
            'active' => true,
        ]);

        EventModality::factory()->create(['event_id' => $event2->id, 'name' => '5km', 'distance_km' => 5]);
        EventModality::factory()->create(['event_id' => $event2->id, 'name' => '10km', 'distance_km' => 10]);
        EventModality::factory()->create(['event_id' => $event2->id, 'name' => 'Caminhada', 'distance_km' => 3]);

        EventKit::factory()->create([
            'event_id' => $event2->id,
            'name' => 'Kit Treinão Solidário',
            'price' => 39.90,
            'description' => 'Medalha de participação em MDF + Café comunitário'
        ]);

        // --- EVENTO 3: Desafio Virtual Pastelaria Pastelícia & Cia ---
        $event3 = Event::create([
            'organizer_id' => $organizer1->id,
            'title' => 'Desafio Virtual Pastelaria Pastelícia & Cia',
            'slug' => 'desafio-virtual-pastelaria-pastelicia-2025',
            'description' => "Desafio virtual onde você escolhe a sua distância: 30KM, 50KM, 70KM ou 100KM. Complete o desafio entre 01/07 e 31/07 e ganhe uma medalha de ferro. A maior distância acumulada nas categorias masculino e feminino ganhará o troféu Rei dos KM's e Rainha dos KM's. Mais informações e inscrições pelo WhatsApp: (19) 99706-1361 ou Instagram @corre_virtual.",
            'location' => 'Mogi Guaçu, SP - Virtual',
            'event_date' => '2025-07-31 23:59:59',
            'registration_deadline' => '2025-06-30 23:59:59',
            'banner_url' => 'desafio-virtual-2025.jpg',
            'active' => true,
        ]);

        EventModality::factory()->create(['event_id' => $event3->id, 'name' => '30KM', 'distance_km' => 30]);
        EventModality::factory()->create(['event_id' => $event3->id, 'name' => '50KM', 'distance_km' => 50]);
        EventModality::factory()->create(['event_id' => $event3->id, 'name' => '70KM', 'distance_km' => 70]);
        EventModality::factory()->create(['event_id' => $event3->id, 'name' => '100KM', 'distance_km' => 100]);

        EventKit::factory()->create([
            'event_id' => $event3->id,
            'name' => 'Kit Desafio Básico',
            'price' => 49.90,
            'description' => 'Medalha de Ferro'
        ]);

        // ==============================================================
        // EVENTOS DO ORGANIZADOR 2 (Borafitness - Eventos Reais de SP)
        // ==============================================================

        // --- EVENTO 4: São Silvestre ---
        $event4 = Event::create([
            'organizer_id' => $organizer2->id,
            'title' => '98ª Corrida Internacional de São Silvestre',
            'slug' => '98-corrida-internacional-de-sao-silvestre',
            'description' => 'A mais tradicional corrida de rua do Brasil, fechando o ano nas ruas de São Paulo. Tradicional percurso passando pelos principais pontos turísticos da cidade.',
            'location' => 'Avenida Paulista, São Paulo, SP',
            'event_date' => '2023-12-31 07:25:00',
            'registration_deadline' => '2023-11-30 23:59:59',
            'banner_url' => 'sao-silvestre-2023.jpg',
            'active' => true,
        ]);

        EventModality::factory()->create(['event_id' => $event4->id, 'name' => '15KM', 'distance_km' => 15]);

        EventKit::factory()->create([
            'event_id' => $event4->id,
            'name' => 'Kit Geral',
            'price' => 240.00,
            'description' => 'Camiseta poliamida, número de peito, chip de cronometragem e medalha (pós-prova)'
        ]);

        // --- EVENTO 5: Maratona Internacional de São Paulo ---
        $event5 = Event::create([
            'organizer_id' => $organizer2->id,
            'title' => '28ª Maratona Internacional de São Paulo',
            'slug' => '28-maratona-internacional-de-sao-paulo',
            'description' => 'Uma das maiores e mais importantes maratonas do país, reunindo milhares de corredores no coração de São Paulo.',
            'location' => 'Parque Ibirapuera, São Paulo, SP',
            'event_date' => '2024-04-07 06:10:00',
            'registration_deadline' => '2024-03-31 23:59:59',
            'banner_url' => 'maratona-sp-2024.jpg',
            'active' => true,
        ]);

        EventModality::factory()->create(['event_id' => $event5->id, 'name' => '5KM', 'distance_km' => 5]);
        EventModality::factory()->create(['event_id' => $event5->id, 'name' => '10KM', 'distance_km' => 10]);
        EventModality::factory()->create(['event_id' => $event5->id, 'name' => '21KM (Meia)', 'distance_km' => 21]);
        EventModality::factory()->create(['event_id' => $event5->id, 'name' => '42KM (Maratona)', 'distance_km' => 42]);

        EventKit::factory()->create([
            'event_id' => $event5->id,
            'name' => 'Kit Participação',
            'price' => 149.90,
            'description' => 'Número de peito, chip, sacochila e medalha'
        ]);
        EventKit::factory()->create([
            'event_id' => $event5->id,
            'name' => 'Kit Premium',
            'price' => 299.90,
            'description' => 'Camiseta exclusiva, jaqueta corta-vento, número de peito, chip, sacochila e medalha'
        ]);

        // --- EVENTO 6: Night Run - Etapa São Paulo ---
        $event6 = Event::create([
            'organizer_id' => $organizer2->id,
            'title' => 'Night Run - Etapa Fogo',
            'slug' => 'night-run-etapa-fogo-sp',
            'description' => 'O maior circuito de corridas noturnas do mundo! Muita música, luzes e suor na clássica praça Charles Miller.',
            'location' => 'Praça Charles Miller (Pacaembu), São Paulo, SP',
            'event_date' => '2023-05-06 19:00:00',
            'registration_deadline' => '2023-04-30 23:59:59',
            'banner_url' => 'night-run-sp-2023.jpg',
            'active' => true,
        ]);

        EventModality::factory()->create(['event_id' => $event6->id, 'name' => '5KM', 'distance_km' => 5]);
        EventModality::factory()->create(['event_id' => $event6->id, 'name' => '10KM', 'distance_km' => 10]);

        EventKit::factory()->create([
            'event_id' => $event6->id,
            'name' => 'Kit Básico',
            'price' => 119.90,
            'description' => 'Camiseta manga longa, número de peito, chip, led luminoso e medalha'
        ]);
    }
}
