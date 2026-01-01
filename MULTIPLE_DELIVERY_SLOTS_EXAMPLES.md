# Multiple Delivery Slots - Usage Examples

## Frontend Implementation Example

### 1. Fetch Available Slots

```javascript
// Get available slots for authenticated user
async function fetchAvailableSlots() {
    const response = await fetch("/api/delivery-slots/available", {
        headers: {
            Authorization: `Bearer ${userToken}`,
        },
    });
    const data = await response.json();
    return data.data; // Array of available slots
}
```

### 2. Display Slots with Multi-Select (Up to 3)

```javascript
// React/Vue example
function DeliverySlotSelector() {
    const [availableSlots, setAvailableSlots] = useState([]);
    const [selectedSlotIds, setSelectedSlotIds] = useState([]);

    const handleSlotToggle = (slotId) => {
        if (selectedSlotIds.includes(slotId)) {
            // Remove slot
            setSelectedSlotIds(selectedSlotIds.filter((id) => id !== slotId));
        } else {
            // Add slot (max 3)
            if (selectedSlotIds.length < 3) {
                setSelectedSlotIds([...selectedSlotIds, slotId]);
            } else {
                alert("You can select up to 3 delivery slots");
            }
        }
    };

    return (
        <div>
            <h3>Select up to 3 delivery time slots</h3>
            <p>Selected: {selectedSlotIds.length}/3</p>
            {availableSlots.map((slot) => (
                <div key={slot.id}>
                    <input
                        type="checkbox"
                        checked={selectedSlotIds.includes(slot.id)}
                        onChange={() => handleSlotToggle(slot.id)}
                        disabled={
                            !selectedSlotIds.includes(slot.id) &&
                            selectedSlotIds.length >= 3
                        }
                    />
                    <label>
                        {slot.slot_name} ({slot.start_time} - {slot.end_time}) -
                        Remaining: {slot.remaining_capacity}
                        {slot.price_adjustment > 0 &&
                            ` +${slot.price_adjustment} MAD`}
                    </label>
                </div>
            ))}
        </div>
    );
}
```

### 3. Submit Order with Multiple Slots

```javascript
async function createOrder(orderData, selectedSlotIds) {
    const response = await fetch("/api/orders", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${userToken}`,
        },
        body: JSON.stringify({
            infos: {
                firstName: "John",
                lastName: "Doe",
                phoneNumber: "+212600000000",
                address: "123 Main St, Casablanca",
                email: "john@example.com",
            },
            meals: [
                { id: 1, quantity: 2 },
                { id: 3, quantity: 1 },
            ],
            drinks: [],
            plan: { id: 1 },
            paymentMethod: "cash_on_delivery",
            totalAmount: 147.0,
            delivery_slot_ids: selectedSlotIds, // [1, 3, 5] - Up to 3 slots
            purchaseType: "one_time",
        }),
    });

    const result = await response.json();

    if (result.success) {
        console.log("Order created with multiple delivery slots");
        console.log("Order details:", result.data);
    } else {
        console.error("Order creation failed:", result.message);
    }

    return result;
}
```

## Backend - Accessing Multiple Deliveries

### In Controller or Service

```php
// Get order with all deliveries
$order = Order::with(['deliveries.deliverySlot'])->find($orderId);

// Loop through all delivery records
foreach ($order->deliveries as $delivery) {
    echo "Delivery Slot: " . $delivery->deliverySlot->slot_name;
    echo "Time: " . $delivery->delivery_window_start->format('H:i')
         . " - " . $delivery->delivery_window_end->format('H:i');
    echo "Status: " . $delivery->status->value;
}

// Count total deliveries
$totalDeliveries = $order->deliveries()->count(); // Max 3

// Get only pending deliveries
$pendingDeliveries = $order->deliveries()
    ->where('status', DeliveryStatus::PENDING)
    ->get();
```

## API Request/Response Examples

### Example 1: Single Slot Selection

**Request:**

```json
{
    "infos": { "firstName": "Alice", "lastName": "Smith", ... },
    "meals": [{"id": 2, "quantity": 1}],
    "plan": {"id": 1},
    "paymentMethod": "card",
    "totalAmount": 49.00,
    "delivery_slot_ids": [3]
}
```

**Result:** 1 delivery record created for the order

---

### Example 2: Two Slots Selection

**Request:**

```json
{
    "infos": { "firstName": "Bob", "lastName": "Johnson", ... },
    "meals": [{"id": 1, "quantity": 2}],
    "plan": {"id": 2},
    "paymentMethod": "cash_on_delivery",
    "totalAmount": 98.00,
    "delivery_slot_ids": [1, 5]
}
```

**Result:** 2 delivery records created for the order

---

### Example 3: Maximum 3 Slots

**Request:**

```json
{
    "infos": { "firstName": "Carol", "lastName": "Davis", ... },
    "meals": [{"id": 4, "quantity": 3}],
    "plan": {"id": 3},
    "paymentMethod": "card",
    "totalAmount": 147.00,
    "delivery_slot_ids": [2, 4, 7]
}
```

**Result:** 3 delivery records created for the order

---

### Example 4: Invalid - More than 3 Slots (Rejected)

**Request:**

```json
{
    "delivery_slot_ids": [1, 2, 3, 4]
}
```

**Response (422 Error):**

```json
{
    "message": "You can select up to 3 delivery slots.",
    "errors": {
        "delivery_slot_ids": ["You can select up to 3 delivery slots."]
    }
}
```

---

### Example 5: Invalid - Duplicate Slots (Rejected)

**Request:**

```json
{
    "delivery_slot_ids": [1, 1, 3]
}
```

**Response (422 Error):**

```json
{
    "message": "You cannot select the same slot multiple times.",
    "errors": {
        "delivery_slot_ids.1": [
            "You cannot select the same slot multiple times."
        ]
    }
}
```

## Use Cases

### Use Case 1: Flexible Delivery Windows

Customer selects 3 different time slots for the same day to maximize delivery flexibility:

-   Morning: 8:00-10:00
-   Afternoon: 14:00-16:00
-   Evening: 18:00-20:00

The delivery can be fulfilled in any of these windows.

### Use Case 2: Multi-Day Delivery

For subscription orders, select slots across different days:

-   Monday morning
-   Wednesday afternoon
-   Friday evening

### Use Case 3: Backup Slots

Select primary slot + 2 backup slots in case the first one gets full or has issues.

## Important Notes

1. **Validation:**

    - Maximum 3 slots per order
    - All slot IDs must be unique
    - Each slot must exist and be available
    - User eligibility is checked for each slot

2. **Capacity Management:**

    - Each selected slot's capacity is decremented
    - If any slot becomes unavailable during processing, the entire order creation may fail

3. **Delivery Records:**

    - One delivery record per selected slot
    - All deliveries belong to the same order
    - Each delivery can have independent status tracking

4. **Cancellation:**
    - When cancelling deliveries, remember to free up slot capacity
    ```php
    foreach ($order->deliveries as $delivery) {
        $delivery->deliverySlot->cancelBooking();
        $delivery->delete();
    }
    ```
