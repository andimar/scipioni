<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'users' => User::query()->count(),
            'events' => Event::query()->count(),
            'published_events' => Event::query()->where('status', 'published')->count(),
            'bookings' => Booking::query()->count(),
        ];

        $recentEvents = Event::query()
            ->with('category')
            ->withCount('bookings')
            ->latest('starts_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentEvents' => $recentEvents,
        ]);
    }
}
