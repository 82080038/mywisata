# MyWisata API Documentation

**Version:** 2.0.0  
**Base URL:** `https://mywisata.com/api`  
**Content Type:** `application/json`

---

## Overview

This document describes the RESTful API endpoints for the MyWisata application. The API provides access to destinations, tour guides, hotels, restaurants, events, and search functionality.

### Authentication

Most endpoints require authentication using session-based authentication. Include the session cookie in your requests.

### Response Format

All responses follow this standard format:

```json
{
  "status": "success|error",
  "data": {},
  "message": "Optional message"
}
```

### HTTP Status Codes

- `200 OK` - Request successful
- `400 Bad Request` - Invalid request parameters
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Access denied
- `404 Not Found` - Resource not found
- `419 Authentication Timeout` - CSRF token mismatch
- `500 Internal Server Error` - Server error

---

## Endpoints

### Destinations

#### Get All Destinations

**Endpoint:** `GET /api/destinations`  
**Description:** Retrieve all active destinations  
**Authentication:** Not required  
**Cache:** 30 minutes

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Bali Beach",
      "city": "Bali",
      "category_name": "Beach",
      "rating_avg": 4.5,
      "review_count": 120,
      "entry_fee": 50000,
      "description": "Beautiful beach in Bali",
      "latitude": -8.409518,
      "longitude": 115.188919,
      "is_active": 1,
      "is_featured": 1
    }
  ]
}
```

#### Get Destination Detail

**Endpoint:** `GET /api/destinations/{id}`  
**Description:** Retrieve detailed information about a specific destination  
**Authentication:** Not required  
**Parameters:**
- `id` (path) - Destination ID

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "Bali Beach",
    "city": "Bali",
    "category_name": "Beach",
    "rating_avg": 4.5,
    "review_count": 120,
    "entry_fee": 50000,
    "description": "Beautiful beach in Bali",
    "latitude": -8.409518,
    "longitude": 115.188919,
    "images": [
      {
        "id": 1,
        "image_url": "https://mywisata.com/uploads/destinations/1.jpg",
        "sort_order": 1
      }
    ],
    "reviews": [
      {
        "id": 1,
        "user_name": "John Doe",
        "rating": 5,
        "comment": "Amazing place!",
        "created_at": "2026-07-01 10:00:00"
      }
    ]
  }
}
```

---

### Tour Guides

#### Get All Tour Guides

**Endpoint:** `GET /api/tourguides`  
**Description:** Retrieve all verified and available tour guides  
**Authentication:** Not required  
**Cache:** 30 minutes

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Made Putra",
      "avatar": "https://mywisata.com/uploads/avatars/1.jpg",
      "rating_avg": 4.8,
      "hourly_rate": 150000,
      "daily_rate": 1000000,
      "is_verified": 1,
      "is_available": 1,
      "specializations": ["Cultural", "Adventure"],
      "languages": ["English", "Indonesian"]
    }
  ]
}
```

#### Get Tour Guide Detail

**Endpoint:** `GET /api/tourguides/{id}`  
**Description:** Retrieve detailed information about a specific tour guide  
**Authentication:** Not required  
**Parameters:**
- `id` (path) - Tour Guide ID

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "Made Putra",
    "avatar": "https://mywisata.com/uploads/avatars/1.jpg",
    "rating_avg": 4.8,
    "hourly_rate": 150000,
    "daily_rate": 1000000,
    "is_verified": 1,
    "is_available": 1,
    "phone": "+6281234567890",
    "bio": "Experienced tour guide with 10 years of experience",
    "languages": [
      {
        "language": "English",
        "proficiency": "Fluent"
      }
    ],
    "specializations": [
      {
        "name": "Cultural",
        "description": "Expert in cultural tours"
      }
    ]
  }
}
```

---

### Hotels

#### Get All Hotels

**Endpoint:** `GET /api/hotels`  
**Description:** Retrieve all approved hotels  
**Authentication:** Not required  
**Cache:** 30 minutes

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Bali Resort Hotel",
      "city": "Bali",
      "rating_avg": 4.5,
      "price_per_night": 500000,
      "address": "Jalan Beach Road No. 1",
      "is_approved": 1,
      "amenities": ["WiFi", "Pool", "Restaurant"]
    }
  ]
}
```

---

### Restaurants

#### Get All Restaurants

**Endpoint:** `GET /api/restaurants`  
**Description:** Retrieve all approved restaurants  
**Authentication:** Not required  
**Cache:** 30 minutes

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Warung Bali",
      "city": "Bali",
      "rating_avg": 4.3,
      "cuisine_type": "Indonesian",
      "price_range": "$$",
      "address": "Jalan Food Street No. 5",
      "is_approved": 1
    }
  ]
}
```

---

### Events

#### Get Upcoming Events

**Endpoint:** `GET /api/events`  
**Description:** Retrieve all approved upcoming events  
**Authentication:** Not required  
**Cache:** 15 minutes

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Bali Cultural Festival",
      "city": "Bali",
      "event_date": "2026-08-15",
      "event_time": "10:00:00",
      "description": "Annual cultural festival",
      "ticket_price": 100000,
      "is_approved": 1
    }
  ]
}
```

---

### Search

#### Search All Content

**Endpoint:** `GET /api/search`  
**Description:** Search across destinations, guides, hotels, restaurants, and events  
**Authentication:** Not required  
**Parameters:**
- `q` (query) - Search query
- `type` (query, optional) - Filter by type: `all`, `destinations`, `guides`, `hotels`, `restaurants`, `events` (default: `all`)

**Response:**
```json
{
  "status": "success",
  "data": {
    "destinations": [],
    "guides": [],
    "hotels": [],
    "restaurants": [],
    "events": []
  }
}
```

**Example Request:**
```
GET /api/search?q=beach&type=destinations
```

---

## Authentication Endpoints

### Login

**Endpoint:** `POST /auth/login`  
**Description:** Authenticate user and create session  
**Authentication:** Not required  
**Parameters:**
- `email` (body) - User email
- `password` (body) - User password
- `csrf_token` (body) - CSRF token

**Response:**
```json
{
  "status": "success",
  "message": "Login successful",
  "redirect": "home"
}
```

### Register

**Endpoint:** `POST /auth/register`  
**Description:** Register new user account  
**Authentication:** Not required  
**Parameters:**
- `name` (body) - Full name
- `email` (body) - Email address
- `password` (body) - Password (min 6 characters)
- `phone` (body, optional) - Phone number
- `csrf_token` (body) - CSRF token

**Response:**
```json
{
  "status": "success",
  "message": "Registration successful",
  "redirect": "auth/login"
}
```

### Logout

**Endpoint:** `GET /auth/logout`  
**Description:** End user session  
**Authentication:** Required

**Response:**
```json
{
  "status": "success",
  "message": "Logout successful",
  "redirect": "home"
}
```

---

## Booking Endpoints

### Create Booking

**Endpoint:** `POST /booking/create`  
**Description:** Create a new tour guide booking  
**Authentication:** Required (role: wisatawan)  
**Parameters:**
- `guide_id` (body) - Tour Guide ID
- `booking_date` (body) - Booking date (YYYY-MM-DD)
- `booking_time` (body) - Booking time (HH:MM)
- `duration_hours` (body) - Duration in hours
- `participants` (body) - Number of participants
- `special_requests` (body, optional) - Special requests
- `csrf_token` (body) - CSRF token

**Response:**
```json
{
  "status": "success",
  "message": "Booking created successfully",
  "data": {
    "booking_id": 123,
    "booking_code": "BK20260718001"
  }
}
```

### Get User Bookings

**Endpoint:** `GET /booking/index`  
**Description:** Retrieve user's bookings  
**Authentication:** Required (role: wisatawan)  
**Parameters:**
- `page` (query, optional) - Page number (default: 1)
- `status` (query, optional) - Filter by status

**Response:**
```json
{
  "status": "success",
  "data": {
    "bookings": [
      {
        "id": 123,
        "booking_code": "BK20260718001",
        "guide_name": "Made Putra",
        "booking_date": "2026-08-01",
        "booking_time": "10:00",
        "duration_hours": 2,
        "participants": 3,
        "total_amount": 300000,
        "status": "pending"
      }
    ],
    "total": 10,
    "page": 1,
    "per_page": 10
  }
}
```

### Cancel Booking

**Endpoint:** `POST /booking/cancel`  
**Description:** Cancel a booking  
**Authentication:** Required (role: wisatawan)  
**Parameters:**
- `booking_id` (body) - Booking ID
- `reason` (body, optional) - Cancellation reason
- `csrf_token` (body) - CSRF token

**Response:**
```json
{
  "status": "success",
  "message": "Booking cancelled successfully"
}
```

---

## Error Responses

### Validation Error

```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "field_name": "Error message"
  }
}
```

### Authentication Error

```json
{
  "status": "error",
  "message": "Unauthorized"
}
```

### Not Found Error

```json
{
  "status": "error",
  "message": "Resource not found"
}
```

### Server Error

```json
{
  "status": "error",
  "message": "Internal server error"
}
```

---

## Rate Limiting

API endpoints are rate-limited to prevent abuse:
- **Default limit:** 100 requests per minute per IP
- **Authentication endpoints:** 30 requests per minute per IP
- **Search endpoints:** 60 requests per minute per IP

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Maximum requests
- `X-RateLimit-Remaining`: Remaining requests
- `X-RateLimit-Reset`: Unix timestamp when limit resets

---

## CSRF Protection

All POST requests must include a valid CSRF token. Obtain the token from:

**Endpoint:** `GET /csrf-token`  
**Response:**
```json
{
  "status": "success",
  "data": {
    "csrf_token": "abc123..."
  }
}
```

Include the token in your POST requests:
```
csrf_token: abc123...
```

---

## Caching

The following endpoints use caching to improve performance:
- `GET /api/destinations` - 30 minutes
- `GET /api/tourguides` - 30 minutes
- `GET /api/hotels` - 30 minutes
- `GET /api/restaurants` - 30 minutes
- `GET /api/events` - 15 minutes

Cache headers are included in responses:
- `X-Cache`: `HIT` or `MISS`
- `X-Cache-Expires`: Unix timestamp when cache expires

---

## SDKs and Libraries

### JavaScript/Fetch API Example

```javascript
// Get destinations
async function getDestinations() {
  const response = await fetch('https://mywisata.com/api/destinations');
  const data = await response.json();
  return data;
}

// Create booking (with CSRF token)
async function createBooking(bookingData, csrfToken) {
  const response = await fetch('https://mywisata.com/booking/create', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
      ...bookingData,
      csrf_token: csrfToken
    })
  });
  const data = await response.json();
  return data;
}
```

### cURL Examples

```bash
# Get destinations
curl -X GET https://mywisata.com/api/destinations

# Login
curl -X POST https://mywisata.com/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123","csrf_token":"abc123"}'

# Create booking
curl -X POST https://mywisata.com/booking/create \
  -H "Content-Type: application/json" \
  -H "Cookie: session_id=xyz" \
  -d '{"guide_id":1,"booking_date":"2026-08-01","booking_time":"10:00","duration_hours":2,"participants":3,"csrf_token":"abc123"}'
```

---

## Changelog

### Version 2.0.0 (2026-07-18)
- Added response caching for API endpoints
- Improved rate limiting
- Added OpenAPI/Swagger documentation
- Enhanced error responses

### Version 1.0.0 (2026-07-01)
- Initial API release
- Basic CRUD operations for destinations, guides, hotels, restaurants, events
- Authentication endpoints
- Booking system

---

## Support

For API support and questions:
- **Email:** api@mywisata.com
- **Documentation:** https://docs.mywisata.com
- **Status Page:** https://status.mywisata.com
