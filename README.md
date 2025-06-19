# 📘 Laravel Blog API

A RESTful Blog API built with Laravel 11 that supports multi-language posts, JWT authentication, nested comments, and media upload.

---

## Features

-  **User Authentication**
  - Register with password confirmation
  - Login using JWT
  - All API requests protected with token

-  **Posts**
  - CRUD operations
  - Multilingual `title` & `content` using [Spatie Translatable](https://github.com/spatie/laravel-translatable)
  - Upload multiple images using [Spatie MediaLibrary](https://spatie.be/docs/laravel-medialibrary)
  - Slug used instead of ID

-  **Comments**
  - Add comments and nested replies
  - Update/Delete own comment
  - Show author name with each comment

-  **General**
  - Language support via `Accept-Language` header (e.g. `en`, `ar`)
  - Laravel 11
  - JWT via `tymon/jwt-auth`
  - Fully tested via Postman

---

##  Installation

```bash
git clone https://github.com/Alaa278/laravel-blog-api.git
cd laravel-blog-api
composer install
cp .env.example .env
php artisan key:generate

## Configure .env file:
Set your database settings and JWT secret:

DB_DATABASE=your_db
DB_USERNAME=root
DB_PASSWORD=
JWT_SECRET=generate_this_later

php artisan jwt:secret
php artisan migrate

