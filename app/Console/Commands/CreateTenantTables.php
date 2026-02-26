<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateTenantTables extends Command
{
    protected $signature = 'tenant:create-tables {database}';
    protected $description = 'Create all necessary tables for a new tenant database';

    public function handle()
    {
        $database = $this->argument('database');
        
        $this->info("Creating tables for tenant database: {$database}");

        try {
            // Conectar a la base de datos del tenant
            config(['database.connections.tenant.database' => $database]);
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Crear tablas básicas
            $this->createUsersTable();
            $this->createCategoriesTable();
            $this->createProductsTable();
            $this->createCustomersTable();
            $this->createSuppliersTable();
            $this->createSalesTable();
            $this->createCashRegistersTable();
            
            $this->info("Tables created successfully for: {$database}");
            
            return 0;
        } catch (\Exception $e) {
            Log::error("Error creating tenant tables: " . $e->getMessage());
            $this->error("Failed to create tables: " . $e->getMessage());
            return 1;
        }
    }

    private function createUsersTable(): void
    {
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS users (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'cashier', 'manager') DEFAULT 'cashier',
                is_active BOOLEAN DEFAULT TRUE,
                last_login_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }

    private function createCategoriesTable(): void
    {
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS categories (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT NULL,
                parent_id BIGINT UNSIGNED NULL,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
            )
        ");
    }

    private function createProductsTable(): void
    {
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS products (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                sku VARCHAR(100) UNIQUE,
                barcode VARCHAR(100),
                name VARCHAR(255) NOT NULL,
                description TEXT,
                category_id BIGINT UNSIGNED,
                cost_price DECIMAL(12,2) DEFAULT 0,
                sale_price DECIMAL(12,2) DEFAULT 0,
                stock_quantity INT DEFAULT 0,
                min_stock INT DEFAULT 0,
                unit VARCHAR(50) DEFAULT 'unidad',
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            )
        ");
    }

    private function createCustomersTable(): void
    {
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS customers (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                phone VARCHAR(50),
                address TEXT,
                tax_id VARCHAR(50),
                credit_limit DECIMAL(12,2) DEFAULT 0,
                current_balance DECIMAL(12,2) DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }

    private function createSuppliersTable(): void
    {
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS suppliers (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                contact_name VARCHAR(255),
                email VARCHAR(255),
                phone VARCHAR(50),
                address TEXT,
                tax_id VARCHAR(50),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }

    private function createSalesTable(): void
    {
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS sales (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                invoice_number VARCHAR(50) UNIQUE,
                customer_id BIGINT UNSIGNED,
                user_id BIGINT UNSIGNED NOT NULL,
                subtotal DECIMAL(12,2) DEFAULT 0,
                tax_amount DECIMAL(12,2) DEFAULT 0,
                discount_amount DECIMAL(12,2) DEFAULT 0,
                total DECIMAL(12,2) DEFAULT 0,
                payment_method ENUM('cash', 'card', 'transfer', 'credit') DEFAULT 'cash',
                status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
    }

    private function createCashRegistersTable(): void
    {
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS cash_registers (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                opening_amount DECIMAL(12,2) DEFAULT 0,
                closing_amount DECIMAL(12,2),
                opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                closed_at TIMESTAMP NULL,
                status ENUM('open', 'closed') DEFAULT 'open',
                notes TEXT,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
    }
}
