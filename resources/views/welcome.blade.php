<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUMI - Eye Health</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --lumi-green: #eef9f1;
            --lumi-mint: #dcfce7;
            --lumi-purple: #a855f7;
            --lumi-pink: #fbcfe8;
            --text-main: #1f2937;
        }

        body {
            font-family: 'Fredoka', sans-serif;
            background-color: #ffffff;
            margin: 0;
            overflow-x: hidden;
            color: var(--text-main);
        }

        /* Large LUMI Background Text */
        .bg-lumi-text {
            position: absolute;
            top: -27%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 30vw;
            font-weight: 900;
            color: #d7eaceab; 
            z-index: -1;
            letter-spacing: 25px;
            user-select: none;
        }

        .hero-section {
            position: relative;
            min-height: 100vh;
            background: 
                radial-gradient(circle at 90% 50%, rgba(42, 131, 68, 0.2) 0%, transparent 35%),
                radial-gradient(circle at 50% 50%, #E4FFD8 0%, transparent 60%),
                radial-gradient(circle at 15% 20%, rgba(251, 207, 232, 0.6) 0%, transparent 20%);
               
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 80px;
        }

        /* Semi-Circle Decoration */
        .hero-section::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            width: 100%;
            height: 800px;
            background: #F5FFF7;
            border-radius: 0 0 50% 50%;
            z-index: -2;
            pointer-events: none;
        }

        /* Small Separate Gradient Glow */
        .hero-section::after {
            content: '';
            position: absolute;
            top: 30%;
            left: 65%;
            width: 400px;
            height: 400px;
            border-radius: 100%;
            background: radial-gradient(circle, #def8e4 0%, transparent 50%);
            z-index: -2;
            pointer-events: none;
        }

        /* Top Navigation Overlay */
        .top-nav {
            position: absolute;
            top: 30px;
            right: 50px;
            z-index: 100;
        }
        .top-nav a {
            text-decoration: none;
            color: #94a3b8;
            font-weight: 600;
            margin-left: 20px;
            transition: color 0.3s;
        }
        .top-nav a:hover { color: var(--lumi-purple); }

        /* The Phone Centerpiece */
        .phone-wrapper {
            position: relative;
            width: 700px;
            z-index: 10;
            text-align: center;
            margin-top: -200px;
        }

        .phone-header {
            padding-top: 20px;
            text-align: center;
        }

        .phone-welcome {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .btn-phone-start {
            border: 2px solid #000;
            background: white;
            border-radius: 20px;
            padding: 4px 20px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-top: 10px;
            transition: transform 0.2s;
        }

        .phone-character {
            width: 100%;
            display: block;
            margin-top: 20px;
        }

        .mascot-image {
            width: 100%;
            font-size: 120px;
            line-height: 1;
            margin-bottom: 10px;
        }

        /* Mascot Side Elements */
        .mascot-side {
            position: absolute;
            width: 220px;
            text-align: center;
        }
        .mascot-side img {
            position: relative;
            z-index: 5;
        }
        .mascot-left {
            left: 10%;
            top: 25%;
        }
        .mascot-right {
            right: 10%;
            top: 20%;
            width: 250px;
        }

        .speech-bubble {
            background: white;
            padding: 150px 20px 40px;
            border-radius: 0 50px 0 0;
            font-size: 0.9rem;
            line-height: 1.4;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03);
            margin-top: -150px;
            text-align: left;
            position: relative;
            z-index: 4;
        }

        /* Bottom Pill Shape */
        .bottom-pill {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            width: 160px;
            height: 52px;
            background: #527267;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            border-radius: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            z-index: 100;
        }

        .bottom-pill:hover {
            background: var(--lumi-purple);
            color: white;
            transform: translateX(-50%) translateY(-5px);
            box-shadow: 0 8px 25px rgba(168, 85, 247, 0.3);
        }

        @media (max-width: 992px) {
            .mascot-side { display: none; }
            .bg-lumi-text { font-size: 40vw; top: -28%; }
        }
    </style>
</head>
<body>

    <section class="hero-section">
        <div class="bg-lumi-text">LUMI</div>

        <div class="mascot-side mascot-left">
            <img src="{{ asset('assets/restlumi.png') }}" width="180" alt="Frog" style="display: block; margin: 0 auto;">
            <div class="speech-bubble">
                Track patient eye strain and screen behavior in real time.
            </div>
        </div>

        <div class="phone-wrapper">
           
            <img src="{{ asset('assets/phone.png') }}" class="phone-character" alt="Phone Mascot" style="width: 100%; height: auto;">
        </div>

        <div class="mascot-side mascot-right">
           <img src="{{ asset('assets/hello.png') }}" class="mascot-image" alt="hello Mascot" style="width: 100%; height: auto;">
        </div>

        <a href="{{ Route::has('login') ? route('login') : '#' }}" class="bottom-pill">Sign In</a>
    </section>

    <div class="container py-5">
        <div class="row g-4 mb-5">
            <!-- Large Left Card -->
            <div class="col-md-6">
                <div class="p-5 rounded-4 h-100 position-relative overflow-hidden" style="background: var(--lumi-mint); display: flex; flex-direction: column; justify-content: center; min-height: 240px;">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 500px; height: 500px; background: radial-gradient(circle, rgba(255, 182, 249, 0.7) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
                    <div class="position-relative" style="z-index: 1;">
                        <i class="bi bi-people h1 text-success mb-3"></i>
                        <h4 class="fw-bold">Professional Network</h4>
                        <p class="text-muted">Connect with verified eye care specialists</p>
                    </div>
                </div>
            </div>

            <!-- Tall Middle Card -->
            <div class="col-md-3">
                <div class="p-5 rounded-4 h-100" style="background: #527267; display: flex; flex-direction: column; justify-content: center; min-height: 360px;">
                    <i class="bi bi-star h1 text-danger mb-3"></i>
                    <h4 class="fw-bold">Premium Features</h4>
                    <p class="text-muted">Advanced tools for better eye health management</p>
                </div>
            </div>

            <!-- Large Right Card -->
            <div class="col-md-3">
                <div class="p-5 rounded-4 h-100" style="background: #E3F2E6; display: flex; flex-direction: column; justify-content: center; min-height: 240px;">
                    <i class="bi bi-shield-lock h1 text-primary mb-3"></i>
                    <h5 class="fw-bold">Secure & Safe</h5>
                    <p class="text-muted">Your health data is encrypted</p>
                </div>
            </div>
        </div>

        <!-- Wide Bottom Card -->
        <div class="row">
            <div class="col-12">
                <div class="rounded-4 text-center overflow-hidden" style="background: #B2C3B5; min-height: 320px;">
                    <img src="{{ asset('assets/parentsphone.png') }}" alt="Parents" style="width: 50%; height: 320px; object-fit: cover; display: block; margin: 0 auto;">
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 text-muted border-top">
        <p>&copy; 2026 LUMI Eye Health. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>