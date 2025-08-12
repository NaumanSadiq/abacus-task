# Abacus Task - Setup Guide

This guide will help you set up and run the Abacus Task Laravel application with the new service-oriented architecture.

## 🏗️ Architecture Overview

The application now follows a clean, service-oriented architecture with the following layers:

- **Controllers**: Handle HTTP requests and responses
- **Services**: Contain business logic and data processing
- **Models**: Handle database interactions
- **Requests**: Validate input data
- **Traits**: Provide reusable functionality
- **Listeners**: Handle application events

## 📋 Prerequisites

- PHP 8.1 or higher
- Composer 2.0 or higher
- MySQL 5.7 or higher
- Node.js 16+ and NPM
- Git

## 🚀 Installation Steps

### 1. Clone the Repository
```bash
git clone <repository-url>
cd abacus-task
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database
Edit your `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=abacus_task
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Database Migrations
```bash
php artisan migrate
```

### 6. Seed the Database
```bash
php artisan db:seed
```

### 7. Start the Development Server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## 🧪 Testing the API

### Option 1: Using Postman Collection

1. Import the `Abacus_Task_API.postman_collection.json` file into Postman
2. Set the environment variable `base_url` to `http://localhost:8000`
3. Run the requests in sequence (Authentication → Products → Checkout → Login Duration)

### Option 2: Using the Test Script

```bash
# Make the script executable
chmod +x test_api.sh

# Run the test script
./test_api.sh
```

### Option 3: Manual Testing with cURL

#### 1. Register a User
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### 2. Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

#### 3. Get Products (use token from login)
```bash
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 🔧 Service Layer Architecture

### Services Overview

- **AuthService**: Handles user authentication, registration, and logout
- **ProductService**: Manages product operations and formatting
- **CheckoutService**: Handles checkout process, order creation, and payment simulation
- **LoginDurationService**: Tracks user login sessions and durations

### Benefits of Service Layer

- **Separation of Concerns**: Business logic is separated from controllers
- **Reusability**: Services can be used by multiple controllers
- **Testability**: Easy to unit test business logic
- **Maintainability**: Cleaner, more organized code structure

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/Controller/
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── CheckoutController.php
│   │   └── LoginDurationController.php
│   ├── Requests/
│   │   ├── Auth/
│   │   │   ├── LoginRequest.php
│   │   │   └── RegisterRequest.php
│   │   └── Checkout/
│   │       └── CheckoutRequest.php
│   └── Middleware/
├── Services/
│   ├── AuthService.php
│   ├── ProductService.php
│   ├── CheckoutService.php
│   └── LoginDurationService.php
├── Models/
│   ├── User.php
│   ├── Product.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Payment.php
│   └── LoginSession.php
├── Traits/
│   └── ApiResponse.php
└── Listeners/
    ├── StartLoginSession.php
    └── EndLoginSession.php
```

## 🔐 Authentication Flow

1. **Register**: User creates account with name, email, and password
2. **Login**: User authenticates and receives Bearer token
3. **API Calls**: Token is used in Authorization header for protected endpoints
4. **Logout**: Token is invalidated and user is logged out

## 🛒 Checkout Process

1. **View Checkout**: Preview cart items with totals and tax calculation
2. **Create Order**: Create order with selected items and decrease stock
3. **Payment Simulation**: Simulate payment processing (90% success rate)
4. **Order Status**: Order status is updated based on payment result

## 📊 Login Duration Tracking

- **Automatic Tracking**: Login sessions are automatically created on login
- **Duration Calculation**: Session duration is calculated on logout
- **Statistics**: Total duration and session history are available via API

## 🧪 Testing

### Running Tests
```bash
php artisan test
```

### Code Quality
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Static analysis with PHPStan
./vendor/bin/phpstan analyse
```

## 🚀 Deployment

### Production Considerations

1. **Environment**: Set `APP_ENV=production` in `.env`
2. **Debug**: Set `APP_DEBUG=false` in `.env`
3. **Cache**: Run `php artisan config:cache` and `php artisan route:cache`
4. **Database**: Ensure proper database configuration and permissions
5. **Security**: Use HTTPS in production

### Environment Variables

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your_db_host
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection**: Ensure MySQL is running and credentials are correct
2. **Permissions**: Check storage and bootstrap/cache directory permissions
3. **Composer**: Run `composer dump-autoload` if classes are not found
4. **Cache**: Clear cache with `php artisan cache:clear` if needed

### Logs

Check Laravel logs in `storage/logs/laravel.log` for detailed error information.

## 📚 API Documentation

Complete API documentation is available in the `README.md` file and the Postman collection.

## 🤝 Contributing

1. Follow the existing code structure and patterns
2. Use the service layer for business logic
3. Implement proper validation with Form Requests
4. Add tests for new functionality
5. Follow PSR-12 coding standards

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). 