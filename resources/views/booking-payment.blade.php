<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Booking Lapangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #212121; /* Dark background */
            color: #fff; /* Light text color */
        }

        .container {
            max-width: 600px;
            margin-top: 50px;
            background-color: #2c2c2c; /* Darker container for contrast */
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .container h2 {
            font-weight: 600;
            color: #ffc451; /* Yellow color for header */
            margin-bottom: 20px;
        }

        .container p {
            font-size: 16px;
            color: #ddd; /* Light text for paragraphs */
        }

        .total-payment {
            font-size: 18px;
            font-weight: 600;
            color: #28a745; /* Green color for total payment */
        }

        .pay-button {
            width: 100%;
            padding: 12px;
            background-color: #ffc451;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            color: #212121; /* Dark text on button */
        }

        .pay-button:hover {
            background-color: #e6ac30; /* Darker yellow on hover */
        }

        .alert {
            margin-top: 20px;
        }

        .d-flex.justify-content-center {
            margin-top: 20px;
        }

        /* For Responsive Design */
        @media (max-width: 576px) {
            .container {
                padding: 20px;
            }

            .pay-button {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Pembayaran Booking Lapangan</h2>

        <!-- Menampilkan Order ID -->
        <p><strong>Nomor Order:</strong> {{ $orderId }}</p>
        <!-- Menampilkan lapangan yang dipesan -->
        <p><strong>Lapangan:</strong> {{ $field->name }}</p>

        <!-- Menampilkan jadwal booking -->
        <p><strong>Tanggal Booking:</strong> {{ $bookingDate }}</p>
        <p><strong>Waktu Mulai:</strong> {{ $startTime }}</p>
        <p><strong>Waktu Selesai:</strong> {{ $endTime }}</p>

        <!-- Menampilkan jumlah yang harus dibayar -->
        <p class="total-payment"><strong>Total Pembayaran:</strong> Rp {{ number_format($amount, 0, ',', '.') }}</p>

        <!-- Tombol untuk memulai pembayaran -->
        <button id="pay-button" class="pay-button">Bayar Sekarang</button>

        <!-- Alert messages -->
        <div id="payment-alert" class="alert alert-info d-none" role="alert">
            Menunggu konfirmasi pembayaran...
        </div>

        <div id="payment-error" class="alert alert-danger d-none" role="alert">
            Pembayaran gagal!
        </div>
    </div>

    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script type="text/javascript">
        document.getElementById('pay-button').onclick = function () {
            snap.pay("{{ $snapToken }}", {
                onSuccess: function(result) {
                    // Redirect ke halaman utama dengan order_id
                    window.location.href = "/?order_id=" + result.order_id;
                },
                onPending: function(result) {
                    showNotification('Menunggu konfirmasi pembayaran...', 'info');
                },
                onError: function(result) {
                    showNotification('Pembayaran gagal! Silakan coba lagi.', 'error');
                }
            });
        };

        // Jika ada notifikasi di localStorage, tampilkan di halaman utama
        document.addEventListener('DOMContentLoaded', function() {
            const notif = localStorage.getItem('notification');
            if (notif) {
                const {message, type} = JSON.parse(notif);
                showNotification(message, type);
                localStorage.removeItem('notification');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
