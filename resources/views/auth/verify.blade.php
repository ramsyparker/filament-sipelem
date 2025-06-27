<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Verifikasi Email - SIPELEM-FUTSAL</title>
    <meta name="description" content="">
    <meta name="keywords" content="">

    <!-- Favicons -->
    <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
    <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@400;700&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: #181818 url('{{ asset('assets/img/hero-bg.jpeg') }}') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', 'Roboto', sans-serif;
        }
        .verification-modal {
            background: rgba(30, 30, 30, 0.95);
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            max-width: 400px;
            width: 100%;
            padding: 2.5rem 2rem 2rem 2rem;
            color: #fff;
            margin: 2rem auto;
        }
        .verification-modal .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffc107;
            margin-bottom: 1rem;
        }
        .verification-modal .modal-icon {
            font-size: 3rem;
            color: #ffc107;
            margin-bottom: 1rem;
        }
        .verification-modal .alert-success {
            background: #232323;
            color: #ffc107;
            border: none;
        }
        .verification-modal .btn-yellow {
            background: #ffc107;
            color: #181818;
            border: none;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            transition: background 0.2s;
        }
        .verification-modal .btn-yellow:hover {
            background: #ffb300;
            color: #181818;
        }
        .verification-modal .btn-outline-light {
            border: 1.5px solid #fff;
            color: #fff;
            background: transparent;
            border-radius: 8px;
            font-weight: 500;
        }
        .verification-modal .btn-outline-light:hover {
            background: #fff;
            color: #181818;
        }
        .verification-modal small {
            color: #bbb;
        }
        .verification-modal .form-link {
            color: #ffc107;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="verification-modal">
        <div class="text-center">
            <div class="modal-icon">
                <i class="bi bi-envelope-check-fill"></i>
            </div>
            <div class="modal-title mb-2">Verifikasi Email Anda</div>
            <p class="mb-3" style="color:#fff;">
                Terima kasih telah mendaftar! Sebelum Anda dapat menggunakan akun, <br>
                silakan verifikasi alamat email Anda dengan mengklik link yang telah kami kirim.
            </p>
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            <div class="mb-3">
                <span style="color:#ffc107;"><strong>Email:</strong></span> {{ auth()->user()->email }}
            </div>
            <form method="POST" action="{{ route('verification.send') }}" class="d-grid gap-2 mb-3">
                @csrf
                <button type="submit" class="btn btn-yellow w-100">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Kirim Ulang Email Verifikasi
                </button>
            </form>
            <a href="{{ route('logout') }}" class="btn btn-outline-light w-100 mb-2">
                <i class="bi bi-box-arrow-right me-2"></i>
                Logout
            </a>
            <div class="mt-3">
                <small>
                    Tidak menerima email? Periksa folder spam atau <span class="form-link" onclick="document.querySelector('form').submit(); return false;">kirim ulang</span>
                </small>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html> 