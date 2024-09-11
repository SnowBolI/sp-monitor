@include('layouts.header')
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="navbar_inner">
                    <nav class="navbar navbar-expand-lg">
                        <a class="navbar-brand">
                        <img src="{{ asset('logobank.png') }}" alt="Logo" style="height: 60px; margin-left: 5px; margin-right: 8px;">    
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
                            @if(Auth::user()->jabatan_id == 99) {{-- Hanya tampilkan jika user adalah super-admin --}}
                            <a href="{{ route('super-admin.cabang') }}" class="menu-item">
                                <i class="fas fa-code-branch"></i> Cabang
                            </a>
                            <a href="{{ route('super-admin.kantorkas') }}" class="menu-item">
                                <i class="fa fa-area-chart"></i> Kantor Kas
                            </a>
                            <a href="{{ route('super-admin.key') }}" class="menu-item">
                                <i class="fas fa-key"></i> Key
                            </a>
                            <a href="{{ route('super-admin.dashboard') }}" class="menu-item">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                            @endif 
                            <a href="{{ route('logout') }}" class="menu-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                            <a href="{{ route('password.change.form') }}" class="menu-item">
                                <i class="fas fa-key"></i> Change Password
                            </a>
                        </div>
                    </div>
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