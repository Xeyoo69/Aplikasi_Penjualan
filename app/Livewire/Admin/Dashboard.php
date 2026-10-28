<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);
    }

    public function render(ReportService $reports)
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $revenueToday = (int) Sale::query()
            ->completed()
            ->whereDate('created_at', $today)
            ->sum('total');

        $revenueMonth = (int) Sale::query()
            ->completed()
            ->whereBetween('created_at', [$monthStart.' 00:00:00', $today.' 23:59:59'])
            ->sum('total');

        $transactionsToday = Sale::query()
            ->completed()
            ->whereDate('created_at', $today)
            ->count();

        $transactionsMonth = Sale::query()
            ->completed()
            ->whereBetween('created_at', [$monthStart.' 00:00:00', $today.' 23:59:59'])
            ->count();

        return view('livewire.admin.dashboard', [
            'revenueToday' => $revenueToday,
            'revenueMonth' => $revenueMonth,
            'transactionsToday' => $transactionsToday,
            'transactionsMonth' => $transactionsMonth,
            'bestSellers' => $reports->bestSellers($monthStart, $today, 5),
            'lowStockProducts' => Product::query()
                ->active()
                ->with('category:id,name')
                ->lowStock()
                ->orderBy('stock')
                ->limit(6)
                ->get(),
            'recentSales' => Sale::query()
                ->with(['user:id,name', 'customer:id,name'])
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }
}
