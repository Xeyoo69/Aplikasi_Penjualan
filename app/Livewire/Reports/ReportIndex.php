<?php

namespace App\Livewire\Reports;

use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Laporan')]
class ReportIndex extends Component
{
    use WithPagination;

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $activeTab = 'sales';

    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);

        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['sales', 'products', 'cashiers'], true)) {
            return;
        }

        $this->activeTab = $tab;
        $this->resetPage();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function period(): array
    {
        try {
            $from = Carbon::parse($this->dateFrom ?: now()->startOfMonth()->toDateString())->toDateString();
            $to = Carbon::parse($this->dateTo ?: now()->toDateString())->toDateString();
        } catch (\Throwable) {
            $from = now()->startOfMonth()->toDateString();
            $to = now()->toDateString();
        }

        if ($to < $from) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    public function render(ReportService $reports)
    {
        [$from, $to] = $this->period();

        $rows = match ($this->activeTab) {
            'products' => $reports->topProducts($from, $to)->paginate(15),
            'cashiers' => $reports->perCashier($from, $to)->paginate(15),
            default => $reports->salesByDay($from, $to)->paginate(15),
        };

        return view('livewire.reports.report-index', [
            'from' => $from,
            'to' => $to,
            'summary' => $reports->summary($from, $to),
            'rows' => $rows,
        ]);
    }
}
