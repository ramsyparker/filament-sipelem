<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran</title>
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #212121; /* Dark background */
            color: #fff; /* Light text color */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .card {
            background-color: #2c2c2c; /* Dark card background */
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #ffc451; /* Yellow color for title */
        }

        .info {
            text-align: left;
            margin-bottom: 25px;
        }

        .info p {
            margin: 10px 0;
            font-size: 16px;
        }

        .label {
            font-weight: bold;
            color: #ddd; /* Light text for labels */
        }

        #pay-button {
            background-color: #ffc451; /* Yellow button */
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #pay-button:hover {
            background-color: #e6ac30; /* Darker yellow on hover */
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Konfirmasi Pembayaran</h2>
        <div class="info">
            <p><span class="label">Item:</span> {{ $itemName }}</p>
            <p><span class="label">Jumlah:</span> Rp {{ number_format($amount, 0, ',', '.') }}</p>
        </div>
        <button id="pay-button">Bayar Sekarang</button>
    </div>

    <script type="text/javascript">
        document.getElementById('pay-button').onclick = function () {
            snap.pay("{{ $snapToken }}", {
                onSuccess: function(result){
                    alert("Pembayaran berhasil!");
                    window.location.href = "/";  // Redirect to home or success page
                },
                onPending: function(result){
                    alert("Menunggu konfirmasi pembayaran...");
                },
                onError: function(result){
                    alert("Pembayaran gagal!");
                }
            });
        };
    </script>
</body>
</html>
