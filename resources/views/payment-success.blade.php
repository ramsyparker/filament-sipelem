<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background-color: #fff;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 420px;
            width: 100%;
        }
        .card h1 {
            color: #28a745;
            font-size: 28px;
            margin-bottom: 15px;
        }
        .card p {
            color: #333;
            font-size: 16px;
            margin: 10px 0;
        }
        .card a {
            display: inline-block;
            margin-top: 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 10px 25px;
            border-radius: 8px;
            transition: background-color 0.3s;
        }
        .card a:hover {
            background-color: #0056b3;
        }
        .checkmark {
            font-size: 50px;
            color: #28a745;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="checkmark">✔️</div>
        <h1>Pembayaran Berhasil!</h1>
        <p>Terima kasih. Pembayaran Anda telah diterima.</p>
        <p>Silakan cek email atau dashboard Anda untuk detail selengkapnya.</p>
        <a href="/">Kembali ke Beranda</a>
    </div>
</body>
</html>
