@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $max = max(1, collect($series)->flatMap(fn ($s) => [$s['income'], $s['expense']])->max() ?: 1);
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan {{ $from->toDateString() }} – {{ $to->toDateString() }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, "Segoe UI", sans-serif; color: #0f172a; margin: 0; padding: 32px; }
        .head { display: flex; align-items: center; gap: 12px; border-bottom: 2px solid #16a34a; padding-bottom: 16px; }
        .logo { width: 44px; height: 44px; border-radius: 12px; background: #16a34a; color: #fff; font-weight: 800; font-size: 22px; display: grid; place-items: center; }
        h1 { font-size: 18px; margin: 0; }
        .muted { color: #64748b; font-size: 13px; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 20px 0; }
        .card { border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; }
        .card .lbl { font-size: 12px; color: #64748b; }
        .card .val { font-size: 18px; font-weight: 700; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 13px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase; }
        h2 { font-size: 14px; margin: 24px 0 6px; }
        .bars { display: flex; align-items: flex-end; gap: 10px; height: 160px; margin-top: 10px; }
        .bars .col { flex: 1; text-align: center; }
        .bars .pair { display: flex; gap: 3px; align-items: flex-end; height: 130px; justify-content: center; }
        .bars .bar { width: 14px; border-radius: 3px 3px 0 0; }
        .green { color: #059669; } .red { color: #dc2626; }
        .toolbar { position: fixed; top: 16px; right: 16px; }
        .btn { background: #16a34a; color: #fff; border: 0; border-radius: 10px; padding: 10px 18px; font-weight: 600; font-size: 13px; cursor: pointer; }
        @media print { .toolbar { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="toolbar"><button class="btn" onclick="window.print()">🖨️ Cetak / Simpan PDF</button></div>

    <div class="head">
        <div class="logo">C</div>
        <div>
            <h1>{{ $appName }} — Laporan Keuangan</h1>
            <div class="muted">Periode {{ $from->translatedFormat('d M Y') }} – {{ $to->translatedFormat('d M Y') }}</div>
        </div>
    </div>

    <div class="grid">
        <div class="card"><div class="lbl">Pendapatan</div><div class="val green">{{ $rp($summary['income']) }}</div></div>
        <div class="card"><div class="lbl">Pengeluaran</div><div class="val red">{{ $rp($summary['expense']) }}</div></div>
        <div class="card"><div class="lbl">Laba bersih</div><div class="val">{{ $rp($summary['profit']) }}</div></div>
        <div class="card"><div class="lbl">Piutang</div><div class="val">{{ $rp($summary['receivables']) }}</div></div>
    </div>

    <h2>Hunian</h2>
    <table>
        <tr><th>Total kamar</th><th>Terisi</th><th>Kosong</th><th>Tingkat hunian</th><th>Penghuni aktif</th></tr>
        <tr>
            <td>{{ $summary['occupancy']['total'] }}</td>
            <td>{{ $summary['occupancy']['occupied'] }}</td>
            <td>{{ $summary['occupancy']['available'] }}</td>
            <td>{{ $summary['occupancy']['rate'] }}%</td>
            <td>{{ $summary['active_tenants'] }}</td>
        </tr>
    </table>

    <h2>Tren Tahunan ({{ $to->year }})</h2>
    <div class="bars">
        @foreach ($series as $s)
            <div class="col">
                <div class="pair">
                    <div class="bar" style="height: {{ (int) ($s['income'] / $max * 100) }}%; background:#16a34a;" title="Pendapatan {{ $rp($s['income']) }}"></div>
                    <div class="bar" style="height: {{ (int) ($s['expense'] / $max * 100) }}%; background:#fca5a5;" title="Pengeluaran {{ $rp($s['expense']) }}"></div>
                </div>
                <div class="muted" style="font-size:11px;margin-top:4px">{{ $s['month'] }}</div>
            </div>
        @endforeach
    </div>

    @php $td = fn ($d) => $d ? \Carbon\CarbonImmutable::parse($d)->translatedFormat('d M Y') : '-'; @endphp

    <h2>Pembayaran (Kas Masuk)</h2>
    <table>
        <tr><th>Tanggal</th><th>Penghuni</th><th>Kamar</th><th>No. Invoice</th><th>Metode</th><th style="text-align:right">Nominal</th></tr>
        @forelse ($payments as $p)
            <tr>
                <td>{{ $td($p->paid_at) }}</td>
                <td>{{ $p->invoice?->lease?->tenant?->name ?? '-' }}</td>
                <td>{{ $p->invoice?->lease?->room?->room_number ?? '-' }}</td>
                <td>{{ $p->invoice?->invoice_number ?? '-' }}</td>
                <td>{{ $p->method->value }}</td>
                <td style="text-align:right">{{ $rp($p->amount) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">Tidak ada pembayaran.</td></tr>
        @endforelse
        @if (count($payments))
            <tr><td colspan="5" style="text-align:right;font-weight:700">TOTAL</td><td style="text-align:right;font-weight:700">{{ $rp(collect($payments)->sum('amount')) }}</td></tr>
        @endif
    </table>

    <h2>Tagihan</h2>
    <table>
        <tr><th>No. Invoice</th><th>Penghuni</th><th>Kamar</th><th>Periode</th><th>Jatuh Tempo</th><th style="text-align:right">Total</th><th style="text-align:right">Sisa</th><th>Status</th></tr>
        @forelse ($invoices as $inv)
            <tr>
                <td>{{ $inv->invoice_number }}</td>
                <td>{{ $inv->lease?->tenant?->name ?? '-' }}</td>
                <td>{{ $inv->lease?->room?->room_number ?? '-' }}</td>
                <td>{{ $td($inv->period_start) }} – {{ $td($inv->period_end) }}</td>
                <td>{{ $td($inv->due_date) }}</td>
                <td style="text-align:right">{{ $rp($inv->amount) }}</td>
                <td style="text-align:right">{{ $rp((float) $inv->amount - (float) $inv->paid_amount) }}</td>
                <td>{{ $inv->status->label() }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted">Tidak ada tagihan.</td></tr>
        @endforelse
    </table>

    <h2>Pengeluaran</h2>
    <table>
        <tr><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th style="text-align:right">Nominal</th></tr>
        @forelse ($expenses as $e)
            <tr>
                <td>{{ $td($e->spent_at) }}</td>
                <td>{{ $e->category->label() }}</td>
                <td>{{ $e->description }}</td>
                <td style="text-align:right">{{ $rp($e->amount) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">Tidak ada pengeluaran.</td></tr>
        @endforelse
        @if (count($expenses))
            <tr><td colspan="3" style="text-align:right;font-weight:700">TOTAL</td><td style="text-align:right;font-weight:700">{{ $rp(collect($expenses)->sum('amount')) }}</td></tr>
        @endif
    </table>

    <h2>Kontrak</h2>
    <table>
        <tr><th>Penghuni</th><th>Kamar</th><th>Mulai</th><th>Habis</th><th style="text-align:right">Harga/bln</th><th>Status</th></tr>
        @forelse ($leases as $l)
            <tr>
                <td>{{ $l->tenant?->name ?? '-' }}</td>
                <td>{{ $l->room?->room_number ?? '-' }}</td>
                <td>{{ $td($l->start_date) }}</td>
                <td>{{ $td($l->end_date) }}</td>
                <td style="text-align:right">{{ $rp($l->monthly_price) }}</td>
                <td>{{ $l->status->label() }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">Tidak ada kontrak.</td></tr>
        @endforelse
    </table>

    <p class="muted" style="margin-top:28px">Dicetak {{ now()->translatedFormat('d M Y H:i') }} · {{ $appName }}</p>
</body>
</html>
