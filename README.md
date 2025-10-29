# 📸 Kislap - Photography Booking Platform

<div align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5">
  <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3">
</div>

<div align="center">
  <h3>🌟 Connect Clients with Professional Photographers 🌟</h3>
  <p>A modern, full-featured photography booking platform that bridges the gap between talented photographers and clients seeking professional photography services.</p>
</div>

---

## 🚀 Features

### 👥 **Dual User System**
- **Clients**: Browse, book, and manage photography sessions
- **Photographers**: Showcase portfolios, manage bookings, and grow their business

### 📱 **Core Functionality**
- 🔍 **Advanced Search & Filtering** - Find photographers by specialty, location, price range
- 💬 **Real-time Messaging** - Built-in chat system for seamless communication
- 📅 **Booking Management** - Complete booking lifecycle from inquiry to completion
- 💳 **Payment Processing** - Secure deposit and final payment handling
- ⭐ **Rating & Review System** - Build trust through authentic feedback
- 📊 **Analytics Dashboard** - Comprehensive statistics for photographers

### 🎨 **User Experience**
- 📱 **Responsive Design** - Optimized for desktop, tablet, and mobile
- 🌙 **Modern Dark Theme** - Sleek, professional interface
- ⚡ **Fast Performance** - Optimized loading and smooth interactions
- 🔒 **Secure Authentication** - Robust user registration and login system

---

## 🛠️ Technology Stack

### **Backend**
- **PHP 8.0+** - Server-side logic and API endpoints
- **MySQL** - Relational database for data persistence
- **PDO** - Database abstraction layer for security

### **Frontend**
- **HTML5** - Semantic markup structure
- **CSS3** - Modern styling with Flexbox/Grid
- **JavaScript (ES6+)** - Interactive functionality and AJAX
- **Font Awesome** - Professional icon library

### **Architecture**
- **MVC Pattern** - Clean separation of concerns
- **Repository Pattern** - Data access abstraction
- **RESTful Design** - Consistent API structure

---

## 📋 Prerequisites

Before running Kislap, ensure you have:

- **PHP 8.0 or higher**
- **MySQL 5.7 or higher**
- **Apache/Nginx Web Server**
- **Composer** (for dependency management)

---

## ⚙️ Installation

### 1. **Clone the Repository**
```bash
git clone https://github.com/yourusername/kislap.git
cd kislap
```

### 2. **Database Setup**
```bash
# Create a new MySQL database
mysql -u root -p
CREATE DATABASE kislap;
exit

# Import the database schema
mysql -u root -p kislap < kislap.sql
```

### 3. **Configuration**
```bash
# Copy and configure database settings
cp config/database.example.php config/database.php

# Edit database credentials
nano config/database.php
```

### 4. **Web Server Setup**

#### **Apache (XAMPP/WAMP)**
```bash
# Place project in htdocs folder
# Access via: http://localhost/kislap
```

#### **Nginx**
```nginx
server {
    listen 80;
    server_name kislap.local;
    root /path/to/kislap;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### 5. **File Permissions**
```bash
# Set proper permissions for upload directories
chmod 755 uploads/
chmod 755 uploads/user/
chmod 755 uploads/workers/
chmod 755 uploads/chat/
```

---

## 🎯 Usage

### **For Clients**
1. **Register** as a client with your personal details
2. **Browse** photographers by specialty, location, or ratings
3. **View Portfolios** and read reviews from other clients
4. **Start a Conversation** with your preferred photographer
5. **Book a Session** with customized requirements
6. **Make Payments** securely through the platform
7. **Rate & Review** after session completion

### **For Photographers**
1. **Apply** to join the platform with portfolio samples
2. **Complete Profile** with specialties, bio, and pricing
3. **Upload Portfolio** to showcase your best work
4. **Manage Bookings** through the dashboard
5. **Communicate** with clients via built-in messaging
6. **Track Earnings** and performance analytics
7. **Build Reputation** through client reviews

---

## 📁 Project Structure

```
kislap/
├── 📁 controllers/          # Application controllers (MVC)
│   ├── AuthController.php
│   ├── UserController.php
│   ├── WorkerController.php
│   ├── ChatController.php
│   └── ...
├── 📁 model/               # Data models and repositories
│   ├── 📁 entities/        # Entity classes
│   ├── 📁 repositories/    # Data access layer
│   └── Validator.php       # Input validation
├── 📁 views/               # View templates
│   ├── 📁 home/           # Public pages
│   ├── 📁 user/           # Client interface
│   ├── 📁 worker/         # Photographer interface
│   └── 📁 shared/         # Reusable components
├── 📁 public/              # Static assets
│   ├── 📁 css/            # Stylesheets
│   ├── 📁 js/             # JavaScript files
│   └── 📁 images/         # Static images
├── 📁 uploads/             # User-generated content
│   ├── 📁 user/           # Client uploads
│   ├── 📁 workers/        # Photographer portfolios
│   └── 📁 chat/           # Message attachments
├── 📁 config/              # Configuration files
├── Router.php              # URL routing
├── index.php              # Application entry point
└── README.md              # This file
```

---

## 🔧 Key Features Deep Dive

### **🔐 Authentication System**
- Secure password hashing with bcrypt
- Session management with CSRF protection
- Email and phone number validation
- Proper name capitalization enforcement

### **💬 Real-time Messaging**
- AJAX-powered chat interface
- File and image sharing capabilities
- Message status indicators
- Conversation history management

### **📊 Booking Management**
- Multi-stage booking process
- Deposit and final payment tracking
- Status updates and notifications
- Booking history and analytics

### **🎨 Portfolio System**
- Image upload and optimization
- Gallery display with lightbox
- Portfolio categorization
- Work samples validation

---

## 🤝 Contributing

We welcome contributions to make Kislap even better! Here's how you can help:

### **Getting Started**
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Commit your changes (`git commit -m 'Add amazing feature'`)
5. Push to the branch (`git push origin feature/amazing-feature`)
6. Open a Pull Request

### **Contribution Guidelines**
- Follow PSR-12 coding standards for PHP
- Write clear, descriptive commit messages
- Add comments for complex logic
- Test your changes thoroughly
- Update documentation as needed

---

## 🐛 Bug Reports & Feature Requests

Found a bug or have a feature idea? We'd love to hear from you!

- **Bug Reports**: [Create an Issue](https://github.com/yourusername/kislap/issues/new?template=bug_report.md)
- **Feature Requests**: [Request a Feature](https://github.com/yourusername/kislap/issues/new?template=feature_request.md)

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Authors & Acknowledgments

### **Development Team**
- **Lead Developer**: [Your Name](https://github.com/yourusername)
- **UI/UX Design**: [Designer Name](https://github.com/designerusername)

### **Special Thanks**
- Font Awesome for the beautiful icons
- The PHP community for excellent documentation
- All beta testers who provided valuable feedback

---

## 📞 Support & Contact

Need help or have questions?

- 📧 **Email**: support@kislap.com
- 💬 **Discord**: [Join our community](https://discord.gg/kislap)
- 📖 **Documentation**: [Wiki](https://github.com/yourusername/kislap/wiki)
- 🐦 **Twitter**: [@KislapPlatform](https://twitter.com/kislapplatform)

---

<div align="center">
  <h3>⭐ If you found this project helpful, please give it a star! ⭐</h3>
  <p>Made with ❤️ for the photography community</p>
  
  <img src="https://img.shields.io/github/stars/yourusername/kislap?style=social" alt="GitHub stars">
  <img src="https://img.shields.io/github/forks/yourusername/kislap?style=social" alt="GitHub forks">
  <img src="https://img.shields.io/github/watchers/yourusername/kislap?style=social" alt="GitHub watchers">
</div>

---

<div align="center">
  <sub>Built with passion by the Kislap team 📸</sub>
</div>