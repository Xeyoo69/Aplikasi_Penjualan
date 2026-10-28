<?php

namespace App\Livewire\Sales;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Riwayat Transaksi')]
class SaleIndex extends Component
{
    use WithPagination;

    public string $dateFrom = '';

    public string $dateTo = '';

    public $cashierId = '';

    public string $paymentFilter = '';

    public string $statusFilter = '';

    public string $search = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Sale::class);

        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['dateFrom', 'dateTo', 'cashierId', 'paymentFilter', 'statusFilter', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
        $this->cashierId = '';
        $this->paymentFilter = '';
        $this->statusFilter = '';
        $this->search = '';
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

    public function render()
    {
        $user = auth()->user();
        [$from, $to] = $this->period();

        $base = Sale::query()
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->when($user->isAdmin() && $this->cashierId !== '', fn ($query) => $query->where('user_id', $this->cashierId))
            ->when($this->paymentFilter !== '', fn ($query) => $query->where('payment_method', $this->paymentFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->search !== '', fn ($query) => $query->where('invoice_no', 'like', '%'.$this->search.'%'));

        $summary = (clone $base)
            ->where('status', SaleStatus::Completed->value)
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(total),0) as total')
            ->first();

        $sales = (clone $base)
            ->with(['user:id,name', 'customer:id,name'])
            ->withCount('items')
            ->latest()
            ->paginate(15);

        return view('livewire.sales.sale-index', [
            'sales' => $sales,
            'from' => $from,
            'to' => $to,
            'summaryCount' => (int) ($summary->transactions ?? 0),
            'summaryTotal' => (int) ($summary->total ?? 0),
            'cashiers' => $user->isAdmin()
                ? User::query()->orderBy('name')->get(['id', 'name'])
                : collect(),
            'paymentMethods' => PaymentMethod::cases(),
            'statuses' => SaleStatus::cases(),
        ]);
    }
}
