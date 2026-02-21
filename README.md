# Smart Parking Management System

A comprehensive web-based parking management system that connects parking space providers with customers looking for convenient parking solutions.

## 🚀 Features

### Dual User System
- **Provider Dashboard**: Parking space owners can list, manage, and track bookings
- **Customer Dashboard**: Car owners can search, book, and manage their parking reservations

### Core Functionality
- ✅ User registration and authentication (JWT-based)
- ✅ Secure password hashing with bcrypt
- ✅ Parking space management (add, edit, delete)
- ✅ Advanced search with filters (location, date, time, price)
- ✅ Real-time booking system with conflict detection
- ✅ Booking status management (pending, confirmed, completed, cancelled)
- ✅ Responsive design with modern UI/UX
- ✅ Mobile-friendly interface

## 🎨 Design

### Color Palette
- **Primary**: #6f1d1b (deep red-brown)
- **Secondary**: #bb9457 (warm gold)
- **Accent**: #432818 (dark brown)
- **Highlight**: #99582a (copper tone)
- **Background**: #ffe6a7 (light cream)

### UI Features
- Modern card-based layout
- Smooth animations and transitions
- Responsive grid system
- Intuitive navigation
- Status badges and visual feedback

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Server**: XAMPP (Apache + MySQL + PHP)
- **Authentication**: JWT (JSON Web Tokens)
- **Security**: Password hashing with bcrypt

## 📋 Prerequisites

- XAMPP installed and running
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

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
- **Email**: john@provider.com
- **Password**: password
- **Type**: Provider

### Customer Account
- **Email**: jane@customer.com
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

## 🔧 API Endpoints

### Authentication
- `POST /api/auth/login.php` - User login
- `POST /api/auth/register.php` - User registration

### Parking Management
- `POST /api/parking/add.php` - Add parking space (Provider only)
- `GET /api/parking/my-parkings.php` - Get provider's parking spaces
- `POST /api/parking/search.php` - Search parking spaces
- `DELETE /api/parking/delete.php` - Delete parking space (Provider only)

### Booking Management
- `POST /api/bookings/create.php` - Create booking (Customer only)
- `GET /api/bookings/my-bookings.php` - Get customer bookings
- `GET /api/bookings/provider-bookings.php` - Get provider bookings
- `POST /api/bookings/update-status.php` - Update booking status

## 🔒 Security Features

- **JWT Authentication**: Secure token-based authentication
- **Password Hashing**: bcrypt password encryption
- **Input Validation**: Server-side validation for all inputs
- **SQL Injection Prevention**: Prepared statements
- **CORS Protection**: Cross-origin request handling
- **Access Control**: Role-based permissions

## 🎨 Customization

### Adding New Features
1. **Frontend**: Modify `script.js` and `styles.css`
2. **Backend**: Add new PHP endpoints in the `api/` directory
3. **Database**: Update `database.sql` for schema changes

### Styling Changes
- Modify color variables in `styles.css`
- Update the color palette constants
- Adjust responsive breakpoints as needed

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Ensure XAMPP MySQL is running
   - Check database credentials in `api/config.php`
   - Verify database `smartpark` exists

2. **API Endpoints Not Working**
   - Check Apache is running in XAMPP
   - Verify file permissions
   - Check browser console for errors

3. **Login Issues**
   - Use default credentials: john@provider.com / password
   - Check if database was imported correctly
   - Clear browser cache and localStorage

4. **CORS Errors**
   - Ensure you're accessing via localhost
   - Check API base URL in `script.js`

## 🚀 Future Enhancements

- **Payment Integration**: Stripe/PayPal integration
- **Google Maps**: Visual location mapping
- **Email Notifications**: Booking confirmations
- **Mobile App**: React Native/Flutter app
- **Admin Dashboard**: System administration
- **Analytics**: Usage statistics and reports
- **Real-time Updates**: WebSocket notifications

## 📄 License

This project is created for educational and demonstration purposes.

## 🤝 Contributing

Feel free to submit issues, feature requests, or pull requests to improve the system.

## 📞 Support

For technical support or questions, please refer to the troubleshooting section or create an issue in the project repository.

---

**SmartPark** - Making parking management simple and efficient! 🚗✨
