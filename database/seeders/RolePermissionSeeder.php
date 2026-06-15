<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * RolePermissionSeeder
 * ─────────────────────────────────────────────
 * Seeds all roles and their permissions for the ISP system.
 * Run with: php artisan db:seed --class=RolePermissionSeeder
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Define all permissions ──────────────────────────────
        $permissions = [

            // Core access
            'isp-admin',
            'super-admin',
            'create-reseller',

            // Customer
            'customer.view',
            'customer.create',
            'customer.edit',
            'customer.delete',
            'customer.suspend',

            // Payment / Billing
            'payment.view',
            'payment.collect',
            'payment.void',
            'invoice.view',
            'invoice.create',

            // Packages
            'package.view',
            'package.manage',

            // MikroTik
            'mikrotik.view',
            'mikrotik.manage',

            // Reports
            'report.revenue',
            'report.due',
            'report.customers',

            // Tickets
            'ticket.view',
            'ticket.manage',

            // Settings
            'settings.manage',

            // Users
            'user.manage',

            // Agents
            'agent.view',
            'agent.manage',

            // Inventory
            'inventory.view',
            'inventory.manage',

            // HR / Payroll
            'hr.view',
            'hr.manage',

            // Accounting
            'accounting.view',
            'accounting.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── 2. Define roles with their permissions ─────────────────

        $roleMap = [

            // Super Admin — system-wide
            'super-admin' => ['super-admin'],

            // ISP Admin — full access within tenant
            'isp-admin' => ['isp-admin', 'create-reseller',
                'customer.view','customer.create','customer.edit','customer.delete','customer.suspend',
                'payment.view','payment.collect','payment.void','invoice.view','invoice.create',
                'package.view','package.manage',
                'mikrotik.view','mikrotik.manage',
                'report.revenue','report.due','report.customers',
                'ticket.view','ticket.manage',
                'settings.manage','user.manage',
                'agent.view','agent.manage',
                'inventory.view','inventory.manage',
                'hr.view','hr.manage',
                'accounting.view','accounting.manage',
            ],

            // Manager — all except system settings
            'manager' => [
                'customer.view','customer.create','customer.edit','customer.delete','customer.suspend',
                'payment.view','payment.collect','payment.void','invoice.view','invoice.create',
                'package.view',
                'mikrotik.view',
                'report.revenue','report.due','report.customers',
                'ticket.view','ticket.manage',
                'agent.view',
                'inventory.view','inventory.manage',
                'hr.view',
                'accounting.view',
            ],

            // Staff — day-to-day customer & billing only
            'staff' => [
                'customer.view','customer.create','customer.edit','customer.suspend',
                'payment.view','payment.collect','invoice.view','invoice.create',
                'package.view',
                'ticket.view','ticket.manage',
                'inventory.view',
            ],

            // Agent — own customers & commissions only
            'agent' => [
                'customer.view','customer.create',
                'invoice.view',
            ],

            // Accountant — finance only
            'accountant' => [
                'payment.view','payment.collect','payment.void',
                'invoice.view','invoice.create',
                'report.revenue','report.due',
                'accounting.view','accounting.manage',
                'customer.view',
            ],

            // Support — tickets & customer info only
            'support' => [
                'customer.view',
                'ticket.view','ticket.manage',
            ],
        ];

        foreach ($roleMap as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
            $this->command->info("Role '{$roleName}' → " . count($perms) . " permissions");
        }

        $this->command->info('✅ Roles & Permissions seeded successfully.');
    }
}
