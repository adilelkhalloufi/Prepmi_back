# Membership Admin API Documentation

## Overview
This document describes membership-related API endpoints intended for the Admin/Manager panel only. It covers managing membership plans, memberships (administrative actions) and membership transactions. Use these endpoints from the admin panel UI to create/update/delete plans, view membership lists and statistics, activate memberships, and manage transactions.

Base URL: `https://your-domain.com/api`

Authentication: All endpoints below require authentication using Laravel Sanctum with an admin/manager account.
Headers (required):
```
Authorization: Bearer {admin-token}
Content-Type: application/json
Accept: application/json
```

---

**Quick map of admin endpoints**
- Membership Plans (Admin)
  - `POST /membership-plans` — create plan
  - `PUT /membership-plans/{id}` — update plan
  - `DELETE /membership-plans/{id}` — delete plan
  - `POST /membership-plans/{id}/toggle-active` — toggle plan active status

- Memberships (Admin)
  - `GET /memberships` — list memberships (filters/pagination)
  - `GET /memberships/statistics` — membership counts & breakdown
  - `POST /memberships/{id}/activate` — activate a pending membership

- Membership Transactions (Admin)
  - `GET /membership-transactions` — list transactions (filters/pagination)
  - `GET /membership-transactions/statistics` — transaction statistics & revenue
  - `POST /membership-transactions` — create/manual transaction
  - `POST /membership-transactions/{id}/complete` — mark transaction completed
  - `POST /membership-transactions/{id}/fail` — mark transaction failed
  - `POST /membership-transactions/{id}/refund` — process refund

---

**Detailed Endpoints**

**Membership Plans (Admin)**

- POST /membership-plans
  - Purpose: Create a new membership plan.
  - Body (JSON):
    - `name` (string, required)
    - `description` (string, optional)
    - `monthly_fee` (number, required)
    - `discount_percentage` (number, optional)
    - `delivery_slots` (int, optional)
    - `includes_free_desserts` (boolean, optional)
    - `free_desserts_quantity` (int, optional)
    - `perks` (array, optional)
    - `is_active` (boolean, optional)
    - `billing_day_of_month` (int 1-28, optional)
  - Success: 201 Created with created plan object.

- PUT /membership-plans/{id}
  - Purpose: Update a plan.
  - Body: same as create; send only fields to update.
  - Success: 200 OK with updated plan object.

- DELETE /membership-plans/{id}
  - Purpose: Remove a plan. Will return 400 if plan has active memberships.
  - Success: 200 OK `{ "message": "Membership plan deleted successfully" }`.

- POST /membership-plans/{id}/toggle-active
  - Purpose: Toggle `is_active` flag on a plan.
  - Success: 200 OK with updated plan object.

Example create request:

```
POST /api/membership-plans
Authorization: Bearer {admin-token}
Content-Type: application/json

{
  "name": "Silver",
  "monthly_fee": 19.99,
  "discount_percentage": 5,
  "delivery_slots": 3,
  "includes_free_desserts": false,
  "is_active": true,
  "billing_day_of_month": 5
}
```

Example success response (201):

```json
{
  "message": "Membership plan created successfully",
  "data": {
    "id": 10,
    "name": "Silver",
    "monthly_fee": "19.99",
    "is_active": true,
    "billing_day_of_month": 5
  }
}
```

---

**Memberships (Admin)**

- GET /memberships
  - Purpose: Admin list of memberships with filters & pagination.
  - Query parameters supported (examples):
    - `status` (string) — filter by membership status (e.g., `active`, `pending`, `frozen`, `cancelled`)
    - `user_id` (int)
    - `membership_plan_id` (int)
    - `started_from` (date)
    - `started_to` (date)
    - `order_by` (string) default `created_at`
    - `order_direction` (`asc`|`desc`)
    - `per_page` (int)
    - `paginate` (boolean)
  - Success: 200 OK — paginated memberships array (includes `user` and `membershipPlan` relations by default).

- GET /memberships/statistics
  - Purpose: Returns aggregates: total, active, frozen, cancelled, pending, and memberships-by-plan counts.
  - Success: 200 OK with stats object.

- POST /memberships/{id}/activate
  - Purpose: Activate a membership that is in `pending` state (typically after payment confirmation).
  - Body: none required.
  - Success: 200 OK with activated membership object.
  - Errors: 400 if membership not in `pending`; 404 if not found.

Example list request:

```
GET /api/memberships?status=active&per_page=25
Authorization: Bearer {admin-token}
```

Example activate request:

```
POST /api/memberships/42/activate
Authorization: Bearer {admin-token}
```

Example activate response (200):

```json
{
  "message": "Membership activated successfully",
  "data": {
    "id": 42,
    "status": "active",
    "started_at": "2025-03-01T12:00:00.000000Z",
    "user": { "id": 77, "name": "Jane Admin" },
    "membership_plan": { "id": 2, "name": "Gold" }
  }
}
```

---

**Membership Transactions (Admin)**

- GET /membership-transactions
  - Purpose: Admin listing of transactions with filters and pagination.
  - Query parameters: `membership_id`, `user_id`, `payment_status`, `transaction_type`, `from_date`, `to_date`, `order_by`, `order_direction`, `per_page`, `paginate`.
  - Success: 200 OK paginated array with membership and user relations.

- GET /membership-transactions/statistics
  - Purpose: Returns transaction aggregates (counts by status, revenue, refunds, revenue_by_month).
  - Success: 200 OK with statistics object.

- POST /membership-transactions
  - Purpose: Create a transaction manually (e.g., administrative adjustment, manual charge).
  - Body (JSON):
    - `membership_id` (int, required)
    - `amount` (number, required)
    - `transaction_type` (string, required) — one of: `monthly_charge`, `refund`, `adjustment`
    - `payment_method` (string, optional)
    - `payment_reference` (string, optional)
    - `billing_period_start` (date, required)
    - `billing_period_end` (date, required)
    - `notes` (string, optional)
  - Success: 201 Created with transaction object (initial `payment_status` will be `pending`).

- POST /membership-transactions/{id}/complete
  - Purpose: Mark a transaction as completed and update membership next billing date if `transaction_type` is `monthly_charge`.
  - Body: optional `payment_reference`.
  - Success: 200 OK with updated transaction object.

- POST /membership-transactions/{id}/fail
  - Purpose: Mark a transaction as failed.
  - Body (JSON): `failure_reason` (string, required)
  - Success: 200 OK with updated transaction object.

- POST /membership-transactions/{id}/refund
  - Purpose: Process a refund for a completed transaction.
  - Body (JSON):
    - `refund_amount` (number, optional) — default to original amount if omitted
    - `refund_reason` (string, required)
  - Success: 200 OK with original transaction (updated) and the new refund transaction object.

Example create transaction request:

```
POST /api/membership-transactions
Authorization: Bearer {admin-token}
Content-Type: application/json

{
  "membership_id": 42,
  "amount": 29.99,
  "transaction_type": "monthly_charge",
  "payment_method": "manual",
  "billing_period_start": "2025-03-01",
  "billing_period_end": "2025-03-31",
  "notes": "Manual charge created by admin"
}
```

Example mark complete request:

```
POST /api/membership-transactions/101/complete
Authorization: Bearer {admin-token}
Content-Type: application/json

{ "payment_reference": "ch_admin_101" }
```

Example refund request:

```
POST /api/membership-transactions/101/refund
Authorization: Bearer {admin-token}
Content-Type: application/json

{ "refund_amount": 29.99, "refund_reason": "Customer requested refund" }
```

Example refund response (200):

```json
{
  "message": "Refund processed successfully",
  "data": {
    "original_transaction": { /* original updated transaction */ },
    "refund_transaction": { /* new refund transaction object */ }
  }
}
```

---

**Status codes & common errors**
- 200 OK: successful read/update actions
- 201 Created: resource created
- 400 Bad Request: business rule violation (e.g., plan has active memberships)
- 401 Unauthorized: missing/invalid auth token
- 404 Not Found: resource not found
- 422 Validation Failed: invalid request data
- 500 Server Error: unexpected server error

All error responses use a JSON shape similar to:

```json
{ "message": "Description of error" }
```

Validation errors include an `errors` object:

```json
{ "message": "Validation failed", "errors": { "field": ["error message"] } }
```

---

**Frontend Admin Panel notes**
- Use server-side pagination for lists (`/memberships`, `/membership-transactions`). Provide `per_page` and current `page` to backend.
- For `memberships` list: include filters (status, plan, user search) and quick actions: `activate`, `freeze/unfreeze` (freeze/unfreeze endpoints exist for user-facing API; admin may call them as well if needed).
- For `membership-plans` UI: validate `billing_day_of_month` (1-28) and ensure `monthly_fee` is numeric.
- For transactions: show `payment_status` and allow admins to mark `complete`/`fail` or issue `refund` with required reason.
- Show friendly messages from `message` field returned by API and surface error details from `errors` when validation fails.

---

If you want, I can also:
- generate a Postman collection (JSON) for these admin endpoints,
- add cURL examples for each endpoint,
- or update the README with a link to this file.

File path: `MEMBERSHIP_ADMIN_API_DOCUMENTATION.md` in the project root.
