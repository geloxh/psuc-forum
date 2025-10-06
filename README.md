<div align="center">

# 🎓 PSUC Forum

**Philippine State Universities and Colleges Forum**

*A modern, responsive forum platform connecting PSUC communities nationwide*

[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/License-Educational-green?style=flat-square)](LICENSE)

[🚀 Quick Start](#-quick-start-windows) • [📋 Features](#-features) • [🛠️ Tech Stack](#️-tech-stack) • [📖 Documentation](#-documentation)

</div>

---

## 🚀 Quick Start (Windows)

### Prerequisites
- **XAMPP** (includes PHP 7.4+ and MySQL 5.7+)
- **Web Browser** (Chrome, Firefox, Edge)
- **Text Editor** (VS Code, Notepad++)

### 📦 Installation Steps

#### Step 1: Download and Install XAMPP
```bash
# Download XAMPP from https://www.apachefriends.org/
# Install XAMPP to C:\xampp
```

#### Step 2: Setup Project
```bash
# 1. Copy forum files to XAMPP htdocs
C:\xampp\htdocs\psuc-forum\

# 2. Start XAMPP Control Panel
# - Start Apache
# - Start MySQL
```

#### Step 3: Database Setup
```bash
# 1. Open phpMyAdmin: http://localhost/phpmyadmin
# 2. Create new database: psuc_forum
# 3. Import config/init.sql file
```

#### Step 4: Configuration
```php
// Edit config/database.php
$host = 'localhost';
$db_name = 'psuc_forum';
$username = 'root';
$password = ''; // Leave empty for XAMPP default
```

#### Step 5: Launch Forum
```bash
# Open browser and navigate to:
http://localhost/psuc-forum
```

### 🎯 First Admin Setup
1. Register a new account
2. Open phpMyAdmin → psuc_forum → users table
3. Edit your user record: change `role` from `user` to `admin`
4. Login and access Admin Panel

---

## ✨ Features

<table>
<tr>
<td width="50%">

### 👥 **User Management**
- 🔐 Secure registration & authentication
- 🏫 University-specific profiles
- 🎭 Role-based permissions
- ⭐ Reputation system

### 💬 **Forum Structure**
- 📁 Hierarchical categories
- 🗂️ Organized forums
- 📌 Pinned & locked topics
- 📊 Real-time statistics

### 🔍 **Search & Discovery**
- 🔎 Full-text search
- 🏷️ Topic categorization
- 📈 Trending discussions
- 🔥 Popular content

</td>
<td width="50%">

### 💌 **Communication**
- 📧 Private messaging
- 💬 Thread discussions
- 👍 Voting system
- 🔔 Notifications

### 🎨 **User Experience**
- 📱 Fully responsive design
- 🌙 Dark/Light mode toggle
- ⚡ Fast loading
- 🎯 Intuitive navigation

### 🛡️ **Administration**
- 📊 Comprehensive dashboard
- 👮 Content moderation
- 📈 Analytics & insights
- ⚙️ System management

</td>
</tr>
</table>

---

## 🛠️ Tech Stack

<div align="center">

| Layer | Technology | Version |
|-------|------------|----------|
| **Backend** | PHP | 7.4+ |
| **Database** | MySQL | 5.7+ |
| **Frontend** | HTML5, CSS3, JavaScript | Latest |
| **Icons** | Font Awesome | 6.0+ |
| **Fonts** | Google Fonts (Inter) | Latest |
| **Server** | Apache (XAMPP) | Latest |

</div>

---

## 📁 Project Structure

```
📦 PSUC Forum/
├── 📂 config/
│   ├── 🔧 database.php      # Database configuration
│   └── 🗄️ init.sql          # Database schema & seed data
├── 📂 includes/
│   ├── 🔐 auth.php          # Authentication system
│   ├── 💬 forum.php         # Core forum functionality
│   └── 🧩 header.php        # Reusable header component
├── 📂 assets/
│   ├── 🎨 style.css         # Main stylesheet
│   └── 🌙 dark-theme.css    # Dark mode styles
├── 📂 admin/
│   └── 📊 index.php         # Admin dashboard
├── 🏠 index.php             # Homepage
├── 🔑 login.php             # User login
├── 📝 register.php          # User registration
├── 💬 forum.php             # Forum discussions
├── 📄 topic.php             # Topic view & replies
├── 🔍 search.php            # Search functionality
├── 💌 messages.php          # Private messaging
├── ➕ new_topic.php         # Create new topics
└── 🚪 logout.php            # User logout
```

---

## 📖 Documentation

### 🎯 User Roles & Permissions

| Role | Permissions |
|------|-------------|
| **👑 Admin** | Full system access, user management, content moderation |
| **🛡️ Moderator** | Content moderation, topic management |
| **👨‍🏫 Faculty** | Create announcements, pin topics, moderate discussions |
| **🎓 Student** | Create topics, reply to posts, private messaging |

### 🚀 Getting Started Guide

#### For Students & Faculty
1. **Register** → Select your university from the dropdown
2. **Explore** → Browse categories relevant to your field
3. **Participate** → Create topics, reply to discussions
4. **Connect** → Use private messaging to collaborate
5. **Contribute** → Vote on helpful content

#### For Administrators
1. **Dashboard** → Monitor forum activity and statistics
2. **User Management** → Manage roles and permissions
3. **Content Moderation** → Review and moderate discussions
4. **System Settings** → Configure forum preferences

### 🔒 Security Features

- 🔐 **Password Security**: Bcrypt hashing with salt
- 🛡️ **SQL Injection Protection**: Prepared statements
- 🚫 **XSS Prevention**: Input sanitization
- 🎫 **Session Management**: Secure session handling
- 👮 **Role-Based Access**: Granular permissions

### 🌐 Browser Compatibility

| Browser | Minimum Version |
|---------|----------------|
| Chrome | 70+ |
| Firefox | 65+ |
| Safari | 12+ |
| Edge | 79+ |

### 🤝 Contributing

We welcome contributions from the PSUC community! Please follow these guidelines:

- 📝 Follow PHP PSR standards
- 🧪 Test your changes thoroughly
- 📚 Update documentation as needed
- 🔒 Maintain security best practices

### 📄 License

**Educational Use License** - Designed specifically for Philippine State Universities and Colleges

---

<div align="center">

*Connecting minds, sharing knowledge, building futures*

</div>