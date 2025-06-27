<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Booking - SIPELEM FUTSAL</title>
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">
    <style>
        body {
            background: #181818 url('{{ asset('assets/img/hero-bg.jpeg') }}') center/cover no-repeat;
            min-height: 100vh;
            font-family: 'Poppins', 'Roboto', sans-serif;
        }
        .glass-effect {
            background: rgba(30,30,30,0.97) !important;
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.12);
        }
        .table-dark th, .table-dark td {
            vertical-align: middle;
        }
        .table-dark thead th {
            color: #ffc107;
            background: #232323;
            border: none;
        }
        .table-dark tbody tr {
            border-bottom: 1px solid #232323;
        }
        .badge-status {
            color: #181818;
            font-weight: 600;
            border-radius: 6px;
            padding: 6px 14px;
            font-size: 0.95rem;
        }
        .badge-completed { background: #ffc107; }
        .badge-pending { background: #fd7e14; }
        .badge-cancelled { background: #dc3545; }
        .badge-confirmed { background: #20c997; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card glass-effect shadow-lg border-0">
            <div class="card-body">
                <h2 class="mb-4" style="color:#ffc107;"><i class="bi bi-clock-history me-2"></i>History Booking</h2>
                @if($bookings->isEmpty())
                    <div class="alert alert-warning text-center">Belum ada riwayat booking.</div>
                @else
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle rounded">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Lapangan</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Status</th>
                                <th>Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $i => $booking)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $booking->field->name ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</td>
                                <td>
                                    <span class="badge badge-status badge-{{ $booking->status }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td>Rp {{ number_format($booking->price,0,',','.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html> 