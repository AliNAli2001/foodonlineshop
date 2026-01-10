<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            direction: rtl;
        }
        body { font-family: Arial, sans-serif; }
        .sidebar { background-color: #f8f9fa; min-height: 100vh; }
        .sidebar a { text-decoration: none; color: #333; }
        .sidebar a:hover { background-color: #e9ecef; }
        .sidebar a.active { background-color: #007bff; color: white; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 sidebar p-3">
                <h5 class="mb-4">إدارة الموقع</h5>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">لوحة التحكم</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.products.index') }}">المنتجات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.categories.index') }}">الفئات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.companies.index') }}">الشركات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.tags.index') }}">الوسوم</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.inventory.index') }}">المخازن</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.orders.index') }}">الطلبات</a>
                    </li>
                 
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.damaged-goods.index') }}">البضاعة المخربة</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.delivery.index') }}">عمال التوصيل</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.settings.index') }}">الإعدادات</a>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link">تسجيل الخروج</button>
                        </form>
                    </li>
                </ul>
            </nav>

            <main class="col-md-10 p-4">
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
    
    @yield('scripts')
</body>
</html>

