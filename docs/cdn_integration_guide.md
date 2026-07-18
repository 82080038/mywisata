# CDN INTEGRATION GUIDE
# Tour Guide Application

## OVERVIEW

This guide provides comprehensive instructions for using CDN integration in the Tour Guide Application to improve content delivery speed and global performance.

## ASSET SERVICE

### AssetService Class
Location: `app/services/AssetService.php`

### Basic Operations

#### Get CSS URL
```php
$assetService = new \App\Services\AssetService();
$cssUrl = $assetService->css('style.css');
```

#### Get JS URL
```php
$jsUrl = $assetService->js('main.js');
```

#### Get Image URL
```php
$imageUrl = $assetService->image('logo.png');
```

#### Get Asset URL
```php
$assetUrl = $assetService->asset('fonts/roboto.woff2');
```

#### Get Font URL
```php
$fontUrl = $assetService->font('roboto.woff2');
```

### CDN Status Check
```php
if ($assetService->isEnabled()) {
    // CDN is enabled
    $cdnUrl = $assetService->getCdnUrl();
}
```

## IMAGE SERVICE

### ImageService Class
Location: `app/services/ImageService.php`

### Image Operations

#### Get Image URL with Options
```php
$imageService = new \App\Services\ImageService();

// With width
$url = $imageService->getImageUrl('photo.jpg', ['width' => 800]);

// With height
$url = $imageService->getImageUrl('photo.jpg', ['height' => 600]);

// With quality
$url = $imageService->getImageUrl('photo.jpg', ['quality' => 80]);

// With format
$url = $imageService->getImageUrl('photo.jpg', ['format' => 'webp']);

// Multiple options
$url = $imageService->getImageUrl('photo.jpg', [
    'width' => 800,
    'height' => 600,
    'quality' => 80,
    'format' => 'webp'
]);
```

#### Responsive Images
```php
// Get responsive image sources
$sources = $imageService->getResponsiveImage('photo.jpg', [320, 640, 1024, 1920]);

// Get srcset attribute
$srcset = $imageService->getSrcset('photo.jpg', [320, 640, 1024, 1920]);
```

## HTML TEMPLATE INTEGRATION

### Update Header
```php
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'MyWisata') ?></title>
    
    <?php
    $assetService = new \App\Services\AssetService();
    ?>
    
    <!-- CDN Assets -->
    <link rel="stylesheet" href="<?= $assetService->css('bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= $assetService->css('style.css') ?>">
    
    <?php if ($assetService->isEnabled()): ?>
        <!-- Preconnect to CDN -->
        <link rel="preconnect" href="<?= $assetService->getCdnUrl() ?>">
        <link rel="dns-prefetch" href="<?= $assetService->getCdnUrl() ?>">
    <?php endif; ?>
</head>
```

### Update Footer
```php
<?php
$assetService = new \App\Services\AssetService();
?>

<script src="<?= $assetService->js('jquery.min.js') ?>"></script>
<script src="<?= $assetService->js('bootstrap.bundle.min.js') ?>"></script>
<script src="<?= $assetService->js('main.js') ?>"></script>
```

### Update Images
```php
<?php
$imageService = new \App\Services\ImageService();
?>

<img src="<?= $imageService->getImageUrl('destination.jpg', ['width' => 800]) ?>" 
     alt="Destination" 
     srcset="<?= $imageService->getSrcset('destination.jpg', [320, 640, 1024, 1920]) ?>"
     sizes="(max-width: 600px) 320px, (max-width: 1200px) 640px, 1024px">
```

## CACHE CONTROL HEADERS

### Enable Cache Headers
Add the contents of `public/.htaccess.cdn` to your main `.htaccess` file when CDN is enabled.

### Cache Durations
- CSS/JS files: 1 year
- Images: 1 year
- Fonts: 1 year
- PDF files: 1 month

## ENVIRONMENT CONFIGURATION

### Enable CDN
Add to `.env` file:
```env
CDN_ENABLED=true
CDN_URL=https://yourdomain.com
APP_VERSION=1.0.0
```

### Disable CDN
```env
CDN_ENABLED=false
CDN_URL=
APP_VERSION=1.0.0
```

## VERSION CONTROL

### Asset Versioning
Assets are automatically versioned using the `APP_VERSION` environment variable. When you update assets:
1. Increment `APP_VERSION` in `.env`
2. CDN URLs will automatically include the new version
3. Browsers will fetch the new assets

### Example
```env
APP_VERSION=1.0.1
```

## PERFORMANCE TIPS

### 1. Use Appropriate Cache Durations
- Static assets: 1 year
- Frequently updated assets: 1 month
- Dynamic content: No caching

### 2. Optimize Images
- Compress images before upload
- Use WebP format when possible
- Implement responsive images
- Use lazy loading

### 3. Minify Assets
- Minify CSS files
- Minify JavaScript files
- Enable Cloudflare auto minify

### 4. Use CDN for All Static Assets
- CSS files
- JavaScript files
- Images
- Fonts
- Videos

## TROUBLESHOOTING

### Assets Not Loading from CDN
1. Check CDN is enabled in `.env`
2. Verify CDN URL is correct
3. Check Cloudflare DNS propagation
4. Clear browser cache
5. Check SSL/TLS configuration

### Cache Not Working
1. Verify cache headers are set
2. Check Cloudflare cache rules
3. Test with curl: `curl -I https://yourdomain.com/public/css/style.css`
4. Check cache TTL settings

### Version Not Updating
1. Increment `APP_VERSION` in `.env`
2. Clear Cloudflare cache
3. Clear browser cache
4. Verify asset URLs include version parameter

### Mixed Content Errors
1. Ensure CDN URL uses HTTPS
2. Enable "Always Use HTTPS" in Cloudflare
3. Update all asset URLs to use HTTPS

## MONITORING

### Cloudflare Analytics
1. Go to Cloudflare dashboard
2. Navigate to Analytics & Logs
3. Monitor:
   - Bandwidth usage
   - Request count
   - Cache hit rate
   - Threats blocked

### Cache Hit Rate
Aim for:
- Static assets: 90%+ cache hit rate
- Images: 85%+ cache hit rate
- Overall: 80%+ cache hit rate

## BEST PRACTICES

1. **Always use version control** - Increment version when updating assets
2. **Optimize images** - Compress and use modern formats
3. **Use responsive images** - Serve appropriate sizes for devices
4. **Monitor cache hit rates** - Optimize based on data
5. **Set appropriate TTL** - Balance freshness and performance
6. **Enable compression** - Use Brotli compression
7. **Use HTTPS** - Ensure all assets use HTTPS
8. **Test before going live** - Verify CDN delivery works correctly

## RESOURCES

- [Cloudflare Documentation](https://developers.cloudflare.com/)
- [AssetService.php](../app/services/AssetService.php)
- [ImageService.php](../app/services/ImageService.php)
- [Cloudflare Setup Guide](cloudflare_setup_guide.md)

---

**Version:** 1.0  
**Last Updated:** 2026-07-18
