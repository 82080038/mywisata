# CDN INTEGRATION
# Module 38 - Content Delivery Network Integration for Tour Guide Application

## OVERVIEW

This prompting template guides the AI through integrating a Content Delivery Network (CDN) for the Tour Guide Application to improve content delivery speed, reduce server load, and enhance global performance.

## CDN BENEFITS

### Performance Improvements
- **Faster content delivery** - Edge servers closer to users
- **Reduced latency** - Lower response times globally
- **Lower server load** - Offload static content
- **Better scalability** - Handle traffic spikes
- **Improved SEO** - Faster page load scores

### Use Cases
- Static assets (CSS, JS, images)
- Video content
- File downloads
- API caching (optional)
- Dynamic content acceleration

## CDN OPTIONS

### Recommended CDNs
1. **Cloudflare** - Free tier available, easy setup
2. **AWS CloudFront** - AWS integration
3. **CloudFront** - Google Cloud integration
4. **Fastly** - High performance
5. **Akamai** - Enterprise grade

### Recommended for This Project
**Cloudflare** - Best for this project due to:
- Free tier available
- Easy setup
- SSL/TLS included
- DDoS protection
- Global network
- Caching options

## CLOUDFLARE SETUP

### Account Setup
1. Sign up at https://cloudflare.com
2. Add your domain
3. Update nameservers
4. Wait for DNS propagation

### DNS Configuration
```
Type: A
Name: @
IPv4 address: YOUR_SERVER_IP
Proxy status: Proxied (orange cloud)

Type: CNAME
Name: www
Target: yourdomain.com
Proxy status: Proxied (orange cloud)
```

## ASSET OPTIMIZATION

### Image Optimization
```php
// ImageService.php
class ImageService
{
    private $cdnUrl;
    
    public function __construct()
    {
        $this->cdnUrl = env('CDN_URL', 'https://cdn.yourdomain.com');
    }
    
    /**
     * Get CDN URL for image
     */
    public function getImageUrl($path, $options = [])
    {
        $baseUrl = $this->cdnUrl . '/public/images/' . $path;
        
        // Add Cloudflare Image Resizing parameters
        if (!empty($options)) {
            $params = [];
            
            if (isset($options['width'])) {
                $params['width'] = $options['width'];
            }
            
            if (isset($options['height'])) {
                $params['height'] = $options['height'];
            }
            
            if (isset($options['quality'])) {
                $params['quality'] = $options['quality'];
            }
            
            if (isset($options['format'])) {
                $params['format'] = $options['format'];
            }
            
            if (!empty($params)) {
                $baseUrl .= '?' . http_build_query($params);
            }
        }
        
        return $baseUrl;
    }
    
    /**
     * Generate responsive image sources
     */
    public function getResponsiveImage($path, $sizes = [320, 640, 1024, 1920])
    {
        $sources = [];
        
        foreach ($sizes as $size) {
            $sources[] = [
                'url' => $this->getImageUrl($path, ['width' => $size]),
                'width' => $size
            ];
        }
        
        return $sources;
    }
}
```

### CSS/JS Optimization
```php
// AssetService.php
class AssetService
{
    private $cdnUrl;
    private $version;
    
    public function __construct()
    {
        $this->cdnUrl = env('CDN_URL', 'https://cdn.yourdomain.com');
        $this->version = env('APP_VERSION', '1.0.0');
    }
    
    /**
     * Get CSS URL with version
     */
    public function css($path)
    {
        return $this->cdnUrl . '/public/css/' . $path . '?v=' . $this->version;
    }
    
    /**
     * Get JS URL with version
     */
    public function js($path)
    {
        return $this->cdnUrl . '/public/js/' . $path . '?v=' . $this->version;
    }
    
    /**
     * Get image URL
     */
    public function image($path)
    {
        return $this->cdnUrl . '/public/images/' . $path;
    }
    
    /**
     * Get asset URL
     */
    public function asset($path)
    {
        return $this->cdnUrl . '/public/' . $path;
    }
}
```

## HTML TEMPLATES UPDATE

### Update header layout
```php
<!-- layouts/header.php -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'MyWisata') ?></title>
    
    <!-- CDN Assets -->
    <link rel="stylesheet" href="<?= $assetService->css('bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= $assetService->css('style.css') ?>">
    
    <!-- Preconnect to CDN -->
    <link rel="preconnect" href="<?= env('CDN_URL') ?>">
    <link rel="dns-prefetch" href="<?= env('CDN_URL') ?>">
</head>
```

### Update footer layout
```php
<!-- layouts/footer.php -->
<script src="<?= $assetService->js('jquery.min.js') ?>"></script>
<script src="<?= $assetService->js('bootstrap.bundle.min.js') ?>"></script>
<script src="<?= $assetService->js('main.js') ?>"></script>
```

## CLOUDFLARE PAGE RULES

### Cache Rules
1. **Static Assets**
   - Pattern: `*/public/css/*`
   - Settings: Cache Level: Standard, Edge Cache TTL: 1 month

2. **Images**
   - Pattern: `*/public/images/*`
   - Settings: Cache Level: Standard, Edge Cache TTL: 1 month

3. **JavaScript**
   - Pattern: `*/public/js/*`
   - Settings: Cache Level: Standard, Edge Cache TTL: 1 month

### Performance Rules
1. **Auto Minify**
   - CSS: On
   - JavaScript: On
   - HTML: On

2. **Brotli Compression**
   - Enabled

3. **HTTP/3 (QUIC)**
   - Enabled

## CACHE CONTROL HEADERS

### .htaccess Configuration
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    
    # CSS and JavaScript
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    
    # Images
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    
    # Fonts
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    
    # Other
    ExpiresByType application/pdf "access plus 1 month"
</IfModule>

<IfModule mod_headers.c>
    <FilesMatch "\.(css|js)$">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>
    
    <FilesMatch "\.(jpg|jpeg|png|gif|webp|svg)$">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>
    
    <FilesMatch "\.(woff|woff2)$">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>
</IfModule>
```

## ENVIRONMENT CONFIGURATION

### Add to .env
```env
CDN_URL=https://cdn.yourdomain.com
CDN_ENABLED=true
APP_VERSION=1.0.0
```

### config/cdn.php
```php
<?php
return [
    'enabled' => env('CDN_ENABLED', false),
    'url' => env('CDN_URL', ''),
    'assets' => [
        'css' => env('CDN_URL', '') . '/public/css',
        'js' => env('CDN_URL', '') . '/public/js',
        'images' => env('CDN_URL', '') . '/public/images',
        'fonts' => env('CDN_URL', '') . '/public/fonts'
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 31536000 // 1 year
    ]
];
```

## ASSET UPLOAD TO CDN

### Asset Upload Script
```php
// scripts/upload-to-cdn.php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class CDNUploader
{
    private $s3;
    private $bucket;
    
    public function __construct()
    {
        $this->s3 = new S3Client([
            'region' => env('AWS_REGION', 'us-east-1'),
            'version' => 'latest',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY')
            ]
        ]);
        
        $this->bucket = env('AWS_S3_BUCKET');
    }
    
    /**
     * Upload file to CDN
     */
    public function upload($localPath, $remotePath, $options = [])
    {
        $defaultOptions = [
            'CacheControl' => 'public, max-age=31536000, immutable',
            'ContentType' => $this->getMimeType($localPath)
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        try {
            $result = $this->s3->putObject([
                'Bucket' => $this->bucket,
                'Key' => $remotePath,
                'SourceFile' => $localPath,
                'ACL' => 'public-read'
            ] + $options);
            
            return [
                'success' => true,
                'url' => $result['ObjectURL']
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Upload directory
     */
    public function uploadDirectory($localDir, $remoteDir)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($localDir)
        );
        
        $results = [];
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $localPath = $file->getPathname();
                $relativePath = substr($localPath, strlen($localDir) + 1);
                $remotePath = $remoteDir . '/' . $relativePath;
                
                $results[] = $this->upload($localPath, $remotePath);
            }
        }
        
        return $results;
    }
    
    private function getMimeType($path)
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2'
        ];
        
        return $mimeTypes[$ext] ?? 'application/octet-stream';
    }
}

// Usage
$uploader = new CDNUploader();
$results = $uploader->uploadDirectory(
    __DIR__ . '/../public',
    'public'
);

foreach ($results as $result) {
    if ($result['success']) {
        echo "Uploaded: " . $result['url'] . "\n";
    } else {
        echo "Failed: " . $result['error'] . "\n";
    }
}
```

## CACHE INVALIDATION

### Cloudflare API Cache Purge
```php
// CloudflareService.php
class CloudflareService
{
    private $apiToken;
    private $zoneId;
    private $apiUrl;
    
    public function __construct()
    {
        $this->apiToken = env('CLOUDFLARE_API_TOKEN');
        $this->zoneId = env('CLOUDFLARE_ZONE_ID');
        $this->apiUrl = 'https://api.cloudflare.com/client/v4';
    }
    
    /**
     * Purge single file
     */
    public function purgeFile($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/zones/' . $this->zoneId . '/purge_cache');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'files' => [$url]
        ]));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Purge cache by tag
     */
    public function purgeTag($tag)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/zones/' . $this->zoneId . '/purge_cache');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'tags' => [$tag]
        ]));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Purge entire cache
     */
    public function purgeAll()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/zones/' . $this->zoneId . '/purge_cache');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'purge_everything' => true
        ]));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}
```

## MONITORING

### CDN Analytics Dashboard
```php
// In admin dashboard
public function cdnStats()
{
    $cloudflare = new \App\Services\CloudflareService();
    
    // Get analytics from Cloudflare API
    $stats = [
        'bandwidth' => $this->getBandwidthUsage(),
        'requests' => $this->getRequestCount(),
        'cache_hit_rate' => $this->getCacheHitRate(),
        'threats_blocked' => $this->getThreatsBlocked()
    ];
    
    return $this->view('admin/cdn-stats', [
        'stats' => $stats
    ]);
}
```

## IMPLEMENTATION TASKS

### Phase 1: Setup
1. Sign up for Cloudflare
2. Add domain to Cloudflare
3. Update DNS nameservers
4. Wait for propagation
5. Configure DNS records

### Phase 2: Configuration
1. Configure page rules
2. Set up cache rules
3. Enable auto minify
4. Enable Brotli compression
5. Configure SSL/TLS

### Phase 3: Asset Optimization
1. Create AssetService class
2. Create ImageService class
3. Update HTML templates
4. Configure cache headers
5. Optimize images

### Phase 4: Integration
1. Update asset URLs
2. Implement CDN in views
3. Add version control
4. Implement cache invalidation
5. Test CDN delivery

### Phase 5: Monitoring
1. Set up analytics
2. Monitor cache hit rates
3. Monitor bandwidth usage
4. Set up alerts
5. Optimize based on data

## DELIVERABLES

1. AssetService class
2. ImageService class
3. CloudflareService class
4. Updated HTML templates
5. Cache header configuration
6. Asset upload script
7. Cache invalidation system
8. Monitoring dashboard
9. Configuration files
10. Documentation

## ACCEPTANCE CRITERIA

- CDN configured and active
- Static assets served via CDN
- Cache headers configured
- Image optimization working
- Cache invalidation working
- Performance improved
- Monitoring dashboard active
- Documentation complete
- SSL/TLS configured
- Page rules set up

## NOTES

- Test CDN before going live
- Monitor cache hit rates
- Use cache tags for invalidation
- Optimize images before upload
- Use version control for assets
- Monitor bandwidth costs
- Keep origin server secure
- Regular performance audits
- Document CDN configuration

---

**Module:** 38_CDN_INTEGRATION  
**Priority:** MEDIUM  
**Status:** READY FOR DEVELOPMENT  
**Last Updated:** 2026-07-18
