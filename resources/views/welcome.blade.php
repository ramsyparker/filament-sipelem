<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>SIPELEM-FUTSAL</title>
    <meta name="description" content="">
    <meta name="keywords" content="">

    <meta name="errors" content="{{ $errors->any() ? json_encode($errors->messages()) : '{}' }}">
    <meta name="success" content="{{ session('success') ?? '' }}">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .notification.success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .notification.error {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }
        
        .notification.warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }
        
        .notification-content {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .notification-icon {
            font-size: 1.2rem;
        }
        
        .notification-message {
            flex: 1;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        
        .notification-close:hover {
            opacity: 1;
        }
    </style>

</head>

<body class="index-page">

    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

            <a href="index.html" class="logo d-flex align-items-center me-auto me-lg-0">
                <!-- Uncomment the line below if you also wish to use an image logo -->
                <!-- <img src="assets/img/logo.png" alt=""> -->
                <h1 class="sitename">SIPELEM</h1>
                <span>.</span>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="#hero" class="active">Beranda<br></a></li>
                    <li><a href="#about">Tentang</a></li>
                    <li><a href="#services">Lapangan</a></li>
                    <li><a href="#call-to-action">Membership</a></li>
                    <li><a href="#contact">Kontak</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            @auth
                <div class="dropdown">
                    <a class="btn-getstarted dropdown-toggle" href="#" role="button" id="userDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="{{ route('profile') }}">
                                <i class="bi bi-person me-2"></i>Profile
                            </a></li>

                        <li><a class="dropdown-item" href="{{ route('schedule.view') }}">
                                <i class="bi bi-calendar me-2"></i> Jadwal
                            </a></li>

                        <li><a class="dropdown-item" href="{{ route('booking.history') }}">
                                <i class="bi bi-clock-history me-2"></i> History Booking
                            </a></li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a class="btn-getstarted" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
            @endauth

        </div>
    </header>

    <main class="main">

        <!-- Hero Section -->
        <section id="hero" class="hero section dark-background">

            <img src="{{ asset('assets/img/hero-bg.jpeg') }}" alt="" data-aos="fade-in">

            <div class="container">

                <div class="row justify-content-center text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="col-xl-6 col-lg-8">
                        <h2>FUTSAL SIPELEM<span>.</span></h2>
                        <p>Booking Lapangan Tanpa Ribet</p>
                    </div>

                </div>
            </div>

        </section><!-- /Hero Section -->

        <!-- About Section -->
        <section id="about" class="about section">

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row gy-4">
                    <div class="col-lg-6 order-1 order-lg-2">
                        <img src="assets/img/about.jpg" class="img-fluid" alt="">
                    </div>
                    <div class="col-lg-6 order-2 order-lg-1 content">
                        <h3>SIPELEM FUTSAL</h3>
                        <p class="fst-italic">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                            labore et dolore
                            magna aliqua.
                        </p>
                        <p>
                            Ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in
                            reprehenderit in voluptate
                            velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                            proident
                        </p>
                    </div>
                </div>

            </div>

        </section><!-- /About Section -->

        <section id="services" class="services section">
            <div class="container section-title" data-aos="fade-up">
                <h2>Lapangan</h2>
                <p>Lapangan yang tersedia</p>
            </div>

            <div class="container">
                <div class="row gy-4">
                    @foreach ($fields as $field)
                        <div class="col-md-4">
                            <div class="service-item card-modern">
                                <a href="{{ route('booking.form', ['fieldId' => $field->id]) }}"
                                    class="text-decoration-none text-dark">
                                    @if ($field->image)
                                        <div class="image-container mb-3">
                                            <img src="{{ asset('storage/' . $field->image) }}"
                                                alt="{{ $field->name }}" class="img-fluid rounded">
                                        </div>
                                    @endif
                                    <div class="card-content">
                                        <h3 class="h5">{{ $field->name }}</h3>
                                        <div class="field-info d-flex justify-content-between">
                                            <span class="type">{{ $field->type }}</span>
                                            <span class="price">Rp
                                                {{ number_format($field->price, 0, ',', '.') }}/Jam</span>
                                        </div>
                                        <div class="text-center mt-3">
                                            <a href="{{ route('booking.form', ['fieldId' => $field->id]) }}" class="btn btn-success w-100">
                                                Booking Sekarang
                                            </a>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </section>
        <!-- Call To Action Section -->
        <section id="call-to-action" class="call-to-action section dark-background">

            <img src="{{ asset('assets/img/cta-bg.jpeg') }}" alt="">

            <div class="container">
                <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
                    <div class="col-xl-10">
                        <div class="text-center">
                            <h3>Join Membership</h3>
                            <p>Jadi Membership di SIPELEM FUTSAL untuk jadwal yang lebih di prioritaskan.</p>
                            @auth
                                @if (Auth::user()->role === 'member')
                                    <button class="cta-btn" disabled>Anda Sudah Menjadi Member</button>
                                @else
                                    <a class="cta-btn" href="#" data-bs-toggle="modal"
                                        data-bs-target="#membershipModal">
                                        Daftar Sekarang
                                    </a>
                                @endif
                            @else
                                <a class="cta-btn" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                                    Login Untuk Daftar
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

        </section><!-- /Call To Action Section -->


        <!-- Contact Section -->
        <section id="contact" class="contact section">

            <!-- Section Title -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Kontak</h2>
                <p>Hubungi Kami</p>
            </div><!-- End Section Title -->

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="mb-4" data-aos="fade-up" data-aos-delay="200">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3961.2297042986174!2d109.11829147499589!3d-6.86305219313548!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6fb740746478db%3A0x88a8176134e8e9bc!2sFutsal%20Sipelem!5e0!3m2!1sid!2sid!4v1745324958988!5m2!1sid!2sid"
                        width="1120" height="400" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div><!-- End Google Maps -->

                <div class="row gy-4">

                    <div class="col-lg-4">
                        <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
                            <i class="bi bi-geo-alt flex-shrink-0"></i>
                            <div>
                                <h3>Alamat</h3>
                                <p>Jl. Sipelem, Kraton, Kec. Tegal Bar., Kota Tegal, Jawa Tengah 52111
                                </p>
                            </div>
                        </div><!-- End Info Item -->

                        <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
                            <i class="bi bi-whatsapp flex-shrink-0"></i>
                            <div>
                                <h3>WhatsApp</h3>
                                <p>+62 895 1446 3758</p>
                            </div>
                        </div><!-- End Info Item -->

                        <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="500">
                            <i class="bi bi-instagram flex-shrink-0"></i>
                            <div>
                                <h3>Instagram</h3>
                                <p>sipelem</p>
                            </div>
                        </div><!-- End Info Item -->

                    </div>

                    <div class="col-lg-8">
                        <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up"
                            data-aos-delay="200">
                            <div class="row gy-4">

                                <div class="col-md-6">
                                    <input type="text" name="name" class="form-control" placeholder="Nama"
                                        required="">
                                </div>

                                <div class="col-md-6 ">
                                    <input type="email" class="form-control" name="email" placeholder="Email"
                                        required="">
                                </div>

                                <div class="col-md-12">
                                    <input type="text" class="form-control" name="subject" placeholder="Subjek"
                                        required="">
                                </div>

                                <div class="col-md-12">
                                    <textarea class="form-control" name="message" rows="6" placeholder="Pesan" required=""></textarea>
                                </div>

                                <div class="col-md-12 text-center">
                                    <div class="loading">Loading</div>
                                    <div class="error-message"></div>
                                    <div class="sent-message">Pesan kamu berhasil terkirim, terima kasih!</div>

                                    <button type="submit">Kirim Pesan</button>
                                </div>

                            </div>
                        </form>
                    </div><!-- End Contact Form -->

                </div>

            </div>

        </section><!-- /Contact Section -->

    </main>

    <footer id="footer" class="footer dark-background">

        <div class="footer-top">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-4 col-md-6 footer-about">
                        <a href="index.html" class="logo d-flex align-items-center">
                            <span class="sitename">SIPELEM FUTSAL</span>
                        </a>
                        <div class="footer-contact pt-3">
                            <p>Jl. Sipelem, Kraton, Kec. Tegal Barat</p>
                            <p>Kota Tegal, Jawa Tengah 52111</p>
                            <p class="mt-3"><strong>Phone:</strong> <span>+62 895 1446 3758</span></p>
                            <p><strong>Email:</strong> <span>sipelem@gmail.com</span></p>
                        </div>
                        <div class="social-links d-flex mt-4">
                            <a href="https://wa.me/+6289514463758"><i class="bi bi-whatsapp"></i></a>
                            <a href="https://instagram.com/aa.ramsy"><i class="bi bi-instagram"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>Menu</h4>
                        <ul>
                            <li><i class="bi bi-chevron-right"></i> <a href="#"> Beranda</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="#"> Tentang Kami</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="#"> Lapangan</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="#"> Kontak</a></li>
                        </ul>
                    </div>


                    <div class="col-lg-4 col-md-12 footer-newsletter">
                        <h4>Informasi</h4>
                        <p>Berlangganan untuk dapat info dan penawaran menarik dari SIPELEM-FUTSAL!</p>
                        <form action="forms/newsletter.php" method="post" class="php-email-form">
                            <div class="newsletter-form"><input type="email" name="email"><input type="submit"
                                    value="Subscribe"></div>
                            <div class="loading">Loading</div>
                            <div class="error-message"></div>
                            <div class="sent-message">Terimakasih telah berlangganan informasi di SIPELEM-FUTSAL!</div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <div class="copyright">
            <div class="container text-center">
                <p>© <span>Copyright</span> <strong class="px-1 sitename">Sipelem</strong> <span>All Rights
                        Reserved</span>
                </p>
            </div>
        </div>

    </footer>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>
    <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>

    <!-- Main JS File -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                showNotification(@json(session('success')), '{{ session('notif_type', 'success') }}');
            @endif
        });
    </script>

    <!-- filepath: d:\sipelem-filament\resources\views\welcome.blade.php -->
    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Masuk ke SIPELEM<span>.</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Masukkan email" required value="{{ old('email') }}">
                        </div>
                        <div class="mb-4">
                            <label for="password">Password</label>
                            <div class="password-field">
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Masukkan password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-4 text-end">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal"
                                data-bs-dismiss="modal">Belum punya akun? Daftar</a>
                        </div>
                        <div class="mb-4 text-end">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal"
                                data-bs-dismiss="modal">Lupa Password?</a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Masuk</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Daftar SIPELEM<span>.</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('register.store') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="register_name">Nama Lengkap</label>
                            <input type="text" class="form-control" id="register_name" name="name"
                                placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div class="mb-4">
                            <label for="register_email">Email</label>
                            <input type="email" class="form-control" id="register_email" name="email"
                                placeholder="Masukkan email" required>
                        </div>
                        <div class="mb-4">
                            <label for="register_phone">No HP</label>
                            <input type="text" class="form-control" id="register_phone" name="phone"
                                placeholder="Masukkan no hp" required>
                        </div>
                        <div class="mb-4">
                            <label for="register_password">Password</label>
                            <div class="password-field">
                                <input type="password" class="form-control" id="register_password" name="password"
                                    placeholder="Masukkan password" required>
                                <button type="button" class="password-toggle"
                                    onclick="togglePassword('register_password')">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="password_confirmation">Konfirmasi Password</label>
                            <div class="password-field">
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" placeholder="Konfirmasi password" required>
                                <button type="button" class="password-toggle"
                                    onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-4 text-end">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal"
                                data-bs-dismiss="modal">Sudah punya akun? Masuk</a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Daftar</button>
                    </form>
                </div>
            </div>
        </div>
    </div> <!-- End Register Modal -->
    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Lupa Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Masukkan email" required>
                        </div>
                        <div class="mb-4 text-end">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal"
                                data-bs-dismiss="modal">Kembali ke halaman login</a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Kirim Link Reset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- filepath: d:\sipelem-filament\resources\views\welcome.blade.php -->
    <div class="modal fade" id="membershipModal" tabindex="-1" aria-labelledby="membershipModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="membershipModalLabel">Pilih Paket Membership<span>.</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        @foreach ($memberships as $membership)
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $membership->name }}</h5>
                                        <p class="card-text">{{ $membership->description }}</p>
                                        <ul class="list-unstyled">
                                            <li><i class="bi bi-check-circle-fill text-success"></i>
                                                {{ $membership->duration }} Hari</li>

                                        </ul>
                                        <div class="text-center mt-4">
                                            <h4 class="mb-3">Rp {{ number_format($membership->price, 0, ',', '.') }}
                                            </h4>
                                            <button type="button" class="btn btn-primary"
                                                onclick="selectMembership({{ $membership->id }}, '{{ $membership->name }}')">
                                                Pilih Paket
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Membership Form (initially hidden) -->
                    <form id="membershipForm" action="{{ route('membership.register') }}" method="POST"
                        class="d-none mt-4">
                        @csrf
                        <input type="hidden" name="membership_id" id="membership_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pilih Lapangan</label>
                                <select name="field_id" class="form-select" required>
                                    @foreach ($fields as $field)
                                        <option value="{{ $field->id }}">{{ $field->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pilih Hari</label>
                                <select name="day_of_week" class="form-select" required>
                                    <option value="Monday">Senin</option>
                                    <option value="Tuesday">Selasa</option>
                                    <option value="Wednesday">Rabu</option>
                                    <option value="Thursday">Kamis</option>
                                    <option value="Friday">Jumat</option>
                                    <option value="Saturday">Sabtu</option>
                                    <option value="Sunday">Minggu</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pilih Jam</label>
                                <input type="time" name="booking_time" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-secondary" onclick="backToPackages()">
                                        <i class="bi bi-arrow-left"></i> Kembali
                                    </button>
                                    <button type="submit" class="btn btn-primary flex-grow-1">Daftar
                                        Membership</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function selectMembership(membershipId, membershipName) {
            document.getElementById('membership_id').value = membershipId;
            document.querySelector('.row.g-4').classList.add('d-none');
            document.getElementById('membershipForm').classList.remove('d-none');
            document.getElementById('membershipModalLabel').innerHTML = 'Pengaturan Jadwal - ' + membershipName;
        }

        function backToPackages() {
            document.querySelector('.row.g-4').classList.remove('d-none');
            document.getElementById('membershipForm').classList.add('d-none');
            document.getElementById('membershipModalLabel').innerHTML = 'Pilih Paket Membership<span>.</span>';
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(isset($notif))
                showNotification(@json($notif), 'success');
            @endif
        });
    </script>
</body>

</html>
