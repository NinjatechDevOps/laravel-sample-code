<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Tell the browser to be responsive to screen width -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="robots" content="noindex, nofollow"  />
        <!-- Favicon icon -->
        <link rel="icon" type="image/x-icon" sizes="16x16" href="{{getSetting('site_favicon')}}">
        <title>@yield('title') - {{ siteName() }}</title>
        <!-- This page CSS -->
        <!-- chartist CSS -->
        <link href="{{ asset('assets/admin/node_modules/morrisjs/morris.css') }}" rel="stylesheet">
        <!--Toaster Popup message CSS -->
        <link href="{{ asset('assets/admin/node_modules/toast-master/css/jquery.toast.css') }}" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/node_modules/datatables/css/dataTables.bootstrap4.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/node_modules/datatables/css/responsive.dataTables.min.css') }}">
        <!--alerts CSS -->
        <link href="{{ asset('assets/admin/node_modules/sweetalert2/dist/sweetalert2.min.css') }}" rel="stylesheet">
        <!-- Custom CSS -->
        <link href="{{ asset('assets/admin/dist/css/style.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/admin/dist/css/custom.css') }}" rel="stylesheet">
        <!-- Dashboard 1 Page CSS -->
        <link href="{{ asset('assets/admin/dist/css/pages/dashboard1.css') }}" rel="stylesheet">
        <!-- Bootstrap Icon Selector -->
        <link href="{{ asset('plugins/bootstrap-icon-picker/dist/css/bootstrapicons-iconpicker.css') }}" rel="stylesheet" />
        <link href="{{ asset('scss/icons/scss/iconmoon.css') }}" rel="stylesheet" />
        <script>
            let baseAdminUrl = '{{route('admin.home')}}';
            var imagesUploadUrl = '{{ route('admin.image.upload') }}?_token=' + encodeURIComponent('{{ csrf_token() }}');
        </script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="{{ asset('assets/admin/dist/js/html5shiv.js') }}"></script>
        <script src="{{ asset('assets/admin/dist/js/respond.min.js') }}"></script>

    <![endif]-->
    </head>

    <body class="skin-blue fixed-layout">
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <div class="preloader">
            <div class="loader">
                <div class="loader__figure"></div>
                <p class="loader__label"></p>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- Main wrapper - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <div id="main-wrapper">
            <!-- ============================================================== -->
            <!-- Topbar header - style you can find in pages.scss -->
            <!-- ============================================================== -->
            <header class="topbar">
                <nav class="navbar top-navbar navbar-expand-md navbar-dark navbar-bg-white">
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <div class="navbar-header">
                        <a class="navbar-brand" href="javascript:void(0)">
                            <!-- Logo icon -->
                            <!--End Logo icon -->
                            <!-- Logo text -->
                            <span>
                                <!-- dark Logo text -->
                                <img src="{!! siteLogo() !!}" alt="homepage" class="dark-logo" />
                                <!-- Light Logo text -->
                                <img src="{!! siteLogo() !!}" class="light-logo" alt="homepage" />
                            </span>
                        </a>
                    </div>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <div class="navbar-collapse">
                        <!-- ============================================================== -->
                        <!-- toggle and nav items -->
                        <!-- ============================================================== -->
                        <ul class="navbar-nav me-auto">
                            <!-- This is  -->
                            <li class="nav-item"> <a class="nav-link nav-toggler d-block d-md-none waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
                            <li class="nav-item"> <a class="nav-link sidebartoggler d-none d-lg-block d-md-block waves-effect waves-dark" href="javascript:void(0)"><i class="icon-menu"></i></a> </li>
                            <!-- ============================================================== -->
                        </ul>
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                        <ul class="navbar-nav my-lg-0">
                            <!-- ============================================================== -->
                            <!-- ============================================================== -->
                            <!-- ============================================================== -->
                            <!-- ============================================================== -->
                            <!-- User Profile -->
                            <!-- ============================================================== -->
                            <li class="nav-item dropdown u-pro">
                                <a class="nav-link cacheClear" href="javascript:void(0);">Purge Cache</a>
                            </li>
                            <li class="nav-item dropdown u-pro">
                                <a class="nav-link dropdown-toggle waves-effect waves-dark profile-pic" href="" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="{{ asset('assets/admin/images/users/1.jpg') }}" alt="user" class=""> <span class="hidden-md-down">{{ Auth::user()->name }} &nbsp;<i class="fa fa-angle-down"></i></span> </a>
                                <div class="dropdown-menu dropdown-menu-end animated flipInY">
                                    <!-- text-->
                                    <a href="{!! route('admin.myprofile') !!}" class="dropdown-item"><i class="ti-user"></i> My Profile</a>
                                    <a href="{!! route('admin.changepassword') !!}" class="dropdown-item"><i class="ti-key"></i> Change Password</a>
                                    <!-- text-->
                                    <!--<div class="dropdown-divider"></div>-->
                                    <!-- text-->
                                    <a href="{{ route('logout') }}" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-power-off"></i> {{ __('Logout') }}</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                    <!-- text-->
                                </div>
                            </li>
                            <!-- ============================================================== -->
                            <!-- End User Profile -->
                        </ul>
                    </div>
                </nav>
            </header>
            <!-- ============================================================== -->
            <!-- End Topbar header -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Left Sidebar - style you can find in sidebar.scss  -->
            <!-- ============================================================== -->
            <aside class="left-sidebar">
                <!-- Sidebar scroll-->
                <div class="scroll-sidebar">
                    <!-- Sidebar navigation-->
                    <nav class="sidebar-nav">
                        <ul id="sidebarnav">
                            <li class="{{ request()->routeIs('admin.home') ? 'active' : '' }}">
                                <a class="waves-effect waves-dark" href="{{ route('admin.home') }}" aria-expanded="false"><i class="fas fa-chart-line"></i><span class="hide-menu">{{ __('Dashboard') }}</span></a>
                            </li>
                            @canany(['product-list', 'import-product-list', 'export-list', 'product-qty-price-update'])
                            <li>
                            <a class="has-arrow waves-effect waves-dark {{ request()->routeIs('admin.products*') || request()->routeIs('admin.exports*') || request()->routeIs('admin.import-products*') ? 'active' : '' }}" style="display: flex" href="javascript:void(0)" aria-expanded="false"><i class="fas fa-tasks"></i><span class="hide-menu">Product Management</span></a>
                                <ul aria-expanded="false" class="collapse {{ request()->routeIs('admin.products*') || request()->routeIs('admin.import-products*') || request()->routeIs('admin.exports*') ? 'in' : '' }}">
                                    @can('product-list')
                                    <li class="{{ (request()->routeIs('admin.products*') && !request()->routeIs('admin.products.qtyPriceUpdate')) ? 'active' : '' }}">
                                        <a class="waves-effect waves-dark {{ (request()->routeIs('admin.products*') && !request()->routeIs('admin.products.qtyPriceUpdate')) ? 'active' : '' }}" href="{{ route('admin.products.index') }}" aria-expanded="false">{{ __('Products') }}</a>
                                    </li>
                                    @endcan
                                    @can('import-product-list')
                                    <li class="{{ request()->routeIs('admin.import-products*') ? 'active' : '' }}">
                                        <a class="waves-effect waves-dark {{ request()->routeIs('admin.import-products*') ? 'active' : '' }}" href="{{ route('admin.import-products.index') }}" aria-expanded="false">{{ __('Import Products') }}</a>
                                    </li>
                                    @endcan
                                    @can('export-list')
                                    <li class="{{ request()->routeIs('admin.exports*') ? 'active' : '' }}">
                                        <a class="waves-effect waves-dark {{ request()->routeIs('admin.exports*') ? 'active' : '' }}" href="{{ route('admin.exports.index') }}" aria-expanded="false">{{ __('Export Products') }}</a>
                                    </li>
                                    @endcan
                                    @can('product-qty-price-update')
                                    <li class="{{ request()->routeIs('admin.products.qtyPriceUpdate') ? 'active' : '' }}">
                                        <a class="waves-effect waves-dark {{ request()->routeIs('admin.products.qtyPriceUpdate') ? 'active' : '' }}" href="{{ route('admin.products.qtyPriceUpdate') }}" aria-expanded="false">{{ __('Products Qty & Price Update') }}</a>
                                    </li>
                                    @endcan
                                </ul>
                            </li>
                            @endcanany
                            @can('category-list')
                            <li class="{{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                                <a class="waves-effect waves-dark" href="{{ route('admin.categories.index') }}" aria-expanded="false"><i class="fas fa-th-list"></i><span class="hide-menu">{{ __('Categories') }}</span></a>
                            </li>
                            @endcan
                            <!-- @can('manufacturer-list')
                            <li class="{{ (request()->routeIs('admin.manufacturer*') && !request()->routeIs('admin.manufacturer.setting')) ? 'active' : '' }}">
                                <a class="waves-effect waves-dark" href="{{ route('admin.manufacturer.index') }}" aria-expanded="false"><i class="fas fa-industry"></i><span class="hide-menu">{{ __('Manufacturers') }}</span></a>
                            </li>
                            @endcan -->
                            <!-------------------------------------------------------START : Manufacturer section----------------------------------------------------------------- -->
                            @canany(['manufacturer-list', 'merge-manufacturer'])
                            <li>
                                <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fas fa-industry"></i><span class="hide-menu">Manufacturers Management</span></a>
                                    <ul aria-expanded="false" class="collapse">
                                        @can('manufacturer-list')
                                        <li class="{{ (request()->routeIs('admin.manufacturer*') && !request()->routeIs('admin.manufacturer.setting')) ? 'active' : '' }}">
                                            <a class="waves-effect waves-dark" href="{{ route('admin.manufacturer.index') }}" aria-expanded="false"><span class="hide-menu">{{ __('Manufacturers') }}</span></a>
                                        </li>
                                        @endcan
                                        @can('merge-manufacturer')
                                        <li class="{{ (request()->routeIs('admin.manage-manufacturer*') ) ? 'active' : '' }}">
                                            <a href="{{ route('admin.manage-manufacturer.index') }}" class="waves-effect waves-dark" aria-expanded="false"><span class="hide-menu">{{ __('Merge') }}</a>
                                        </li>
                                        @endcan
                                    </ul>
                            </li>
                            @endcanany
                            <!-------------------------------------------------------END : Manufacturer section----------------------------------------------------------------- -->

                            <!--------------------------------------------------------- START : Quotes module--------------------------------------------------------->
                            @can('quote-list')
                                <li class="{{ request()->routeIs('admin.quotes*') ? 'active' : '' }}">
                                    <a class="waves-effect waves-dark" href="{{ route('admin.quotes.index') }}" aria-expanded="false"><i class="ti-quote-left"></i><span class="hide-menu">{{ __('Quotes') }}</span></a>
                                </li>
                            @endcan
                            <!--------------------------------------------------------- END : Quotes module--------------------------------------------------------->

                            @can('order-list')
                                <li class="{{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                                    <a class="waves-effect waves-dark" href="{{ route('admin.orders.index') }}" aria-expanded="false"><i class="ti-quote-left"></i><span class="hide-menu">{{ __('Online Orders') }}</span></a>
                                </li>
                            @endcan
                            @can('contact-inquiries-list')
                                <li class="{{ request()->routeIs('admin.contact-inquiries*') ? 'active' : '' }}">
                                    <a class="waves-effect waves-dark" href="{{ route('admin.contact-inquiries.index') }}" aria-expanded="false"><i class="ti-quote-left"></i><span class="hide-menu">{{ __('Contact Inquiries') }}</span></a>
                                </li>
                            @endcan
                            @canany(['news-list', 'news-categories-list', 'technology-list','news-settings-list'])
                                <li>
                                    <a class="has-arrow waves-effect waves-dark {{ (request()->routeIs('admin.news-categories*') || request()->routeIs('admin.news*') || request()->routeIs('admin.technology*')) ? 'active' : '' }}" href="javascript:void(0)" aria-expanded="false"><i class="ti-world"></i><span class="hide-menu">News Management</span></a>
                                    <ul aria-expanded="false" class="collapse {{ (request()->routeIs('admin.news*') || request()->routeIs('admin.news-categories*') || request()->routeIs('admin.technology*')) ? 'in' : '' }}">
                                        @can('news-list')
                                            <li class="{{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                                                <a class="waves-effect waves-dark {{ request()->routeIs('admin.news.*') ? 'active' : '' }}" href="{{ route('admin.news.index') }}" aria-expanded="false"><i class=""></i><span class="hide-menu">{{ __('News') }}</span></a>
                                            </li>
                                        @endcan
                                        @can('technology-list')
                                            <li class="{{ request()->routeIs('admin.technology*') ? 'active' : '' }}">
                                                <a class="waves-effect waves-dark {{ request()->routeIs('admin.technology*') ? 'active' : '' }}" href="{{ route('admin.technology.index') }}" aria-expanded="false"><i class=""></i><span class="hide-menu">{{ __('Technology') }}</span></a>
                                            </li>
                                        @endcan
                                        @can('news-categories-list')
                                            <li class="{{ request()->routeIs('admin.news-categories*') ? 'active' : '' }}"><a class="{{ request()->routeIs('admin.news-categories*') ? 'active' : '' }}" href="{{ route('admin.news-categories.index') }}">{{ __('News Categories') }}</a></li>
                                        @endcan
                                        @can('news-settings-list')
                                            <li class="{{ request()->routeIs('admin.news-settings*') ? 'active' : '' }}"><a class="{{ request()->routeIs('admin.news-settings*') ? 'active' : '' }}" href="{{ route('admin.news-settings.index') }}">{{ __('Featured News') }}</a></li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcanany
                            @canany(['pages-list'])
                                <li>
                                    <a class="has-arrow waves-effect waves-dark {{ request()->routeIs('admin.pages*') || request()->routeIs('admin.pages*') ? 'active' : '' }}" href="javascript:void(0)" aria-expanded="false"><i class="ti-comment-alt    "></i><span class="hide-menu">Content Management</span></a>
                                    <ul aria-expanded="false" class="collapse {{ request()->routeIs('admin.pages*') || request()->routeIs('admin.pages*') ? 'in' : '' }}">
                                        @can('pages-list')
                                            <li class="{{ request()->routeIs('admin.pages*') ? 'active' : '' }}"><a class="{{ request()->routeIs('admin.pages*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}">{{ __('Pages') }}</a></li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcanany
                            @canany(['user-list', 'role-list'])
                                <li>
                                    <a class="has-arrow waves-effect waves-dark {{ request()->routeIs('admin.users*') || request()->routeIs('admin.roles*') ? 'active' : '' }}" href="javascript:void(0)" aria-expanded="false"><i class="fas fa-users"></i><span class="hide-menu">User Management</span></a>
                                    <ul aria-expanded="false" class="collapse {{ request()->routeIs('admin.users*') || request()->routeIs('admin.roles*') ? 'in' : '' }}">
                                        @can('user-list')
                                            <li class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}"><a class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">{{ __('Users') }}</a></li>
                                        @endcan
                                        @can('role-list')
                                            <li class="{{ request()->routeIs('admin.roles*') ? 'active' : '' }}"><a class="{{ request()->routeIs('admin.roles*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">{{ __('Roles') }}</a></li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcanany
                            @canany(['settings-categories-list','settings-manufacturer-list','currency-list','global-setting-list', 'stripe-setting-list', 'smtp-setting-list'])
                                <li>
                                    <a class="has-arrow waves-effect waves-dark {{ request()->routeIs('admin.settings*') || request()->routeIs('admin.currency*') || request()->routeIs('admin.setting/manufacturer*')  ? 'active' : '' }}" href="javascript:void(0)" aria-expanded="false"><i class="ti-settings"></i><span class="hide-menu">Settings</span></a>
                                    <ul aria-expanded="false" class="collapse {{ request()->routeIs('admin.settings*') ? 'in' : '' }}">
                                        @can('settings-categories-list')
                                            <li class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}"><a class="{{ request()->routeIs('admin.settings.index') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">{{ __('Category Setting') }}</a></li>
                                        @endcan
                                        @can('settings-manufacturer-list')
                                            <li class="{{ request()->routeIs('admin.manufacturer.setting') ? 'active' : '' }}"><a class="{{ request()->routeIs('admin.manufacturer.setting*') ? 'active' : '' }}" href="{{ route('admin.manufacturer.setting') }}">{{ __('Manufacturer Setting') }}</a></li>
                                        @endcan
                                        @can('currency-list')
                                            <li class="{{ request()->routeIs('admin.currency*') ? 'active' : '' }}">
                                                <a class="waves-effect waves-dark" href="{{ route('admin.currency.index') }}" aria-expanded="false"><span class="hide-menu">{{ __('Currency Setting') }}</span></a>
                                            </li>
                                        @endcan
                                        @can('global-setting-list')
                                            <li class="{{ request()->routeIs('admin.global-settings*') ? 'active' : '' }}">
                                                <a class="waves-effect waves-dark" href="{{ route('admin.global-settings.index') }}" aria-expanded="false"><span class="hide-menu">{{ __('Global Setting') }}</span></a>
                                            </li>
                                        @endcan
                                        @can('stripe-setting-list')
                                            <li class="{{ request()->routeIs('admin.stripe-settings*') ? 'active' : '' }}">
                                                <a class="waves-effect waves-dark" href="{{ route('admin.stripe-settings.index') }}" aria-expanded="false"><span class="hide-menu">{{ __('Stripe Setting') }}</span></a>
                                            </li>
                                        @endcan
                                        @can('smtp-setting-list')
                                            <li class="{{ request()->routeIs('admin.smtp-settings*') ? 'active' : '' }}">
                                                <a class="waves-effect waves-dark" href="{{ route('admin.smtp-settings.index') }}" aria-expanded="false"><span class="hide-menu">{{ __('SMTP Setting') }}</span></a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcan
                        </ul>
                    </nav>
                    <!-- End Sidebar navigation -->
                </div>
                <!-- End Sidebar scroll-->
            </aside>
            <!-- ============================================================== -->
            <!-- End Left Sidebar - style you can find in sidebar.scss  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Page wrapper  -->
            <!-- ============================================================== -->
            <div class="page-wrapper">
                <!-- ============================================================== -->
                <!-- Container fluid  -->
                <!-- ============================================================== -->
                <div class="container-fluid">
                    <!-- ============================================================== -->
                    <!-- Bread crumb and right sidebar toggle -->
                    <!-- ============================================================== -->
                    <div class="row page-titles">
                        <div class="col-md-5 align-self-center">
                            <h4 class="text-themecolor">@yield('title')</h4>
                        </div>
                        <div class="col-md-7 align-self-center text-end">
                            <div class="d-flex justify-content-end align-items-center">
                                <ol class="breadcrumb justify-content-end">
                                    @yield('breadcrumb-content')
                                </ol>
                            </div>
                        </div>
                    </div>
                    <!-- ============================================================== -->
                    <!-- End Bread crumb and right sidebar toggle -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    @yield('content')
                </div>
                <!-- ============================================================== -->
                <!-- End Container fluid  -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Page wrapper  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer">
                © {{ date('Y') }} All rights reserved by
                <a href="javascript:void(0)">{{siteName()}}</a>
            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Wrapper -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- All Jquery -->
        <!-- ============================================================== -->
        <script src="{{ asset('assets/admin/node_modules/jquery/dist/jquery.min.js') }}"></script>
        <script type="text/javascript" src="{!! asset('assets/admin/js/jquery.validate.min.js') !!}"></script>
        <!-- Bootstrap tether Core JavaScript -->
        <script src="{{ asset('assets/admin/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
        <!-- slimscrollbar scrollbar JavaScript -->
        <script src="{{ asset('assets/admin/dist/js/perfect-scrollbar.jquery.min.js') }}"></script>
        <!-- Wave Effects -->
        <script src="{{ asset('assets/admin/dist/js/waves.js') }}"></script>
        <!-- Menu sidebar -->
        <script src="{{ asset('assets/admin/dist/js/sidebarmenu.js') }}"></script>
        <!-- Custom JavaScript -->
        <script src="{{ asset('assets/admin/dist/js/custom.js') }}"></script>
        <!-- data table -->
        <script src="{{ asset('assets/admin/node_modules/datatables/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/admin/node_modules/datatables/js/dataTables.responsive.min.js') }}"></script>
        <!-- Popup message jquery -->
        <script src="{{ asset('assets/admin/node_modules/toast-master/js/jquery.toast.js') }}"></script>
        <!-- Sweet-Alert  -->
        <script src="{{ asset('assets/admin/node_modules/sweetalert2/dist/sweetalert2.all.min.js') }}"></script>
        <!-- Bootstrap Icon Picker -->
        <script src="{{ asset('plugins/bootstrap-icon-picker/dist/js/bootstrapicon-iconpicker.js') }}"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.5.1/tinymce.min.js" integrity="sha512-UhysBLt7bspJ0yBkIxTrdubkLVd4qqE4Ek7k22ijq/ZAYe0aadTVXZzFSIwgC9VYnJabw7kg9UMBsiLC77LXyw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>
        var baseUrl = "{!! url('admin') !!}/";
        var pageAction = "{!! (explode(".",request()->route()->getName()))[2] ?? '' !!}";
        </script>
        <script src="{{ asset('assets/admin/js/custom.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <!-- Common toast  -->
        <script type="text/javascript">
        $(document).ready(function () {
            @if (Session::has('error'))
                errorToast('{{ Session::get('error') }}');
            @elseif(Session::has('success'))
                successToast('{{ Session::get('success') }}');
            @endif
        });
        </script>
        @yield('script')
    </body>
</html>
