# E-Cargo Management System

A simple web-based cargo freight booking and management system built with PHP and MySQL.

## Features

### Customer Features
- User registration and login
- Book cargo with type, weight, and destination
- Automatic weight validation against route capacity
- Multiple payment options (Online/On Delivery)
- View booking history
- Print booking receipts

### Admin Features
- Secure admin dashboard
- Manage routes (add, edit, delete)
- Manage cargo shipments and update status
- View all customers
- View payment records
- Comprehensive statistics

## Tech Stack
- **Frontend**: HTML, CSS, JavaScript, jQuery
- **Backend**: PHP
- **Database**: MySQL
- **Server**: XAMPP (Apache + MySQL)

## Installation Instructions

### Prerequisites
- XAMPP installed on your system
- Web browser (Chrome, Firefox, etc.)

### Setup Steps

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Copy Project Files**
   - Copy the `cargo` folder to `C:\xampp\htdocs\`
   - Your project should be at: `C:\xampp\htdocs\cargo\`

3. **Create Database**
   - Open your browser and go to: `http://localhost/phpmyadmin`
   - Click on "Import" tab
   - Click "Choose File" and select: `C:\xampp\htdocs\cargo\database\cargo_db.sql`
   - Click "Go" button at the bottom
   - The database `cargo_db` will be created with all tables and sample data

4. **Create Admin Account**
   - Open browser and go to: `http://localhost/cargo/setup.php`
   - The setup script will automatically create the admin account
   - You'll see a success message with the credentials

5. **Access the Application**
   - Open browser and go to: `http://localhost/cargo/`

## Default Login Credentials

### Admin Login
- URL: `http://localhost/cargo/admin/login.php`
- Username: `admin`
- Password: `admin123`
- **Note:** Run `setup.php` first to create the admin account

### Customer Login
- Register a new account at: `http://localhost/cargo/pages/register.php`
- Or use the customer credentials you create

## Database Structure

### Tables
1. **login** - User authentication (username, password, role)
2. **customers** - Customer information
3. **route** - Available shipping routes with pricing and capacity
4. **cargo** - Cargo bookings
5. **payment** - Payment records

## Project Structure

```
cargo/
├── admin/                  # Admin panel
│   ├── dashboard.php
│   ├── login.php
│   ├── manage_cargo.php
│   ├── manage_customers.php
│   ├── manage_routes.php
│   ├── view_payments.php
│   └── logout.php
├── assets/
│   └── css/
│       └── style.css       # All styles
├── config/
│   └── database.php        # Database configuration
├── database/
│   └── cargo_db.sql        # Database dump
├── pages/                  # Customer pages
│   ├── book_cargo.php
│   ├── dashboard.php
│   ├── login.php
│   ├── logout.php
│   ├── my_bookings.php
│   ├── receipt.php
│   └── register.php
├── index.php               # Landing page
└── README.md               # This file
```

## Usage Guide

### For Customers

1. **Register Account**
   - Click "Register" on home page
   - Fill in all required information
   - Submit to create account

2. **Login**
   - Use your username and password
   - Access customer dashboard

3. **Book Cargo**
   - Click "Book Cargo" from dashboard
   - Select route (displays capacity and price)
   - Enter cargo details
   - Weight validation is automatic
   - Select payment method
   - Submit booking

4. **View Bookings**
   - Access "My Bookings" page
   - View all your shipments
   - Print receipts

### For Admins

1. **Login**
   - Access admin login page
   - Use admin credentials

2. **Manage Routes**
   - Add new routes with capacity limits
   - Edit existing routes
   - Delete routes

3. **Manage Cargo**
   - View all shipments
   - Update shipment status (Pending → In Transit → Delivered)
   - Filter by status

4. **Manage Customers**
   - View all registered customers
   - See booking statistics
   - Delete customers if needed

5. **View Payments**
   - View all payment records
   - See payment statistics
   - Track pending and completed payments

## Key Features Explained

### Weight Validation
- System automatically checks if cargo weight exceeds route capacity
- Real-time validation using jQuery
- Prevents booking if weight exceeds limit

### Payment System
- Two options: Pay Online or Pay on Delivery
- Online payments marked as "completed"
- On delivery payments marked as "pending"

### Receipt Generation
- Automatic receipt generation for each booking
- Printable format
- Includes all booking and customer details

## Security Features
- Password hashing using PHP's `password_hash()`
- SQL injection prevention using prepared statements
- Session-based authentication
- Admin and customer access separation

## Customization

### Change Database Credentials
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cargo_db');
```

### Change Admin Password
Run this in phpMyAdmin SQL tab:
```sql
UPDATE login 
SET password = '$2y$10$NEW_HASHED_PASSWORD' 
WHERE username = 'admin';
```

## Troubleshooting

### Database Connection Error
- Make sure MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Verify database `cargo_db` exists in phpMyAdmin

### Page Not Found (404)
- Ensure files are in correct location: `C:\xampp\htdocs\cargo\`
- Check Apache is running in XAMPP
- Access using: `http://localhost/cargo/`

### Session Issues
- Clear browser cookies
- Check PHP session is enabled in php.ini

## Browser Compatibility
- Google Chrome (Recommended)
- Mozilla Firefox
- Microsoft Edge
- Opera

## Future Enhancements
- Payment gateway integration (Paystack/Flutterwave)
- Real-time cargo tracking with GPS
- Email/SMS notifications
- Multi-language support
- Mobile responsive design improvements
- Export reports to PDF/Excel

## Support
For issues or questions, refer to the code comments or modify as needed.

## License
Free to use for educational purposes.

---

**Last Updated**: November 9, 2025
**Version**: 1.0.0
