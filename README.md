# Abacus Task - Laravel 10 API

A comprehensive Laravel 10 API application that implements a checkout system with MySQL database, featuring product management, order processing, simulated payments, and login duration tracking.

## Features

- **User Authentication**: Register, login, and logout with Laravel Sanctum
- **Product Management**: View products with stock and pricing information
- **Checkout System**: View checkout data and create orders
- **Payment Processing**: Simulated payment system (90% success rate)
- **Order Management**: Complete order lifecycle with status tracking
- **Login Duration Tracking**: Monitor user session durations
- **MySQL Database**: Robust data storage with proper relationships

## Requirements

- PHP 8.1 or higher
- Laravel 10
- MySQL 5.7 or higher
- Composer
- Node.js & NPM (for frontend assets)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd abacus-task
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database in .env file**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=abacus_task
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run database migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed the database with sample data**
   ```bash
   php artisan db:seed
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

## API Endpoints

### Authentication

#### Register User
```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Login User
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Logout User
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

### Products

#### Get All Products
```http
GET /api/products
Authorization: Bearer {token}
```

#### Get Single Product
```http
GET /api/products/{id}
Authorization: Bearer {token}
```

### Checkout

#### View Checkout Data
```http
POST /api/checkout/view
Authorization: Bearer {token}
Content-Type: application/json

{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        },
        {
            "product_id": 3,
            "quantity": 1
        }
    ]
}
```

#### Create Order
```http
POST /api/checkout
Authorization: Bearer {token}
Content-Type: application/json

{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        }
    ],
    "currency": "USD"
}
```

#### Simulate Payment
```http
POST /api/checkout/{order_id}/payment/simulate
Authorization: Bearer {token}
```

### Login Duration Tracking

#### Get Total Login Duration
```http
GET /api/login-duration/total
Authorization: Bearer {token}
```

#### Get Login Sessions
```http
GET /api/login-duration/sessions
Authorization: Bearer {token}
```

## Database Schema

### Tables

- **users**: User accounts and authentication
- **products**: Product catalog with pricing and stock
- **orders**: Order information and status
- **order_items**: Individual items within orders
- **payments**: Payment records and status
- **login_sessions**: User login session tracking

### Key Relationships

- Users have many Orders
- Orders have many OrderItems
- Orders have one Payment
- Users have many LoginSessions

## Sample Data

The seeder creates:
- Test user: `test@example.com` / `password123`
- 8 sample products with realistic pricing and descriptions

## Payment Simulation

The system includes a simulated payment processor that:
- Has a 90% success rate
- Generates unique transaction IDs
- Updates order and payment statuses
- Provides detailed success/failure responses

## Testing the API

### 1. Register/Login
```bash
# Register a new user
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"user@test.com","password":"password123","password_confirmation":"password123"}'

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@test.com","password":"password123"}'
```

### 2. View Products
```bash
# Get all products (use token from login)
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer {your_token}"
```

### 3. Checkout Process
```bash
# View checkout data
curl -X POST http://localhost:8000/api/checkout/view \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{"items":[{"product_id":1,"quantity":2}]}'

# Create order
curl -X POST http://localhost:8000/api/checkout \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{"items":[{"product_id":1,"quantity":2}]}'

# Simulate payment (use order ID from previous response)
curl -X POST http://localhost:8000/api/checkout/{order_id}/payment/simulate \
  -H "Authorization: Bearer {your_token}"
```

### 4. Login Duration
```bash
# Get total login duration
curl -X GET http://localhost:8000/api/login-duration/total \
  -H "Authorization: Bearer {your_token}"

# Get login sessions
curl -X GET http://localhost:8000/api/login-duration/sessions \
  -H "Authorization: Bearer {your_token}"
```

## Error Handling

The API provides consistent error responses:
- HTTP status codes for different error types
- Detailed error messages
- Validation error arrays when applicable
- Success/failure flags in responses

## Security Features

- Laravel Sanctum for API authentication
- CSRF protection
- Input validation and sanitization
- Database transaction safety
- User authorization checks

## Development

### Running Tests
```bash
php artisan test
```

### Code Quality
```bash
# Laravel Pint for code formatting
./vendor/bin/pint

# PHPStan for static analysis
./vendor/bin/phpstan analyse
```

### Database
```bash
# Reset and reseed
php artisan migrate:fresh --seed

# View database
php artisan tinker
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
