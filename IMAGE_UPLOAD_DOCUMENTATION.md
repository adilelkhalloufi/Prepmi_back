# Meal Image Upload Implementation

## Overview
Implemented complete image handling for meal creation and updates, including main image and gallery images.

## Changes Made

### 1. ✅ MealController.php

#### Store Method (Create Meal)
- Changed validation from `'image_path' => 'nullable|string'` to `'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'`
- Changed validation from `'gallery_images.*' => 'string'` to `'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048'`
- Added automatic file upload handling:
  - Main image saved to `storage/app/public/meals/images/`
  - Gallery images saved to `storage/app/public/meals/gallery/`
  - Unique filenames using timestamp + uniqid

#### Update Method
- Same validation changes as store
- Automatically deletes old images before uploading new ones
- Handles both main image and gallery image replacements

#### Force Delete Method
- Updated to use `Storage::disk('public')` instead of default disk
- Properly cleans up images when permanently deleting meals

#### New Methods Added:
1. **`uploadImage()`** - Upload main image separately
2. **`uploadGalleryImages()`** - Upload multiple gallery images
3. **`deleteImage()`** - Delete a specific image by path

### 2. ✅ MealResource.php
Added image URL fields for frontend convenience:
- `image_url` - Full URL to main image
- `gallery_urls` - Array of full URLs to gallery images

### 3. ✅ routes/api.php
Added new protected routes:
```php
POST /api/meals/upload-image
POST /api/meals/upload-gallery  
DELETE /api/meals/delete-image
```

## API Usage

### Option 1: Upload Images with Meal Creation (Single Request)

```http
POST /api/meals
Content-Type: multipart/form-data

{
  "name": "Delicious Pancakes",
  "description": "Fluffy pancakes",
  "image": <file>,                    // Main image file
  "gallery_images[]": <file1>,        // Gallery image 1
  "gallery_images[]": <file2>,        // Gallery image 2
  "gallery_images[]": <file3>,        // Gallery image 3
  "price": "12.99",
  "category_id": 2,
  // ... other fields
}
```

### Option 2: Upload Images First, Then Create Meal (Two-Step)

#### Step 1: Upload Main Image
```http
POST /api/meals/upload-image
Content-Type: multipart/form-data

{
  "image": <file>
}
```

**Response:**
```json
{
  "message": "Image uploaded successfully",
  "path": "meals/images/1729123456_abc123.jpg",
  "url": "http://yourdomain.com/storage/meals/images/1729123456_abc123.jpg"
}
```

#### Step 2: Upload Gallery Images
```http
POST /api/meals/upload-gallery
Content-Type: multipart/form-data

{
  "images[]": <file1>,
  "images[]": <file2>,
  "images[]": <file3>
}
```

**Response:**
```json
{
  "message": "Images uploaded successfully",
  "images": [
    {
      "path": "meals/gallery/1729123456_xyz789.jpg",
      "url": "http://yourdomain.com/storage/meals/gallery/1729123456_xyz789.jpg"
    },
    {
      "path": "meals/gallery/1729123457_def456.jpg",
      "url": "http://yourdomain.com/storage/meals/gallery/1729123457_def456.jpg"
    }
  ]
}
```

#### Step 3: Create Meal with Image Paths
```http
POST /api/meals
Content-Type: application/json

{
  "name": "Delicious Pancakes",
  "description": "Fluffy pancakes",
  "image_path": "meals/images/1729123456_abc123.jpg",
  "gallery_images": [
    "meals/gallery/1729123456_xyz789.jpg",
    "meals/gallery/1729123457_def456.jpg"
  ],
  "price": "12.99",
  "category_id": 2
}
```

### Update Meal with New Images

```http
PUT /api/meals/{id}
Content-Type: multipart/form-data

{
  "name": "Updated Pancakes",
  "image": <new_file>,              // Optional: Upload new main image
  "gallery_images[]": <file1>,      // Optional: Replace all gallery images
  "gallery_images[]": <file2>,
  // ... other fields
}
```

### Delete a Specific Image

```http
DELETE /api/meals/delete-image
Content-Type: application/json

{
  "path": "meals/gallery/1729123456_xyz789.jpg"
}
```

## Response Structure

When retrieving a meal, you'll get:

```json
{
  "data": {
    "id": 1,
    "name": "Delicious Pancakes",
    "image_path": "meals/images/1729123456_abc123.jpg",
    "image_url": "http://yourdomain.com/storage/meals/images/1729123456_abc123.jpg",
    "gallery_images": [
      "meals/gallery/1729123456_xyz789.jpg",
      "meals/gallery/1729123457_def456.jpg"
    ],
    "gallery_urls": [
      "http://yourdomain.com/storage/meals/gallery/1729123456_xyz789.jpg",
      "http://yourdomain.com/storage/meals/gallery/1729123457_def456.jpg"
    ],
    // ... other fields
  }
}
```

## Frontend Integration

### Example: Using React/Vue with FormData

```javascript
// Create FormData for meal with images
const formData = new FormData();
formData.append('name', 'Pancakes');
formData.append('description', 'Fluffy and delicious');
formData.append('price', '12.99');
formData.append('category_id', 2);

// Append main image
if (mainImageFile) {
  formData.append('image', mainImageFile);
}

// Append gallery images
galleryFiles.forEach(file => {
  formData.append('gallery_images[]', file);
});

// Append other fields
formData.append('is_vegetarian', true);
formData.append('calories', '350');

// Send request
const response = await fetch('/api/meals', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    // Don't set Content-Type, browser will set it automatically with boundary
  },
  body: formData
});
```

### Example: Two-Step Upload (Upload First, Submit Later)

```javascript
// Step 1: Upload main image when user selects it
async function handleMainImageSelect(file) {
  const formData = new FormData();
  formData.append('image', file);
  
  const response = await fetch('/api/meals/upload-image', {
    method: 'POST',
    headers: { 'Authorization': 'Bearer ' + token },
    body: formData
  });
  
  const data = await response.json();
  setMainImagePath(data.path);  // Save path for later
  setMainImageUrl(data.url);    // Display preview
}

// Step 2: Upload gallery images
async function handleGalleryImagesSelect(files) {
  const formData = new FormData();
  files.forEach(file => formData.append('images[]', file));
  
  const response = await fetch('/api/meals/upload-gallery', {
    method: 'POST',
    headers: { 'Authorization': 'Bearer ' + token },
    body: formData
  });
  
  const data = await response.json();
  setGalleryPaths(data.images.map(img => img.path));
  setGalleryUrls(data.images.map(img => img.url));
}

// Step 3: Submit meal with image paths
async function handleSubmit() {
  const response = await fetch('/api/meals', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer ' + token,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      name: 'Pancakes',
      description: 'Delicious',
      image_path: mainImagePath,        // From step 1
      gallery_images: galleryPaths,     // From step 2
      price: '12.99',
      // ... other fields
    })
  });
}
```

## File Storage Structure

```
storage/
└── app/
    └── public/
        └── meals/
            ├── images/           # Main meal images
            │   ├── 1729123456_abc123.jpg
            │   ├── 1729123457_def456.png
            │   └── ...
            └── gallery/          # Gallery images
                ├── 1729123456_xyz789.jpg
                ├── 1729123457_uvw012.jpg
                └── ...
```

## Image Specifications

- **Allowed formats:** JPEG, PNG, JPG, GIF, WebP
- **Max file size:** 2MB (2048 KB)
- **Storage location:** `storage/app/public/meals/`
- **Public access:** Via `/storage` symlink

## Important Setup

Make sure the storage link exists:
```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

## Validation Rules

### Main Image (`image`)
- Required when using upload endpoints
- Optional when creating/updating meals
- Must be image file
- Allowed: jpeg, png, jpg, gif, webp
- Max size: 2MB

### Gallery Images (`gallery_images[]` or `images[]`)
- Optional
- Each must be image file
- Allowed: jpeg, png, jpg, gif, webp
- Max size: 2MB per image

### Image Path (`image_path`)
- When passing pre-uploaded path: `'nullable|string'`
- System validates and stores the path

## Testing Checklist

- [ ] Create meal with main image (FormData)
- [ ] Create meal with gallery images (FormData)
- [ ] Create meal with both main and gallery images
- [ ] Upload image separately via `/upload-image`
- [ ] Upload gallery images via `/upload-gallery`
- [ ] Update meal with new main image
- [ ] Update meal with new gallery images
- [ ] Delete specific image via `/delete-image`
- [ ] Force delete meal (verify images are deleted from storage)
- [ ] Verify image URLs in API response work
- [ ] Verify storage/app/public/meals directory is created
- [ ] Verify `php artisan storage:link` has been run

## Error Handling

### Common Errors:

1. **"The image failed to upload"**
   - Check file size (must be < 2MB)
   - Check file format (must be jpeg, png, jpg, gif, or webp)
   - Check storage permissions

2. **"404 Not Found" when accessing image URL**
   - Run `php artisan storage:link`
   - Check `storage/app/public/meals` exists
   - Check file permissions

3. **"The gallery images field must be an array"**
   - Use `gallery_images[]` notation in FormData
   - Or send as proper array in JSON

## Notes

- Old images are automatically deleted when updating
- Images are permanently deleted when force-deleting a meal
- Soft-deleted meals keep their images until force-deleted
- Image paths are stored in database, actual files in storage
- Use `Storage::url()` to get public URL from path
