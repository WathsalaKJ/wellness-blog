# 🧘‍♀️ SoulBalance - Wellness & Yoga Blog Platform

![SoulBalance Banner](assets/images/hero-yoga.png)

**SoulBalance** is a modern, feature-rich blog platform dedicated to wellness, yoga, meditation, and holistic health. Built with PHP, MySQL, and modern web technologies, it provides a complete blogging ecosystem for wellness enthusiasts.

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Active-success)](https://github.com/yourusername/soulbalance)

---

## 📋 Table of Contents

- [Features](#-features)
- [Demo](#-demo)
- [Screenshots](#-screenshots)
- [Tech Stack](#-tech-stack)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Deployment](#-deployment)
- [Usage](#-usage)
- [Project Structure](#-project-structure)
- [API Endpoints](#-api-endpoints)
- [Security](#-security)
- [Contributing](#-contributing)
- [License](#-license)
- [Contact](#-contact)

---

## ✨ Features

### User Features
- 🔐 **User Authentication** - Secure registration, login, and session management
- ✍️ **Rich Text Editor** - Create and edit blog posts with formatting, links, lists, and headings
- 🖼️ **Image Upload** - Upload featured images for blog posts (up to 5MB)
- 📂 **Category System** - Organize posts by categories (Yoga, Meditation, Nutrition, etc.)
- ⭐ **Rating System** - Rate posts with 5-star system (both logged-in and anonymous users)
- 💬 **Comments** - Engage with blog posts through comments
- 📱 **Responsive Design** - Fully mobile-friendly interface
- 🎨 **Modern UI/UX** - Clean, intuitive design with smooth animations

### Content Management
- 📝 **Personal Dashboard** - Manage your blog posts in one place
- ✏️ **CRUD Operations** - Create, Read, Update, Delete blog posts
- 🔍 **Search & Filter** - Browse posts by category
- 📊 **Post Statistics** - View total posts and last activity
- 🖼️ **Featured Images** - Visual blog post thumbnails

### Technical Features
- 🔒 **Security** - Password hashing, SQL injection prevention, XSS protection
- 📧 **Contact Form** - Store contact messages in database
- 🎯 **SEO Optimized** - Meta tags, semantic HTML, sitemap-ready
- 🚀 **Performance** - Optimized database queries, lazy loading images
- 📱 **Progressive Enhancement** - Works without JavaScript (core features)
- 🌐 **Cross-browser Compatible** - Works on Chrome, Firefox, Safari, Edge

---

## 🎥 Demo

**Live Demo:** [https://soulbalance.infinityfreeapp.com](https://soulbalance.infinityfreeapp.com) *(Coming Soon)*

### Test Credentials:
```
Username: demo_user
Password: Demo@123
```

---

## 📸 Screenshots

### Homepage
![Homepage](docs/screenshots/homepage.png)

### Blog Post View
![Blog Post](docs/screenshots/blog-post.png)

### Dashboard
![Dashboard](docs/screenshots/dashboard.png)

### Rich Text Editor
![Editor](docs/screenshots/editor.png)

---

## 🛠️ Tech Stack

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Custom properties, Grid, Flexbox, animations
- **JavaScript (Vanilla)** - No frameworks, pure JS
- **Google Fonts** - Playfair Display, Lato, Inter

### Backend
- **PHP 7.4+** - Server-side logic
- **MySQL 5.7+** - Database management
- **PDO** - Database abstraction layer
- **Sessions** - User authentication

### Tools & Libraries
- **FileZilla** - FTP client for deployment
- **phpMyAdmin** - Database administration
- **Font Awesome** - Icons
- **InfinityFree** - Free hosting platform

---

## 💻 Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP (for local development)

### Local Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/soulbalance.git
   cd soulbalance
   ```

2. **Start XAMPP/WAMP**
   - Start Apache and MySQL services

3. **Create Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create new database: `soulbalance_blog`
   - Import SQL file: `database.sql`

4. **Configure Environment**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` file:
   ```env
   DB_HOST=localhost
   DB_NAME=soulbalance_blog
   DB_USER=root
   DB_PASS=
   APP_URL=http://localhost/soulbalance
   ```

5. **Set Folder Permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/blogs/
   chmod 755 logs/
   ```

6. **Access Application**
   - Open browser: `http://localhost/soulbalance`
   - Register new account or use demo credentials

---

## ⚙️ Configuration

### Environment Variables

All configuration is managed through the `.env` file:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=soulbalance_blog
DB_USER=root
DB_PASS=

# Application Settings
APP_URL=http://localhost/soulbalance
APP_ENV=development
DEBUG_MODE=true

# Security
SECRET_KEY=your-random-secret-key-here

# Session
SESSION_LIFETIME=3600
TIMEZONE=Asia/Colombo

# File Uploads
MAX_UPLOAD_SIZE=5242880
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif,webp
```

### Database Configuration

The database schema includes:
- `user` - User accounts and authentication
- `blogPost` - Blog posts with content and metadata
- `blog_comments` - User comments on posts
- `blog_ratings` - User ratings (logged-in users)
- `blog_ratings_public` - Anonymous ratings
- `contact_messages` - Contact form submissions

---

## 🚀 Deployment

### InfinityFree Hosting

Detailed deployment guide: [DEPLOYMENT.md](docs/DEPLOYMENT.md)

**Quick Steps:**

1. **Create Account** at [InfinityFree.net](https://infinityfree.net)

2. **Create Database** via control panel

3. **Upload Files** via FTP (FileZilla)
   ```
   Host: ftpupload.net
   Username: if0_XXXXXXXX
   Password: [your_password]
   Port: 21
   ```

4. **Import Database** via phpMyAdmin
   - Use `database_infinityfree.sql` (without CREATE DATABASE)

5. **Update .env** with production credentials
   ```env
   DB_HOST=sqlXXX.infinityfree.com
   DB_NAME=if0_XXXXXXXX_soulbalance
   DB_USER=if0_XXXXXXXX
   DB_PASS=[database_password]
   APP_URL=https://yoursite.infinityfreeapp.com
   APP_ENV=production
   DEBUG_MODE=false
   ```

6. **Set Permissions** (755 for uploads/, logs/)

7. **Test Site** and verify all functionality

---

## 📖 Usage

### For Users

1. **Register an Account**
   - Navigate to Sign Up page
   - Enter username, email, and password
   - Verify email (if enabled)

2. **Create a Blog Post**
   - Login to your account
   - Go to Dashboard
   - Click "Create New Post"
   - Add title, content, category, and featured image
   - Click "Publish Post"

3. **Manage Posts**
   - View all your posts in Dashboard
   - Edit or delete posts
   - View post statistics

4. **Engage with Content**
   - Rate blog posts (1-5 stars)
   - Leave comments
   - Browse posts by category

### For Developers

**Creating Custom Pages:**

```php
<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';

// Your page logic here
$db = getDB();
// Database operations

// Include header
include 'includes/header.php';
?>

<!-- Your HTML content -->

<?php include 'includes/footer.php'; ?>
```

**Database Queries:**

```php
// Get all posts
$stmt = $db->query("SELECT * FROM blogPost ORDER BY created_at DESC");
$posts = $stmt->fetchAll();

// Get post by ID
$stmt = $db->prepare("SELECT * FROM blogPost WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();

// Insert new post
$stmt = $db->prepare("INSERT INTO blogPost (user_id, title, content) VALUES (?, ?, ?)");
$stmt->execute([$userId, $title, $content]);
```

---

## 📁 Project Structure

```
soulbalance/
├── api/                          # API endpoints
│   ├── auth/                     # Authentication APIs
│   │   ├── login.php            # Login endpoint
│   │   ├── logout.php           # Logout endpoint
│   │   └── register.php         # Registration endpoint
│   ├── blogs/                    # Blog CRUD APIs
│   │   ├── create.php           # Create blog post
│   │   ├── read.php             # Read blog posts
│   │   ├── update.php           # Update blog post
│   │   └── delete.php           # Delete blog post
│   └── blog_interactions.php    # Comments & ratings
│
├── assets/                       # Static assets
│   ├── css/
│   │   └── style.css            # Main stylesheet
│   ├── images/                  # Site images
│   └── js/
│       └── main.js              # JavaScript functions
│
├── config/                       # Configuration files
│   ├── config.php               # App configuration
│   └── database.php             # Database connection
│
├── includes/                     # Reusable components
│   ├── header.php               # Site header
│   └── footer.php               # Site footer
│
├── uploads/                      # User uploads
│   └── blogs/                   # Blog featured images
│
├── logs/                         # Error logs
│   └── error.log
│
├── .env                         # Environment variables (DO NOT COMMIT)
├── .env.example                 # Environment template
├── .gitignore                   # Git ignore rules
├── .htaccess                    # Apache configuration
│
├── index.php                    # Homepage
├── about.php                    # About page
├── contact.php                  # Contact page
├── categories.php               # Categories listing
├── category_posts.php           # Posts by category
├── latest_blogs.php             # All blogs page
│
├── login.php                    # Login page
├── register.php                 # Registration page
├── logout.php                   # Logout handler
│
├── dashboard.php                # User dashboard
├── add_blog.php                 # Create blog post
├── edit_blog.php                # Edit blog post
├── delete_blog.php              # Delete blog post
├── view_blog.php                # View single blog post
│
├── database.sql                 # Database schema (local)
├── database_infinityfree.sql    # Database schema (hosting)
├── README.md                    # This file
└── LICENSE                      # MIT License
```

---

## 🔌 API Endpoints

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/auth/register.php` | Register new user | No |
| POST | `/api/auth/login.php` | User login | No |
| POST | `/api/auth/logout.php` | User logout | Yes |

### Blog Posts

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/blogs/read.php` | Get all posts | No |
| GET | `/api/blogs/read.php?id={id}` | Get single post | No |
| POST | `/api/blogs/create.php` | Create new post | Yes |
| PUT | `/api/blogs/update.php` | Update post | Yes (Owner) |
| DELETE | `/api/blogs/delete.php` | Delete post | Yes (Owner) |

### Interactions

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/blog_interactions.php` | Add rating | Varies |
| POST | `/api/blog_interactions.php` | Add comment | Yes |
| DELETE | `/api/blog_interactions.php` | Delete comment | Yes (Owner) |

**Example API Request:**

```javascript
// Create blog post
fetch('/api/blogs/create.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title: 'My First Post',
    content: 'Post content here',
    category: 'Yoga Practices'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

---

## 🔒 Security

### Implemented Security Measures

✅ **Password Security**
- Bcrypt hashing (PASSWORD_BCRYPT)
- Minimum 8 characters requirement
- Salted hashes

✅ **SQL Injection Prevention**
- PDO prepared statements
- Input validation and sanitization
- Parameterized queries

✅ **XSS Protection**
- `htmlspecialchars()` for output
- Content Security Policy headers
- Input sanitization

✅ **Session Security**
- Secure session handling
- Session regeneration on login
- Session timeout (1 hour default)

✅ **File Upload Security**
- File type validation
- File size limits (5MB)
- Secure file naming (unique IDs)
- Mime type checking

✅ **CSRF Protection**
- Form token validation (recommended to add)
- Referer checking

✅ **Environment Security**
- `.env` file protection
- Sensitive data not in version control
- `.htaccess` protections

### Security Best Practices

```php
// Always escape output
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// Use prepared statements
$stmt = $db->prepare("SELECT * FROM user WHERE email = ?");
$stmt->execute([$email]);

// Validate file uploads
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($extension, $allowed)) {
    die('Invalid file type');
}

// Check user authorization
if ($_SESSION['user_id'] !== $post['user_id']) {
    die('Unauthorized');
}
```

---

## 🤝 Contributing

We welcome contributions! Please follow these guidelines:

### How to Contribute

1. **Fork the Repository**
   ```bash
   git clone https://github.com/yourusername/soulbalance.git
   ```

2. **Create Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make Changes**
   - Follow coding standards
   - Add comments for complex logic
   - Test thoroughly

4. **Commit Changes**
   ```bash
   git add .
   git commit -m "Add: Your feature description"
   ```

5. **Push to GitHub**
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Create Pull Request**
   - Describe your changes
   - Reference any issues
   - Wait for review

### Coding Standards

- **PHP**: PSR-12 coding style
- **CSS**: BEM naming convention
- **JavaScript**: ES6+ syntax
- **Commits**: Conventional commits format

### Development Guidelines

- Write clean, readable code
- Comment complex logic
- Use meaningful variable names
- Follow existing code structure
- Test before committing
- Update documentation

---

## 🐛 Bug Reports & Feature Requests

### Reporting Bugs

Found a bug? Please open an issue with:
- Bug description
- Steps to reproduce
- Expected vs actual behavior
- Screenshots (if applicable)
- System information

### Requesting Features

Have an idea? Open an issue with:
- Feature description
- Use case
- Proposed implementation
- Priority level

---

## 📝 Changelog

### Version 1.0.0 (2025-01-XX)

#### Added
- User registration and authentication
- Blog post CRUD operations
- Rich text editor with formatting
- Image upload functionality
- Category system
- Rating system (user + anonymous)
- Comments system
- Responsive design
- Contact form
- Dashboard for users

#### Security
- Password hashing with bcrypt
- SQL injection prevention
- XSS protection
- Session management

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2025 SoulBalance

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
```

---

## 👥 Authors

**Your Name**
- GitHub: [@yourusername](https://github.com/yourusername)
- Email: your.email@example.com
- LinkedIn: [Your Name](https://linkedin.com/in/yourprofile)

---

## 🙏 Acknowledgments

- **Font Awesome** for beautiful icons
- **Google Fonts** for typography
- **InfinityFree** for hosting
- **Unsplash** for stock images
- **PHP Community** for excellent documentation

---

## 📞 Contact & Support

### Get in Touch
- **Website:** [soulbalance.infinityfreeapp.com](https://soulbalance.infinityfreeapp.com)
- **Email:** hello@soulbalance.com
- **Twitter:** [@soulbalance](https://twitter.com/soulbalance)
- **Instagram:** [@soulbalance](https://instagram.com/soulbalance)

### Support
- 📖 [Documentation](docs/)
- 💬 [Discussions](https://github.com/yourusername/soulbalance/discussions)
- 🐛 [Issue Tracker](https://github.com/yourusername/soulbalance/issues)
- 📧 Email Support: support@soulbalance.com

---

## 🌟 Star History

[![Star History Chart](https://api.star-history.com/svg?repos=yourusername/soulbalance&type=Date)](https://star-history.com/#yourusername/soulbalance&Date)

---

## 🎯 Roadmap

### Upcoming Features (v1.1.0)
- [ ] Email verification for registration
- [ ] Password reset functionality
- [ ] User profile pages
- [ ] Social media sharing
- [ ] Newsletter subscription
- [ ] Admin dashboard
- [ ] Post analytics
- [ ] Tag system
- [ ] Search functionality
- [ ] Draft posts

### Future Plans (v2.0.0)
- [ ] RESTful API
- [ ] Mobile app (React Native)
- [ ] Multi-language support
- [ ] Dark mode
- [ ] Advanced text editor (Quill/TinyMCE)
- [ ] Video uploads
- [ ] Live chat support
- [ ] Push notifications

---

<div align="center">

**Made with ❤️ for the wellness community**

⭐ Star this repo if you find it helpful!

[Report Bug](https://github.com/yourusername/soulbalance/issues) · [Request Feature](https://github.com/yourusername/soulbalance/issues) · [Documentation](docs/)

</div>
