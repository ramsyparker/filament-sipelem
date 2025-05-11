<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Form Booking Lapangan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #212121;
            /* Dark background */
            color: #fff;
            /* Light text color */
        }

        .booking-container {
            max-width: 900px;
            margin: 40px auto;
            background-color: #2c2c2c;
            /* Darker background for the form */
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        h2 {
            font-weight: 600;
            color: #ffc451;
            /* Matching yellow color */
        }

        .form-label {
            font-weight: 500;
            color: #ffc451;
            /* Yellow color for labels */
        }

        .form-control {
            background-color: #333;
            /* Dark input fields */
            border: 1px solid #444;
            color: #fff;
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: #ffc451;
            /* Yellow border on focus */
            background-color: #444;
        }

        .btn-success {
            background-color: #ffc451;
            /* Button color matching the design */
            border-color: #ffc451;
            font-weight: 600;
            color: #212121;
            /* Dark text on the button */
            border-radius: 8px;
        }

        .btn-success:hover {
            background-color: #e6ac30;
            /* Darker shade on hover */
            border-color: #e6ac30;
        }

        .jadwal-table th {
            background-color: #ffc451;
            /* Yellow background for headers */
            color: #212121;
            /* Dark text color for the header */
        }

        .jadwal-table th,
        .jadwal-table td {
            text-align: center;
            vertical-align: middle;
            padding: 12px;
        }

        .jadwal-table td {
            background-color: #333;
            /* Dark background for table rows */
            color: #fff;
            /* White text for better contrast */
        }

        .jadwal-table tr:nth-child(even) td {
            background-color: #444;
            /* Slightly lighter background for even rows */
        }

        .jadwal-table tr:hover td {
            background-color: #555;
            /* Highlight row on hover for better visibility */
        }


        .section-divider {
            margin-top: 40px;
            margin-bottom: 20px;
            border-top: 2px dashed #444;
            /* Dark dashed line */
        }

        .pagination .page-link {
            color: #ffc451;
        }

        .pagination .page-item.active .page-link {
            background-color: #ffc451;
            border-color: #ffc451;
            color: #212121;
        }

        .alert {
            border-radius: 10px;
        }

        .d-flex.justify-content-center {
            margin-top: 20px;
        }

        /* Input Select Box */
        .form-select {
            background-color: #333;
            border: 1px solid #444;
            color: #fff;
            border-radius: 8px;
        }

        .form-select:focus {
            border-color: #ffc451;
            /* Yellow border on focus */
            background-color: #444;
        }
    </style>
</head>

<body>

    <div class="container booking-container">
        <h2 class="mb-3">Booking Lapangan: {{ $field->name }}</h2>
        <p><strong>Harga:</strong> Rp {{ number_format($field->price, 0, ',', '.') }} / Jam</p>

        <!-- Filter Hari -->
        <form method="GET" action="{{ route('booking.form', ['fieldId' => $field->id]) }}" class="mb-4">
            <label for="dayFilter" class="form-label">Filter Hari</label>
            <select name="day" id="dayFilter" onchange="this.form.submit()" class="form-select w-auto">
                <option value="">-- Semua Hari --</option>
                @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                    <option value="{{ $day }}" {{ request('day') === $day ? 'selected' : '' }}>
                        {{ $day }}</option>
                @endforeach
            </select>
        </form>

        <!-- Alert -->
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Form Booking -->
        <form method="POST" action="{{ route('booking.store') }}" class="row gy-3">
            @csrf
            <input type="hidden" name="field_id" value="{{ $field->id }}">

            <!-- Baris Pertama -->
            <div class="col-md-6">
                <label for="booking_date" class="form-label">Tanggal Booking</label>
                <input type="date" name="booking_date" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="start_time" class="form-label">Jam Mulai</label>
                <input type="time" name="start_time" class="form-control" required>
            </div>

            <!-- Baris Kedua -->
            <div class="col-md-6">
                <label for="duration" class="form-label">Durasi (Jam)</label>
                <input type="number" name="duration" class="form-control" min="1" max="5" required>
            </div>

            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    Booking
                </button>
            </div>
        </form>


        <hr class="section-divider">

        <!-- Jadwal Tersedia -->
        <h4 class="mb-3">Jadwal Tersedia:</h4>
        <div class="table-responsive">
            <table class="table table-bordered jadwal-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($availableSchedules as $schedule)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">Tidak ada jadwal tersedia untuk hari yang dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3 mr-4">
            {{ $availableSchedules->withQueryString()->links() }}
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
