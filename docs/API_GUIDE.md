# API GUIDE
# MyWisata Application
# Version: 1.0.0
# Last Updated: 2026-07-18

## TABLE OF CONTENTS

1. [Overview](#overview)
2. [API Architecture](#api-architecture)
3. [Request/Response Format](#requestresponse-format)
4. [Authentication](#authentication)
5. [API Endpoints](#api-endpoints)
6. [Error Handling](#error-handling)
7. [Rate Limiting](#rate-limiting)
8. [API Testing](#api-testing)
9. [OpenAPI Specification](#openapi-specification)

---

## OVERVIEW

The MyWisata Application uses a RESTful API architecture for communication between the frontend (jQuery/AJAX) and backend (PHP Native). All API responses are in JSON format.

### Base URL
- **Development**: `http://localhost:8080`
- **Production**: `https://yourdomain.com`

### API URL Pattern
```
http://localhost:8080/?url=controller/method
```

---

## API ARCHITECTURE

### REST API Best Practices

#### HTTP Methods

| Method | Use Case | Idempotent | Safe |
|--------|----------|------------|------|
| GET | Retrieve data | Yes | Yes |
| POST | Create resource | No | No |
| PUT | Update/Replace resource | Yes | No |
| PATCH | Partial update | No | No |
| DELETE | Delete resource | Yes | No |

#### Status Codes

| Code | Meaning | Use Case |
|------|---------|----------|
| 200 | OK | Successful GET, PUT, PATCH |
| 201 | Created | Successful POST |
| 204 | No Content | Successful DELETE |
| 400 | Bad Request | Invalid input |
| 401 | Unauthorized | Not authenticated |
| 403 | Forbidden | No permission |
| 404 | Not Found | Resource not found |
| 409 | Conflict | Duplicate resource |
| 422 | Unprocessable Entity | Validation error |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

#### URL Design Patterns

```
# Resource-based URLs
GET    /api/destinations           # List all destinations
GET    /api/destinations/{id}      # Get specific destination
POST   /api/destinations           # Create destination
PUT    /api/destinations/{id}      # Update destination
DELETE /api/destinations/{id}      # Delete destination

# Action-based URLs
POST   /api/destinations/{id}/favorite
POST   /api/bookings/{id}/cancel
POST   /api/payments/{id}/refund
```

---

## REQUEST/RESPONSE FORMAT

### Standard Response Format

All API responses follow this format:

```json
{
  "status": "success|error",
  "message": "Optional message",
  "data": {},
  "meta": {
    "total": 100,
    "page": 1,
    "per_page": 20
  }
}
```

### Success Response Example

```json
{
  "status": "success",
  "message": "Destination retrieved successfully",
  "data": {
    "id": 1,
    "name": "Borobudur Temple",
    "description": "Ancient Buddhist temple",
    "location": "Magelang, Central Java",
    "price": 50000,
    "rating": 4.8
  }
}
```

### Error Response Example

```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": "Email is required",
    "password": "Password must be at least 8 characters"
  }
}
```

---

## AUTHENTICATION

### Session-Based Authentication

The application uses session-based authentication. Users must be logged in to access protected endpoints.

#### Login Endpoint

**POST** `/?url=auth/login`

**Request Body**:
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response**:
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "user"
    },
    "redirect": "/dashboard"
  }
}
```

#### Protected Endpoints

Protected endpoints require an active session. If the user is not authenticated, the API returns:

```json
{
  "status": "error",
  "message": "Authentication required",
  "redirect": "/auth/login"
}
```

### CSRF Protection

All POST requests must include a CSRF token.

#### Get CSRF Token

**GET** `/?url=auth/csrf-token`

**Response**:
```json
{
  "status": "success",
  "data": {
    "csrf_token": "abc123xyz789"
  }
}
```

#### Include CSRF Token in Request

```javascript
$.ajax({
  url: '/?url=booking/create',
  method: 'POST',
  headers: {
    'X-CSRF-Token': 'abc123xyz789'
  },
  data: {
    // request data
  }
});
```

---

## API ENDPOINTS

### Authentication Endpoints

#### Login
- **POST** `/?url=auth/login`
- **Description**: User login
- **Request**: `{ email, password }`
- **Response**: User data, redirect URL

#### Register
- **POST** `/?url=auth/register`
- **Description**: User registration
- **Request**: `{ name, email, password, role }`
- **Response**: Success message, redirect URL

#### Logout
- **POST** `/?url=auth/logout`
- **Description**: User logout
- **Request**: None
- **Response**: Success message, redirect URL

#### Forgot Password
- **POST** `/?url=auth/forgot-password`
- **Description**: Password reset request
- **Request**: `{ email }`
- **Response**: Success message

### Destination Endpoints

#### List Destinations
- **GET** `/?url=destinations`
- **Description**: Get all destinations
- **Query Params**: `page, per_page, search, category, location`
- **Response**: Array of destinations

#### Get Destination Detail
- **GET** `/?url=destinations/show/{id}`
- **Description**: Get destination details
- **Response**: Destination object with full details

#### Search Destinations
- **POST** `/?url=destinations/search`
- **Description**: Search destinations
- **Request**: `{ query, filters }`
- **Response**: Array of matching destinations

### Tour Guide Endpoints

#### List Tour Guides
- **GET** `/?url=tourguides`
- **Description**: Get all tour guides
- **Query Params**: `page, per_page, location, specialization, rating`
- **Response**: Array of tour guides

#### Get Tour Guide Detail
- **GET** `/?url=tourguides/show/{id}`
- **Description**: Get tour guide details
- **Response**: Tour guide object with full details

#### Book Tour Guide
- **POST** `/?url=tourguides/book`
- **Description**: Book a tour guide
- **Request**: `{ guide_id, date, participants, notes }`
- **Response**: Booking confirmation

### Hotel Endpoints

#### List Hotels
- **GET** `/?url=hotels`
- **Description**: Get all hotels
- **Query Params**: `page, per_page, location, price_range, amenities`
- **Response**: Array of hotels

#### Get Hotel Detail
- **GET** `/?url=hotels/show/{id}`
- **Description**: Get hotel details
- **Response**: Hotel object with full details

### Restaurant Endpoints

#### List Restaurants
- **GET** `/?url=restaurants`
- **Description**: Get all restaurants
- **Query Params**: `page, per_page, location, cuisine, halal`
- **Response**: Array of restaurants

#### Get Restaurant Detail
- **GET** `/?url=restaurants/show/{id}`
- **Description**: Get restaurant details
- **Response**: Restaurant object with full details

### Event Endpoints

#### List Events
- **GET** `/?url=events`
- **Description**: Get all events
- **Query Params**: `page, per_page, date_range, category, location`
- **Response**: Array of events

#### Get Event Detail
- **GET** `/?url=events/show/{id}`
- **Description**: Get event details
- **Response**: Event object with full details

#### Register for Event
- **POST** `/?url=events/register`
- **Description**: Register for an event
- **Request**: `{ event_id, participants }`
- **Response**: Registration confirmation

### Booking Endpoints

#### List Bookings
- **GET** `/?url=bookings`
- **Description**: Get user bookings
- **Query Params**: `status, date_range`
- **Response**: Array of bookings

#### Create Booking
- **POST** `/?url=bookings/create`
- **Description**: Create new booking
- **Request**: `{ type, item_id, date, participants, notes }`
- **Response**: Booking confirmation

#### Get Booking Detail
- **GET** `/?url=bookings/show/{id}`
- **Description**: Get booking details
- **Response**: Booking object with full details

#### Cancel Booking
- **POST** `/?url=bookings/cancel/{id}`
- **Description**: Cancel a booking
- **Response**: Success message

### Payment Endpoints

#### Create Payment
- **POST** `/?url=payment/create`
- **Description**: Create payment
- **Request**: `{ booking_id, payment_method }`
- **Response**: Payment token, redirect URL

#### Payment Callback
- **POST** `/?url=payment/callback`
- **Description**: Midtrans payment callback
- **Request**: Midtrans notification data
- **Response**: Success message

#### Check Payment Status
- **GET** `/?url=payment/status/{id}`
- **Description**: Check payment status
- **Response**: Payment status

### Address Endpoints

#### Get Provinces
- **GET** `/?url=address/getProvinces`
- **Description**: Get all provinces
- **Response**: Array of provinces

#### Get Regencies
- **GET** `/?url=address/getRegencies?province_id={id}`
- **Description**: Get regencies by province
- **Response**: Array of regencies

#### Get Districts
- **GET** `/?url=address/getDistricts?regency_id={id}`
- **Description**: Get districts by regency
- **Response**: Array of districts

#### Get Villages
- **GET** `/?url=address/getVillages?district_id={id}`
- **Description**: Get villages by district
- **Response**: Array of villages

### Favorites Endpoints

#### List Favorites
- **GET** `/?url=favorites`
- **Description**: Get user favorites
- **Response**: Array of favorites

#### Add to Favorites
- **POST** `/?url=favorites/add`
- **Description**: Add item to favorites
- **Request**: `{ type, item_id }`
- **Response**: Success message

#### Remove from Favorites
- **POST** `/?url=favorites/remove`
- **Description**: Remove item from favorites
- **Request**: `{ type, item_id }`
- **Response**: Success message

### Admin Endpoints

#### Dashboard Stats
- **GET** `/?url=admin/dashboard`
- **Description**: Get admin dashboard statistics
- **Response**: Dashboard statistics

#### Manage Users
- **GET** `/?url=admin/users`
- **Description**: Get all users
- **Response**: Array of users

#### Manage Destinations
- **GET** `/?url=admin/destinations`
- **Description**: Get all destinations (admin view)
- **Response**: Array of destinations with admin controls

### AI Endpoints

#### Get Recommendations
- **POST** `/?url=ai/recommendations`
- **Description**: Get AI-powered recommendations
- **Request**: `{ preferences, location, budget }`
- **Response**: Array of recommendations

#### AI Chat
- **POST** `/?url=ai/chat`
- **Description**: AI chat conversation
- **Request**: `{ message, context }`
- **Response**: AI response

---

## ERROR HANDLING

### Error Response Format

```json
{
  "status": "error",
  "message": "Error description",
  "errors": {
    "field": "Error message for specific field"
  },
  "code": "ERROR_CODE"
}
```

### Common Error Codes

| Code | Description |
|------|-------------|
| VALIDATION_ERROR | Input validation failed |
| AUTHENTICATION_ERROR | Authentication required or failed |
| AUTHORIZATION_ERROR | User lacks permission |
| NOT_FOUND | Resource not found |
| CONFLICT | Resource conflict (duplicate) |
| SERVER_ERROR | Internal server error |
| RATE_LIMIT_EXCEEDED | Too many requests |

### Error Handling Examples

#### Validation Error
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": "Email is required",
    "password": "Password must be at least 8 characters"
  },
  "code": "VALIDATION_ERROR"
}
```

#### Authentication Error
```json
{
  "status": "error",
  "message": "Authentication required",
  "redirect": "/auth/login",
  "code": "AUTHENTICATION_ERROR"
}
```

#### Not Found Error
```json
{
  "status": "error",
  "message": "Resource not found",
  "code": "NOT_FOUND"
}
```

---

## RATE LIMITING

### Rate Limit Rules

| Endpoint | Limit | Period |
|----------|-------|--------|
| Public endpoints | 60 requests | per minute |
| Auth endpoints | 10 requests | per minute |
| API endpoints | 30 requests | per minute |
| Admin endpoints | 100 requests | per minute |

### Rate Limit Response

When rate limit is exceeded:

```json
{
  "status": "error",
  "message": "Rate limit exceeded",
  "code": "RATE_LIMIT_EXCEEDED",
  "retry_after": 60
}
```

### Rate Limit Headers

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1634567890
```

---

## API TESTING

### Using cURL

```bash
# Get destinations
curl "http://localhost:8080/?url=destinations"

# Login
curl -X POST "http://localhost:8080/?url=auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# Create booking
curl -X POST "http://localhost:8080/?url=bookings/create" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-Token: abc123xyz789" \
  -d '{"type":"guide","item_id":1,"date":"2026-08-01","participants":2}'
```

### Using Postman

1. Import API collection
2. Set base URL: `http://localhost:8080`
3. Add CSRF token header for POST requests
4. Use environment variables for tokens

### Using JavaScript/jQuery

```javascript
// GET request
$.get('/?url=destinations', function(response) {
  if (response.status === 'success') {
    console.log(response.data);
  }
});

// POST request
$.ajax({
  url: '/?url=auth/login',
  method: 'POST',
  headers: {
    'X-CSRF-Token': csrfToken
  },
  data: {
    email: 'user@example.com',
    password: 'password123'
  },
  success: function(response) {
    if (response.status === 'success') {
      window.location.href = response.data.redirect;
    }
  }
});
```

---

## OPENAPI SPECIFICATION

### OpenAPI 3.0 Specification

```yaml
openapi: 3.0.0
info:
  title: MyWisata Application API
  description: API for Tour Guide Application
  version: 1.0.0
  contact:
    name: API Support
    email: admin@mywisata.com

servers:
  - url: http://localhost:8080
    description: Development Server
  - url: https://yourdomain.com
    description: Production Server

components:
  securitySchemes:
    sessionAuth:
      type: apiKey
      in: cookie
      name: PHPSESSID

security:
  - sessionAuth: []

paths:
  /destinations:
    get:
      summary: Get all destinations
      tags:
        - Destinations
      responses:
        '200':
          description: Successful response
          content:
            application/json:
              schema:
                type: object
                properties:
                  status:
                    type: string
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Destination'

  /auth/login:
    post:
      summary: User login
      tags:
        - Authentication
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                email:
                  type: string
                password:
                  type: string
      responses:
        '200':
          description: Login successful
          content:
            application/json:
              schema:
                type: object
                properties:
                  status:
                    type: string
                  data:
                    type: object

components:
  schemas:
    Destination:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
        description:
          type: string
        location:
          type: string
        price:
          type: number
        rating:
          type: number
```

---

## BEST PRACTICES

### For API Consumers

1. **Handle errors gracefully** - Always check `status` field
2. **Use appropriate HTTP methods** - GET for retrieval, POST for creation
3. **Include CSRF tokens** - For all POST requests
4. **Validate input** - Before sending to API
5. **Handle rate limits** - Implement retry logic
6. **Cache responses** - When appropriate
7. **Use pagination** - For large datasets

### For API Developers

1. **Use consistent response format** - Standard JSON structure
2. **Implement proper error handling** - Meaningful error messages
3. **Document endpoints** - Keep API documentation updated
4. **Version your API** - Use versioning for breaking changes
5. **Implement rate limiting** - Protect against abuse
6. **Log API requests** - For debugging and monitoring
7. **Test thoroughly** - Unit and integration tests

---

## ADDITIONAL RESOURCES

### Documentation
- [Developer Guide](docs/DEVELOPER_GUIDE.md)
- [Testing Guide](docs/TESTING_GUIDE.md)
- [Project Structure](docs/PROJECT_STRUCTURE.md)

### External Links
- [REST API Tutorial](https://restfulapi.net/)
- [OpenAPI Specification](https://swagger.io/specification/)
- [JSON API Specification](https://jsonapi.org/)

---

**Version**: 1.0.0  
**Last Updated**: 2026-07-18  
**API Version**: 1.0.0
