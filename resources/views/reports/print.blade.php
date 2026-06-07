@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $max = max(1, collect($series)->flatMap(fn ($s) => [$s['income'], $s['expense']])->max() ?: 1);
    $td = fn ($d) => $d ? \Carbon\CarbonImmutable::parse($d)->translatedFormat('d M Y') : '-';
    $kos = $kos ?? [];
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan {{ $from->toDateString() }} – {{ $to->toDateString() }}</title>
    <style>
        :root { --green:#16a34a; --green-deep:#15803d; --ink:#0f172a; --muted:#64748b; --line:#e2e8f0; --soft:#f8fafc; }
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", system-ui, sans-serif; color: var(--ink); margin: 0; padding: 36px 40px; font-size: 13px; line-height: 1.5; background:#fff; }

        /* Header */
        .head { display: flex; align-items: center; gap: 16px; border-bottom: 3px solid var(--green); padding-bottom: 18px; }
        .head img { width: 54px; height: 54px; object-fit: contain; }
        .head .title { flex: 1; }
        .head h1 { font-size: 20px; margin: 0; color: var(--green-deep); letter-spacing: -.01em; }
        .head .sub { color: var(--muted); font-size: 12.5px; margin-top: 2px; }
        .head .meta { text-align: right; font-size: 12px; color: var(--muted); }
        .head .meta b { color: var(--ink); display:block; font-size: 13px; }

        /* Summary cards */
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 22px 0; }
        .card { border: 1px solid var(--line); border-radius: 12px; padding: 14px 16px; border-left: 4px solid var(--green); }
        .card.exp { border-left-color: #ef4444; }
        .card.profit { border-left-color: #0ea5e9; }
        .card.due { border-left-color: #f59e0b; }
        .card .lbl { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
        .card .val { font-size: 18px; font-weight: 800; margin-top: 4px; }
        .val.green { color: var(--green-deep); } .val.red { color: #dc2626; }

        /* Occupancy strip */
        .strip { display:flex; gap:24px; flex-wrap:wrap; background:var(--soft); border:1px solid var(--line); border-radius:12px; padding:14px 18px; margin-bottom: 8px; }
        .strip div { font-size:12px; color:var(--muted); }
        .strip b { display:block; font-size:16px; color:var(--ink); font-weight:700; }

        /* Sections */
        h2 { font-size: 14px; margin: 28px 0 10px; color: var(--green-deep); display:flex; align-items:center; gap:8px; }
        h2::before { content:""; width:5px; height:16px; background:var(--green); border-radius:3px; display:inline-block; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        thead th { background: var(--green); color:#fff; text-align: left; padding: 9px 11px; font-size: 11px; text-transform: uppercase; letter-spacing:.03em; font-weight:600; }
        thead th:first-child { border-radius: 8px 0 0 0; }
        thead th:last-child { border-radius: 0 8px 0 0; }
        tbody td { padding: 8px 11px; border-bottom: 1px solid var(--line); }
        tbody tr:nth-child(even) { background: var(--soft); }
        .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .total-row td { font-weight: 800; background: #ecfdf5 !important; border-top: 2px solid var(--green); }
        .empty { color: var(--muted); font-style: italic; }
        .pill { display:inline-block; padding:2px 9px; border-radius:20px; font-size:11px; font-weight:700; background:#e2e8f0; color:#475569; }
        .pill.lunas { background:#dcfce7; color:#15803d; } .pill.telat { background:#fee2e2; color:#b91c1c; }
        .pill.aktif { background:#dcfce7; color:#15803d; }

        /* Chart */
        .bars { display: flex; align-items: flex-end; gap: 8px; height: 150px; margin-top: 12px; border:1px solid var(--line); border-radius:12px; padding:14px; }
        .bars .col { flex: 1; text-align: center; }
        .bars .pair { display: flex; gap: 3px; align-items: flex-end; height: 110px; justify-content: center; }
        .bars .bar { width: 12px; border-radius: 3px 3px 0 0; }

        .muted { color: var(--muted); font-size: 12px; }
        .legend { display:flex; gap:16px; font-size:11px; color:var(--muted); margin-top:8px; }
        .legend span { display:inline-flex; align-items:center; gap:5px; }
        .dot { width:9px; height:9px; border-radius:2px; display:inline-block; }

        .toolbar { position: fixed; top: 16px; right: 16px; }
        .btn { background: var(--green); color: #fff; border: 0; border-radius: 10px; padding: 10px 18px; font-weight: 600; font-size: 13px; cursor: pointer; box-shadow:0 6px 16px rgba(22,163,74,.3); }
        footer { margin-top: 30px; border-top: 1px solid var(--line); padding-top: 12px; color: var(--muted); font-size: 11px; display:flex; justify-content:space-between; }

        table, .bars, .strip { page-break-inside: avoid; }
        h2 { page-break-after: avoid; }
        @media print { .toolbar { display: none; } body { padding: 0; } @page { margin: 16mm 12mm; } }
    </style>
</head>
<body>
    <div class="toolbar"><button class="btn" onclick="window.print()">🖨️ Cetak / Simpan PDF</button></div>

    <div class="head">
        <img src="{{ asset('logo.png') }}" alt="logo">
        <div class="title">
            <h1>{{ $appName }} — Laporan Keuangan</h1>
            <div class="sub">
                {{ $kos['tagline'] ?? '' }}@if(!empty($kos['address_line1'])) · {{ $kos['address_line1'] }}@endif
            </div>
        </div>
        <div class="meta">
            <b>{{ $from->translatedFormat('d M Y') }} – {{ $to->translatedFormat('d M Y') }}</b>
            Dicetak {{ now()->translatedFormat('d M Y H:i') }}
        </div>
    </div>

    <div class="grid">
        <div class="card"><div class="lbl">Pendapatan</div><div class="val green">{{ $rp($summary['income']) }}</div></div>
        <div class="card exp"><div class="lbl">Pengeluaran</div><div class="val red">{{ $rp($summary['expense']) }}</div></div>
        <div class="card profit"><div class="lbl">Laba Bersih</div><div class="val">{{ $rp($summary['profit']) }}</div></div>
        <div class="card due"><div class="lbl">Piutang</div><div class="val">{{ $rp($summary['receivables']) }}</div></div>
    </div>

    <div class="strip">
        <div>Total kamar <b>{{ $summary['occupancy']['total'] }}</b></div>
        <div>Terisi <b>{{ $summary['occupancy']['occupied'] }}</b></div>
        <div>Kosong <b>{{ $summary['occupancy']['available'] }}</b></div>
        <div>Tingkat hunian <b>{{ $summary['occupancy']['rate'] }}%</b></div>
        <div>Penghuni aktif <b>{{ $summary['active_tenants'] }}</b></div>
    </div>

    <h2>Tren {{ $to->year }}</h2>
    <div class="bars">
        @foreach ($series as $s)
            <div class="col">
                <div class="pair">
                    <div class="bar" style="height: {{ (int) ($s['income'] / $max * 100) }}%; background:var(--green);" title="Pendapatan {{ $rp($s['income']) }}"></div>
                    <div class="bar" style="height: {{ (int) ($s['expense'] / $max * 100) }}%; background:#fca5a5;" title="Pengeluaran {{ $rp($s['expense']) }}"></div>
                </div>
                <div class="muted" style="font-size:10.5px;margin-top:5px">{{ $s['month'] }}</div>
            </div>
        @endforeach
    </div>
    <div class="legend"><span><i class="dot" style="background:var(--green)"></i> Pendapatan</span><span><i class="dot" style="background:#fca5a5"></i> Pengeluaran</span></div>

    <h2>Pembayaran (Kas Masuk)</h2>
    <table>
        <thead><tr><th>Tanggal</th><th>Penghuni</th><th>Kamar</th><th>No. Invoice</th><th>Metode</th><th class="num">Nominal</th></tr></thead>
        <tbody>
            @forelse ($payments as $p)
                <tr>
                    <td>{{ $td($p->paid_at) }}</td>
                    <td>{{ $p->invoice?->lease?->tenant?->name ?? '-' }}</td>
                    <td>{{ $p->invoice?->lease?->room?->room_number ?? '-' }}</td>
                    <td>{{ $p->invoice?->invoice_number ?? '-' }}</td>
                    <td>{{ ucfirst($p->method->value) }}</td>
                    <td class="num">{{ $rp($p->amount) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">Tidak ada pembayaran pada periode ini.</td></tr>
            @endforelse
            @if (count($payments))
                <tr class="total-row"><td colspan="5" class="num">TOTAL</td><td class="num">{{ $rp(collect($payments)->sum('amount')) }}</td></tr>
            @endif
        </tbody>
    </table>

    <h2>Tagihan</h2>
    <table>
        <thead><tr><th>No. Invoice</th><th>Penghuni</th><th>Kamar</th><th>Periode</th><th>Jatuh Tempo</th><th class="num">Total</th><th class="num">Sisa</th><th>Status</th></tr></thead>
        <tbody>
            @forelse ($invoices as $inv)
                <tr>
                    <td>{{ $inv->invoice_number }}</td>
                    <td>{{ $inv->lease?->tenant?->name ?? '-' }}</td>
                    <td>{{ $inv->lease?->room?->room_number ?? '-' }}</td>
                    <td>{{ $td($inv->period_start) }} – {{ $td($inv->period_end) }}</td>
                    <td>{{ $td($inv->due_date) }}</td>
                    <td class="num">{{ $rp($inv->amount) }}</td>
                    <td class="num">{{ $rp((float) $inv->amount - (float) $inv->paid_amount) }}</td>
                    <td><span class="pill {{ $inv->status->value === 'paid' ? 'lunas' : ($inv->status->value === 'overdue' ? 'telat' : '') }}">{{ $inv->status->label() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="8" class="empty">Tidak ada tagihan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Pengeluaran</h2>
    <table>
        <thead><tr><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th class="num">Nominal</th></tr></thead>
        <tbody>
            @forelse ($expenses as $e)
                <tr>
                    <td>{{ $td($e->spent_at) }}</td>
                    <td>{{ $e->category->label() }}</td>
                    <td>{{ $e->description }}</td>
                    <td class="num">{{ $rp($e->amount) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Tidak ada pengeluaran pada periode ini.</td></tr>
            @endforelse
            @if (count($expenses))
                <tr class="total-row"><td colspan="3" class="num">TOTAL</td><td class="num">{{ $rp(collect($expenses)->sum('amount')) }}</td></tr>
            @endif
        </tbody>
    </table>

    <h2>Kontrak</h2>
    <table>
        <thead><tr><th>Penghuni</th><th>Kamar</th><th>Mulai</th><th>Habis</th><th class="num">Harga/bln</th><th>Status</th></tr></thead>
        <tbody>
            @forelse ($leases as $l)
                <tr>
                    <td>{{ $l->tenant?->name ?? '-' }}</td>
                    <td>{{ $l->room?->room_number ?? '-' }}</td>
                    <td>{{ $td($l->start_date) }}</td>
                    <td>{{ $td($l->end_date) }}</td>
                    <td class="num">{{ $rp($l->monthly_price) }}</td>
                    <td><span class="pill {{ $l->status->value === 'active' ? 'aktif' : '' }}">{{ $l->status->label() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">Tidak ada kontrak pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <footer>
        <span>{{ $appName }} · Laporan dibuat otomatis oleh sistem</span>
        <span>{{ now()->translatedFormat('d M Y H:i') }}</span>
    </footer>
</body>
</html>
