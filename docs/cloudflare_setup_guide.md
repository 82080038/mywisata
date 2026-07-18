# CLOUDFLARE SETUP GUIDE
# Tour Guide Application

## OVERVIEW

This guide provides step-by-step instructions for setting up Cloudflare CDN for the Tour Guide Application.

## CLOUDFLARE ACCOUNT SETUP

### 1. Create Account
1. Go to [Cloudflare](https://cloudflare.com)
2. Click "Sign Up"
3. Enter your email address and password
4. Verify your email address

### 2. Add Your Domain
1. Log in to Cloudflare dashboard
2. Click "Add a Site"
3. Enter your domain name (e.g., mywisata.com)
4. Select the Free plan
5. Click "Add Site"

## DNS CONFIGURATION

### 1. Add DNS Records
1. After adding your site, Cloudflare will scan existing DNS records
2. Review and add the following records:

```
Type: A
Name: @
IPv4 address: YOUR_SERVER_IP
Proxy status: Proxied (orange cloud)
TTL: Auto

Type: CNAME
Name: www
Target: yourdomain.com
Proxy status: Proxied (orange cloud)
TTL: Auto
```

### 2. Update Nameservers
1. Cloudflare will provide two nameservers (e.g., ns1.cloudflare.com, ns2.cloudflare.com)
2. Log in to your domain registrar
3. Replace existing nameservers with Cloudflare nameservers
4. Save changes

### 3. Wait for Propagation
- DNS propagation can take 24-48 hours
- Cloudflare will notify you when propagation is complete
- Check status in Cloudflare dashboard

## SSL/TLS CONFIGURATION

### 1. SSL/TLS Mode
1. Go to SSL/TLS tab in Cloudflare dashboard
2. Set SSL/TLS mode to "Flexible" (recommended for start)
3. For production, use "Full" or "Full (strict)"

### 2. Always Use HTTPS
1. Go to SSL/TLS > Edge Certificates
2. Enable "Always Use HTTPS"
3. Enable "Automatic HTTPS Rewrites"

## CACHING CONFIGURATION

### 1. Caching Level
1. Go to Caching > Configuration
2. Set Caching Level to "Standard"

### 2. Browser Cache TTL
1. Go to Caching > Configuration
2. Set Browser Cache TTL to "Respect Existing Headers"

### 3. Page Rules
Create the following page rules:

#### Static Assets
- Pattern: `*/public/css/*`
- Settings: Cache Level: Standard, Edge Cache TTL: 1 month

- Pattern: `*/public/js/*`
- Settings: Cache Level: Standard, Edge Cache TTL: 1 month

- Pattern: `*/public/images/*`
- Settings: Cache Level: Standard, Edge Cache TTL: 1 month

#### Dynamic Content
- Pattern: `*/`
- Settings: Cache Level: Bypass, Disable Performance

## PERFORMANCE OPTIMIZATION

### 1. Auto Minify
1. Go to Speed > Optimization
2. Enable Auto Minify for:
   - CSS: On
   - JavaScript: On
   - HTML: On

### 2. Brotli Compression
1. Go to Speed > Optimization
2. Enable Brotli compression

### 3. HTTP/3 (QUIC)
1. Go to Network > HTTP/3 (with QUIC)
2. Enable HTTP/3

### 4. Rocket Loader
1. Go to Speed > Optimization
2. Enable Rocket Loader for JavaScript

## SECURITY CONFIGURATION

### 1. Firewall Rules
1. Go to Security > WAF
2. Enable Cloudflare Managed Ruleset
3. Configure rate limiting rules

### 2. Bot Protection
1. Go to Security > Bot Fight Mode
2. Enable Bot Fight Mode

### 3. DDoS Protection
1. Go to Security > Settings
2. Enable DDoS protection (included in free plan)

## ENVIRONMENT CONFIGURATION

### Update .env File
```env
CDN_ENABLED=true
CDN_URL=https://yourdomain.com
APP_VERSION=1.0.0
```

### Update Application
1. Enable CDN in configuration
2. Update asset URLs to use CDN
3. Test asset delivery

## CACHE INVALIDATION

### Manual Purge
1. Go to Caching > Configuration
2. Click "Purge Everything"
3. Or purge individual files

### API Purge
```php
$cloudflare = new \App\Services\CloudflareService();
$cloudflare->purgeFile('https://yourdomain.com/public/css/style.css');
```

## MONITORING

### Analytics Dashboard
1. Go to Analytics & Logs
2. Monitor:
   - Bandwidth usage
   - Request count
   - Cache hit rate
   - Threats blocked

### Real-time Logs
1. Go to Analytics & Logs > Logpush
2. Enable real-time logs for debugging

## TROUBLESHOOTING

### Assets Not Loading
- Check DNS propagation status
- Verify SSL/TLS mode
- Check proxy status (orange cloud)
- Clear browser cache

### Cache Not Working
- Verify cache rules are active
- Check cache headers
- Test with curl: `curl -I https://yourdomain.com/public/css/style.css`

### SSL Errors
- Check SSL/TLS mode
- Verify origin server SSL certificate
- Check firewall settings

### High Bandwidth Costs
- Review cache hit rate
- Optimize image sizes
- Enable compression
- Review page rules

## PRODUCTION CHECKLIST

- [ ] DNS propagated
- [ ] SSL/TLS configured
- [ ] Page rules set up
- [ ] Cache rules configured
- [ ] Auto minify enabled
- [ ] Brotli compression enabled
- [ ] HTTP/3 enabled
- [ ] Security rules configured
- [ ] CDN enabled in application
- [ ] Asset URLs updated
- [ ] Cache invalidation tested
- [ ] Monitoring configured

## RESOURCES

- [Cloudflare Documentation](https://developers.cloudflare.com/)
- [Cloudflare Community](https://community.cloudflare.com/)
- [Cloudflare Status](https://www.cloudflarestatus.com/)

---

**Version:** 1.0  
**Last Updated:** 2026-07-18
