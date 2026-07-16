# MyWisata - Image Requirements Guide

## Image Directory Structure

```
public/assets/
├── icons/              # PWA icons and app icons
│   ├── icon-72x72.png
│   ├── icon-96x96.png
│   ├── icon-128x128.png
│   ├── icon-144x144.png
│   ├── icon-152x152.png
│   ├── icon-192x192.png
│   ├── icon-384x384.png
│   ├── icon-512x512.png
│   ├── maskable-icon-192x192.png
│   ├── maskable-icon-512x512.png
│   ├── shortcut-search.png
│   ├── shortcut-booking.png
│   ├── shortcut-favorites.png
│   ├── shortcut-ai.png
│   └── badge-72x72.png
├── images/
│   ├── destinations/    # Destination images
│   ├── hotels/          # Hotel images
│   ├── restaurants/     # Restaurant images
│   ├── tour_guides/     # Tour guide profile images
│   ├── users/           # User profile images
│   ├── badges/          # Gamification badges
│   └── avatars/        # User avatars
└── screenshots/        # App screenshots for PWA
    ├── home.png
    └── mobile-home.png
```

## Required Images

### 1. PWA Icons (Required for PWA Installation)

**App Icons:**
- `icon-72x72.png` - 72x72 pixels
- `icon-96x96.png` - 96x96 pixels
- `icon-128x128.png` - 128x128 pixels
- `icon-144x144.png` - 144x144 pixels
- `icon-152x152.png` - 152x152 pixels
- `icon-192x192.png` - 192x192 pixels
- `icon-384x384.png` - 384x384 pixels
- `icon-512x512.png` - 512x512 pixels

**Maskable Icons (for adaptive icons):**
- `maskable-icon-192x192.png` - 192x192 pixels
- `maskable-icon-512x512.png` - 512x512 pixels

**Shortcut Icons:**
- `shortcut-search.png` - 96x96 pixels
- `shortcut-booking.png` - 96x96 pixels
- `shortcut-favorites.png` - 96x96 pixels
- `shortcut-ai.png` - 96x96 pixels

**Badge Icon:**
- `badge-72x72.png` - 72x72 pixels

### 2. Category Images (Content Images)

**Destinations:**
- Recommended size: 1200x800 pixels (3:2 ratio)
- Format: JPG, PNG, or WebP
- Max file size: 5MB
- Content: High-quality photos of Indonesian destinations

**Hotels:**
- Recommended size: 1200x800 pixels (3:2 ratio)
- Format: JPG, PNG, or WebP
- Max file size: 5MB
- Content: Hotel exterior, rooms, amenities

**Restaurants:**
- Recommended size: 1200x800 pixels (3:2 ratio)
- Format: JPG, PNG, or WebP
- Max file size: 5MB
- Content: Restaurant interior, food, ambiance

**Tour Guides:**
- Recommended size: 400x400 pixels (1:1 ratio)
- Format: JPG, PNG, or WebP
- Max file size: 2MB
- Content: Professional profile photos

**Users/Avatars:**
- Recommended size: 200x200 pixels (1:1 ratio)
- Format: JPG, PNG, or WebP
- Max file size: 1MB
- Content: User profile pictures

**Badges:**
- Recommended size: 100x100 pixels (1:1 ratio)
- Format: PNG with transparency
- Content: Gamification badge icons

### 3. PWA Screenshots (Optional)

- `home.png` - 1280x720 pixels (desktop view)
- `mobile-home.png` - 390x844 pixels (mobile view)

## Image Generation Options

### Option 1: Use Placeholder Services (Development Phase)

For development, you can use placeholder image services:

```php
// In your views, use placeholder URLs
<img src="https://via.placeholder.com/800x600/3b82f6/ffffff?text=MyWisata+Destination" alt="Destination">
```

### Option 2: Use Free Stock Photo Sites

**Recommended Free Stock Photo Sites:**
- Unsplash (https://unsplash.com) - High-quality free photos
- Pexels (https://pexels.com) - Free stock photos
- Pixabay (https://pixabay.com) - Free images and vectors
- Freepik (https://freepik.com) - Free graphics and photos

**Search Terms for Indonesian Tourism:**
- "Bali temple", "Borobudur", "Komodo island"
- "Indonesian beach", "Raja Ampat", "Ubud rice terraces"
- "Indonesian food", "Nasi Goreng", "Satay"
- "Indonesian culture", "Batik", "Wayang kulit"

### Option 3: Generate Icons Using Online Tools

**Recommended Icon Generators:**
- Favicon.io (https://favicon.io) - Generate all icon sizes from one image
- RealFaviconGenerator (https://realfavicongenerator.net) - Complete favicon package
- AppIconGenerator (https://appicon.co) - Generate app icons for all platforms

**Steps:**
1. Create a 1024x1024 PNG logo with transparent background
2. Upload to icon generator
3. Download generated icons
4. Place in `public/assets/icons/` directory

### Option 4: Use AI Image Generation

**AI Image Generation Tools:**
- DALL-E (OpenAI)
- Midjourney
- Stable Diffusion
- Canva AI Image Generator

**Prompts for MyWisata:**
- "Modern Indonesian tourism app logo, blue and white, minimalist"
- "Beautiful Indonesian beach sunset, tropical paradise"
- "Traditional Indonesian temple, Borobudur style"
- "Delicious Indonesian cuisine, Nasi Goreng presentation"

## Image Helper Usage

The `Image` helper class provides the following functionality:

```php
// Upload image
$image = new Image();
$result = $image->upload($_FILES['image'], 'destinations');

// Get image URL
$url = $image->getUrl('destination_1.jpg', 'destinations');

// Get thumbnail URL
$thumbUrl = $image->getThumbnailUrl('destination_1.jpg', 'destinations');

// Delete image
$image->delete('/assets/images/destinations/destination_1.jpg');

// Get placeholder
$placeholder = $image->getPlaceholder('destinations');
```

## Automatic Image Processing

The Image helper automatically:
- Validates file type and size
- Generates unique filenames
- Resizes large images (max 1920px)
- Creates thumbnails (300x200px)
- Optimizes image quality
- Maintains aspect ratio

## Placeholder Fallback

If no image is available, the system automatically uses placeholder images:
- Destinations: Blue placeholder with "MyWisata Destination" text
- Hotels: Green placeholder with "MyWisata Hotel" text
- Restaurants: Orange placeholder with "MyWisata Restaurant" text
- Tour Guides: Purple placeholder with "Guide" text
- Users: Indigo placeholder with "User" text
- Badges: Pink placeholder with "Badge" text

## Production Recommendations

For production deployment:

1. **Use CDN** - Serve images through a CDN for faster loading
2. **WebP Format** - Convert images to WebP for better compression
3. **Lazy Loading** - Implement lazy loading for image-heavy pages
4. **Image Optimization** - Use tools like TinyPNG or ImageOptim
5. **Responsive Images** - Serve different sizes for different devices
6. **Image Compression** - Configure server compression for images

## Environment Variables

Set these in your `.env` file:

```env
# Image settings
IMAGE_MAX_SIZE=5242880
IMAGE_ALLOWED_TYPES=jpg,jpeg,png,webp
IMAGE_QUALITY=85
THUMBNAIL_WIDTH=300
THUMBNAIL_HEIGHT=200
```

## Next Steps

1. **Create App Logo** - Design a 1024x1024 PNG logo for MyWisata
2. **Generate Icons** - Use icon generator to create all required icon sizes
3. **Add Sample Images** - Add a few sample destination images for testing
4. **Test Upload** - Test image upload functionality
5. **Optimize for Production** - Implement CDN and image optimization
