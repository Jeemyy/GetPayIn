# GetPayIn – Payment & Inventory Hold System (Laravel)

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)

## 📌 Project Summary

GetPayIn is a Laravel-based backend system designed to ensure **safe payments**,  
**real-time product stock reservation**, and **idempotent webhook handling**.

A clean and production-ready Laravel API implementing:
- ✅ Idempotent payment processing  
- ✅ Webhook-safe order updates  
- ✅ Stock reservation using holds  
- ✅ Automatic hold expiry and release  
- ✅ Database transactions & locking  
- ✅ RESTful API structure  

---

## 🚀 Features

### 🔹 Payment Processing
- Uses **idempotency keys** to ensure safe retries
- Payment webhook (`/api/payments/webhook`) updates orders:
  - **success** → set order to *paid*
  - **failur** → release stock & cancel order
- Handles webhook arriving *multiple times or before client response* safely
- Prevents duplicate payment processing

### 🔹 Product Stock Hold System
- Atomic database transactions using `lockForUpdate()`
- Prevents overselling during checkout
- Auto-delete expired holds
- Supports multi-client concurrency
- Hold expires automatically after 2 minutes (configurable)
- Real-time stock availability calculation

### 🔹 API Reliability
- Fully REST-based endpoints
- Strong request validation
- Consistent JSON error handling
- Clear success/error structures
- Proper HTTP status codes

---

## 🛠️ Tech Stack

- **Framework:** Laravel 12.x
- **PHP:** 8.2+
- **Database:** MySQL/PostgreSQL/SQLite
- **Cache:** Laravel Cache (for stock calculations)

---

## 📦 Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL/PostgreSQL/SQLite
- Node.js & NPM (for frontend assets if needed)

### Setup Steps

```bash
# Clone the repository
git clone https://github.com/Jeemyy/GetPayIn.git
cd GetPayIn

# Install dependencies
composer install

# Environment setup
php artisan key:generate

# Configure your database in .env file
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=your_database
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# (Optional) Seed database with sample data
php artisan db:seed

# Start the development server
php artisan serve
```

The API will be available at `http://localhost:8000`

---

## 📚 API Documentation

All endpoints return JSON responses. The API uses standard HTTP status codes.

### Base URL
```
http://localhost:8000/api
```

---

### 1. Get Product Details

**Endpoint:** `GET /api/product/{productId}`  
**Purpose:** Get product details with real-time stock availability (excluding held stock).

**URL Parameters:**
- `productId` (integer, required) - The product ID

**Response (200 OK):**
```json
{
    "id": 4,
    "name": "Product Name",
    "price": "1000.00",
    "stock": 3
}
```

**Error Response (404 Not Found):**
```json
{
    "msg": "No query results for model [App\\Models\\Product] 4"
}
```

**Error Response (500 Internal Server Error):**
```json
{
    "msg": "Error message"
}
```

---

### 2. Create Hold

**Endpoint:** `POST /api/holds`  
**Purpose:** Create a temporary reservation for a product quantity. Hold expires after 2 minutes.

**Request Body:**
```json
{
    "product_id": 4,
    "qty": 2
}
```

**Validation Rules:**
- `product_id` (required, exists in products table)
- `qty` (required, integer, minimum: 1)

**Success Response (201 Created):**
```json
{
    "hold_id": 10,
    "expires_at": "2024-01-15T10:32:00.000000Z"
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
    "error": "Validation failed",
    "messages": {
        "product_id": ["The selected product id is invalid."],
        "qty": ["Not Enough Stock Available"]
    }
}
```

**Notes:**
- Automatically deletes expired holds before checking availability
- Uses database locking to prevent race conditions
- Clears product stock cache after creation

---

### 3. Create Order

**Endpoint:** `POST /api/orders`  
**Purpose:** Convert a valid hold into a pre-payment order.

**Request Body:**
```json
{
    "hold_id": 10
}
```

**Validation Rules:**
- `hold_id` (required, exists in holds table)

**Success Response (201 Created):**
```json
{
    "order_id": 5,
    "status": "pending"
}
```

**Error Response (422 Unprocessable Entity):**

Hold not found:
```json
{
    "error": "Validation Error",
    "messages": {
        "hold_id": ["The selected hold is invalid."]
    }
}
```

Hold already used:
```json
{
    "error": "Validation Error",
    "messages": {
        "hold_id": ["This hold has already been used."]
    }
}
```

Hold expired:
```json
{
    "error": "Validation Error",
    "messages": {
        "hold_id": ["This hold has expired."]
    }
}
```

**Notes:**
- Marks the hold as `used` after order creation
- Uses database transactions with row-level locking
- Order status is set to `pending` initially

---

### 4. Payment Webhook

**Endpoint:** `POST /api/payments/webhook`  
**Purpose:** Handle payment notifications in an idempotent and concurrency-safe manner.

**Request Body:**
```json
{
    "idempotency": "123456789",
    "order_id": 5,
    "status": "success"
}
```

**Validation Rules:**
- `idempotency` (required, string) - Unique key to prevent duplicate processing
- `order_id` (required, exists in orders table)
- `status` (required, in: `success`, `failur`)

**Success Response (201 Created):**
```json
{
    "msg": "The Payment Process Is Success",
    "order_status": "paid"
}
```

**Error Responses:**

Duplicate webhook (422):
```json
{
    "msg": "The Webhook Is Already Processed"
}
```

Order already completed (422):
```json
{
    "msg": "The Order Is Already Completed"
}
```

Validation error (422):
```json
{
    "error": "Validation Error",
    "msg": {
        "order_id": ["Order Not Found"]
    }
}
```

**Behavior:**
- **Status `success`:** Sets order status to `paid`
- **Status `failur`:** Sets order status to `cancelled` and releases the hold (sets `used = false`)
- Uses idempotency keys to prevent duplicate processing
- Uses database transactions with row-level locking

---

## 🔄 Workflow

### Typical Order Flow

1. **Check Product Availability**
   ```
   GET /api/product/{productId}
   ```
   Returns available stock (excluding held quantities)

2. **Create Hold**
   ```
   POST /api/holds
   Body: { "product_id": 4, "qty": 2 }
   ```
   Reserves stock for 2 minutes

3. **Create Order**
   ```
   POST /api/orders
   Body: { "hold_id": 10 }
   ```
   Converts hold to order (status: `pending`)

4. **Process Payment Webhook**
   ```
   POST /api/payments/webhook
   Body: { "idempotency": "unique-key", "order_id": 5, "status": "success" }
   ```
   Updates order status:
   - `success` → `paid`
   - `failur` → `cancelled` (releases hold)

---

## 📊 HTTP Status Codes

| Code | Description |
|------|-------------|
| 200  | OK - Request successful |
| 201  | Created - Resource created successfully |
| 404  | Not Found - Resource not found |
| 422  | Unprocessable Entity - Validation error or business logic error |
| 500  | Internal Server Error - Server error |

---

## ⚠️ Error Response Format

All error responses follow a consistent structure:

**Validation Errors (422):**
```json
{
    "error": "Validation Error",
    "messages": {
        "field_name": ["Error message 1", "Error message 2"]
    }
}
```

**General Errors:**
```json
{
    "msg": "Error message"
}
```

---

## 🔒 Concurrency & Safety Features

### Database Locking
- Uses `lockForUpdate()` to prevent race conditions
- Ensures atomic operations during stock checks and updates

### Idempotency
- Payment webhooks use idempotency keys stored in `payment_keys` table
- Prevents duplicate processing of the same payment notification

### Transaction Safety
- All critical operations wrapped in database transactions
- Automatic rollback on errors

### Hold Expiry
- Holds automatically expire after 2 minutes
- Expired holds are cleaned up before stock availability checks
- Stock is released when holds expire

---

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/OrderTest.php

# Run with filter
php artisan test --filter=testName
```

---

## 📝 Environment Configuration

Key environment variables in `.env`:

```env
APP_NAME=GetPayIn
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

---

## 🗂️ Database Schema

### Products
- `id` - Primary key
- `name` - Product name
- `price` - Product price
- `stock` - Available stock quantity

### Holds
- `id` - Primary key
- `product_id` - Foreign key to products
- `qty` - Reserved quantity
- `expires_at` - Hold expiration timestamp
- `used` - Boolean flag (true when converted to order)

### Orders
- `id` - Primary key
- `hold_id` - Foreign key to holds
- `status` - Order status (`pending`, `paid`, `cancelled`)

### Payment Keys
- `id` - Primary key
- `idempotency` - Unique idempotency key
- `order_id` - Foreign key to orders
- `processed_at` - Processing timestamp

---

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 👤 Author

**Jeemyy**

- GitHub: [@Jeemyy](https://github.com/Jeemyy)

---

## 🙏 Acknowledgments

- Laravel Framework
- All contributors and maintainers
