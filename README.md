# GetPayIn – Payment & Inventory Hold System (Laravel)

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

## 📦 Installation

```bash
git clone https://github.com/your-username/your-repo.git
cd your-repo

composer install
cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan serve
