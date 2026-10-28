<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SaleReceiptController extends Controller
{
    public function __invoke(Request $request, Sale $sale)
    {
        Gate::authorize('view', $sale);

        $width = (int) $request->query('width', 58);
        $width = in_array($width, [58, 80], true) ? $width : 58;

        $sale->load(['items', 'customer', 'user', 'voidedBy']);

        return response()
            ->view('sales.receipt', [
                'sale' => $sale,
                'width' => $width,
                'setting' => Setting::current(),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
