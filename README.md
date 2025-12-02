# GetPayIn – Payment & Inventory Hold System (Laravel)


## 📌 Project Summary

GetPayIn is a Laravel-based backend system designed to ensure **safe payments**,  
**real-time product stock reservation**, and **idempotent webhook handling**.


A clean and production-ready Laravel API implementing:
- Idempotent payment processing  
- Webhook-safe order updates  
- Stock reservation using holds  
- Automatic hold expiry and release  
- Database transactions & locking  
- RESTful API structure  

---

## 🚀 Features

### 🔹 Payment Processing
- Uses **idempotency keys** to ensure safe retries.
- Payment webhook (`/api/payments/webhook`) updates orders:
  - **paid** → set order to *paid*
  - **failed** → release stock & cancel order
- Handles webhook arriving *multiple times or before client response* safely.

### 🔹 Product Stock Hold System
- Atomic database transactions using `lockForUpdate()`
- Prevents overselling during checkout
- Auto-delete expired holds
- Supports multi-client concurrency
- Hold expires automatically after 2 minutes (configurable)

### 🔹 API Reliability
- Fully REST-based endpoints
- Strong request validation
- Consistent JSON error handling
- Clear success/error structures

---

## API Endpoints

### 1. Product
**Route:** `GET /api/product/{productId}`  
**Purpose:** Get product details with real-time stock availability.


```json
{
    "id": "4",
    "name": "Product",
    "price": "1000.00",
    "stock": 3
}
```

---

### 2. Hold
**Route:** `POST /api/holds`  
**Purpose:** Create a temporary reservation for a product. 
**Request Example:**


```json
{
  "product_id": 4,
  "qty": 2
}
```
---

### 3. Order
**Route:** `POST /api/orders`  
**Purpose:** Convert a valid hold into a pre-payment order.  
**Request Example:**
```json
{
  "hold_id": 10
}
```

---

### 4. Payment Webhook
**Route:** `POST /api/payments/webhook`  
**Purpose:** Handle payment notifications in an idempotent and concurrency-safe manner.  
**Request Example:**
```json
{
  "idempotency": "123456789",
  "order_id": 5,
  "status": "success"
}
```



## 📦 Installation

```bash
git clone https://github.com/Jeemyy/GetPayIn.git
cd GetPayIn

composer install
cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan serve
