<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $sale->invoice_no }}</title>
    <style>
        :root {
            --paper: {{ $width }}mm;
            --font: {{ $width === 80 ? 12 : 10 }}px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #e2e8f0;
            color: #0f172a;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: var(--font);
            line-height: 1.45;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            background: #0f172a;
            color: #fff;
        }

        .toolbar button,
        .toolbar a {
            border: 0;
            border-radius: 8px;
            padding: 8px 12px;
            background: #10b981;
            color: #fff;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar .secondary { background: #334155; }

        .receipt {
            width: var(--paper);
            margin: 18px auto;
            padding: 3mm 2.5mm;
            background: #fff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .18);
        }

        .center { text-align: center; }
        .bold { font-weight: 700; }
        .muted { color: #475569; }
        .store-name { font-size: 1.25em; font-weight: 800; letter-spacing: .03em; }
        .divider { border-top: 1px dashed #94a3b8; margin: 2mm 0; }
        .row { display: flex; justify-content: space-between; gap: 4mm; }
        .row span:last-child { text-align: right; }

        .items { width: 100%; border-collapse: collapse; }
        .items td { padding: .6mm 0; vertical-align: top; }
        .items .qty-line { color: #475569; }

        .total-row { font-size: 1.1em; font-weight: 800; }
        .footer { margin-top: 2mm; font-size: .92em; color: #475569; text-align: center; }

        .void {
            margin: 2mm 0;
            border: 1px solid #be123c;
            color: #be123c;
            font-weight: 800;
            text-align: center;
            padding: 1.5mm;
            letter-spacing: .12em;
        }

        @page {
            size: {{ $width }}mm auto;
            margin: 0;
        }

        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .receipt { margin: 0; box-shadow: none; width: var(--paper); padding: 2.5mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Cetak Struk</button>
        <a class="secondary" href="javascript:window.close()">Tutup</a>
        <span style="font-size: 11px;">Lebar {{ $width }} mm</span>
    </div>

    <main class="receipt">
        <div class="center">
            <div class="store-name">{{ $setting->store_name }}</div>
            @if ($setting->address)
                <div class="muted">{{ $setting->address }}</div>
            @endif
            @if ($setting->phone)
                <div class="muted">Telp. {{ $setting->phone }}</div>
            @endif
        </div>

        <div class="divider"></div>

        @if ($sale->isVoid())
            <div class="void">TRANSAKSI VOID</div>
            <div class="muted center" style="margin-bottom: 2mm;">
                {{ $sale->void_reason }}
            </div>
        @endif

        <div class="row"><span>No. Invoice</span><span class="bold">{{ $sale->invoice_no }}</span></div>
        <div class="row"><span>Tanggal</span><span>{{ tanggal_indonesia($sale->created_at, true) }}</span></div>
        <div class="row"><span>Kasir</span><span>{{ $sale->user->name }}</span></div>
        <div class="row"><span>Pelanggan</span><span>{{ $sale->customer->name ?? 'Umum' }}</span></div>

        <div class="divider"></div>

        <table class="items">
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td colspan="2" class="bold">{{ $item->product_name }}</td>
                    </tr>
                    <tr>
                        <td class="qty-line">{{ $item->qty }} x {{ rupiah($item->unit_price) }}</td>
                        <td class="qty-line" style="text-align: right;">{{ rupiah($item->subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <div class="row"><span>Subtotal</span><span>{{ rupiah($sale->subtotal) }}</span></div>
        @if ($sale->discount_amount > 0)
            <div class="row"><span>Diskon</span><span>- {{ rupiah($sale->discount_amount) }}</span></div>
        @endif
        @if ($sale->tax_amount > 0)
            <div class="row"><span>Pajak ({{ rtrim(rtrim(number_format((float) $sale->tax_percent, 2, ',', '.'), '0'), ',') }}%)</span><span>{{ rupiah($sale->tax_amount) }}</span></div>
        @endif

        <div class="divider"></div>

        <div class="row total-row"><span>TOTAL</span><span>{{ rupiah($sale->total) }}</span></div>
        <div class="row"><span>{{ $sale->payment_method->label() }}</span><span>{{ rupiah($sale->paid_amount) }}</span></div>
        <div class="row"><span>Kembalian</span><span>{{ rupiah($sale->change_amount) }}</span></div>

        <div class="divider"></div>

        @if ($setting->receipt_footer)
            <div class="footer">{{ $setting->receipt_footer }}</div>
        @endif
        <div class="footer">Dicetak {{ tanggal_indonesia(now(), true) }}</div>
    </main>
</body>
</html>
