<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [

            // ── Dashboard ──────────────────────────────
            'dashboard.view',

            // ── MikroTik ───────────────────────────────
            'mikrotik.view',
            'mikrotik.create',
            'mikrotik.edit',
            'mikrotik.delete',
            'mikrotik.sync',
            'mikrotik.session.view',
            'mikrotik.import.customer',

            // ── OLT ────────────────────────────────────
            'olt.view',
            'olt.create',
            'olt.edit',
            'olt.delete',
            'olt.user.view',
            'olt.user.create',
            'olt.user.edit',
            'olt.user.delete',

            // ── Packages ───────────────────────────────
            'package.view',
            'package.create',
            'package.edit',
            'package.delete',
            'package.mikrotik.sync',

            // ── Customers ──────────────────────────────
            'customer.view',
            'customer.create',
            'customer.edit',
            'customer.delete',
            'customer.suspend',
            'customer.import.view',

            // ── Billing — Invoice ──────────────────────
            'invoice.view',
            'invoice.create',
            'invoice.delete',
            'invoice.bulk',

            // ── Billing — Payment ──────────────────────
            'payment.view',
            'payment.collect',
            'payment.void',

            // ── Accounting ─────────────────────────────
            'accounting.view',
            'accounting.income.view',
            'accounting.income.create',
            'accounting.income.edit',
            'accounting.income.delete',
            'accounting.income.void',
            'accounting.expense.view',
            'accounting.expense.create',
            'accounting.expense.edit',
            'accounting.expense.delete',
            'accounting.expense.void',
            'accounting.expense.approve',
            'accounting.income.category.view',
            'accounting.income.category.create',
            'accounting.income.category.edit',
            'accounting.income.category.delete',
            'accounting.expense.category.view',
            'accounting.expense.category.create',
            'accounting.expense.category.edit',
            'accounting.expense.category.delete',
            'accounting.report.view',

            // ── SMS ────────────────────────────────────
            'sms.view',
            'sms.send',
            'sms.gateway.view',
            'sms.gateway.manage',
            'sms.template.view',
            'sms.template.create',
            'sms.template.edit',
            'sms.template.delete',
            'sms.report.view',

            // ── Support & Ticketing ────────────────────
            'support.category.view',
            'support.category.create',
            'support.category.edit',
            'support.category.delete',
            'support.client.view',
            'support.client.create',
            'support.client.edit',
            'support.client.delete',
            'support.history.view',

            // ── HR & Payroll ───────────────────────────
            'hr.department.view',
            'hr.department.create',
            'hr.department.edit',
            'hr.department.delete',
            'hr.position.view',
            'hr.position.create',
            'hr.position.edit',
            'hr.position.delete',
            'hr.salary.head.view',
            'hr.salary.head.create',
            'hr.salary.head.edit',
            'hr.salary.head.delete',
            'hr.employee.view',
            'hr.employee.create',
            'hr.employee.edit',
            'hr.employee.delete',
            'hr.payroll.view',
            'hr.payroll.create',
            'hr.payroll.manage',
            'hr.leave.type.view',
            'hr.leave.type.create',
            'hr.leave.type.edit',
            'hr.leave.type.delete',
            'hr.leave.view',
            'hr.leave.create',
            'hr.leave.edit',
            'hr.leave.delete',
            'hr.leave.approve',
            'hr.salary.advance.view',
            'hr.salary.advance.create',
            'hr.salary.advance.edit',
            'hr.salary.advance.delete',
            'hr.salary.advance.approve',

            // ── Bandwidth Buy ──────────────────────────
            'bandwidth.provider.view',
            'bandwidth.provider.create',
            'bandwidth.provider.edit',
            'bandwidth.provider.delete',
            'bandwidth.service.view',
            'bandwidth.service.create',
            'bandwidth.service.edit',
            'bandwidth.service.delete',
            'bandwidth.purchase.view',
            'bandwidth.purchase.create',
            'bandwidth.purchase.edit',
            'bandwidth.purchase.void',
            'bandwidth.report.view',

            // ── Bandwidth Sale ─────────────────────────
            'bandwidth.sale.view',
            'bandwidth.sale.customer.view',
            'bandwidth.sale.customer.create',
            'bandwidth.sale.customer.edit',
            'bandwidth.sale.customer.delete',
            'bandwidth.sale.invoice.view',
            'bandwidth.sale.invoice.create',
            'bandwidth.sale.invoice.edit',
            'bandwidth.sale.invoice.delete',
            'bandwidth.sale.daily.bill.view',
            'bandwidth.sale.recurring.view',
            'bandwidth.sale.recurring.create',
            'bandwidth.sale.recurring.edit',
            'bandwidth.sale.recurring.delete',

            // ── Reports ────────────────────────────────
            'report.revenue.view',
            'report.collection.view',
            'report.sms.view',

            // ── Agents ─────────────────────────────────
            'agent.view',
            'agent.create',
            'agent.edit',
            'agent.delete',

            // ── User Management ────────────────────────
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'user.suspend',

            // ── Role Management ────────────────────────
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',

            // ── Settings ───────────────────────────────
            'settings.manage',
        ];

        // Insert all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('Permissions created: ' . count($permissions));

        // Super Admin role
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(['super-admin']);

        // ISP Admin role — gets ALL permissions
        $ispAdmin = Role::firstOrCreate(['name' => 'isp-admin', 'guard_name' => 'web']);
        $ispAdmin->syncPermissions($permissions);

        $this->command->info('isp-admin assigned ' . count($permissions) . ' permissions.');
        $this->command->info('Done! Run: php artisan permission:cache-reset');
    }
}
