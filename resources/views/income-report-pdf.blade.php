<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemasukan - Sipelem Futsal</title>
    <style>
        @page {
            margin: 1.5cm;
            size: A4;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #222;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .header {
            background: #1a237e;
            color: #fff;
            padding: 18px 24px 12px 24px;
            border-bottom: 3px solid #1565c0;
            text-align: center;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 4px 0;
            letter-spacing: 1px;
        }
        .header .subtitle {
            font-size: 12px;
            margin-bottom: 2px;
        }
        .header .company {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .header .date {
            font-size: 10px;
            color: #bbdefb;
        }
        .company-info, .info-section, .summary-section, .table-container, .daily-summary {
            background: #fff;
            border: 1px solid #bdbdbd;
            border-radius: 6px;
            margin-bottom: 16px;
            padding: 14px 18px;
        }
        .company-info {
            margin-top: 18px;
        }
        .company-info-table {
            width: 100%;
            font-size: 10px;
        }
        .company-info-table td {
            padding: 2px 6px;
        }
        .info-section {
            margin-bottom: 12px;
        }
        .info-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
        }
        .info-col {
            flex: 1 1 200px;
        }
        .info-label {
            font-weight: bold;
            color: #1a237e;
            font-size: 10px;
        }
        .info-value {
            color: #222;
            font-size: 10px;
        }
        .summary-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f5f7fa;
            border: 1px solid #bdbdbd;
            margin-bottom: 18px;
        }
        .summary-box {
            flex: 1 1 0;
            text-align: center;
            padding: 10px 0;
            border-right: 1px solid #bdbdbd;
        }
        .summary-box:last-child {
            border-right: none;
        }
        .summary-label {
            font-size: 10px;
            color: #607d8b;
            margin-bottom: 2px;
        }
        .summary-value {
            font-size: 13px;
            font-weight: bold;
            color: #1a237e;
        }
        .table-container {
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th {
            background: #1a237e;
            color: #fff;
            font-weight: bold;
            padding: 7px 4px;
            border-bottom: 2px solid #1565c0;
            font-size: 10px;
        }
        td {
            padding: 7px 4px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background: #f5f7fa;
        }
        .amount-column {
            text-align: right;
            font-weight: bold;
            color: #1565c0;
        }
        .number-column, .date-column {
            text-align: center;
        }
        .daily-summary {
            margin-top: 10px;
        }
        .daily-summary h3 {
            font-size: 11px;
            color: #1a237e;
            font-weight: bold;
            margin-bottom: 8px;
            text-align: left;
        }
        .footer {
            margin-top: 18px;
            text-align: center;
            font-size: 9px;
            color: #607d8b;
            border-top: 1px solid #bdbdbd;
            padding-top: 10px;
        }
        .page-number {
            text-align: right;
            font-size: 9px;
            color: #bdbdbd;
            margin-top: 6px;
        }
        .no-data {
            text-align: center;
            color: #bdbdbd;
            font-style: italic;
            padding: 18px 0;
        }
        .summary-row {
            display: flex;
            justify-content: center;
            align-items: stretch;
            gap: 16px;
            margin-bottom: 18px;
        }
        .summary-card {
            background: #f7f9fa;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            min-width: 120px;
            padding: 12px 18px;
            text-align: center;
            box-shadow: none;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .summary-label {
            font-size: 10px;
            color: #607d8b;
            margin-bottom: 4px;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PEMASUKAN</h1>
        <div class="subtitle">Sistem Informasi Pemesanan Lapangan</div>
        <div class="company">Sipelem Futsal</div>
        <div class="date">Dicetak pada: {{ now()->format('d F Y H:i') }}</div>
    </div>

    <div class="company-info">
        <table class="company-info-table">
            <tr>
                <td style="width: 25%;"><b>Nama</b></td>
                <td style="width: 25%;">Sipelem Futsal</td>
                <td style="width: 25%;"><b>Telepon</b></td>
                <td style="width: 25%;">(021) 1234-5678</td>
            </tr>
            <tr>
                <td><b>Alamat</b></td>
                <td>Jl. Contoh No. 123, Kota, Provinsi</td>
                <td><b>Email</b></td>
                <td>info@sipelemfutsal.com</td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <div class="info-grid">
            <div class="info-col">
                <div class="info-label">Periode Laporan:</div>
                <div class="info-value">
                    @if(isset($filters['from']) && isset($filters['until']))
                        {{ \Carbon\Carbon::parse($filters['from'])->format('d F Y') }} - {{ \Carbon\Carbon::parse($filters['until'])->format('d F Y') }}
                    @elseif(isset($filters['bulan']) && isset($filters['tahun']))
                        {{ \Carbon\Carbon::createFromDate($filters['tahun'], $filters['bulan'], 1)->format('F Y') }}
                    @else
                        Semua Data
                    @endif
                </div>
            </div>
            <div class="info-col">
                <div class="info-label">Total Transaksi:</div>
                <div class="info-value">{{ $totalTransactions }} transaksi</div>
            </div>
            <div class="info-col">
                <div class="info-label">Status Laporan:</div>
                <div class="info-value">Selesai</div>
            </div>
            <div class="info-col">
                <div class="info-label">Jenis Laporan:</div>
                <div class="info-value">Laporan Pemasukan</div>
            </div>
        </div>
    </div>

    @if($totalTransactions > 0)
    <div class="summary-row">
        <div class="summary-card">
            <div class="summary-label">Total Pemasukan</div>
            <div class="summary-value">Rp {{ number_format($total, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Rata-rata</div>
            <div class="summary-value">Rp {{ number_format($averageAmount, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Tertinggi</div>
            <div class="summary-value">Rp {{ number_format($highestAmount, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Terendah</div>
            <div class="summary-value">Rp {{ number_format($lowestAmount, 0, ',', '.') }}</div>
        </div>
    </div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 7%;">No.</th>
                    <th style="width: 22%;">Order ID</th>
                    <th style="width: 25%;">Nominal</th>
                    <th style="width: 23%;">Tanggal</th>
                    <th style="width: 13%;">Jam</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $index => $payment)
                    <tr>
                        <td class="number-column">{{ $index + 1 }}</td>
                        <td>{{ $payment->order_id }}</td>
                        <td class="amount-column">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td class="date-column">{{ $payment->created_at->format('d/m/Y') }}</td>
                        <td class="date-column">{{ $payment->created_at->format('H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="no-data">
                            Tidak ada data pemasukan untuk periode yang dipilih
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($totalTransactions > 0 && $dailySummary->count() > 1)
    <div class="daily-summary">
        <h3>Ringkasan Harian</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Tanggal</th>
                    <th style="width: 30%;">Jumlah Transaksi</th>
                    <th style="width: 30%;">Total Pemasukan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailySummary as $date => $summary)
                    <tr>
                        <td>{{ $summary['date'] }}</td>
                        <td class="number-column">{{ $summary['count'] }} transaksi</td>
                        <td class="amount-column">Rp {{ number_format($summary['total'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem Sipelem Futsal. Dokumen ini valid dan dapat dipercaya untuk keperluan administrasi.</p>
        <p>© {{ date('Y') }} Sipelem Futsal. All rights reserved.</p>
    </div>
    <div class="page-number">
        Halaman 1 dari 1
    </div>
</body>
</html> 