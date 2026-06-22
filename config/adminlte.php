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
            'active' => true,
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
        [
            'text'       => 'Permissions',
            'url'        => 'super-admin/permissions',
            'icon'       => 'fas fa-fw fa-key',
            'icon_color' => 'orange',
            'can'        => 'super-admin',
        ],
        [
            'text'       => 'ISP Admin Permissions',
            'url'        => 'super-admin/roles/isp-admin',
            'icon'       => 'fas fa-fw fa-user-shield',
            'icon_color' => 'red',
            'can'        => 'super-admin',
        ],
        
        
         // ISP Admin menu তে
        ['text' => 'Dashboard', 'url' => 'dashboard', 'icon' => 'fas fa-fw fa-tachometer-alt', 'icon_color' => 'blue', 'can' => 'dashboard.view'],

        [
            'text'       => 'MikroTik',
            'icon'       => 'fas fa-fw fa-server',
            'icon_color' => 'red',
            'can'        => 'mikrotik.view',
            'submenu'    => [
                ['text' => 'Routers',        'url' => 'mikrotik', 'icon' => 'fas fa-fw fa-network-wired', 'can' => 'mikrotik.view'],
                ['text' => 'Active Sessions','url' => 'mikrotik/active-sessions', 'icon' => 'fas fa-fw fa-circle text-success', 'can' => 'mikrotik.session.view'],
                ['text' => 'Import',         'url' => 'import',                'icon' => 'fas fa-fw fa-file-import'],
            ],
        ],

        // ── OLT Management ────────────────────────
        [
            'text'       => 'OLT Management',
            'icon'       => 'fas fa-fw fa-network-wired',
            'icon_color' => 'cyan',
            'can'        => 'olt.view',
            'submenu'    => [
                ['text' => 'OLT',       'url' => 'olt',       'icon' => 'fas fa-fw fa-server', 'can' => 'olt.view'],
                ['text' => 'OLT Users', 'url' => 'olt/users', 'icon' => 'fas fa-fw fa-users',  'can' => 'olt.user.view'],
            ],
        ],        
       // ['header' => 'SETTINGS', 'can' => 'isp-admin'],

        // ── ISP Admin Only ────────────────────────
       // ['header' => 'CUSTOMER MANAGEMENT', 'can' => 'isp-admin'],
    [
    'text'       => 'Packages',
    'icon'       => 'fas fa-fw fa-box',
    'icon_color' => 'purple',
    'can'        => 'package.view',
    'submenu'    => [
        [
            'text' => 'All Packages',
            'url'  => 'packages',
            'icon' => 'fas fa-fw fa-list',
        ],
        [
            'text' => 'Sync from mikrotik',
            'url'  => 'packages/sync',
            'icon' => 'fas fa-sync mr-1',
        ],
    ],
],        
       
    [
        'text'       => 'Customers',
        'url'        => 'customers',
        'icon'       => 'fas fa-fw fa-users',
        'icon_color' => 'green',
        'can'        => 'customer.view',
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
        'can'        => 'invoice.view',
        'submenu'    => [
            [
                'text' => 'Invoices',
                'url'  => 'invoices',
                'icon' => 'fas fa-fw fa-file-invoice',
            ],
            [
                'text' => 'Collect Payment',
                'url'  => 'payments/collect',
                'icon' => 'fas fa-fw fa-hand-holding-usd',
            ],
            [
                'text' => 'Payments History',
                'url'  => 'payments',
                'icon' => 'fas fa-fw fa-money-bill-wave',
            ],
        ],
    ],

    // ── Accounting Module ─────────────────────
    [
        'text'       => 'Accounting',
        'icon'       => 'fas fa-fw fa-book',
        'icon_color' => 'teal',
        'can'        => 'accounting.view',
        'submenu'    => [
            [
                'text' => 'Dashboard',
                'url'  => 'accounting/dashboard',
                'icon' => 'fas fa-fw fa-tachometer-alt',
            ],
            [
                'text' => 'Income',
                'url'  => 'incomes',
                'icon' => 'fas fa-fw fa-arrow-circle-up',
            ],
            [
                'text' => 'Expense',
                'url'  => 'expenses',
                'icon' => 'fas fa-fw fa-arrow-circle-down',
            ],
            [
                'text' => 'Profit & Loss',
                'url'  => 'expenses/reports/profit-loss',
                'icon' => 'fas fa-fw fa-chart-pie',
            ],
            [
                'text' => 'Income Categories',
                'url'  => 'income-categories',
                'icon' => 'fas fa-fw fa-tags',
            ],
            [
                'text' => 'Expense Categories',
                'url'  => 'expense-categories',
                'icon' => 'fas fa-fw fa-tags',
            ],
        ],
    ],

    [
        'text'       => 'Reports',
        'icon'       => 'fas fa-fw fa-chart-bar',
        'icon_color' => 'blue',
        'can'        => 'report.revenue.view',
        'submenu'    => [
            ['text' => 'Bill Collection', 'url' => 'reports/bill/receive-history', 'icon' => 'fas fa-fw fa-receipt', 'icon_color' => 'green'],
            ['text' => 'Monthly Billing', 'url' => 'reports/bill/monthly-billing', 'icon' => 'fas fa-fw fa-calendar-alt', 'icon_color' => 'blue'],
            [
                'text' => 'Daily Collection',    
                'url' => 'reports/bill/daily-collection', 
                'icon' => 'fas fa-fw fa-cash-register',   
                'icon_color' => 'green'
            ],
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
        'can'        => 'sms.view',
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
    

        [
            'text'       => 'Support & Ticketing',
            'icon'       => 'fas fa-fw fa-headset',
            'icon_color' => 'red',
            'can'        => 'support.client.view',
            'submenu'    => [
                [
                    'text' => 'Support Category',
                    'url'  => 'support-categories',
                    'icon' => 'fas fa-fw fa-tags',
                ],
                [
                    'text' => 'Client Support',
                    'url'  => 'client-support',
                    'icon' => 'fas fa-fw fa-ticket-alt',
                ],
                [
                    'text' => 'Support History',
                    'url'  => 'support-history',
                    'icon' => 'fas fa-fw fa-history',
                ],
            ],
        ],
        [
            'text' => 'System Settings',
            'icon' => 'fas fa-fw fa-cog',
            'can'  => 'settings.manage',
            'submenu' => [
                ['text' => 'General Settings', 'url' => 'settings/general', 'icon' => 'fas fa-fw fa-sliders-h'],
                ['text' => 'Zone',     'url' => 'settings/zones',     'icon' => 'fas fa-fw fa-map-marked-alt'],
                ['text' => 'Sub Zone', 'url' => 'settings/sub-zones', 'icon' => 'fas fa-fw fa-map-pin'],
                ['text' => 'Box',      'url' => 'settings/box',       'icon' => 'fas fa-fw fa-box'],                ['text' => 'Connection Type', 'url' => 'settings/connection-types', 'icon' => 'fas fa-fw fa-plug'],
                ['text' => 'Client Type',     'url' => 'settings/client-types',     'icon' => 'fas fa-fw fa-user-tag'],           
                ['text' => 'Protocol Type', 'url' => 'settings/protocol-types', 'icon' => 'fas fa-fw fa-network-wired'],
                ['text' => 'OLT Type',      'url' => 'settings/olt-types',      'icon' => 'fas fa-fw fa-server'],
          ],
        ],

        [
            'text'       => 'HR & Payroll',
            'icon'       => 'fas fa-fw fa-user-tie',
            'icon_color' => 'blue',
            'can'        => 'hr.employee.view',
            'submenu'    => [
                [
                    'text' => 'Departments',
                    'url'  => 'departments',
                    'icon' => 'fas fa-fw fa-building',
                ],
                [
                    'text' => 'Positions',
                    'url'  => 'positions',
                    'icon' => 'fas fa-fw fa-briefcase',
                ],
                [
                    'text' => 'Salary Heads',
                    'url'  => 'salary-heads',
                    'icon' => 'fas fa-fw fa-list',
                ],
                [
                    'text' => 'Employee List',
                    'url'  => 'employees',
                    'icon' => 'fas fa-fw fa-users',
                ],
                [
                    'text' => 'Add Employee',
                    'url'  => 'employees/create',
                    'icon' => 'fas fa-fw fa-user-plus',
                ],
                [
                    'text' => 'Payroll',
                    'url'  => 'payroll',
                    'icon' => 'fas fa-fw fa-money-bill-wave',
                ],
                [
                    'text' => 'Leave Types',
                    'url'  => 'leave/types',
                    'icon' => 'fas fa-fw fa-calendar-times',
                ],
                [
                    'text' => 'Leave Applications',
                    'url'  => 'leave',
                    'icon' => 'fas fa-fw fa-calendar-check',
                ],
                [
                    'text' => 'Salary Advance',
                    'url'  => 'salary-advance',
                    'icon' => 'fas fa-fw fa-hand-holding-usd',
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

        [
            'text'       => 'Bandwidth Buy',
            'icon'       => 'fas fa-fw fa-wifi',
            'icon_color' => 'blue',
            'can'        => 'bandwidth.provider.view',
            'submenu'    => [
                [
                    'text' => 'Provider',
                    'url'  => 'bandwidth-buy/provider',
                    'icon' => 'fas fa-fw fa-building',
                ],
                [
                    'text' => 'Service',
                    'url'  => 'bandwidth-buy/service',
                    'icon' => 'fas fa-fw fa-network-wired',
                ],
                [
                    'text' => 'Purchase Bill',
                    'url'  => 'bandwidth-buy/purchase',
                    'icon' => 'fas fa-fw fa-file-invoice-dollar',
                ],
                [
                    'text' => 'Purchase Report',
                    'url'  => 'bandwidth-buy/report',
                    'icon' => 'fas fa-fw fa-chart-bar',
                ],
            ],
        ],        

        // ── Bandwidth Sale Module ─────────────────
        [
            'text'       => 'Bandwidth Sale',
            'icon'       => 'fas fa-fw fa-satellite-dish',
            'icon_color' => 'cyan',
            'can'        => 'bandwidth.sale.view',
            'submenu'    => [
                [
                    'text' => 'Dashboard',
                    'url'  => 'bandwidth-sale/dashboard',
                    'icon' => 'fas fa-fw fa-tachometer-alt',
                ],
                [
                    'text' => 'Customers',
                    'url'  => 'bandwidth-sale/customers',
                    'icon' => 'fas fa-fw fa-users',
                ],
                [
                    'text' => 'Invoices',
                    'url'  => 'bandwidth-sale/invoices',
                    'icon' => 'fas fa-fw fa-file-invoice',
                ],
                [
                    'text' => 'Create Invoice',
                    'url'  => 'bandwidth-sale/invoices/create',
                    'icon' => 'fas fa-fw fa-plus-circle',
                ],
                [
                    'text' => 'Daily Bill',
                    'url'  => 'bandwidth-sale/daily-bill',
                    'icon' => 'fas fa-fw fa-calendar-day',
                ],
                [
                    'text' => 'Recurring Invoice',
                    'url'  => 'bandwidth-sale/recurring',
                    'icon' => 'fas fa-fw fa-redo-alt',
                ],
            ],
        ],

        ['header' => 'MANAGEMENT', 'can' => 'user.view'],
        [
            'text'       => 'User Management',
            'icon'       => 'fas fa-fw fa-users-cog',
            'icon_color' => 'indigo',
            'can'        => 'user.view',
            'submenu'    => [
                ['text' => 'All Users', 'url' => 'users',        'icon' => 'fas fa-fw fa-list'],
                ['text' => 'Add User',  'url' => 'users/create', 'icon' => 'fas fa-fw fa-user-plus'],
            ],
        ],
        [
            'text'       => 'Role Management',
            'icon'       => 'fas fa-fw fa-user-tag',
            'icon_color' => 'orange',
            'can'        => 'role.view',
            'submenu'    => [
                ['text' => 'All Roles', 'url' => 'roles',        'icon' => 'fas fa-fw fa-list'],
                ['text' => 'New Role',  'url' => 'roles/create', 'icon' => 'fas fa-fw fa-plus'],
            ],
        ],
        ['text' => 'Agents',    'url' => 'agents',    'icon' => 'fas fa-fw fa-user-tie', 'icon_color' => 'yellow', 'can' => 'agent.view'],
        ['text' => 'Inventory', 'url' => 'inventory', 'icon' => 'fas fa-fw fa-boxes',    'icon_color' => 'brown',  'can' => 'isp-admin'],  // inventory permission not defined yet
      //  ['text' => 'SMS',       'url' => 'sms',       'icon' => 'fas fa-fw fa-sms',      'icon_color' => 'green',  'can' => 'isp-admin'],
        [
            'text'       => 'SMS',
            'icon'       => 'fas fa-fw fa-sms',
            'icon_color' => 'green',
            'can'        => 'sms.view',
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
  
    ],

];
