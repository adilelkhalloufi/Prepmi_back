# Delivery Slots API Documentation

## Overview

The delivery slots system allows you to manage delivery time slots for both membership and normal users. Slots can be restricted to specific user types and have capacity management. **Users can select up to 3 delivery slots per order** for flexibility in delivery scheduling.

## Slot Types

-   **MEMBERSHIP**: Exclusive for membership users
-   **NORMAL**: Available for normal users only
-   **BOTH**: Available for all users

## API Endpoints

### 1. Get All Delivery Slots

```http
GET /api/delivery-slots
```

**Query Parameters:**

-   `day_of_week` (optional): Filter by day (0=Sunday, 1=Monday, ..., 6=Saturday)

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "slot_name": "Early Morning (All Users)",
            "slot_type": "both",
            "slot_type_label": "All Users",
            "start_time": "08:00",
            "end_time": "10:00",
            "max_capacity": 15,
            "current_bookings": 3,
            "remaining_capacity": 12,
            "day_of_week": null,
            "day_of_week_name": "All Days",
            "is_active": true,
            "is_available": true,
            "is_full": false,
            "price_adjustment": 0.0,
            "description": "Early morning delivery slot available for all users"
        }
    ]
}
```

### 2. Get Available Slots (User-Specific)

```http
GET /api/delivery-slots/available
```

Automatically filters slots based on authenticated user's membership status.

**Query Parameters:**

-   `day_of_week` (optional): Filter by day

**Response includes:**

```json
{
  "success": true,
  "data": [...],
  "meta": {
    "has_membership": true,
    "user_type": "membership"
  }
}
```

### 3. Get Slots by Type

```http
GET /api/delivery-slots/type/{type}
```

**Types:** `membership`, `normal`, `both`

**Query Parameters:**

-   `day_of_week` (optional): Filter by day

### 4. Get Specific Slot

```http
GET /api/delivery-slots/{id}
```

## Creating Orders with Delivery Slots

### Order Request with Delivery Slots

```http
POST /api/orders
```

**Request Body:**

```json
{
    "infos": {
        "firstName": "John",
        "lastName": "Doe",
        "phoneNumber": "+212600000000",
        "address": "123 Main St, Casablanca",
        "email": "john@example.com",
        "password": "password123"
    },
    "meals": [
        {
            "id": 1,
            "quantity": 2
        }
    ],
    "drinks": [],
    "plan": {
        "id": 1
    },
    "paymentMethod": "cash_on_delivery",
    "totalAmount": 98.0,
    "delivery_slot_ids": [1, 3, 5],
    "purchaseType": "one_time"
}
```

**New Field:**

-   `delivery_slot_ids` (optional): Array of delivery slot IDs (maximum 3 slots)
    -   Can select 1, 2, or 3 slots
    -   Each slot ID must be unique
    -   Each slot must exist and be available

## Validation Rules

When creating an order:

-   `delivery_slot_ids` accepts an array with maximum 3 slot IDs
-   Each slot ID is validated against the `delivery_slots` table
-   Each slot ID must be unique (no duplicates)
-   The system checks if each slot is available (not full)
-   The system verifies the user's eligibility based on membership status:
    -   Membership users can only book `MEMBERSHIP` or `BOTH` slots
    -   Normal users can only book `NORMAL` or `BOTH` slots
-   Each slot's capacity is automatically decremented when booked

## Automatic Delivery Creation

When an order is created with `delivery_slot_ids`:

1. Multiple `Delivery` records are automatically created (one per selected slot)
2. Each delivery's window times are set from its slot's `start_time` and `end_time`
3. Each slot's `current_bookings` is incremented
4. All deliveries' status are set to `PENDING`
5. The order has a `deliveries()` relationship to access all delivery records

## Database Schema

### delivery_slots Table

```sql
- id
- slot_name
- slot_type (enum: membership, normal, both)
- start_time (time)
- end_time (time)
- max_capacity (integer)
- current_bookings (integer)
- day_of_week (0-6, nullable for all days)
- is_active (boolean)
- price_adjustment (decimal)
- description (text)
- timestamps
```

### deliveries Table (updated)

```sql
- id
- order_id (foreign key)
- delivery_slot_id (foreign key, nullable)
- courier_name
- tracking_number
- delivery_window_start
- delivery_window_end
- delivered_at
- status
- notes
- timestamps
```

## Example Usage

### Frontend Flow

1. **Fetch available slots** based on user type:

```javascript
// Authenticated user - auto-detects membership status
fetch("/api/delivery-slots/available?day_of_week=1");

// Or filter by type manually
fetch("/api/delivery-slots/type/membership");
```

2. **Display slots** to user with:

    - Slot name and time range
    - Remaining capacity
    - Price adjustment (if any)
    - Day restrictions
    - Allow selection of up to 3 slots

3. **Submit order** with selected `delivery_slot_ids` array (1-3 slots)

```javascript
// Example: User selects 2 slots
const orderData = {
    // ... other fields
    delivery_slot_ids: [1, 5], // Can be 1, 2, or 3 slot IDs
};
```

4. **System handles**:
    - Validation (max 3 slots, unique IDs)
    - Capacity check for each slot
    - User eligibility verification
    - Automatic creation of multiple delivery records

## Model Methods

### DeliverySlot Model

```php
// Check availability
$slot->isAvailable()
$slot->isAvailableForMembership()
$slot->isAvailableForNormalUser()
$slot->isFull()

// Get info
$slot->getRemainingCapacity()

// Book/Cancel
$slot->book()
$slot->cancelBooking()

// Scopes
DeliverySlot::active()->available()->forMembership()->get()
DeliverySlot::byDay(1)->forNormalUsers()->get()
```

## Notes

-   Users can select up to 3 delivery slots per order for flexible scheduling
-   Each selected slot creates a separate delivery record for the same order
-   All selected slot IDs must be unique (no duplicates allowed)
-   Slots with `day_of_week = null` are available every day
-   Slots with specific days only show on those days
-   Price adjustments are added to the order total
-   Membership status is checked from active memberships
-   When a delivery is cancelled, remember to call `$slot->cancelBooking()` to free up capacity
-   Use `$order->deliveries()` relationship to access all delivery records for an order
