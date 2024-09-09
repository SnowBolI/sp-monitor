@include('layouts.header')
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="navbar_inner">
                    <nav class="navbar navbar-expand-lg">
                        <a class="navbar-brand">
                        <img src="{{ asset('logobank.png') }}" alt="Logo" style="height: 50px; margin-right: 8px;">    
                        Monitoring SP
                    </a>
                        <ul class="d-flex justify-content-end w-100">
                            @auth
                            <div class="user-menu">Welcome {{ Auth::user()->name }}</div>
                            <div class="menu-container">
                                <button id="menuButton" class="menu-button">
                                    <i class="fas fa-bars"></i> Menu
                                </button>
                                <div id="menuDropdown" class="menu-dropdown">
                                    <a href="{{ route('logout') }}" class="menu-item">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                    <a href="{{ route('password.change.form') }}" class="menu-item">
                                        <i class="fas fa-key"></i> Change Password
                                    </a>
                                </div>
                            </div>
                            @if(Auth::user()->jabatan_id == 99)
                                <li><a class="custom-btn" href="{{ route('super-admin.cabang') }}">Cabang</a></li>
                                <li><a class="custom-btn" href="{{ route('super-admin.kantorkas') }}">Kantor Kas</a></li>
                                <li><a class="custom-btn" href="dashboard">Dashboard</a></li>
                                <li><a class="custom-btn" href="{{ route('super-admin.key') }}">Keys</a></li>
                            @endif
                            @endauth
                            @guest
                                <li><a class="custom-btn" href="{{ route('login') }}">Login</a></li>
                                <li><a class="custom-btn" href="{{ route('register') }}">Registration</a></li>
                            @endguest
                        </ul>
                    </nav>
                    <div class="navbar-underline"></div>
                </div>
            </div>
        </div>
        @yield('main-content')
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </div>
    @include('layouts.footer')
    @stack('js')
</body>
</html>