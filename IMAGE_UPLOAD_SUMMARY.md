# Image Upload Implementation Summary

## ✅ Complete! Image handling has been fully implemented.

## What Changed:

### 1. **MealController** - File Upload Handling
✅ **Store method:** Accepts `image` file and `gallery_images[]` files
✅ **Update method:** Accepts new images and deletes old ones
✅ **Force delete:** Properly cleans up image files from storage
✅ **New endpoints:**
   - `POST /api/meals/upload-image` - Upload main image separately
   - `POST /api/meals/upload-gallery` - Upload gallery images separately  
   - `DELETE /api/meals/delete-image` - Delete specific image

### 2. **MealResource** - Image URLs
✅ Added `image_url` - Full public URL to main image
✅ Added `gallery_urls` - Array of full public URLs to gallery images

### 3. **Routes** - New Image Endpoints
✅ Added 3 new protected routes for image operations

## How to Use from Frontend:

### Method 1: Upload Everything Together (Recommended for Simple Forms)
```javascript
const formData = new FormData();
formData.append('name', 'Pancakes');
formData.append('image', mainImageFile);           // File object
formData.append('gallery_images[]', galleryFile1); // File object
formData.append('gallery_images[]', galleryFile2); // File object
formData.append('price', '12.99');
// ... other fields

fetch('/api/meals', {
  method: 'POST',
  headers: { 'Authorization': 'Bearer ' + token },
  body: formData
});
```

### Method 2: Upload Images First (Recommended for Better UX)
```javascript
// Step 1: Upload main image when user selects it
const imageFormData = new FormData();
imageFormData.append('image', file);
const imageResponse = await fetch('/api/meals/upload-image', {
  method: 'POST',
  body: imageFormData
});
const { path, url } = await imageResponse.json();
// Show preview using `url`, save `path` for step 3

// Step 2: Upload gallery images
const galleryFormData = new FormData();
files.forEach(f => galleryFormData.append('images[]', f));
const galleryResponse = await fetch('/api/meals/upload-gallery', {
  method: 'POST',
  body: galleryFormData
});
const { images } = await galleryResponse.json();
// images = [{ path: '...', url: '...' }, ...]

// Step 3: Create meal with paths
await fetch('/api/meals', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    name: 'Pancakes',
    image_path: path,              // from step 1
    gallery_images: images.map(i => i.path), // from step 2
    price: '12.99'
  })
});
```

## API Response Example:

```json
{
  "data": {
    "id": 1,
    "name": "Pancakes",
    "image_path": "meals/images/1729123456_abc123.jpg",
    "image_url": "http://yourdomain.com/storage/meals/images/1729123456_abc123.jpg",
    "gallery_images": [
      "meals/gallery/1729123456_xyz789.jpg"
    ],
    "gallery_urls": [
      "http://yourdomain.com/storage/meals/gallery/1729123456_xyz789.jpg"
    ],
    "category_id": 2,
    "category": "Breakfast",
    // ... other fields
  }
}
```

## Frontend Data Structure:

### When Creating/Updating (Option 1 - Direct Upload):
```javascript
{
  name: "Pancakes",
  image: File,                    // Actual file object from <input type="file">
  gallery_images: [File, File],   // Array of file objects
  price: "12.99",
  category_id: 2,
  // ... other fields (all accept empty strings, will convert to null)
}
```

### When Creating/Updating (Option 2 - Pre-uploaded):
```javascript
{
  name: "Pancakes",
  image_path: "meals/images/123.jpg",        // String path from upload endpoint
  gallery_images: ["meals/gallery/1.jpg"],   // Array of string paths
  price: "12.99",
  category_id: 2,
  // ... other fields
}
```

## Important Notes:

1. **Storage Link Required:**
   ```bash
   php artisan storage:link
   ```

2. **Image Specs:**
   - Formats: JPEG, PNG, JPG, GIF, WebP
   - Max size: 2MB per image
   - Stored in: `storage/app/public/meals/images/` and `storage/app/public/meals/gallery/`

3. **Automatic Features:**
   - ✅ Unique filenames (timestamp + uniqid)
   - ✅ Old images deleted when updating
   - ✅ Images deleted when force-deleting meal
   - ✅ Empty strings converted to null
   - ✅ Full URLs provided in response

4. **Both Methods Work:**
   - You can send files directly in create/update requests
   - OR upload images first, then send paths
   - Mix and match as needed

## Files Modified:
- ✅ `app/Http/Controllers/MealController.php`
- ✅ `app/Http/Resources/MealResource.php`
- ✅ `routes/api.php`

## Documentation Created:
- ✅ `IMAGE_UPLOAD_DOCUMENTATION.md` - Complete API documentation
- ✅ `MEAL_CATEGORY_ENUM.md` - Category enum documentation
- ✅ `FRONTEND_BACKEND_COMPATIBILITY.md` - Empty string handling

## Ready to Test! 🚀
