# 🛍️ ZOONACART

> Premium Cosmetics E-Commerce Platform

A modern, full-featured online store for luxury cosmetics built with
PHP, MySQL, and vanilla JavaScript.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=flat-square&logo=javascript&logoColor=black)

------------------------------------------------------------------------

## ✨ Advanced Features

### 🛍️ Customer Experience
- **One-Tap Google Login**: Secure and seamless authentication using Google Identity Services.
- **Native Digital Wallet**: Prepaid balance system for faster checkout and instant refunds.
- **Dynamic GST Engine**: Automatic tax calculation based on HSN codes (CGST/SGST/IGST).
- **Premium PDF Invoices**: Professional, Amazon-style tax invoices generated on-the-fly.
- **Returns Workflow**: Structured return request system with admin approval.
- **Wallet Ledger**: Full transaction history for transparency.

### 👨‍💼 Business Management (Admin)
- **Advanced Dashboard**: Real-time sales analytics with interactive charts.
- **Inventory & Tax Control**: Manage products with integrated HSN and Tax % settings.
- **Financial Control**: Approve return requests and manage wallet balances.
- **Modular Architecture**: 3-Tier architecture for scalability and clean code.

------------------------------------------------------------------------

## 🚀 Installation & Setup

### Prerequisites
- **XAMPP** (Apache, PHP 8.2+, MySQL)
- **SMTP Mailer**: Configured in `config.php` for OTP and verification emails.
- **Google Client ID**: For authentication features.

------------------------------------------------------------------------

### Setup Steps

1. **Start XAMPP**: Open XAMPP Control Panel and start **Apache** and **MySQL**.
2. **Place Project**: Copy the project folder to `C:\xampp\htdocs\zoonacart`.
3. **Database Setup**:
   - Create a database named `zoonacart` in phpMyAdmin.
   - Import `database.sql` to initialize tables.
   - Run `fix_db.php` in your browser to ensure the latest schema is applied.
4. **Seed Content**: Run `db_seeder.php` or `seed_images.php` to populate core products.
5. **SMTP & Config**: Update `config.php` with your Gmail App Password and Google Client ID.

------------------------------------------------------------------------

## 📁 Project Structure

```text
zoonacart/
├── admin/          # Admin Control Center (Orders, Products, Returns)
├── api/            # Central API Endpoint for AJAX actions
├── assets/         # CSS, JS, and Product Images
├── includes/       # Core Logic (Mailer, Shiprocket, Database, Functions)
├── js/             # Frontend Logic (Cart, Wallet, Auth)
├── profile.php     # User Account & Verification Settings
├── wallet.php      # Digital Balance & Transaction History
├── shop.php        # Fast-loading Catalog with Search
├── receipt.php     # Premium PDF Invoice Generator
└── config.php      # Environment & Secret Configuration
```

------------------------------------------------------------------------

## ⚙️ Database Configuration

Edit `config.php` file:

``` php
$host = 'localhost';
$dbname = 'zoonacart';
$username = 'root';
$password = '';
```


## 👨‍💻 Author

Developed by Nayra1837 ❤️
