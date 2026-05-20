<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */

    'title'         => 'ISP Management',
    'title_prefix'  => '',
    'title_postfix' => ' | ISP',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    */

    'use_ico_only'      => false,
    'use_full_favicon'  => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    */

    'logo'         => '<b>ISP</b> Management',
    'logo_img'     => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class'  => 'brand-image img-circle elevation-3',
    'logo_img_xl'     => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt'    => 'ISP Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    */

    'auth_logo' => [
        'enabled' => false,
        'img'     => [
            'path'   => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt'    => 'ISP Logo',
            'class'  => '',
            'width'  => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    */

    'preloader' => [
        'enabled' => true,
        'mode'    => 'fullscreen',
        'img'     => [
            'path'   => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt'    => 'ISP Logo',
            'effect' => 'animation__wobble',
            'width'  => '60',
            'height' => '60',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu (Top Right)
    |--------------------------------------------------------------------------
    */

    'usermenu_enabled'      => true,
    'usermenu_image'        => false,
    'usermenu_desc'         => '',
    'usermenu_profile_url'  => 'dashboard',  // No profile route — redirect to dashboard

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */

    'layout_topnav'             => null,
    'layout_boxed'              => null,
    'layout_fixed_sidebar'      => true,
    'layout_fixed_navbar'       => true,
    'layout_fixed_footer'       => null,
    'layout_dark_mode'          => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    */

    'classes_auth_card'             => 'card-outline card-primary',
    'classes_auth_header'           => '',
    'classes_auth_body'             => '',
    'classes_auth_footer'           => '',
    'classes_auth_icon'             => '',
    'classes_auth_btn'              => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    */

    'classes_body'               => '',
    'classes_brand'              => '',
    'classes_brand_text'         => '',
    'classes_content_wrapper'    => '',
    'classes_content_header'     => '',
    'classes_content'            => '',
    'classes_sidebar'            => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav'        => '',
    'classes_topnav'             => 'navbar-white navbar-light',
    'classes_topnav_nav'         => 'navbar-expand',
    'classes_topnav_container'   => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    */

    'sidebar_mini'              => 'lg',
    'sidebar_collapse'          => false,
    'sidebar_collapse_auto_size'=> false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme'   => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion'     => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    */

    'right_sidebar'                  => false,
    'right_sidebar_icon'             => 'fas fa-cogs',
    'right_sidebar_theme'            => 'dark',
    'right_sidebar_slide'            => true,
    'right_sidebar_push'             => true,
    'right_sidebar_scrollbar_theme'  => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */

    'use_route_url' => false,
    'dashboard_url' => 'dashboard',
    'logout_url'    => 'logout',
    'logout_method' => 'POST',
    'login_url'     => 'login',
    'register_url'  => false,  // Disable public registration

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    */

    'enabled_laravel_mix'         => false,
    'laravel_mix_css_path'        => 'css/app.css',
    'laravel_mix_js_path'         => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins
    |--------------------------------------------------------------------------
    */

    'plugins' => [

        'Datatables' => [
            'active' => false,
            'files'  => [
                ['type' => 'js',  'asset' => true, 'location' => 'vendor/datatables/js/jquery.dataTables.min.js'],
                ['type' => 'js',  'asset' => true, 'location' => 'vendor/datatables/js/dataTables.bootstrap4.min.js'],
                ['type' => 'css', 'asset' => true, 'location' => 'vendor/datatables/css/dataTables.bootstrap4.min.css'],
            ],
        ],

        'Select2' => [
            'active' => false,
            'files'  => [
                ['type' => 'js',  'asset' => true, 'location' => 'vendor/select2/js/select2.full.min.js'],
                ['type' => 'css', 'asset' => true, 'location' => 'vendor/select2/css/select2.min.css'],
                ['type' => 'css', 'asset' => true, 'location' => 'vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css'],
            ],
        ],

        'Chartjs' => [
            'active' => false,
            'files'  => [
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdn.jsdelivr.net/npm/chart.js'],
            ],
        ],

        'Sweetalert2' => [
            'active' => false,
            'files'  => [
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11'],
            ],
        ],

        'Pace' => [
            'active' => false,
            'files'  => [
                ['type' => 'css', 'asset' => false, 'location' => 'https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css'],
                ['type' => 'js',  'asset' => false, 'location' => 'https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js'],
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    */

    'iframe' => [
        'default_tab' => ['url' => null, 'title' => null],
        'buttons'     => ['tabCloseAll', 'tabCloseAllOther', 'tabScrollLeft', 'tabScrollRight', 'tabFullscreen'],
        'options'     => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    */

    'livewire' => false,

    /*
    |--------------------------------------------------------------------------
    | Navigation Menu
    |--------------------------------------------------------------------------
    */

    'menu' => [

        // ── Dashboard ─────────────────────────────────────
        [
            'text'        => 'Dashboard',
            'url'         => 'dashboard',
            'icon'        => 'fas fa-fw fa-tachometer-alt',
            'icon_color'  => 'blue',
        ],
        [
            'text'        => 'Super Admin',
            'icon'        => 'fas fa-fw fa-crown',
            'can'         => 'super-admin', // শুধু super-admin role দেখবে
            'submenu'     => [
                ['text' => 'Dashboard', 'url' => 'super-admin'],
                ['text' => 'ISP List',  'url' => 'super-admin/tenants'],
                ['text' => 'Plans',     'url' => 'super-admin/plans'],
            ],
        ],
        // ── Customer Management ───────────────────────────
        ['header' => 'CUSTOMER MANAGEMENT'],

        [
            'text'       => 'Customers',
            'url'        => 'customers',
            'icon'       => 'fas fa-fw fa-users',
            'icon_color' => 'teal',
            'label'      => '',
        ],
        [
            'text'       => 'Import Customers',
            'url'        => 'import',
            'icon'       => 'fas fa-fw fa-file-import',
            'icon_color' => 'teal',
        ],
        [
            'text'       => 'Packages',
            'url'        => 'packages',
            'icon'       => 'fas fa-fw fa-box',
            'icon_color' => 'purple',
        ],

        // ── Billing ───────────────────────────────────────
        ['header' => 'BILLING'],

        [
            'text'       => 'Invoices',
            'url'        => 'invoices',
            'icon'       => 'fas fa-fw fa-file-invoice',
            'icon_color' => 'orange',
        ],

        [
            'text'       => 'Payments',
            'url'        => 'payments',
            'icon'       => 'fas fa-fw fa-money-bill-wave',
            'icon_color' => 'green',
        ],

        // ── Support ───────────────────────────────────────
        ['header' => 'SUPPORT'],

        [
            'text'       => 'Tickets',
            'url'        => 'tickets',
            'icon'       => 'fas fa-fw fa-ticket-alt',
            'icon_color' => 'red',
        ],

        // ── Network ───────────────────────────────────────
        ['header' => 'NETWORK'],

        [
            'text'       => 'MikroTik Routers',
            'url'        => 'mikrotik',
            'icon'       => 'fas fa-fw fa-network-wired',
            'icon_color' => 'blue',
        ],

        // ── Management ────────────────────────────────────
        ['header' => 'MANAGEMENT'],

        [
            'text'       => 'Agents',
            'url'        => 'agents',
            'icon'       => 'fas fa-fw fa-user-tie',
            'icon_color' => 'indigo',
        ],

        [
            'text'       => 'Inventory',
            'url'        => 'inventory',
            'icon'       => 'fas fa-fw fa-warehouse',
            'icon_color' => 'brown',
        ],
        [
            'text'       => 'SMS Management',
            'url'        => 'sms',
            'icon'       => 'fas fa-fw fa-sms',
            'icon_color' => 'green',
        ],
        // ── Reports ───────────────────────────────────────
        ['header' => 'REPORTS'],

        [
            'text'    => 'Reports',
            'icon'    => 'fas fa-fw fa-chart-bar',
            'submenu' => [
                [
                    'text' => 'Revenue Report',
                    'url'  => 'reports/revenue',
                    'icon' => 'fas fa-fw fa-coins',
                ],
                [
                    'text' => 'Outstanding Dues',
                    'url'  => 'reports/due',
                    'icon' => 'fas fa-fw fa-exclamation-circle',
                ],
                [
                    'text' => 'Customer Report',
                    'url'  => 'reports/customers',
                    'icon' => 'fas fa-fw fa-users',
                ],
            ],
        ],

    ],

];
