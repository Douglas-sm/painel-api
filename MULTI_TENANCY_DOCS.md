# Multi-Tenancy Implementation Documentation

This document outlines the multi-tenancy implementation in the Painel API, which uses a multi-database approach where each tenant has its own separate database.

## Architecture Overview

The multi-tenancy system is designed with the following characteristics:

1. **Tenant Identification via Subdomain**
   - Each tenant is identified by a unique subdomain (e.g., alfa.example.com, beta.example.com, celta.example.com)
   - A middleware detects the current tenant based on the subdomain in the request

2. **Multiple Database Configuration**
   - Each tenant has its own separate database
   - Database names follow the pattern: `painel_alfa`, `painel_beta`, `painel_celta`
   - A central database (`painel_central`) manages tenant information

3. **Dynamic Database Connection**
   - The system dynamically switches the active database connection based on the subdomain
   - All queries are automatically directed to the correct tenant's database

## Setup Instructions

### 1. Database Creation

Create the necessary databases for the multi-tenancy system:

```sql
CREATE DATABASE painel_central;
CREATE DATABASE painel_alfa;
CREATE DATABASE painel_beta;
CREATE DATABASE painel_celta;
```

### 2. Environment Configuration

Update your `.env` file to use the central database as the default:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=painel_central
DB_USERNAME=root
DB_PASSWORD=root
```

### 3. Run Migrations

Run the migrations on the central database to create the tenants table and other central tables:

```bash
php artisan migrate
```

### 4. Seed Tenant Data

Run the TenantSeeder to populate the tenants table with initial tenant data:

```bash
php artisan db:seed --class=TenantSeeder
```

### 5. Run Tenant Migrations

Run migrations on all tenant databases to set up the schema for each tenant:

```bash
php artisan tenants:migrate
```

Optionally, include the `--seed` flag to seed tenant databases:

```bash
php artisan tenants:migrate --seed
```

## Configuration for Subdomains

To use the multi-tenancy system with subdomains, you need to configure your local development environment or production server to handle subdomains correctly.

### Local Development with Hosts File

1. Edit your hosts file (`C:\Windows\System32\drivers\etc\hosts` on Windows or `/etc/hosts` on Linux/Mac):

```
127.0.0.1    alfa.localhost
127.0.0.1    beta.localhost
127.0.0.1    celta.localhost
127.0.0.1    localhost
```

2. Configure your web server (Apache/Nginx) to handle wildcard subdomains

### Production Setup

1. Set up DNS records for your subdomains pointing to your server
2. Configure your web server to handle the subdomains
3. Update your `.env` file with the appropriate domain settings

## Usage

### Accessing Tenant-Specific Data

The system automatically determines the current tenant based on the subdomain and directs all database queries to the appropriate tenant database. You can access the current tenant in your code using:

```php
$tenant = app('tenant');
```

### User Management

Users are automatically associated with the tenant they registered under. When a user registers through a specific subdomain, they are linked to that tenant via the `tenant_id` field.

### Running Migrations for All Tenants

When you need to update the database schema for all tenants, use the custom command:

```bash
php artisan tenants:migrate
```

## Components Overview

The multi-tenancy implementation consists of the following key components:

1. **Tenant Model**
   - Represents tenant entities with name, domain, and database fields
   - Located at `app/Models/Tenant.php`

2. **TenantMiddleware**
   - Detects the current tenant based on the subdomain
   - Configures the database connection for the tenant
   - Located at `app/Http/Middleware/TenantMiddleware.php`

3. **MigrateTenants Command**
   - Custom Artisan command to run migrations on all tenant databases
   - Located at `app/Console/Commands/MigrateTenants.php`

4. **User-Tenant Relationship**
   - Users are associated with tenants via a foreign key relationship
   - The User model has a `tenant()` relationship method

## Considerations and Best Practices

1. **Session Management**
   - Sessions should be domain-specific to prevent sharing between tenants
   - Configure your session settings appropriately

2. **File Storage**
   - For file uploads, consider using tenant-specific directories
   - You can use the tenant identifier to organize files

3. **Cache Management**
   - Configure cache prefixes per tenant to avoid shared cache issues
   - Consider using tenant-specific cache stores

4. **Schema Changes**
   - When adding new tables or columns, remember to run migrations on all tenant databases
   - Use the `tenants:migrate` command to ensure consistency

5. **Testing**
   - Test your application with different tenant subdomains
   - Verify data isolation between tenants

## Troubleshooting

### Subdomain Not Recognized

If the system doesn't recognize a subdomain correctly:

1. Check your hosts file configuration
2. Verify that the subdomain matches a domain in the tenants table
3. Ensure your web server is properly configured for subdomains

### Database Connection Issues

If you experience database connection problems:

1. Verify that all tenant databases exist
2. Check database credentials in your `.env` file
3. Ensure the TenantMiddleware is properly registered and running

### Migration Failures

If tenant migrations fail:

1. Run migrations on the central database first
2. Check if the tenant record exists in the tenants table
3. Verify that the tenant database exists and is accessible
