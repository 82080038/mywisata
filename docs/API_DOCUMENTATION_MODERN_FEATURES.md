# API Documentation - Modern Features (Modules 40-45)

> **Version:** 1.0  
> **Date:** 2026-07-18  
> **Status:** Implemented

---

## Module 40: AI Self-Hosted (Ollama)

### Endpoints

#### AI Search
```
POST /ai/search
Content-Type: application/json

{
  "query": "wisata pantai di Bali",
  "context": "destination"
}

Response:
{
  "success": true,
  "data": {
    "results": [...],
    "model": "llama2",
    "tokens_used": 150
  }
}
```

#### AI Customer Service
```
POST /ai/chat
Content-Type: application/json

{
  "message": "Bagaimana cara booking tour guide?",
  "session_id": "session_123"
}

Response:
{
  "success": true,
  "data": {
    "response": "...",
    "intent": "booking_inquiry"
  }
}
```

---

## Module 41: Sustainability (Carbon Tracking)

### Endpoints

#### Get User Sustainability Statistics
```
GET /sustainability

Response:
{
  "success": true,
  "data": {
    "eco_score": {
      "score": 75,
      "level": "gold"
    },
    "total_co2_saved": 125.5,
    "total_points": 350,
    "emissions_by_type": {
      "transport": 50.2,
      "accommodation": 30.1,
      "food": 20.5,
      "activity": 24.7
    },
    "recent_actions": [...]
  }
}
```

#### Record Carbon Emission
```
POST /sustainability/recordEmission

Form Data:
- emission_type: transport|accommodation|food|activity
- co2_kg: 10.5
- transport_mode: car|bus|train|flight
- distance_km: 50
- booking_id: 123 (optional)

Response:
{
  "success": true,
  "message": "Carbon emission recorded successfully"
}
```

#### Record Eco Action
```
POST /sustainability/recordAction

Form Data:
- action_type: public_transport|eco_accommodation|local_food|carbon_offset|waste_reduction
- description: "Used bus instead of car"

Response:
{
  "success": true,
  "message": "Eco action recorded! You earned 10 points",
  "points_earned": 10
}
```

#### Get Low-Carbon Routes
```
GET /sustainability/getRoutes?origin_id=1&destination_id=2

Response:
{
  "success": true,
  "data": [
    {
      "transport_mode": "train",
      "duration_hours": 2.5,
      "co2_kg": 5.2,
      "eco_score": 85,
      "is_recommended": true
    }
  ]
}
```

#### Get Eco Leaderboard
```
GET /sustainability/leaderboard?limit=10

Response:
{
  "success": true,
  "data": [
    {
      "user_id": 1,
      "name": "User Name",
      "eco_score": 95,
      "level": "platinum",
      "total_co2_saved": 250.5
    }
  ]
}
```

---

## Module 42: WhatsApp Integration (Self-Hosted)

### Endpoints

#### Register WhatsApp Contact
```
POST /whatsapp/register

Form Data:
- phone_number: +6281234567890

Response:
{
  "success": true,
  "message": "WhatsApp contact registered successfully"
}
```

#### Send Message
```
POST /whatsapp/send

Form Data:
- contact_id: 1
- message_type: custom|booking_confirmation|payment_reminder|review_request|promotion
- content: "Your booking has been confirmed"
- variables: [] (JSON array for template variables)

Response:
{
  "success": true,
  "message": "Message sent successfully"
}
```

#### Get Message Statistics
```
GET /whatsapp/statistics?contact_id=1

Response:
{
  "success": true,
  "data": {
    "total": 50,
    "sent": 45,
    "delivered": 40,
    "read": 35
  }
}
```

---

## Module 43: Business Operations (Self-Hosted)

### Endpoints

#### Match Guide for Booking
```
POST /business-operations/matchGuide

Form Data:
- booking_id: 123
- requirements: {"language": "en", "specialization": "nature", "budget": 500000}

Response:
{
  "success": true,
  "data": {
    "matched_guide_id": 5,
    "match_score": 92.5,
    "match_reasons": ["Language match", "Specialization match"]
  }
}
```

#### Create Schedule Entry
```
POST /business-operations/createSchedule

Form Data:
- guide_id: 5
- booking_id: 123
- title: "Bali Tour Day 1"
- description: "Visit temples and beaches"
- start_datetime: 2026-08-01 09:00:00
- end_datetime: 2026-08-01 17:00:00
- location: "Ubud, Bali"

Response:
{
  "success": true,
  "message": "Schedule entry created successfully"
}
```

#### Clock In
```
POST /business-operations/clockIn

Form Data:
- guide_id: 5
- booking_id: 123
- latitude: -8.5069
- longitude: 115.2625
- location_name: "Ubud Palace"

Response:
{
  "success": true,
  "message": "Clock in successful",
  "record_id": 456
}
```

#### Clock Out
```
POST /business-operations/clockOut

Form Data:
- record_id: 456
- latitude: -8.5069
- longitude: 115.2625
- notes: "Tour completed successfully"

Response:
{
  "success": true,
  "message": "Clock out successful",
  "hours_worked": 8.5
}
```

#### Create Express Book
```
POST /business-operations/expressBook

Form Data:
- guide_id: 5
- customer_name: "John Doe"
- customer_phone: "+6281234567890"
- customer_email: "john@example.com"
- service_type: "private_tour"
- duration_hours: 4
- price: 500000
- payment_method: cash|card|qr|transfer
- start_datetime: 2026-08-01 10:00:00

Response:
{
  "success": true,
  "message": "Express book created successfully"
}
```

#### Get Guide Statistics
```
GET /business-operations/guideStats?guide_id=5

Response:
{
  "success": true,
  "data": {
    "total_bookings": 50,
    "total_hours": 400,
    "total_earnings": 25000000,
    "average_rating": 4.8
  }
}
```

---

## Module 44: Document & Trip Management

### Endpoints

#### Get Wallet
```
GET /document-trip/wallet

Response:
{
  "success": true,
  "data": {
    "wallet": {
      "id": 1,
      "balance": 500000,
      "currency": "IDR"
    },
    "transactions": [...]
  }
}
```

#### Add Funds
```
POST /document-trip/addFunds

Form Data:
- amount: 100000
- description: "Top up wallet"

Response:
{
  "success": true,
  "message": "Funds added successfully",
  "new_balance": 600000
}
```

#### Get Transactions
```
GET /document-trip/transactions?limit=50

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "transaction_type": "credit",
      "amount": 100000,
      "description": "Top up wallet",
      "balance_after": 600000,
      "created_at": "2026-07-18 10:00:00"
    }
  ]
}
```

#### Import Itinerary
```
POST /document-trip/importItinerary

Form Data (multipart):
- file: itinerary.pdf

Response:
{
  "success": true,
  "message": "Itinerary imported successfully",
  "itinerary_id": 789
}
```

#### Get Trip Timeline
```
GET /document-trip/timeline?itinerary_id=789

Response:
{
  "success": true,
  "data": [
    {
      "day_number": 1,
      "time": "09:00:00",
      "activity_type": "transport",
      "title": "Transfer to hotel",
      "location": "Kuta, Bali"
    }
  ]
}
```

#### Generate PDF Itinerary
```
POST /document-trip/generatePDF

Form Data:
- itinerary_id: 789
- file_name: "bali_tour.pdf"

Response:
{
  "success": true,
  "message": "PDF generated successfully",
  "file_path": "/uploads/itineraries/bali_tour.pdf"
}
```

#### Subscribe to Updates
```
POST /document-trip/subscribe

Form Data:
- channel: "booking_123"
- subscription_type: booking|itinerary|message|notification
- reference_id: 123

Response:
{
  "success": true,
  "message": "Subscription created successfully"
}
```

---

## Module 45: Social Features

### Endpoints

#### Create Group Trip
```
POST /social-features/createGroupTrip

Form Data:
- trip_name: "Bali Adventure 2026"
- destination_id: 1
- start_date: 2026-08-01
- end_date: 2026-08-05
- max_participants: 10
- is_public: 1
- description: "Exploring Bali together"

Response:
{
  "success": true,
  "message": "Group trip created successfully",
  "trip_id": 100
}
```

#### Invite Participant
```
POST /social-features/inviteParticipant

Form Data:
- trip_id: 100
- user_id: 5
- role: participant|organizer|viewer

Response:
{
  "success": true,
  "message": "Participant invited successfully"
}
```

#### Accept Invitation
```
POST /social-features/acceptInvitation

Form Data:
- trip_id: 100

Response:
{
  "success": true,
  "message": "Invitation accepted successfully"
}
```

#### Get Group Trip Details
```
GET /social-features/groupTripDetails?trip_id=100

Response:
{
  "success": true,
  "data": {
    "trip": {...},
    "participants": [...]
  }
}
```

#### Create Shared Wishlist
```
POST /social-features/createWishlist

Form Data:
- wishlist_name: "Bali Bucket List"
- is_public: 1
- description: "Places we want to visit in Bali"

Response:
{
  "success": true,
  "message": "Wishlist created successfully",
  "wishlist_id": 200
}
```

#### Add to Wishlist
```
POST /social-features/addToWishlist

Form Data:
- wishlist_id: 200
- destination_id: 5
- notes: "Must visit this temple"
- priority: high|medium|low

Response:
{
  "success": true,
  "message": "Added to wishlist successfully"
}
```

#### Add Collaborator
```
POST /social-features/addCollaborator

Form Data:
- wishlist_id: 200
- user_id: 5
- permission: view|edit|admin

Response:
{
  "success": true,
  "message": "Collaborator added successfully"
}
```

#### Get Wishlist Details
```
GET /social-features/wishlistDetails?wishlist_id=200

Response:
{
  "success": true,
  "data": {
    "wishlist": {...},
    "items": [...],
    "collaborators": [...]
  }
}
```

#### Create Split Payment Group
```
POST /social-features/createPaymentGroup

Form Data:
- group_name: "Bali Trip Expenses"
- booking_id: 123
- total_amount: 5000000
- settlement_deadline: 2026-08-10

Response:
{
  "success": true,
  "message": "Payment group created successfully",
  "group_id": 300
}
```

#### Add Payment Member
```
POST /social-features/addPaymentMember

Form Data:
- group_id: 300
- user_id: 5
- share_amount: 1000000

Response:
{
  "success": true,
  "message": "Payment member added successfully"
}
```

#### Record Payment
```
POST /social-features/recordPayment

Form Data:
- member_id: 50
- amount: 1000000

Response:
{
  "success": true,
  "message": "Payment recorded successfully",
  "remaining_amount": 0
}
```

#### Get Payment Group Details
```
GET /social-features/paymentGroupDetails?group_id=300

Response:
{
  "success": true,
  "data": {
    "group": {...},
    "members": [...],
    "total_paid": 4000000,
    "total_remaining": 1000000
  }
}
```

#### Create Trip Album
```
POST /social-features/createAlbum

Form Data:
- group_trip_id: 100
- album_name: "Bali Memories"
- description: "Our trip photos"
- is_public: 1

Response:
{
  "success": true,
  "message": "Album created successfully",
  "album_id": 400
}
```

#### Add Photo
```
POST /social-features/addPhoto

Form Data (multipart):
- album_id: 400
- photo: [file]
- caption: "Beautiful sunset at Kuta Beach"
- location: "Kuta Beach"
- latitude: -8.7185
- longitude: 115.1686

Response:
{
  "success": true,
  "message": "Photo added successfully",
  "photo_id": 500
}
```

#### Add Comment
```
POST /social-features/addComment

Form Data:
- photo_id: 500
- comment: "Amazing shot!"

Response:
{
  "success": true,
  "message": "Comment added successfully"
}
```

#### Get Album Details
```
GET /social-features/albumDetails?album_id=400

Response:
{
  "success": true,
  "data": {
    "album": {...},
    "photos": [...],
    "photo_count": 25
  }
}
```

---

## Authentication

All endpoints require authentication. Include session cookie or API token in requests.

```
Cookie: PHPSESSID=your_session_id
```

---

## Error Responses

All endpoints return error responses in the following format:

```json
{
  "success": false,
  "error": "Error message description"
}
```

Common error codes:
- `401` - Not authenticated
- `403` - Permission denied
- `404` - Resource not found
- `422` - Validation error
- `500` - Server error

---

## Rate Limiting

API endpoints are rate-limited to prevent abuse:
- Standard endpoints: 100 requests per minute
- AI endpoints: 30 requests per minute
- File upload endpoints: 10 requests per minute

---

## Version History

- **v1.0** (2026-07-18) - Initial implementation of Modern Features (Modules 40-45)
