<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventKit;
use App\Models\EventModality;
use App\Models\Organizer;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PixAmountResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PixAmountResolverTest extends TestCase
{
    use RefreshDatabase;

    private function createSubscriptionWithPrice(float $price): Subscription
    {
        $organizer = Organizer::create([
            'name' => 'Organizador de Teste',
            'domain' => 'localhost',
            'email' => 'tenant@example.com',
            'slug' => 'organizador-de-teste',
            'cnpj' => '00.000.000/0001-00',
        ]);

        $event = Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'Corrida de Teste',
            'slug' => 'corrida-de-teste',
            'description' => 'Descrição',
            'location' => 'Local de Teste',
            'event_date' => now()->addMonth(),
            'registration_deadline' => now()->addWeeks(2),
            'banner_url' => 'banner.jpg',
            'active' => true,
        ]);

        $modality = EventModality::factory()->create(['event_id' => $event->id]);
        $kit = EventKit::factory()->create(['event_id' => $event->id, 'price' => $price]);
        $user = User::factory()->create();

        return Subscription::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'modality_id' => $modality->id,
            'kit_id' => $kit->id,
            'price' => $price,
            'status' => 'pending',
            'bib_number' => null,
        ]);
    }

    public function test_charges_real_kit_price_when_test_mode_is_disabled(): void
    {
        config(['services.mercadopago.test_price_enabled' => false]);
        $subscription = $this->createSubscriptionWithPrice(149.90);

        $this->assertSame(149.90, PixAmountResolver::resolve($subscription));
    }

    public function test_charges_test_price_for_any_kit_when_test_mode_is_enabled(): void
    {
        config([
            'services.mercadopago.test_price_enabled' => true,
            'services.mercadopago.test_price_value' => 0.05,
        ]);
        $subscription = $this->createSubscriptionWithPrice(299.90);

        $this->assertSame(0.05, PixAmountResolver::resolve($subscription));
        // Confirma que o preço real gravado na inscrição não foi alterado pela flag.
        $this->assertSame('299.90', $subscription->fresh()->price);
    }

    public function test_test_price_value_is_configurable(): void
    {
        config([
            'services.mercadopago.test_price_enabled' => true,
            'services.mercadopago.test_price_value' => 0.10,
        ]);
        $subscription = $this->createSubscriptionWithPrice(59.90);

        $this->assertSame(0.10, PixAmountResolver::resolve($subscription));
    }
}
