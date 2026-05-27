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
        'Toastr' => [
            'active' => true,
            'files'  => [
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css',
                ],
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js',
                ],
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
    
        // ── Super Admin Only ──────────────────────
        ['header' => 'SUPER ADMIN', 'can' => 'super-admin'],

        [
            'text'       => 'SA Dashboard',
            'url'        => 'super-admin',
            'icon'       => 'fas fa-fw fa-tachometer-alt',
            'icon_color' => 'blue',
            'can'        => 'super-admin',
        ],
        [
            'text'       => 'ISP List',
            'url'        => 'super-admin/tenants',
            'icon'       => 'fas fa-fw fa-building',
            'icon_color' => 'green',
            'can'        => 'super-admin',
        ],
        [
            'text'       => 'Plans',
            'url'        => 'super-admin/plans',
            'icon'       => 'fas fa-fw fa-tags',
            'icon_color' => 'yellow',
            'can'        => 'super-admin',
        ],
        ['text' => 'SMS Gateways', 'url' => 'super-admin/sms', 'icon' => 'fas fa-fw fa-sms', 'icon_color' => 'green', 'can' => 'super-admin'],
        
        
         // ISP Admin menu তে
        ['text' => 'Dashboard',        'url' => 'dashboard',  'icon' => 'fas fa-fw fa-tachometer-alt', 'icon_color' => 'blue',   'can' => 'isp-admin'],
       
       [
            'text'       => 'MikroTik',
            'icon'       => 'fas fa-fw fa-server',
            'icon_color' => 'red',
            'can'        => 'isp-admin',
            'submenu'    => [
                ['text' => 'Routers',        'url' => 'mikrotik',              'icon' => 'fas fa-fw fa-network-wired'],
                ['text' => 'Active Sessions','url' => 'mikrotik/active',       'icon' => 'fas fa-fw fa-circle text-success'],
                ['text' => 'Import',         'url' => 'import',                'icon' => 'fas fa-fw fa-file-import'],
            ],
        ],        
       // ['header' => 'SETTINGS', 'can' => 'isp-admin'],
        [
            'text' => 'System Settings',
            'icon' => 'fas fa-fw fa-cog',
            'can'  => 'isp-admin',
            'submenu' => [
                ['text' => 'Zone',     'url' => 'settings/zones',     'icon' => 'fas fa-fw fa-map-marked-alt'],
                ['text' => 'Sub Zone', 'url' => 'settings/sub-zones', 'icon' => 'fas fa-fw fa-map-pin'],
                ['text' => 'Connection Type', 'url' => 'settings/connection-types', 'icon' => 'fas fa-fw fa-plug'],
                ['text' => 'Client Type',     'url' => 'settings/client-types',     'icon' => 'fas fa-fw fa-user-tag'],           
                ['text' => 'Protocol Type', 'url' => 'settings/protocol-types', 'icon' => 'fas fa-fw fa-network-wired'],
                ['text' => 'Packages',         'url' => 'packages',   'icon' => 'fas fa-fw fa-box'],
          ],
        ],
        // ── ISP Admin Only ────────────────────────
       // ['header' => 'CUSTOMER MANAGEMENT', 'can' => 'isp-admin'],
        
       
    [
        'text'       => 'Customers',
        'url'        => 'customers',
        'icon'       => 'fas fa-fw fa-users',
        'icon_color' => 'green',
        'can'        => 'isp-admin',
        'submenu'    => [
            [
                'text' => 'All Customers',
                'url'  => 'customers',
                'icon' => 'fas fa-fw fa-list',
            ],
            [
                'text' => 'Add Customer',
                'url'  => 'customers/create',
                'icon' => 'fas fa-fw fa-user-plus',
            ],
            [
                'text' => 'Import Customer',
                'url'  => 'import',
                'icon' => 'fas fa-fw fa-file-import',
            ],
        ],
    ],       
    [
        'text'       => 'Billing',
        'icon'       => 'fas fa-fw fa-file-invoice-dollar',
        'icon_color' => 'orange',
        'can'        => 'isp-admin',
        'submenu'    => [
            [
                'text' => 'Invoices',
                'url'  => 'invoices',
                'icon' => 'fas fa-fw fa-file-invoice',
            ],
            [
                'text' => 'Payments',
                'url'  => 'payments',
                'icon' => 'fas fa-fw fa-money-bill-wave',
            ],
            [
                'text' => 'Due List',
                'url'  => 'reports/due',
                'icon' => 'fas fa-fw fa-exclamation-circle',
            ],
        ],
    ],     
    [
        'text'       => 'Reports',
        'icon'       => 'fas fa-fw fa-chart-bar',
        'icon_color' => 'blue',
        'can'        => 'isp-admin',
        'submenu'    => [
            [
                'text' => 'Revenue',
                'url'  => 'reports/revenue',
                'icon' => 'fas fa-fw fa-chart-line',
            ],
            [
                'text' => 'Collection',
                'url'  => 'reports/customers',
                'icon' => 'fas fa-fw fa-users',
            ],
            [
                'text' => 'SMS Reports',
                'url'  => 'sms/reports',
                'icon' => 'fas fa-fw fa-sms',
            ],
        ],
    ],
    [
        'text'       => 'SMS',
        'icon'       => 'fas fa-fw fa-sms',
        'icon_color' => 'green',
        'can'        => 'isp-admin',
        'submenu'    => [
            [
                'text' => 'Gateway Settings',
                'url'  => 'sms/settings',
                'icon' => 'fas fa-fw fa-server',
            ],
            [
                'text' => 'Templates',
                'url'  => 'sms/templates',
                'icon' => 'fas fa-fw fa-file-alt',
            ],                
            [
                'text' => 'Send SMS',
                'url'  => 'sms',
                'icon' => 'fas fa-fw fa-paper-plane',
            ],
            [
                'text' => 'SMS Reports',
                'url'  => 'sms/reports',
                'icon' => 'fas fa-fw fa-chart-bar',
            ],
        ],
    ],    
    
        ['header' => 'RESELLER', 'can' => 'create-reseller'],
        [
            'text'       => 'My Resellers',
            'url'        => 'my-resellers',
            'icon'       => 'fas fa-fw fa-sitemap',
            'icon_color' => 'orange',
            'can'        => 'create-reseller',
        ],
       
        ['header' => 'SUPPORT', 'can' => 'isp-admin'],
        ['text' => 'Tickets', 'url' => 'tickets', 'icon' => 'fas fa-fw fa-ticket-alt', 'icon_color' => 'red', 'can' => 'isp-admin'],

        
        ['header' => 'MANAGEMENT', 'can' => 'isp-admin'],
        ['text' => 'Agents',    'url' => 'agents',    'icon' => 'fas fa-fw fa-user-tie', 'icon_color' => 'yellow', 'can' => 'isp-admin'],
        ['text' => 'Inventory', 'url' => 'inventory', 'icon' => 'fas fa-fw fa-boxes',    'icon_color' => 'brown',  'can' => 'isp-admin'],
      //  ['text' => 'SMS',       'url' => 'sms',       'icon' => 'fas fa-fw fa-sms',      'icon_color' => 'green',  'can' => 'isp-admin'],
        [
            'text'       => 'SMS',
            'icon'       => 'fas fa-fw fa-sms',
            'icon_color' => 'green',
            'can'        => 'isp-admin',
            'submenu'    => [
                [
                    'text' => 'Gateway Settings',
                    'url'  => 'sms/settings',
                    'icon' => 'fas fa-fw fa-server',
                ],
                [
                    'text' => 'Templates',
                    'url'  => 'sms/templates',
                    'icon' => 'fas fa-fw fa-file-alt',
                ],                
                [
                    'text' => 'Send SMS',
                    'url'  => 'sms',
                    'icon' => 'fas fa-fw fa-paper-plane',
                ],
                [
                    'text' => 'SMS Reports',
                    'url'  => 'sms/reports',
                    'icon' => 'fas fa-fw fa-chart-bar',
                ],
            ],
        ],
        ['header' => 'REPORTS', 'can' => 'isp-admin'],
        [
            'text'       => 'Reports',
            'icon'       => 'fas fa-fw fa-chart-bar',
            'icon_color' => 'blue',
            'can'        => 'isp-admin',
            'submenu'    => [
                ['text' => 'Revenue',   'url' => 'reports/revenue',   'icon' => 'fas fa-fw fa-dollar-sign', 'icon_color' => 'green'],
                ['text' => 'Due',       'url' => 'reports/due',       'icon' => 'fas fa-fw fa-exclamation', 'icon_color' => 'red'],
                ['text' => 'Customers', 'url' => 'reports/customers', 'icon' => 'fas fa-fw fa-users',       'icon_color' => 'blue'],
            ],
        ],
    ],

];
