# SoulBalance

A wellness and yoga blog platform built with PHP and MySQL. Users can register, publish blog posts with a rich-text editor and featured images, browse posts by category, and rate or comment on content.

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479a1)](https://www.mysql.com/)

## Features

- User registration, login, and session-based authentication
- Rich-text blog editor with image upload for featured posts
- Categories (Yoga, Meditation, Nutrition) with filtered browsing
- 5-star rating system for logged-in and anonymous visitors
- Comments on blog posts
- Personal dashboard for managing your own posts (create, edit, delete)
- Contact form with database-backed message storage
- Responsive layout across devices

## Tech Stack

| Layer     | Technology                       |
|-----------|-----------------------------------|
| Frontend  | HTML5, CSS3, vanilla JavaScript  |
| Backend   | PHP 7.4+, PDO                    |
| Database  | MySQL 5.7+                       |
| Local dev | XAMPP or WAMP                    |

## Project Structure

```
soulbalance/
├── api/                  # JSON endpoints (auth, blog CRUD, comments/ratings)
│   ├── auth/
│   └── blogs/
├── assets/                # CSS, images, JS
├── config/                # DB connection and app configuration
├── includes/              # Shared header/footer partials
├── uploads/                # User-uploaded post images
├── database.sql            # Database schema
├── index.php, about.php, contact.php, categories.php, ...
├── login.php, register.php, logout.php
└── dashboard.php, add_blog.php, edit_blog.php, view_blog.php
```

## Author

**Wathsala K.J.** — [github.com/WathsalaKJ](https://github.com/WathsalaKJ)
