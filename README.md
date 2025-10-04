Kislap

Kislap is a web-based project developed in PHP (OOP structure) with MySQL as the backend database. It follows an MVC-like structure with Repositories, Services, and Controllers.

Project Structure
KislapAI/
 ├── public/               # Entry point (index.php, routes)
 ├── config/               # Database connection (Database.php)
 ├── controllers/          # Controllers handle requests
 ├── services/             # Services hold business logic
 ├── repositories/         # Repositories handle database queries
 ├── models/               # Entities (User, Booking, etc.)
 └── vendor/               # Composer dependencies (if any)

 Setup Instructions
Install XAMPP
Place the project folder in htdocs/
Start Apache and MySQL in XAMPP
Create a database in phpMyAdmin
Configure config/Database.php with your DB name and credentials
Open in browser:
http://localhost/KislapAI/public/
