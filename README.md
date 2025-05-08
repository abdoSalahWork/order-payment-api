1. Setup environment:

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

2. Configure database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=order_payment_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

3. Setup database:

```bash
php artisan migrate:fresh --seed
```

4. Start server:

```bash
php artisan serve
```

## API Testing

## Testing

Run the test suite:

```bash
php artisan test
```

## Postman Collection

A Postman collection is included in the repository. Import `postman_collection.json` to test the API endpoints.

# Order Payment API

## Payment Status Flow

### Payment Statuses

1. **pending**

    - Initial state when payment is created
    - Payment request is sent to gateway
    - Waiting for gateway response

2. **successful**

    - Payment is completed successfully
    - Amount is deducted from customer
    - Order status is updated to 'paid' or 'partially_paid'

3. **failed**
    - Payment attempt failed
    - Could be due to:
        - Invalid card details
        - Insufficient funds
        - Gateway error
        - Network issues

### Order Status After Payment

1. **paid**

    - All payments equal or exceed order total
    - Order is fully paid
    - No more payments can be processed

2. **partially_paid**
    - Some payments made but less than total
    - Order can accept more payments
    - Remaining amount can be paid later

### Payment Flow Example

```json
// Initial Payment Request
{
    "order_id": 1,
    "payment_method": "credit_card",
    "payment_details": {
        "card_number": "4111111111111111",
        "expiry_date": "12/25",
        "cvv": "123",
        "amount": 100.00
    }
}

// Successful Payment Response
{
    "success": true,
    "payment": {
        "id": 1,
        "status": "successful",
        "amount": 100.00,
        "payment_method": "credit_card"
    },
    "order": {
        "id": 1,
        "status": "partially_paid",
        "total_amount": 200.00,
        "total_paid": 100.00,
        "remaining_amount": 100.00
    }
}

// Failed Payment Response
{
    "success": false,
    "message": "Payment failed",
    "error": "Insufficient funds",
    "payment": {
        "id": 1,
        "status": "failed",
        "amount": 100.00,
        "payment_method": "credit_card"
    }
}
```

### Payment Validation Rules

1. Order must be in 'pending' or 'partially_paid' status
2. Payment amount cannot exceed remaining order amount
3. Payment method must be supported by configured gateway
4. Card details must be valid and not expired
5. Payment amount must be greater than zero

## Environment Variables

Key environment variables to configure:

-   `APP_ENV`: Application environment (local, production)
-   `APP_DEBUG`: Debug mode
-   `DB_*`: Database configuration
-   `JWT_SECRET`: JWT authentication secret
-   `PAYMENT_GATEWAY_*`: Payment gateway specific settings
