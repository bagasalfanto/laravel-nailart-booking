<section class="py-5" style="background-color: #fff6f9;">
    <div class="container py-4 text-center">
        <h1 class="display-5 fw-bold mb-3">NailArt Booking</h1>
        <p class="text-secondary mb-4">Booking nail art cepat, simpel, dan nyaman.</p>

        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="{{ route('schedule') }}" class="btn btn-danger px-4">Book Now</a>
            <a href="{{ route('naillist') }}" class="btn btn-outline-dark px-4">Lihat Desain</a>

            @auth
                <a href="{{ route('dashboard.home') }}" class="btn btn-dark px-4">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-secondary px-4">Login</a>
                <a href="{{ route('register') }}" class="btn btn-secondary px-4">Register</a>
            @endauth
        </div>
    </div>
</section>
