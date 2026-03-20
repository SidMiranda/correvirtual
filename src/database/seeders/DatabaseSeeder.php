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
        $organizer = Organizer::create([
            'name' => 'Corra Virtual Organizadora',
            'email' => 'contato@correvirtual.com.br',
            'slug'  => 'corra-virtual-organizadora',
        ]);

        // --- EVENTO 1: CarnaRun do Quarteto - 2025 ---
        $event1 = Event::create([
            'organizer_id' => $organizer->id,
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

        // --- EVENTO 2: 1º Corre que a bruxa vem ai - 30 de outubro as 8h ---
        $event2 = Event::create([
            'organizer_id' => $organizer->id,
            'title' => '1º Corre que a bruxa vem ai',
            'slug' => 'primeira-corre-que-a-bruxa-vem-ai',
            'description' => 'Treinão 5k, 10k e Caminhada com largada às 7h. Ação solidária: doe 1kg de alimento não perecível para ajudar famílias necessitadas. Os participantes podem ir fantasiados (não obrigatório). O evento contará com pós-treino com café comunitário e linda medalha de participação em MDF.',
            'location' => 'Saindo do Pastelícia Campo da Brahma, Mogi Guaçu, SP',
            'event_date' => '2025-10-30 08:00:00',
            'registration_deadline' => '2025-10-28 23:59:59',
            'banner_url' => 'halloween.jpg',
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
            'organizer_id' => $organizer->id,
            'title' => 'Desafio Virtual Pastelaria Pastelícia & Cia',
            'slug' => 'desafio-virtual-pastelaria-pastelicia-2025',
            'description' => "Desafio virtual onde você escolhe a sua distância: 30KM, 50KM, 70KM ou 100KM. Complete o desafio entre 01/07 e 31/07 e ganhe uma medalha de ferro. A maior distância acumulada nas categorias masculino e feminino ganhará o troféu Rei dos KM's e Rainha dos KM's. Mais informações e inscrições pelo WhatsApp: (19) 99706-1361 ou Instagram @corre_virtual.",
            'location' => 'Mogi Guaçu, SP - Virtual',
            'event_date' => '2025-07-31 23:59:59',
            'registration_deadline' => '2025-06-30 23:59:59',
            'banner_url' => 'desafio-virtual-25.jpg',
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
    }
}
