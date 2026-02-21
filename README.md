# Smart Parking Management System

A comprehensive web-based parking management system that connects parking space providers with customers looking for convenient parking solutions.

## 🚀 Features

### Dual User System
- **Provider Dashboard**: Parking space owners can list, manage, and track bookings
- **Customer Dashboard**: Car owners can search, book, and manage their parking reserve

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Server**: XAMPP (Apache + MySQL + PHP)
- **Authentication**: JWT (JSON Web Tokens)
- **Security**: Password hashing with bcrypt

## 🚀 Installation & Setup

### 1. Clone/Download Project
```bash
# Download the project files to your XAMPP htdocs directory
# Example: C:\xampp\htdocs\smartpark
```

### 2. Database Setup
1. Start XAMPP and ensure MySQL is running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create a new database called `smartpark`
4. Import the `database.sql` file:
   - Click on the `smartpark` database
   - Go to "Import" tab
   - Choose the `database.sql` file
   - Click "Go" to execute

### 3. Configure Database Connection
The database connection is already configured in `api/config.php` for XAMPP default settings:
- Host: localhost
- Database: smartpark
- Username: root
- Password: (empty)

### 4. Start the Application
1. Ensure XAMPP is running (Apache + MySQL)
2. Open your browser and navigate to: `http://localhost/smartpark`
3. The application should load successfully

## 📁 Project Structure

```
smartpark/
├── index.html              # Main application file
├── styles.css              # CSS styles and responsive design
├── script.js               # Frontend JavaScript functionality
├── database.sql            # Database schema and sample data
├── api/                    # Backend API endpoints
│   ├── config.php          # Database config and utilities
│   ├── auth/               # Authentication endpoints
│   │   ├── login.php       # User login
│   │   └── register.php    # User registration
│   ├── parking/            # Parking management endpoints
│   │   ├── add.php         # Add parking space
│   │   ├── my-parkings.php # Get provider's parking spaces
│   │   ├── search.php      # Search parking spaces
│   │   └── delete.php      # Delete parking space
│   └── bookings/           # Booking management endpoints
│       ├── create.php      # Create booking
│       ├── my-bookings.php # Get customer bookings
│       ├── provider-bookings.php # Get provider bookings
│       └── update-status.php # Update booking status
└── README.md               # This file
```

## 🔐 Default Login Credentials

The system comes with sample users for testing:

### Provider Account
- **Email**: kunal@provider.com
- **Password**: password
- **Type**: Provider

### Customer Account
- **Email**: parth@customer.com
- **Password**: password
- **Type**: Customer

## 🎯 Usage Guide

### For Providers (Parking Space Owners)
1. **Register/Login** as a Provider
2. **Add Parking Spaces** with details like location, slots, pricing, and availability
3. **Manage Bookings** - view, confirm, or cancel customer bookings
4. **Track Revenue** - see booking history and earnings

### For Customers (Car Owners)
1. **Register/Login** as a Customer
2. **Search Parking** using filters (location, date, time, price)
3. **Book Slots** for specific time periods
4. **Manage Bookings** - view history and cancel if needed

## 📄 License

This project is created for educational and demonstration purposes.

## 🤝 Contributing

Feel free to submit issues, feature requests, or pull requests to improve the system.

## 📞 Support

For technical support or questions, please refer to the troubleshooting section or create an issue in the project repository.
Contact:- 8432660285

---

**SmartPark** - Making parking management simple and efficient! 🚗✨
