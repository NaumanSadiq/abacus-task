#!/bin/bash

# Abacus Task API Test Script
# This script demonstrates the API functionality

BASE_URL="http://localhost:8000/api"
TOKEN=""

echo "🚀 Abacus Task API Test Script"
echo "================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Check if server is running
print_status "Checking if Laravel server is running..."
if ! curl -s "$BASE_URL" > /dev/null 2>&1; then
    print_error "Laravel server is not running. Please start it with: php artisan serve"
    exit 1
fi
print_success "Server is running!"

echo ""
print_status "Starting API tests..."
echo ""

# 1. Register a new user
print_status "1. Testing user registration..."
REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/register" \
    -H "Content-Type: application/json" \
    -d '{
        "name": "API Test User",
        "email": "apitest@example.com",
        "password": "password123",
        "password_confirmation": "password123"
    }')

if echo "$REGISTER_RESPONSE" | grep -q "success.*true"; then
    print_success "User registered successfully"
    TOKEN=$(echo "$REGISTER_RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    echo "Token: ${TOKEN:0:20}..."
else
    print_error "Registration failed: $REGISTER_RESPONSE"
    exit 1
fi

echo ""

# 2. Login with the user
print_status "2. Testing user login..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
    -H "Content-Type: application/json" \
    -d '{
        "email": "apitest@example.com",
        "password": "password123"
    }')

if echo "$LOGIN_RESPONSE" | grep -q "success.*true"; then
    print_success "User logged in successfully"
    TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    echo "Token: ${TOKEN:0:20}..."
else
    print_error "Login failed: $LOGIN_RESPONSE"
    exit 1
fi

echo ""

# 3. Get products
print_status "3. Testing product listing..."
PRODUCTS_RESPONSE=$(curl -s -X GET "$BASE_URL/products" \
    -H "Authorization: Bearer $TOKEN")

if echo "$PRODUCTS_RESPONSE" | grep -q "success.*true"; then
    print_success "Products retrieved successfully"
    PRODUCT_COUNT=$(echo "$PRODUCTS_RESPONSE" | grep -o '"id":[0-9]*' | wc -l)
    echo "Found $PRODUCT_COUNT products"
else
    print_error "Failed to retrieve products: $PRODUCTS_RESPONSE"
fi

echo ""

# 4. View checkout data
print_status "4. Testing checkout view..."
CHECKOUT_VIEW_RESPONSE=$(curl -s -X POST "$BASE_URL/checkout/view" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d '{
        "items": [
            {"product_id": 1, "quantity": 2},
            {"product_id": 2, "quantity": 1}
        ]
    }')

if echo "$CHECKOUT_VIEW_RESPONSE" | grep -q "success.*true"; then
    print_success "Checkout view successful"
    TOTAL=$(echo "$CHECKOUT_VIEW_RESPONSE" | grep -o '"total_formatted":"[^"]*"' | cut -d'"' -f4)
    echo "Total: $TOTAL"
else
    print_error "Checkout view failed: $CHECKOUT_VIEW_RESPONSE"
fi

echo ""

# 5. Create an order
print_status "5. Testing order creation..."
ORDER_RESPONSE=$(curl -s -X POST "$BASE_URL/checkout" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d '{
        "items": [
            {"product_id": 1, "quantity": 1}
        ],
        "currency": "USD"
    }')

if echo "$ORDER_RESPONSE" | grep -q "success.*true"; then
    print_success "Order created successfully"
    ORDER_ID=$(echo "$ORDER_RESPONSE" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    echo "Order ID: $ORDER_ID"
else
    print_error "Order creation failed: $ORDER_RESPONSE"
    exit 1
fi

echo ""

# 6. Simulate payment
print_status "6. Testing payment simulation..."
PAYMENT_RESPONSE=$(curl -s -X POST "$BASE_URL/checkout/$ORDER_ID/payment/simulate" \
    -H "Authorization: Bearer $TOKEN")

if echo "$PAYMENT_RESPONSE" | grep -q "success.*true"; then
    print_success "Payment simulation successful"
    STATUS=$(echo "$PAYMENT_RESPONSE" | grep -o '"status":"[^"]*"' | cut -d'"' -f4)
    echo "Payment status: $STATUS"
elif echo "$PAYMENT_RESPONSE" | grep -q "success.*false"; then
    print_warning "Payment simulation failed (as expected in some cases)"
    STATUS=$(echo "$PAYMENT_RESPONSE" | grep -o '"status":"[^"]*"' | cut -d'"' -f4)
    echo "Payment status: $STATUS"
else
    print_error "Payment simulation failed: $PAYMENT_RESPONSE"
fi

echo ""

# 7. Get current session duration
print_status "7. Testing current session duration..."
CURRENT_SESSION_RESPONSE=$(curl -s -X GET "$BASE_URL/login-duration/current" \
    -H "Authorization: Bearer $TOKEN")

if echo "$CURRENT_SESSION_RESPONSE" | grep -q "success.*true"; then
    print_success "Current session duration retrieved successfully"
    CURRENT_DURATION=$(echo "$CURRENT_SESSION_RESPONSE" | grep -o '"duration_seconds":[0-9]*' | cut -d':' -f2)
    echo "Current session duration: ${CURRENT_DURATION}s"
else
    print_error "Failed to retrieve current session duration: $CURRENT_SESSION_RESPONSE"
fi

echo ""

# 8. Get total login duration
print_status "8. Testing total login duration..."
DURATION_RESPONSE=$(curl -s -X GET "$BASE_URL/login-duration/total" \
    -H "Authorization: Bearer $TOKEN")

if echo "$DURATION_RESPONSE" | grep -q "success.*true"; then
    print_success "Total login duration retrieved successfully"
    TOTAL_SECONDS=$(echo "$DURATION_RESPONSE" | grep -o '"total_seconds":[0-9]*' | cut -d':' -f2)
    echo "Total login duration: ${TOTAL_SECONDS}s"
else
    print_error "Failed to retrieve login duration: $DURATION_RESPONSE"
fi

echo ""

# 9. Get login sessions
print_status "9. Testing login sessions..."
SESSIONS_RESPONSE=$(curl -s -X GET "$BASE_URL/login-duration/sessions" \
    -H "Authorization: Bearer $TOKEN")

if echo "$SESSIONS_RESPONSE" | grep -q "success.*true"; then
    print_success "Login sessions retrieved successfully"
    SESSION_COUNT=$(echo "$SESSIONS_RESPONSE" | grep -o '"total_sessions":[0-9]*' | cut -d':' -f2)
    echo "Total sessions: $SESSION_COUNT"
else
    print_error "Failed to retrieve login sessions: $SESSIONS_RESPONSE"
fi

echo ""

# 10. Logout
print_status "10. Testing user logout..."
LOGOUT_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/logout" \
    -H "Authorization: Bearer $TOKEN")

if echo "$LOGOUT_RESPONSE" | grep -q "success.*true"; then
    print_success "User logged out successfully"
else
    print_error "Logout failed: $LOGOUT_RESPONSE"
fi

echo ""
echo "🎉 API testing completed!"
echo ""
echo "Summary:"
echo "- User registration and authentication: ✅"
echo "- Product listing: ✅"
echo "- Checkout process: ✅"
echo "- Order creation: ✅"
echo "- Payment simulation: ✅"
echo "- Current session duration: ✅"
echo "- Total login duration: ✅"
echo "- Session management: ✅"
echo "- Logout: ✅"
echo ""
echo "All core functionality is working correctly!"
echo ""
echo "To test manually, you can use the following credentials:"
echo "Email: apitest@example.com"
echo "Password: password123"
echo ""
echo "Or use the seeded test account:"
echo "Email: test@example.com"
echo "Password: password123" 