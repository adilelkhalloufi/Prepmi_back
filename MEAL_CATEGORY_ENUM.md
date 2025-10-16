# Meal Category Enum Implementation

## Summary
Created a `MealCategory` enum to handle meal categories with the following values:
- **1** = Menu
- **2** = Breakfast
- **3** = Drinks

## Files Created/Modified

### 1. ✅ Created: `app/enum/MealCategory.php`
```php
enum MealCategory: int
{
    case MENU = 1;
    case BREAKFAST = 2;
    case DRINKS = 3;
}
```

**Methods:**
- `label()` - Returns the human-readable label (e.g., "Menu", "Breakfast", "Drinks")
- `toArray()` - Returns all categories as associative array `[1 => 'Menu', 2 => 'Breakfast', 3 => 'Drinks']`
- `fromValue(int $value)` - Gets enum from integer value

### 2. ✅ Updated: `app/Models/Meal.php`
- Imported `MealCategory` enum
- Added `category_id` to `$casts` as integer
- Added `getCategoryLabel()` method to get the category name

### 3. ✅ Updated: `app/Http/Resources/MealResource.php`
Now returns both ID and label:
```json
{
  "category_id": 1,
  "category": "Menu"
}
```

### 4. ✅ Updated: `app/Http/Controllers/MealController.php`
- Imported `MealCategory` enum
- **Validation:** `category_id` must be 1, 2, or 3
- **Added filter:** Can filter meals by category in index endpoint
- **New endpoint:** `getCategories()` - Returns all available categories

### 5. ✅ Updated: `routes/api.php`
Added new public route:
```php
Route::get('meals/categories', [MealController::class, 'getCategories']);
```

## API Usage

### Get All Categories
```http
GET /api/meals/categories
```

**Response:**
```json
{
  "data": {
    "1": "Menu",
    "2": "Breakfast",
    "3": "Drinks"
  }
}
```

### Filter Meals by Category
```http
GET /api/meals?category_id=1
```

### Create/Update Meal with Category
```http
POST /api/meals
Content-Type: application/json

{
  "name": "Pancakes",
  "category_id": 2,  // Breakfast
  // ... other fields
}
```

**Validation:**
- `category_id` is **nullable**
- Must be **1, 2, or 3** if provided
- Empty string (`""`) will be converted to `null`

## Frontend Integration

### When Sending Data:
```javascript
{
  category_id: 1,        // Valid: Menu
  category_id: 2,        // Valid: Breakfast  
  category_id: 3,        // Valid: Drinks
  category_id: "",       // Valid: Converted to null
  category_id: null,     // Valid: No category
  category_id: 4,        // ❌ Invalid: Not in enum
}
```

### When Receiving Data:
```javascript
{
  "id": 1,
  "name": "Pancakes",
  "category_id": 2,
  "category": "Breakfast",  // ← Human-readable label
  // ... other fields
}
```

## Database Schema
Make sure your `meals` table has a `category_id` column:
```php
$table->unsignedTinyInteger('category_id')->nullable();
```

If not, create a migration:
```bash
php artisan make:migration add_category_id_to_meals_table
```

## Testing Checklist
- [ ] GET `/api/meals/categories` returns all 3 categories
- [ ] Filter meals by `category_id=1` (Menu)
- [ ] Filter meals by `category_id=2` (Breakfast)
- [ ] Filter meals by `category_id=3` (Drinks)
- [ ] Create meal with `category_id: 1`
- [ ] Create meal with `category_id: ""` (should convert to null)
- [ ] Create meal with `category_id: 4` (should fail validation)
- [ ] Response includes both `category_id` and `category` label
