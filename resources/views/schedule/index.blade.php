<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Lapangan - SIPELEM</title>
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/jadwal.css') }}">
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card glass-effect">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Jadwal Lapangan</h4>
                        <a href="/" class="btn btn-primary btn-sm">Kembali</a> <!-- Back button -->
                    </div>
                    <div class="card-body">
                        @if (isset($message))
                            <p>{{ $message }}</p>
                        @else
                            <table class="schedule-table">
                                <thead>
                                    <tr>
                                        <th>Nama Lapangan</th>
                                        <th>Waktu Mulai</th>
                                        <th>Waktu Selesai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($schedules as $schedule)
                                        <tr>
                                            <td>{{ $schedule->field->name }}</td>
                                            <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('d-m-Y H:i') }}
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($schedule->end_time)->format('d-m-Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
