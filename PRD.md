# zoonacart - Cosmetics E-Commerce Platform

## Product Requirements Document (PRD) 

---

## 📋 Executive Summary

**zoonacart** is a full-featured e-commerce platform for luxury cosmetics built with PHP, MySQL, and vanilla JavaScript. The platform provides a complete shopping experience for customers and comprehensive management tools for administrators.

**Tech Stack:**

- Backend: PHP 8.2
- Database: MySQL (via XAMPP)
- Frontend: HTML5, CSS3, Vanilla JavaScript
- Icons: Font Awesome 6
- Server: Apache (XAMPP)

---

## 🏗️ System Architecture

### Directory Structure

```
cosmetics/
├── admin/                    # Admin panel pages
│   ├── admin_nav.php         # Shared admin navigation component
│   ├── dashboard.php         # Admin dashboard with stats
│   ├── orders.php            # Order management
│   ├── products.php          # Product CRUD
│   ├── settings.php          # Site configuration
│   └── users.php             # User management
├── api/
│   └── main.php              # REST API endpoints
├── assets/
│   ├── img/                  # Product and site images
│   └── style.css             # Global stylesheet
├── includes/
│   ├── footer.php            # Shared footer
│   ├── functions.php         # Helper functions
│   └── header.php            # Shared header with navigation
├── js/
│   └── app.js                # Frontend JavaScript
├── cart.php                  # Shopping cart page
├── checkout.php              # Checkout flow
├── config.php                # Database configuration
├── database.sql              # Database schema
├── index.php                 # Homepage
├── login.php                 # User login
├── logout.php                # Session termination
├── profile.php               # User profile & orders
├── receipt.php               # Order confirmation/receipt
├── register.php              # User registration
├── seed.php                  # Database seeding script
└── shop.php                  # Product catalog
```

---

## 👤 User Flows

### 1. Customer Registration Flow

```
[Visit Site] → [Click "Join"] → [Fill Form] → [Submit]
     ↓                              ↓
[Browse Products]            [Validate Data]
                                   ↓
                            [Create Account]
                                   ↓
                            [Auto Login]
                                   ↓
                            [Redirect to Home]
```

**Files Involved:** `register.php`, `includes/header.php`

### 2. Customer Login Flow

```
[Click "Login"] → [Enter Credentials] → [Submit]
                                            ↓
                                    [Validate Password]
                                            ↓
                                    [Create Session]
                                            ↓
                                    [Redirect to Home]
```

**Files Involved:** `login.php`, `includes/functions.php`

### 3. Shopping Flow

```
[Browse Shop] → [Select Quantity] → [Click "Add"]
                                        ↓
                                [Update Session Cart]
                                        ↓
                                [Update Cart Badge]
                                        ↓
[View Cart] → [Adjust Quantities] → [Proceed to Checkout]
                                        ↓
                                [Enter Shipping Details]
                                        ↓
                                [Confirm Order]
                                        ↓
                                [Create Order Record]
                                        ↓
                                [Clear Cart]
                                        ↓
                                [Show Receipt]
```

**Files Involved:** `shop.php`, `js/app.js`, `api/main.php`, `cart.php`, `checkout.php`, `receipt.php`

### 4. Admin Management Flow

```
[Admin Login] → [Dashboard] → [Select Action]
                    ↓              ↓
              [View Stats]    [Manage Users]
                              [Manage Products]
                              [Manage Orders]
                              [Site Settings]
```

**Files Involved:** `admin/dashboard.php`, `admin/users.php`, `admin/products.php`, `admin/orders.php`, `admin/settings.php`

---

## 🔐 Authentication System

### Session Management

- Sessions stored server-side via PHP `$_SESSION`
- Session variables: `user_id`, `name`, `role`
- Cart data stored in `$_SESSION['cart']` as `[product_id => quantity]`

### Password Storage

- **Current:** Plain-text storage (per user request)
- **Recommended:** Use `password_hash()` and `password_verify()`

### Role-Based Access

| Role    | Permissions                                                          |
| ------- | -------------------------------------------------------------------- |
| `user`  | Browse, cart, checkout, view own orders, update own password         |
| `admin` | All user permissions + manage products, orders, users, site settings |

---

## 🗄️ Database Schema

### Tables

#### `users`

| Column     | Type                  | Description         |
| ---------- | --------------------- | ------------------- |
| id         | INT AUTO_INCREMENT    | Primary key         |
| name       | VARCHAR(255)          | User's full name    |
| email      | VARCHAR(255) UNIQUE   | Login email         |
| password   | VARCHAR(255)          | Plain-text password |
| role       | ENUM('user', 'admin') | Access level        |
| created_at | TIMESTAMP             | Registration date   |

#### `products`

| Column      | Type               | Description        |
| ----------- | ------------------ | ------------------ |
| id          | INT AUTO_INCREMENT | Primary key        |
| name        | VARCHAR(255)       | Product name       |
| price       | DECIMAL(10,2)      | Price in INR       |
| description | TEXT               | Product details    |
| image       | VARCHAR(255)       | Image filename     |
| stock       | INT                | Available quantity |
| category    | VARCHAR(100)       | Product category   |
| created_at  | TIMESTAMP          | Added date         |

#### `orders`

| Column           | Type               | Description                 |
| ---------------- | ------------------ | --------------------------- |
| id               | INT AUTO_INCREMENT | Primary key                 |
| user_id          | INT                | Foreign key to users        |
| total_amount     | DECIMAL(10,2)      | Order total                 |
| status           | ENUM               | pending/completed/cancelled |
| order_date       | TIMESTAMP          | Order timestamp             |
| delivery_address | TEXT               | Shipping address            |

#### `order_items`

| Column     | Type               | Description             |
| ---------- | ------------------ | ----------------------- |
| id         | INT AUTO_INCREMENT | Primary key             |
| order_id   | INT                | Foreign key to orders   |
| product_id | INT                | Foreign key to products |
| quantity   | INT                | Items ordered           |
| price      | DECIMAL(10,2)      | Price at time of order  |

#### `settings`

| Column        | Type            | Description        |
| ------------- | --------------- | ------------------ |
| setting_key   | VARCHAR(255) PK | Setting identifier |
| setting_value | TEXT            | Setting value      |

**Current Settings:**

- `site_name` - Store name (zoonacart)
- `site_description` - Tagline
- `hero_title` - Homepage hero heading
- `hero_subtitle` - Homepage hero subtext
- `hero_image` - Hero background image
- `footer_text` - Footer description

---

## 🔌 API Endpoints

**Base URL:** `/api/main.php?action=`

| Action            | Method | Description                 | Auth |
| ----------------- | ------ | --------------------------- | ---- |
| `get_products`    | GET    | List all products           | No   |
| `get_cart`        | GET    | Get cart with items & total | No   |
| `add_to_cart`     | POST   | Add item(s) to cart         | No   |
| `update_cart`     | POST   | Update item quantity        | No   |
| `get_auth`        | GET    | Check login status          | No   |
| `get_orders`      | GET    | Get user's orders           | Yes  |
| `update_password` | POST   | Change password             | Yes  |

---

## 🎨 Frontend Features

### Design System

- **Primary Color:** #f43f5e (Rose/Coral)
- **Secondary Color:** #fb923c (Orange)
- **Dark Color:** #0f172a (Navy)
- **Font:** System fonts (Arial, sans-serif)
- **Style:** Sharp corners (border-radius: 0)
- **Effects:** Subtle shadows, glassmorphism

### Responsive Elements

- Fluid grid layouts
- Mobile-friendly navigation
- Flexible product cards

### Interactive Features

- Quantity selectors (+/- buttons)
- Real-time cart count updates
- Hover effects on cards
- Form validation

---

## ✅ Feature Checklist

### Customer Features

- [x] User registration
- [x] User login/logout
- [x] Browse products
- [x] Product quantity selection
- [x] Add to cart
- [x] View/edit cart
- [x] Checkout with address
- [x] Order confirmation receipt
- [x] PDF receipt download
- [x] View order history
- [x] Change password

### Admin Features

- [x] Dashboard with statistics
- [x] Unified admin navigation
- [x] Product management (CRUD)
- [x] Image upload for products
- [x] User management
- [x] Role assignment
- [x] Order management
- [x] Order status updates
- [x] Site settings configuration
- [x] Hero content management
- [x] Admin password change

## 📊 Test Credentials

| Role  | Email              | Password      |
| ----- | ------------------ | ------------- |
| Admin | admin@zoonacart.com | admin123      |
| User  | (register new)     | (your choice) |


## 📁 File Summary

| File                   | Lines | Purpose                          |
| ---------------------- | ----- | -------------------------------- |
| config.php             | ~15   | Database connection & constants  |
| index.php              | ~33   | Homepage with hero & bestsellers |
| shop.php               | ~40   | Full product catalog             |
| cart.php               | ~49   | Shopping cart management         |
| checkout.php           | ~130  | Order placement                  |
| receipt.php            | ~167  | Order confirmation & PDF         |
| login.php              | ~66   | User authentication              |
| register.php           | ~80   | User registration                |
| profile.php            | ~114  | User dashboard & settings        |
| logout.php             | ~7    | Session termination              |
| seed.php               | ~82   | Database population              |
| api/main.php           | ~78   | REST API endpoints               |
| includes/header.php    | ~60   | Shared navigation                |
| includes/footer.php    | ~31   | Shared footer                    |
| includes/functions.php | ~30   | Helper functions                 |
| admin/dashboard.php    | ~102  | Admin overview                   |
| admin/products.php     | ~191  | Product CRUD                     |
| admin/orders.php       | ~99   | Order management                 |
| admin/users.php        | ~84   | User management                  |
| admin/settings.php     | ~159  | Site configuration               |
| admin/admin_nav.php    | ~21   | Admin navigation                 |
| js/app.js              | ~200  | Frontend logic                   |
| assets/style.css       | ~251  | Global styles                    |

