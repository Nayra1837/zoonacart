# 🛍️ ZOONACART

> Premium Cosmetics E-Commerce Platform

A modern, full-featured online store for luxury cosmetics built with
PHP, MySQL, and vanilla JavaScript.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=flat-square&logo=javascript&logoColor=black)

------------------------------------------------------------------------

## ✨ Features

### 🛍️ Customer Features

-   Browse cosmetics product catalog\
-   Add products to cart with quantity selection\
-   Secure checkout (Cash on Delivery)\
-   View order history & download receipts\
-   User profile management

### 👨‍💼 Admin Features

-   Admin dashboard with analytics\
-   Product management (Add / Edit / Delete)\
-   Order management and status update\
-   User management\
-   Site settings and branding control

------------------------------------------------------------------------

## 🚀 Installation & Setup

### Prerequisites

-   XAMPP (Apache, PHP, MySQL)\
-   PHP 8.0 or higher\
-   MySQL 5.7 or higher

------------------------------------------------------------------------

### Step 1: Start XAMPP

1.  Open **XAMPP Control Panel**
2.  Start **Apache** and **MySQL**

------------------------------------------------------------------------

### Step 2: Place Project Files

Copy the project folder to:

    C:\xampp\htdocs\zoonacart

------------------------------------------------------------------------

### Step 3: Create Database

1.  Open: http://localhost/phpmyadmin\
2.  Click **New**
3.  Database Name:

```{=html}
<!-- -->
```
    zoonacart

4.  Click **Create**

------------------------------------------------------------------------

### Step 4: Import Database

1.  Select **zoonacart** database\
2.  Click **Import**\
3.  Choose file:

```{=html}
<!-- -->
```
    C:\xampp\htdocs\zoonacart\database.sql

4.  Click **Go**

------------------------------------------------------------------------

### Step 5: Seed Sample Products (Optional)

    http://localhost/zoonacart/seed.php

------------------------------------------------------------------------

### Step 6: Run Project

    http://localhost/zoonacart/

------------------------------------------------------------------------

## 🔐 Default Admin Login

  Role    Email                 Password
  ------- --------------------- ----------
  Admin   admin@zoonacart.com   admin123

------------------------------------------------------------------------

## 📁 Project Structure

    zoonacart/
    ├── admin/
    ├── api/
    ├── assets/
    │   ├── img/
    │   └── style.css
    ├── includes/
    ├── js/
    ├── index.php
    ├── shop.php
    ├── cart.php
    ├── checkout.php
    ├── login.php
    ├── register.php
    ├── profile.php
    ├── receipt.php
    ├── config.php
    ├── database.sql
    └── seed.php

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
