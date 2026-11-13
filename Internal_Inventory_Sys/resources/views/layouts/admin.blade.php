<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LANMIC Internal System | @yield('title')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    @stack('styles')

    <style>
        @media print {
            /* Hide navigation, sidebar, footer, and non-essential elements */
            .main-sidebar,
            .main-header,
            .main-footer,
            .content-header,
            .breadcrumb,
            .pagination,
            .btn,
            .no-print,
            form,
            nav,
            .card-header button {
                display: none !important;
            }

            /* Hide statistics/summary cards - only show cards with tables */
            .row.mb-4 > .col-md-2,
            .row.mb-4 > .col-md-3,
            .row.mb-4 > .col-md-4,
            .row.mb-4 > .col-xl-2,
            .row.mb-4 > .col-xl-3,
            .row.mb-4 > .col-lg-3,
            .row.mb-4 > .col-lg-4,
            .row.mb-4 > .col-lg-6,
            .row.mb-4 > .col-sm-6 {
                display: none !important;
            }

            /* But show full-width columns that contain tables */
            .row.mb-4 > .col-12,
            .row > .col-12 {
                display: block !important;
            }

            /* Reset body and wrapper for print */
            body {
                margin: 0 !important;
                padding: 0 !important;
            }

            .wrapper,
            .content-wrapper,
            .content,
            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .content-wrapper {
                margin-left: 0 !important;
                background: white !important;
            }

            /* Card styles for print */
            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                margin-bottom: 15px !important;
                page-break-inside: avoid;
            }

            .card-header {
                background: #f8f9fa !important;
                color: #333 !important;
                padding: 8px 12px !important;
            }

            .card-body {
                padding: 10px !important;
            }

            /* Table styles for print */
            .table {
                font-size: 10px !important;
                width: 100% !important;
            }

            .table th,
            .table td {
                padding: 4px 6px !important;
            }

            .table-dark th,
            .table-dark td {
                background-color: #f8f9fa !important;
                color: #333 !important;
            }

            /* Badge styles for print */
            .badge {
                border: 1px solid #333 !important;
                color: #333 !important;
                background: white !important;
            }

            /* Print header for reports */
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 15px;
            }

            /* Links should be visible as text */
            a {
                color: #333 !important;
                text-decoration: none !important;
            }

            /* Ensure page breaks work well */
            .page-break {
                page-break-before: always;
            }
        }

        /* Hide print-only elements on screen */
        @media screen {
            .print-header,
            .print-only {
                display: none !important;
            }
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    @include('layouts.partials.navbar')

    <!-- Sidebar -->
    @include('layouts.partials.sidebar')

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page-title')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Footer -->
    @include('layouts.partials.footer')
</div>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
@stack('scripts')
</body>
</html>