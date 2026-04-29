<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $now = now();

        return response()->json([
            'metrics' => [
                [
                    'label' => 'Eventi pubblicati',
                    'value' => Event::query()->where('status', 'published')->count(),
                    'detail' => 'Eventi visibili e pronti a ricevere prenotazioni.',
                    'icon' => 'i-lucide-calendar-check-2',
                ],
                [
                    'label' => 'Eventi in arrivo',
                    'value' => Event::query()
                        ->where('starts_at', '>=', $now)
                        ->count(),
                    'detail' => 'Programmazione futura gia presente nel catalogo staff.',
                    'icon' => 'i-lucide-calendar-range',
                ],
                [
                    'label' => 'Prenotazioni confermate',
                    'value' => Booking::query()->where('status', 'confirmed')->count(),
                    'detail' => 'Richieste gia convertite in posti assegnati.',
                    'icon' => 'i-lucide-ticket-check',
                ],
                [
                    'label' => 'In approvazione',
                    'value' => Booking::query()->where('status', 'requested')->count(),
                    'detail' => 'Coda operativa per conferma manuale staff.',
                    'icon' => 'i-lucide-hourglass',
                ],
            ],
        ]);
    }
}
