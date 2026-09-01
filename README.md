# ThriftX - Online Thrift Store Platform

A comprehensive e-commerce platform for buying and selling second-hand items, built with PHP, MySQL, and modern web technologies.

## Features

### For Customers
- **Product Browsing**: Browse products by categories (Electronics, Clothing, Furniture, Services)
- **Advanced Search**: Search products by name, description, or category
- **Shopping Cart**: Add, update, and remove items with persistent database storage
- **Checkout Process**: Complete purchase with shipping information
- **User Profile**: Manage personal information and settings

### For Sellers
- **Product Management**: Add, edit, and delete products
- **Image Upload**: Upload product images with validation
- **Dashboard**: View sales analytics and manage products
- **Profile Settings**: Manage seller account information

### For Administrators
- **User Management**: Manage customers, sellers, and admin accounts
- **Product Oversight**: Monitor and manage all products
- **Order Management**: Track and update order statuses
- **Analytics**: View platform statistics and insights
- **System Settings**: Configure platform-wide settings

## Technology stack

- **Backend**: PHP 8.2+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Server**: Apache (XAMPP)
- **Styling**: Custom CSS with modern design principles

## Project structure

```
ThriftX/
├── admin/                 # Admin panel pages
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   ├── images/          # Images
│   └── js/              # JavaScript files
├── config/              # Configuration files
│   ├── db.php           # Database connection
│   └── settings.json    # Application settings
├── customer/            # Customer interface pages
├── includes/            # Shared PHP includes
│   ├── auth.php         # Authentication functions
│   ├── cart_functions.php # Cart management
│   ├── admin_header.php # Admin header component
│   ├── seller_header.php # Seller header component
│   └── customer_header.php # Customer header component
├── seller/              # Seller interface pages
│   └── uploads/         # Product images (with .gitkeep)
├── scrap/               # Development files and prototypes
├── database_setup.sql   # Database schema
├── .gitignore          # Git ignore rules
└── README.md           # This file
```

## Installation and setup

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Git

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd ThriftX
   ```

2. **Database Setup**
   - Start XAMPP and ensure Apache and MySQL are running
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `thriftx`
   - Import the database schema:
     ```sql
     -- Run the SQL commands from database_setup.sql
     ```

3. **Configuration**
   - Update `config/db.php` with your database credentials if needed
   - Ensure the `seller/uploads/` directory has write permissions

4. **Access the Application**
   - Navigate to `http://localhost/ThriftX/`
   - The application should be ready to use

## User roles

### Customer
- Browse and search products
- Add items to cart
- Complete purchases
- Manage profile

### Seller
- Post and manage products
- Upload product images
- View sales dashboard
- Manage seller profile

### Admin
- Manage all users
- Oversee products
- Handle orders
- System administration

## Design features

- **Modern UI**: Clean, responsive design with Facebook-like header
- **Orange Theme**: Consistent orange color scheme throughout
- **Mobile Responsive**: Optimized for all device sizes
- **User-Friendly**: Intuitive navigation and interactions

## Development

### Database Schema
The application uses a well-structured MySQL database with the following main tables:
- `users` - User accounts (customers, sellers, admins)
- `products` - Product listings
- `cart` - Shopping cart items
- `orders` - Order information
- `product_images` - Product image references
- `product_reviews` - Customer reviews

### Key Features
- **Session Management**: Secure user authentication
- **Database Integration**: Full CRUD operations
- **File Upload**: Secure image upload with validation
- **Cart System**: Persistent shopping cart with database storage
- **Search Functionality**: Advanced product search
- **Responsive Design**: Mobile-first approach

## License

This project is proprietary software. All rights reserved.

## Contributing

This is a private project. For any issues or suggestions, please contact the development team.

## Support

For technical support or questions, please contact the development team.

---

**ThriftX**, making second-hand shopping easy and accessible.