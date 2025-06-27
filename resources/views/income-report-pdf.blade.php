<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemasukan</title>
    <style>
        @page {
            margin: 2cm;
        }
        
        body { 
            font-family: DejaVu Sans, Arial, sans-serif; 
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #2563eb;
            font-size: 24px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 25px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #374151;
        }
        
        .info-value {
            color: #1f2937;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        th { 
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        td { 
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        tr:hover {
            background-color: #f3f4f6;
        }
        
        .amount-column {
            text-align: right;
            font-weight: bold;
            color: #059669;
        }
        
        .date-column {
            text-align: center;
        }
        
        .summary-section {
            margin-top: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border-radius: 10px;
            border: 2px solid #0ea5e9;
        }
        
        .summary-title {
            font-size: 16px;
            font-weight: bold;
            color: #0c4a6e;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .summary-amount {
            font-size: 20px;
            font-weight: bold;
            color: #059669;
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 8px;
            border: 2px solid #10b981;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .page-number {
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            margin-top: 10px;
        }
        
        .logo-placeholder {
            width: 60px;
            height: 60px;
            background: #2563eb;
            border-radius: 50%;
            display: inline-block;
            margin-bottom: 10px;
            position: relative;
        }
        
        .logo-placeholder::after {
            content: "SIP";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-placeholder"></div>
        <h1>LAPORAN PEMASUKAN</h1>
        <p>Sistem Informasi Pemesanan Lapangan</p>
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }}</p>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Periode Laporan:</span>
            <span class="info-value">
                @if(isset($filters['date_range']['from']) && isset($filters['date_range']['until']))
                    {{ \Carbon\Carbon::parse($filters['date_range']['from'])->format('d F Y') }} - {{ \Carbon\Carbon::parse($filters['date_range']['until'])->format('d F Y') }}
                @elseif(isset($filters['per_bulan']['bulan']) && isset($filters['per_bulan']['tahun']))
                    {{ \Carbon\Carbon::createFromDate($filters['per_bulan']['tahun'], $filters['per_bulan']['bulan'], 1)->format('F Y') }}
                @elseif(isset($filters['from']) && isset($filters['until']))
                    {{ \Carbon\Carbon::parse($filters['from'])->format('d F Y') }} - {{ \Carbon\Carbon::parse($filters['until'])->format('d F Y') }}
                @elseif(isset($filters['bulan']) && isset($filters['tahun']))
                    {{ \Carbon\Carbon::createFromDate($filters['tahun'], $filters['bulan'], 1)->format('F Y') }}
                @else
                    Semua Data
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Transaksi:</span>
            <span class="info-value">{{ $payments->count() }} transaksi</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status Laporan:</span>
            <span class="info-value">Selesai</span>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">No.</th>
                <th style="width: 25%;">Order ID</th>
                <th style="width: 35%;">Nominal</th>
                <th style="width: 25%;">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $index => $payment)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $payment->order_id }}</td>
                    <td class="amount-column">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    <td class="date-column">{{ $payment->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px; color: #6b7280;">
                        Tidak ada data pemasukan untuk periode yang dipilih
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($payments->count() > 0)
        <div class="summary-section">
            <div class="summary-title">TOTAL PEMASUKAN</div>
            <div class="summary-amount">
                Rp {{ number_format($total, 0, ',', '.') }}
            </div>
        </div>
    @endif
    
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem</p>
        <p>© {{ date('Y') }} Sipelem Futsal. All rights reserved.</p>
    </div>
    
    <div class="page-number">
        Halaman 1
    </div>
</body>
</html> 