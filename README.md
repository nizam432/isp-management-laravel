# ISP Management System

A complete multi-level ISP Management System for managing Internet Service Providers, direct customers, POPs, resellers, billing, accounting, network infrastructure, support, SMS, bandwidth, HR and operational activities.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [System Architecture](#2-system-architecture)
3. [User Hierarchy](#3-user-hierarchy)
4. [Super Admin](#4-super-admin)
5. [ISP Admin](#5-isp-admin)
6. [POP / Reseller](#6-pop--reseller)
7. [Customer Structure](#7-customer-structure)
8. [Access & Permission Model](#8-access--permission-model)
9. [Dashboard](#9-dashboard)
10. [Customer Management](#10-customer-management)
11. [Package Management](#11-package-management)
12. [Billing Management](#12-billing-management)
13. [Accounting](#13-accounting)
14. [MikroTik Management](#14-mikrotik-management)
15. [OLT Management](#15-olt-management)
16. [Bandwidth Management](#16-bandwidth-management)
17. [SMS Service](#17-sms-service)
18. [Payment Gateway](#18-payment-gateway)
19. [SMS Gateway](#19-sms-gateway)
20. [Support & Ticketing](#20-support--ticketing)
21. [HR & Payroll](#21-hr--payroll)
20. [Inventory](#20-inventory)
21. [Reports](#21-reports)
22. [Fund / Reseller Management](#22-fund--reseller-management)
23. [Customer Lifecycle](#23-customer-lifecycle)
24. [Billing Flow](#24-billing-flow)
25. [Fund Flow](#25-fund-flow)
26. [Data Ownership](#26-data-ownership)
27. [Multi-Tenant Architecture](#27-multi-tenant-architecture)
28. [Database Relationship Concept](#28-database-relationship-concept)
29. [Security](#29-security)
30. [Installation](#30-installation)
31. [Environment Configuration](#31-environment-configuration)
32. [Production Deployment](#32-production-deployment)
33. [Cron Jobs & Queue](#33-cron-jobs--queue)
34. [Backup](#34-backup)
35. [Troubleshooting](#35-troubleshooting)
36. [Recommended Development Standards](#36-recommended-development-standards)
37. [Future Improvements](#37-future-improvements)

---

# 1. Project Overview

The ISP Management System is designed to support multiple ISP businesses from a centralized platform.

The application supports three primary administrative levels:

- **Super Admin**
- **ISP Admin**
- **POP / Reseller**

Customers can exist in two ways:

1. Directly under an ISP Admin
2. Under a POP / Reseller belonging to an ISP Admin

Therefore, the main business hierarchy is:

```text
Super Admin
    │
    ├── ISP Admin
    │      │
    │      ├── Direct Customers
    │      │
    │      ├── POP / Reseller
    │      │       ├── Customers
    │      │       └── Customers
    │      │
    │      └── POP / Reseller
    │              └── Customers
    │
    ├── ISP Admin
    │      ├── Direct Customers
    │      └── POP / Reseller
    │              └── Customers
    │
    └── ISP Admin
           └── Direct Customers
```

---

# 2. System Architecture

```text
                         ┌───────────────────────┐
                         │      SUPER ADMIN      │
                         │   Platform Control    │
                         └───────────┬───────────┘
                                     │
                  ┌──────────────────┼──────────────────┐
                  │                  │                  │
           ┌──────▼──────┐    ┌──────▼──────┐    ┌──────▼──────┐
           │  ISP ADMIN  │    │  ISP ADMIN  │    │  ISP ADMIN  │
           │    ISP A    │    │    ISP B    │    │    ISP C    │
           └──────┬──────┘    └──────┬──────┘    └──────┬──────┘
                  │                  │                  │
          ┌───────┴────────┐         │          ┌───────┴────────┐
          │                │         │          │                │
       Direct          POP/Reseller  │       Direct         POP/Reseller
     Customers              │         │      Customers             │
                            │         │                             │
                       Customers     Customers                   Customers
```

---

# 3. User Hierarchy

## 3.1 Super Admin

Highest-level system administrator.

```text
Super Admin
    └── ISP Admins
            └── POP / Resellers
                    └── Customers
```

## 3.2 ISP Admin

An ISP Admin represents an individual ISP/tenant.

```text
ISP Admin
    ├── Direct Customers
    ├── POP / Resellers
    │       └── Customers
    ├── Staff
    ├── Packages
    ├── Billing
    ├── Accounting
    ├── Network
    └── Reports
```

## 3.3 POP / Reseller

A POP/Reseller works under an ISP Admin.

```text
ISP Admin
    └── POP / Reseller
            ├── Clients
            ├── Billing
            ├── Payments
            ├── Tickets
            └── Reports
```

---

# 4. Super Admin

The Super Admin controls the entire platform.

## Responsibilities

- Manage ISP Admin accounts
- Create, update and deactivate ISPs
- Manage system-wide configuration
- Manage users and roles
- Manage global permissions
- Monitor ISP activity
- View platform-level reports
- Manage subscription/tenant configuration
- Manage platform settings
- Maintain system security
- Monitor overall system health

## Super Admin Scope

```text
Super Admin
    │
    ├── ISP A
    ├── ISP B
    ├── ISP C
    └── Global Settings
```

The Super Admin can access system-wide data according to the configured permission policy.

---

# 5. ISP Admin

The ISP Admin is the main operational administrator of an individual ISP.

## ISP Admin Dashboard

The dashboard can show:

- Total Customer
- Active Customer
- Inactive Customer
- Expired Customer
- Paid Customer
- Unpaid Customer
- Online Client
- Free Client
- Collection
- Due Invoice
- Total Expense
- Total Income
- Open Ticket
- Processing Ticket
- Solved Ticket
- Closed Ticket
- Income vs Expense chart

## ISP Admin Modules

### Dashboard

Provides a summary of the ISP's current operations.

### MikroTik

Manage MikroTik devices and customer/network connectivity.

### OLT Management

Manage OLT devices and optical network infrastructure.

### Packages

Create and manage Internet packages, speed, price, duration and related configuration.

### Customers

Manage direct ISP customers.

### Billing

Manage invoices, payments, due amounts and billing operations.

### Accounting

Manage income, expenses and financial transactions.

### Reports

Generate customer, billing, financial and operational reports.

### SMS

Manage SMS notifications and SMS-related services.

### Support & Ticketing

Manage customer support requests and tickets.

### System Settings

Configure ISP-level application settings.

### HR & Payroll

Manage employees, attendance/payroll-related information and HR operations.

### MAC Reseller

Manage MAC reseller-related operations where enabled.

### Bandwidth Buy

Record/manage bandwidth purchased by the ISP.

### Bandwidth Sale

Manage bandwidth sales.

### User Management

Create and manage ISP-level users/staff.

### Role Management

Create roles and assign permissions.

### Agents

Manage agents and agent activities.

### Inventory

Manage networking equipment and other inventory.

---

# 6. POP / Reseller

The POP/Reseller is a subordinate business unit under an ISP Admin.

A POP/Reseller can have its own customers.

```text
ISP Admin
    │
    ├── Direct Customer
    ├── Direct Customer
    │
    └── POP / Reseller
            ├── Customer
            ├── Customer
            └── Customer
```

## POP Dashboard

The POP dashboard can display:

- Total Clients
- Active Clients
- Suspended Clients
- Expired Clients
- Collected This Month
- Total Due
- Unpaid Invoices
- Open Tickets
- Recent Payments
- Recent Tickets
- Recently Added Clients

## POP Information

Typical POP information:

- POP Name
- POP Code
- Contact Person
- Mobile Number
- POP Type
- Tariff
- Remaining Fund

## POP Modules

### Dashboard

POP operational overview.

### Configuration

POP-specific configuration.

### MikroTik Client

Manage assigned MikroTik clients.

### Staff Login

Manage staff access.

### Client

Manage customers belonging to the POP.

### Billing

Manage customer invoices, payments and dues.

### Monitoring

Monitor permitted client/network status.

### Support & Ticketing

Manage support requests.

### HR & Payroll

Manage POP staff where enabled.

### SMS Service

Manage SMS services where enabled.

### Report

View POP-level reports.

### Fund History

View POP fund/credit transactions.

### Tutorials

View application tutorials.

### Settings

Manage POP-specific settings.

---

# 7. Customer Structure

Customers can be created directly by the ISP Admin or through a POP/Reseller.

## Type A: Direct Customer

```text
ISP Admin
    │
    └── Customer
```

This customer belongs directly to the ISP.

The ISP Admin can manage:

- Customer profile
- Package
- Billing
- Payment
- Connection
- MikroTik account
- Support
- Status
- Reports

## Type B: POP Customer

```text
ISP Admin
    │
    └── POP / Reseller
            │
            └── Customer
```

This customer belongs to a POP/Reseller while remaining within the ISP tenant.

The ISP Admin can normally view/manage the customer according to permissions, while the POP manages the customer within its assigned scope.

---

# 8. Access & Permission Model

The application should use Role-Based Access Control (RBAC).

| Role | Scope | Access |
|---|---|---|
| Super Admin | Entire platform | System-wide |
| ISP Admin | Own ISP | Full ISP operations |
| POP/Reseller | Own POP | Assigned clients/resources |
| Staff | Assigned scope | Permission based |
| Customer | Own account | Own data |

## Important Rule

A user must not be able to access records outside their authorized scope.

For example:

```text
ISP A User
   X
ISP B Customer
```

An ISP A user must not be able to access ISP B customer data.

Similarly:

```text
POP A
   X
POP B Customer
```

unless an explicit permission allows it.

---

# 9. Dashboard

## ISP Admin Dashboard

Example dashboard cards:

```text
Total Customer       Active Customer
Inactive Customer    Expired Customer

Paid Customer        Unpaid Customer
Online Client        Free Client

Collection            Due Invoice
Total Expense         Total Income

Open Ticket           Processing Ticket
Solved Ticket         Closed Ticket
```

## POP Dashboard

```text
Total Clients         Active Clients
Suspended              Expired

Collected This Month   Total Due
Unpaid Invoices        Open Tickets
```

---

# 10. Customer Management

Customer management should support:

- Create customer
- Update customer
- View customer
- Delete/deactivate customer
- Assign package
- Change package
- Activate customer
- Suspend customer
- Expire customer
- Record payment
- Generate invoice
- Manage connection credentials
- Assign POP
- Manage support tickets
- View customer history

## Customer Ownership

Recommended ownership fields:

```text
isp_id
pop_id (nullable)
```

Logic:

```text
pop_id = NULL
    → Direct ISP Customer

pop_id != NULL
    → POP/Reseller Customer
```

The exact field names may differ depending on the implementation.

---

# 11. Package Management

Packages define the Internet service offered to customers.

Typical package information:

- Package name
- Speed
- Price
- Billing cycle
- Duration
- Data limit, if applicable
- Connection type
- Status
- Description

Example:

```text
Package
├── Name
├── Download Speed
├── Upload Speed
├── Price
├── Duration
└── Status
```

Packages may be available to:

- Direct ISP customers
- POP customers
- Selected resellers

according to business rules.

---

# 12. Billing Management

Billing handles:

- Invoice generation
- Monthly billing
- Payment collection
- Due management
- Invoice status
- Renewal
- Customer payment history
- POP collection
- Outstanding balance

## Invoice Status

Recommended statuses:

```text
Draft
Pending
Unpaid
Partially Paid
Paid
Overdue
Cancelled
```

## Billing Flow

```text
Customer
   │
   ▼
Package
   │
   ▼
Invoice
   │
   ├── Paid
   │
   ├── Partial
   │
   └── Unpaid
          │
          ▼
       Due Balance
```

---

# 13. Accounting

Accounting manages ISP financial transactions.

## Income

Examples:

- Customer payment
- POP payment
- Bandwidth sale
- Other income

## Expense

Examples:

- Bandwidth purchase
- Salary
- Office expense
- Equipment
- Electricity
- Maintenance
- Other expense

## Financial Flow

```text
Income
  │
  ├── Customer Collection
  ├── POP Collection
  └── Other Income

Expense
  │
  ├── Bandwidth
  ├── Salary
  ├── Equipment
  └── Other Expense
```

---

# 14. MikroTik Management

The MikroTik module is intended for ISP network management.

Possible functionality:

- Router management
- Router connection
- PPPoE user management
- Client activation
- Client suspension
- Profile/package assignment
- Online user monitoring
- Session management
- Bandwidth/profile management
- Disconnect user
- Router status

Typical flow:

```text
Customer
   │
   ▼
Package
   │
   ▼
MikroTik Profile
   │
   ▼
PPPoE / Network Access
```

Credentials and router access information must be stored securely.

---

# 15. OLT Management

The OLT module manages optical access network infrastructure.

Possible functionality:

- OLT device management
- OLT location
- PON/port management
- ONU/ONT information
- Customer mapping
- Optical status
- Connection monitoring
- Device status

Example:

```text
OLT
 ├── PON Port
 │     ├── ONU
 │     │    └── Customer
 │     └── ONU
 │          └── Customer
 └── PON Port
```

---

# 16. Bandwidth Management

The ISP may purchase bandwidth and sell/allocate it to customers or resellers.

## Bandwidth Buy

Records:

- Provider
- Capacity
- Price
- Billing period
- Purchase date
- Due/payment
- Status

## Bandwidth Sale

Records:

- Customer/POP
- Capacity
- Price
- Billing period
- Sale date
- Status

Flow:

```text
Upstream Provider
       │
       ▼
ISP
       │
       ├── Customer
       │
       └── POP / Reseller
```

---

# 17. SMS Service

SMS functionality may be used for:

- Payment confirmation
- Invoice notification
- Due reminder
- Package renewal
- Service suspension notice
- Service activation
- Promotional SMS
- System notifications

Example:

```text
Event
  │
  ▼
SMS Template
  │
  ▼
SMS Gateway
  │
  ▼
Customer Mobile
```

API credentials should never be hard-coded in source code.

---


# 18. Payment Gateway

The system includes **Payment Gateway integration** for collecting customer payments online.

Payment Gateway functionality may be used for:

- Online invoice payment
- Customer package renewal
- Due payment
- Payment confirmation
- Automatic payment status update
- Transaction/reference tracking
- Payment history
- Failed/cancelled transaction handling

## Payment Flow

```text
Customer
   │
   ▼
Invoice / Due
   │
   ▼
Payment Gateway
   │
   ├── Success
   │     └── Payment Recorded
   │
   ├── Failed
   │     └── Payment Not Completed
   │
   └── Cancelled
         └── Payment Cancelled
```

## Recommended Payment Gateway Records

A payment transaction should keep information such as:

- Customer
- Invoice
- Amount
- Gateway
- Transaction ID
- Payment/reference ID
- Status
- Paid date
- Response/reference data
- Created/updated timestamps

## Security

Payment gateway secrets must be stored in environment/configuration settings and must not be hard-coded or exposed to users.

---

# 40. SMS Gateway

The system includes **SMS Gateway integration** for sending transactional and notification SMS.

SMS can be used for:

- Customer registration
- Account activation
- Invoice notification
- Payment confirmation
- Due reminder
- Package renewal reminder
- Service suspension notification
- Service activation notification
- Password/OTP-related messages where implemented
- Promotional SMS
- Support/ticket notifications

## SMS Flow

```text
System Event
     │
     ▼
SMS Template
     │
     ▼
SMS Gateway
     │
     ▼
Customer Mobile
```

## SMS Gateway Configuration

Gateway configuration may include:

```text
Gateway Name
API URL
API Key / Token
Sender ID
Username
Password
Status
```

Actual fields depend on the integrated SMS provider.

## SMS Logs

The system should ideally maintain:

- Recipient
- Message/template
- Gateway
- Message ID
- Send status
- API response
- Sent time
- Related customer
- Related invoice/event

## SMS Security

SMS API credentials must be stored securely and must not be committed to source control.

---

# 40. Support & Ticketing

Ticketing allows customers and staff to manage support issues.

## Ticket Status

```text
Open
  ↓
Processing
  ↓
Solved
  ↓
Closed
```

Typical ticket information:

- Customer
- Subject
- Description
- Priority
- Assigned staff
- Status
- Created date
- Updated date
- Resolution

---

# 40. HR & Payroll

HR & Payroll may manage:

- Employees
- Staff
- Designation
- Department
- Attendance
- Salary
- Allowances
- Deductions
- Payroll
- Payment history

Example:

```text
Employee
   │
   ├── Attendance
   ├── Salary
   ├── Allowance
   ├── Deduction
   └── Payroll
```

---

# 40. Inventory

Inventory management can track:

- ONU/ONT
- Router
- MikroTik
- OLT
- Switch
- Fiber
- Cable
- Power adapter
- Spare parts
- Other equipment

Inventory records should support:

- Purchase
- Stock in
- Stock out
- Assignment
- Return
- Damage
- Current stock

---

# 40. Reports

Recommended reports:

### Customer Reports

- Total customers
- Active customers
- Expired customers
- Suspended customers
- POP-wise customers
- Package-wise customers

### Billing Reports

- Paid invoices
- Unpaid invoices
- Due invoices
- Collection
- Payment history

### Financial Reports

- Income
- Expense
- Profit/loss
- Expense category
- Income category

### Network Reports

- Online users
- Offline users
- MikroTik status
- OLT status
- Bandwidth usage

### POP Reports

- POP customer count
- POP collection
- POP due
- POP fund history

---

# 40. Fund / Reseller Management

A POP/Reseller may operate using prepaid or credit/fund-based business rules.

Example:

```text
ISP Admin
    │
    │ Fund Allocation
    ▼
POP / Reseller
    │
    ├── Customer Billing
    ├── Customer Collection
    └── Fund Usage
```

## Fund History

Every fund transaction should ideally record:

- Date
- POP
- Transaction type
- Amount
- Previous balance
- New balance
- Reference
- Description
- Created by

Example:

```text
Previous Balance: ৳5,000
Fund Added:       ৳2,000
-------------------------
New Balance:      ৳7,000
```

---

# 40. Customer Lifecycle

A customer may follow this lifecycle:

```text
New
 │
 ▼
Package Assigned
 │
 ▼
Active
 │
 ├── Payment
 │
 ├── Renewal
 │
 ▼
Due / Unpaid
 │
 ▼
Suspended
 │
 ▼
Expired
```

The exact status transition depends on the application's billing rules.

---

# 40. Billing Flow

## Direct Customer

```text
ISP Admin
   │
   ▼
Direct Customer
   │
   ▼
Package
   │
   ▼
Invoice
   │
   ▼
Payment
```

## POP Customer

```text
ISP Admin
   │
   ▼
POP / Reseller
   │
   ▼
Customer
   │
   ▼
Package
   │
   ▼
Invoice
   │
   ▼
Payment
```

---

# 40. Fund Flow

For a prepaid POP model:

```text
ISP Admin
    │
    │ Allocate Fund
    ▼
POP / Reseller
    │
    │ Use Fund
    ▼
Customer Service
```

A proper transaction ledger should be maintained for every balance change.

---

# 40. Data Ownership

A recommended ownership model:

```text
ISP
│
├── Users
├── Packages
├── Customers
├── POPs
├── Billing
├── Payments
├── Accounting
├── MikroTik
├── OLT
├── SMS
├── Tickets
├── HR
└── Inventory
```

## Direct Customer

```text
Customer
isp_id = ISP A
pop_id = NULL
```

## POP Customer

```text
Customer
isp_id = ISP A
pop_id = POP A1
```

This allows the ISP to identify the customer even when the customer is managed through a POP.

---

# 40. Multi-Tenant Architecture

Each ISP should operate as an isolated tenant.

Example:

```text
SUPER ADMIN
│
├── ISP A
│   ├── Customers
│   ├── POPs
│   ├── Billing
│   └── Accounting
│
├── ISP B
│   ├── Customers
│   ├── POPs
│   ├── Billing
│   └── Accounting
│
└── ISP C
    ├── Customers
    ├── POPs
    ├── Billing
    └── Accounting
```

## Tenant Isolation

Every tenant-owned record should be associated with the correct ISP/tenant.

Recommended:

```text
isp_id
```

for tenant-owned records.

Where applicable:

```text
pop_id
```

identifies the POP/Reseller.

---

# 40. Database Relationship Concept

A simplified relationship can be:

```text
super_admins
     │
     │ manages
     ▼
isp_admins
     │
     ├──────────────┐
     │              │
     ▼              ▼
customers       pops/resellers
     ▲              │
     │              │
     └──────────────┘
```

More detailed:

```text
ISP
│
├── Users
│
├── POPs
│    └── Customers
│
├── Direct Customers
│
├── Packages
│
├── Invoices
│
├── Payments
│
├── Expenses
│
├── Income
│
├── MikroTik Routers
│
├── OLT Devices
│
├── Tickets
│
├── SMS
│
├── Employees
│
└── Inventory
```

## Recommended Customer Keys

```text
customers
-----------
id
isp_id
pop_id nullable
package_id
name
mobile
username
status
created_at
updated_at
```

`pop_id` should be nullable so that the same customer table supports both direct ISP customers and POP customers.

---

# 40. Security

Security is critical because the system contains customer, billing and network information.

## Required Security Practices

- Role-based authorization
- Tenant-level data isolation
- Server-side permission checks
- CSRF protection
- Password hashing
- Secure sessions
- Input validation
- SQL injection prevention
- XSS protection
- Rate limiting
- Audit logging
- Secure API authentication
- HTTPS
- Database backups
- Secure secrets management

## Sensitive Information

Do not expose:

- Database passwords
- API keys
- SMS credentials
- MikroTik credentials
- OLT credentials
- Payment gateway secrets

Do not commit `.env` to Git.

---

# 40. Installation

> Adjust the commands below according to the actual project configuration.

## Requirements

Recommended environment:

- PHP 8.2+
- Composer
- MySQL/MariaDB
- Apache/Nginx
- Node.js/NPM if frontend assets require it
- SSL certificate for production

## Clone Project

```bash
git clone YOUR_REPOSITORY_URL
cd YOUR_PROJECT_DIRECTORY
```

## Install Dependencies

```bash
composer install
```

If frontend dependencies are used:

```bash
npm install
npm run build
```

## Environment

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

## Database

Create the database and configure `.env`.

Then run:

```bash
php artisan migrate
```

If seeders are available:

```bash
php artisan db:seed
```

or:

```bash
php artisan migrate --seed
```

## Storage

```bash
php artisan storage:link
```

## Local Development

```bash
php artisan serve
```

Default:

```text
http://127.0.0.1:8000
```

---

# 40. Environment Configuration

Example:

```env
APP_NAME="ISP Management"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=isp_management
DB_USERNAME=database_user
DB_PASSWORD=database_password
```

Additional services may require:

```env
MAIL_MAILER=
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=

SMS_API_URL=
SMS_API_KEY=

MIKROTIK_HOST=
MIKROTIK_USERNAME=
MIKROTIK_PASSWORD=
```

Use the actual variable names defined by the project.

---

# 40. Production Deployment

## Checklist

Before production:

- Configure `.env`
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Configure database
- Configure domain
- Enable HTTPS
- Configure storage
- Configure queue
- Configure cron
- Configure mail
- Configure SMS gateway
- Configure MikroTik access
- Configure OLT access
- Configure backup
- Test permissions
- Test tenant isolation
- Test billing
- Test payment
- Test customer activation
- Test customer suspension
- Test POP fund transactions

## Laravel Optimization

For a Laravel-based deployment, commonly used commands include:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Run these only after confirming the production configuration is correct.

---

# 40. Cron Jobs & Queue

If the application uses scheduled tasks, configure the Laravel scheduler.

Typical cron:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Possible scheduled tasks:

- Monthly invoice generation
- Package expiry
- Customer suspension
- Payment reminders
- SMS notifications
- Reports
- Cleanup tasks

If queues are used, configure a queue worker.

Example:

```bash
php artisan queue:work
```

For production, use a process manager such as Supervisor where appropriate.

---

# 40. Backup

A production ISP system should have automated backups.

## Database Backup

Recommended:

- Daily database backup
- Weekly full backup
- Off-server backup
- Backup retention policy

## Important Data

Back up:

- Database
- Uploaded files
- Customer documents, if any
- Configuration required for recovery

Never rely on only one backup location.

---

# 40. Troubleshooting

## Database Connection Error

Check:

```text
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
```

Test the database server and credentials.

## Cache/Configuration Issue

Try:

```bash
php artisan optimize:clear
```

Then rebuild required caches:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Storage Files Not Showing

Run:

```bash
php artisan storage:link
```

## Queue Not Processing

Check:

- Queue connection
- Worker status
- Failed jobs
- Supervisor configuration
- Application logs

## Permission Problem

Check:

- User role
- Assigned permissions
- ISP/tenant ownership
- POP ownership
- Server-side authorization

Never solve a permission problem by simply exposing all records.

---

# 40. Recommended Development Standards

## Code Structure

Use clear separation between:

- Controllers
- Services
- Models
- Repositories, if used
- Requests/Validation
- Policies
- Jobs
- Events
- Notifications

## Authorization

Authorization should be enforced at the server side.

Do not rely only on hiding menu items.

Example concept:

```text
Menu Hidden
    ≠
Permission Granted
```

The backend must still verify:

```text
User
  ↓
Role
  ↓
Permission
  ↓
ISP/Tenant
  ↓
POP Scope
  ↓
Record
```

## Audit Logs

Important actions should be logged:

- Customer creation
- Customer deletion/deactivation
- Package changes
- Payment creation
- Invoice changes
- Fund transfer
- User creation
- Permission changes
- Network configuration changes

---

# 40. Future Improvements

Potential future features:

- Mobile application
- Customer self-service portal
- Online payment gateway integration
- bKash/Nagad/Rocket integration
- Automated MikroTik provisioning
- Automated OLT/ONU provisioning
- Real-time bandwidth monitoring
- Customer mobile app
- WhatsApp notification
- Advanced SMS campaign
- Automated invoice generation
- Automated payment reconciliation
- Advanced financial reports
- Data export/import
- API documentation
- Webhook support
- Two-factor authentication
- Login activity monitoring
- Advanced audit log
- Dashboard widgets
- Multi-language support

---

# Final Business Structure

The complete business relationship is:

```text
                         SUPER ADMIN
                              │
            ┌─────────────────┼─────────────────┐
            │                 │                 │
         ISP A             ISP B             ISP C
       ISP ADMIN          ISP ADMIN          ISP ADMIN
            │                 │                 │
      ┌─────┴─────┐           │           ┌─────┴─────┐
      │           │           │           │           │
   Direct       POP 1      Direct       Direct       POP 1
  Customers       │       Customers    Customers       │
                  │                                   │
              Customers                            Customers
```

## Key Rule

**An ISP Admin can have direct customers.**

**An ISP Admin can also have multiple POP/Resellers.**

**Each POP/Reseller can have multiple customers.**

Therefore:

```text
ISP Admin
├── Customer
├── Customer
├── Customer
│
├── POP / Reseller
│   ├── Customer
│   ├── Customer
│   └── Customer
│
└── POP / Reseller
    ├── Customer
    └── Customer
```

This structure allows the ISP to operate both **direct customer management** and **reseller/POP-based customer management** within the same system.

---

## Project Information

**Project:** ISP Management System

**Architecture:** Multi-level / Multi-tenant ISP Management

**Primary Roles:**

- Super Admin
- ISP Admin
- POP / Reseller
- Staff
- Customer

**Core Areas:**

- ISP Management
- Customer Management
- Package Management
- Billing
- Accounting
- MikroTik
- OLT
- Bandwidth
- Payment Gateway
- SMS Gateway
- Support & Ticketing
- HR & Payroll
- Inventory
- Reports
- Role & Permission Management

---

## Documentation Note

This README describes the application's intended business architecture and module structure. Exact menu names, database table names, API endpoints and configuration variables should be updated to match the final implementation.
