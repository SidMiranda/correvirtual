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

        // --- EVENTO 1: CarnaRun ---
        $event1 = Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'CarnaRun do Quarteto 2026',
            'slug' => 'carnarun-2026',
            'description' => 'O maior evento de carnaval correndo do Brasil!',
            'location' => 'Parque Ibirapuera - SP',
            'event_date' => '2026-03-20 07:00:00',
            'registration_deadline' => '2026-03-18 23:59:59',
            'banner_url' => 'https://placehold.co/1200x400/orange/white?text=CarnaRun+2026',
            'active' => true,
        ]);
        // Kits e Modalidades do Evento 1
        $this->createCommonKitsAndModalities($event1);

        // --- EVENTO 2: Corrida da Independência ---
        $event2 = Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'Corrida da Independência',
            'slug' => 'independencia-2026',
            'description' => 'Celebre o 7 de setembro correndo!',
            'location' => 'Aterro do Flamengo - RJ',
            'event_date' => '2026-09-07 08:00:00',
            'registration_deadline' => '2026-09-05 23:59:59',
            'banner_url' => 'https://placehold.co/1200x400/green/yellow?text=Independencia',
            'active' => true,
        ]);
        $this->createCommonKitsAndModalities($event2);

        // --- EVENTO 3: São Silvestre Virtual ---
        $event3 = Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'São Silvestre Virtual',
            'slug' => 'sao-silvestre-2026',
            'description' => 'Feche o ano com chave de ouro onde estiver.',
            'location' => 'Qualquer lugar (Virtual)',
            'event_date' => '2026-12-31 17:00:00',
            'registration_deadline' => '2026-12-30 12:00:00',
            'banner_url' => 'https://placehold.co/1200x400/1971b1/white?text=Sao+Silvestre',
            'active' => true,
        ]);
        $this->createCommonKitsAndModalities($event3);
    }

    /**
     * Função auxiliar para criar kits e modalidades padrão para um evento
     */
    private function createCommonKitsAndModalities(Event $event)
    {
        // Modalidades
        EventModality::factory()->create(['event_id' => $event->id, 'name' => '5km', 'distance_km' => 5]);
        EventModality::factory()->create(['event_id' => $event->id, 'name' => '10km', 'distance_km' => 10]);
        EventModality::factory()->create(['event_id' => $event->id, 'name' => 'Caminhada', 'distance_km' => 3]);

        // Kits
        EventKit::factory()->create([
            'event_id' => $event->id, 
            'name' => 'Kit Gold', 
            'price' => 89.90,
            'description' => 'Camiseta + Medalha + Sacochila'
        ]);
        EventKit::factory()->create([
            'event_id' => $event->id, 
            'name' => 'Kit Eco', 
            'price' => 49.90,
            'description' => 'Medalha + Número de Peito'
        ]);
    }
}
