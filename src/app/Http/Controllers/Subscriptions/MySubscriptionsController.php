<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Payment;
use App\Models\Event;
use App\Models\EventKit;
use App\Models\EventModality;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;

class MySubscriptionsController extends Controller
{
     public function myRegistrations()
    {
        $user = auth()->user();

        $registrations = Registration::with(['event'])
            ->where('user_id', $user->id)
            ->get();
        return view('registrations.my', compact('registrations'));
    }
}