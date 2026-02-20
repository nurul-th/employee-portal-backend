# Employee Document Portal — Backend

Backend API for Employee Document Portal built using **Laravel 12**.

---

## 🚀 Tech Stack

- Laravel Framework 12.49.0
- PHP 8+
- PostgreSQL
- Laravel Sanctum (API Authentication)
- Spatie Laravel Permission (RBAC)
- REST API

---

## 📦 Features

- Authentication (Login / Logout)
- Role-Based Access Control (Admin / Manager / Employee)
- Document CRUD
- File Upload & Download
- Access Level Control (public / department / private)
- Search (title + description)
- Filter (category + department)
- Sorting & Pagination
- Dashboard statistics:
  - Recent uploads
  - Top downloads

---

## 👥 Demo Accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@example.com | password |
| Manager | manager@example.com | password |
| Employee | employee@example.com | password |

---

## ⚙️ Installation

### 1. Clone project

```bash
    git clone <repo-url>
    cd employee-document-portal-backend

2. Install Dependencies
    composer install

3. Environment Setup
    cp .env.example .env
    php artisan key:generate

    Configure PostgreSQL Database inside .env:
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=your_db
    DB_USERNAME=your_user
    DB_PASSWORD=your_password

4. Run migration and seed
    php artisan migrate:fresh --seed

This will create:

    * Roles (Admin, Manager, Employee)
    * Demo users
    * Sample documents

5. Storage link
    php artisan storage:link

6. Run server
    php artisan serve

Backend URL http://localhost:8000

7. Authentication - uses Laravel Sanctum
    * Login endpoint - POST /api/v1/login
    * Token must be sent via - Authorization: Bearer <token>

8. API Structure

/api/v1
├── login
├── logout
├── user
├── documents
│   ├── list
│   ├── show
│   ├── store
│   ├── update
│   ├── delete
│   └── download
├── departments
└── categories

9. RBAC Rules

i. Admin

    * Full access
    * View all documents
    * Upload / Edit / Delete

ii. Manager

    * Upload documents
    * Edit/Delete own uploads
    * Department-based access

iii. Employee

    * View only allowed documents
    * No upload/edit/delete

Notes

- Seeded documents may not contain real files.
- Download works only for uploaded files that exist in storage.