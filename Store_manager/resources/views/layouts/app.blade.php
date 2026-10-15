<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Store Manager POS')</title>
    {{-- select2 --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet"/>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #212529; }
        .sidebar .nav-link { color: #adb5bd;}
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background-color: #0d6efd; border-radius: 6px; }
        .sidebar .nav-link i { margin-right: 10px; width: 20px; text-align: center; }
        .main-content { padding: 30px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .table-responsive {
    overflow: visible !important;
    }

    /* Ensure the table row doesn't clip children elements */
    .table {
        margin-bottom: 0;
    }
        /* 1. Force container full width */
    .select2-container {
        width: 100% !important;
    }

    /* 2. Match standard Bootstrap form-control height (38px or 42px) */
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
        padding: 4px 8px !important;
        display: flex !important;
        align-items: center !important;
    }

    /* 3. Center and size the text vertically */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        color: #212529 !important;
        padding-left: 4px !important;
    }

    /* 4. Center the dropdown arrow vertically */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        top: 0 !important;
        right: 8px !important;
    }
    /* Add search magnifying glass icon inside Select2 search box */
.select2-search--dropdown {
    position: relative;
}

.select2-search--dropdown .select2-search__field {
    padding-right: 30px !important; /* Space for icon */
}

.select2-search--dropdown::after {
    content: "\f002"; /* FontAwesome search icon code */
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
}
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3 col-lg-2 px-0 sidebar d-flex flex-column p-3">
            <h4 class="text-white text-center py-3 border-bottom border-secondary mb-4">
                <i class="fa-solid font-bold fa-shop text-primary"></i> Store Manager
            </h4>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item mb-1">
                    <a href="#" class="nav-link">
                        <i class="fa-solid fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="{{route('branches.index')}}" class="nav-link {{request()->is('branches*') ? 'active' : ''}}">
                        <i class="fa-solid fa-code-branch"></i> Branches
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="{{ route('warehouses.index') }}" class="nav-link {{ request()->is('warehouses*') ? 'active' : '' }}">
                        <i class="fa-solid fa-warehouse"></i> Warehouses
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="{{ route('products.index') }}" class="nav-link {{ request()->is('products*') ? 'active' : '' }}">
                        <i class="fa-solid fa-box"></i> Products
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->is('purchases*') ? 'active' : '' }}">
                        <i class="fa-solid fa-box"></i> Purchase
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="{{ route('categories.index') }}" class="nav-link {{ request()->is('categories*') ? 'active' : '' }}">
                        <i class="fa-solid fa-tags"></i> Categories
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="{{ route('units.index') }}" class="nav-link {{ request()->is('units*') ? 'active' : '' }}">
                        <i class="fa-solid fa-ruler"></i> Units
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->is('suppliers*') ? 'active' : '' }}">
                        <i class="fa-solid fa-truck-field"></i> Suppliers
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="{{ route('customers.index') }}" class="nav-link {{ request()->is('customers*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i> Customers
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="{{ route('sales.index') }}" class="nav-link {{ request()->is('sales*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i> Sales
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-9 col-lg-10 main-content">
            @yield('content')
        </div>
    </div>
</div>
{{-- Select2 --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script >
    // In your Javascript (external .js resource or <script> tag)
$(document).ready(function() {
    $('.js-example-basic-single').select2();
});
</script>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>