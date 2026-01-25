<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')

</head>

<body>
    <div class="container-fluid">
        <header class="topbar d-flex justify-content-between align-items-center px-3 py-2">
            <div class="d-flex align-items-center">

                <h5 class="mb-0">إدارة الموقع</h5>
                <button id="toggleSidebar" class="btn btn-light me-2 right-space">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="d-flex align-items-center">
                <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                    href="{{ route('admin.settings.index') }}">
                    <i class="fas fa-cog ms-2"></i>
                    الإعدادات
                </a>

            </div>
        </header>

        <div class="row">
            <nav id="sidebar" class="col-md-2 sidebar p-3">

                <ul class="nav flex-column pe-0 gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-home ms-2"></i>
                            لوحة التحكم
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                            href="{{ route('admin.orders.index') }}">
                            <i class="fas fa-shopping-cart ms-2"></i>
                            الطلبات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                            href="{{ route('admin.products.index') }}">
                            <i class="fas fa-box ms-2"></i>
                            المنتجات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}"
                            href="{{ route('admin.inventory.index') }}">
                            <i class="fas fa-warehouse ms-2"></i>
                            المخازن
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                            href="{{ route('admin.categories.index') }}">
                            <i class="fas fa-layer-group ms-2"></i>
                            الفئات
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}"
                            href="{{ route('admin.companies.index') }}">
                            <i class="fas fa-building ms-2"></i>
                            الشركات
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}"
                            href="{{ route('admin.tags.index') }}">
                            <i class="fas fa-tags ms-2"></i>
                            الوسوم
                        </a>
                    </li>





                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.damaged-goods.*') ? 'active' : '' }}"
                            href="{{ route('admin.damaged-goods.index') }}">
                            <i class="fas fa-exclamation-triangle ms-2"></i>
                            البضاعة التالفة
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.adjustments.*') ? 'active' : '' }}"
                            href="{{ route('admin.adjustments.index') }}">
                            <i class="fas fa-money-bill ms-2"></i>
                           التسويات المالية
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.delivery.*') ? 'active' : '' }}"
                            href="{{ route('admin.delivery.index') }}">
                            <i class="fas fa-truck ms-2"></i>
                            عمال التوصيل
                        </a>
                    </li>

                    <li class="nav-item">
                        <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link text-center w-100" id="logout-button">
                                <i class="fas fa-sign-out-alt ms-2"></i>
                                تسجيل الخروج
                            </button>
                        </form>
                    </li>
                </ul>


            </nav>

            <main id="mainContent" class="col-md-10 p-4">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const main = document.getElementById('mainContent');
            const toggleSidebar = document.getElementById('toggleSidebar');

            sidebar.classList.toggle('sidebar-collapsed');
            toggleSidebar.classList.toggle('right-space');
            main.classList.toggle('main-expanded');
        });
    </script>
    @yield('scripts')
</body>

</html>
