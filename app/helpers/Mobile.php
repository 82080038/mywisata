<?php
/**
 * MyWisata Application - Mobile Helper
 * 
 * Handles mobile detection and mobile-specific optimizations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Mobile {
    
    private $userAgent;
    private $isMobile;
    private $isTablet;
    private $isDesktop;
    
    public function __construct() {
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->detectDevice();
    }
    
    /**
     * Detect device type
     */
    private function detectDevice() {
        $mobileAgents = [
            'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 
            'Windows Phone', 'webOS', 'Opera Mini', 'IEMobile', 'Mobile'
        ];
        
        $tabletAgents = ['iPad', 'Android Tablet', 'Kindle', 'Samsung Tablet'];
        
        $this->isMobile = false;
        $this->isTablet = false;
        $this->isDesktop = true;
        
        foreach ($mobileAgents as $agent) {
            if (stripos($this->userAgent, $agent) !== false) {
                $this->isMobile = true;
                $this->isDesktop = false;
                break;
            }
        }
        
        foreach ($tabletAgents as $agent) {
            if (stripos($this->userAgent, $agent) !== false) {
                $this->isTablet = true;
                $this->isMobile = false;
                break;
            }
        }
        
        // Check for iPadOS 13+ which doesn't have iPad in user agent
        if (stripos($this->userAgent, 'Macintosh') !== false && 
            stripos($this->userAgent, 'iPad') === false &&
            isset($_SERVER['HTTP_USER_AGENT']) && 
            (strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false || 
             (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Macintosh') !== false && 
              (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false)))) {
            // Additional check for touch capability
            if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Touch') !== false) {
                $this->isTablet = true;
                $this->isMobile = false;
                $this->isDesktop = false;
            }
        }
    }
    
    /**
     * Check if device is mobile
     * 
     * @return bool
     */
    public function isMobile() {
        return $this->isMobile;
    }
    
    /**
     * Check if device is tablet
     * 
     * @return bool
     */
    public function isTablet() {
        return $this->isTablet;
    }
    
    /**
     * Check if device is desktop
     * 
     * @return bool
     */
    public function isDesktop() {
        return $this->isDesktop;
    }
    
    /**
     * Check if device is touch-enabled
     * 
     * @return bool
     */
    public function isTouch() {
        return $this->isMobile || $this->isTablet;
    }
    
    /**
     * Get device type
     * 
     * @return string
     */
    public function getDeviceType() {
        if ($this->isMobile) {
            return 'mobile';
        } elseif ($this->isTablet) {
            return 'tablet';
        }
        return 'desktop';
    }
    
    /**
     * Get mobile-specific view path
     * 
     * @param string $view Original view path
     * @return string
     */
    public function getMobileView($view) {
        if ($this->isMobile) {
            $mobileView = str_replace('.php', '.mobile.php', $view);
            if (file_exists(ROOT_PATH . '/app/views/' . $mobileView)) {
                return $mobileView;
            }
        }
        return $view;
    }
    
    /**
     * Get device-specific CSS classes
     * 
     * @return string
     */
    public function getDeviceClasses() {
        $classes = ['device-' . $this->getDeviceType()];
        
        if ($this->isTouch()) {
            $classes[] = 'touch-enabled';
        }
        
        return implode(' ', $classes);
    }
    
    /**
     * Get viewport meta tag
     * 
     * @return string
     */
    public function getViewportMeta() {
        $viewport = 'width=device-width, initial-scale=1.0, maximum-scale=5.0';
        
        if ($this->isMobile) {
            $viewport = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover';
        }
        
        return '<meta name="viewport" content="' . $viewport . '">';
    }
    
    /**
     * Get apple-touch-icon link
     * 
     * @return string
     */
    public function getAppleTouchIcon() {
        return '<link rel="apple-touch-icon" href="/assets/icons/icon-192x192.png">';
    }
    
    /**
     * Get mobile-specific meta tags
     * 
     * @return string
     */
    public function getMobileMetaTags() {
        $tags = [];
        
        if ($this->isMobile || $this->isTablet) {
            $tags[] = '<meta name="apple-mobile-web-app-capable" content="yes">';
            $tags[] = '<meta name="apple-mobile-web-app-status-bar-style" content="default">';
            $tags[] = '<meta name="apple-mobile-web-app-title" content="MyWisata">';
            $tags[] = '<meta name="mobile-web-app-capable" content="yes">';
            $tags[] = '<meta name="theme-color" content="#3b82f6">';
            $tags[] = '<meta name="application-name" content="MyWisata">';
        }
        
        return implode("\n", $tags);
    }
    
    /**
     * Check if should use bottom navigation
     * 
     * @return bool
     */
    public function useBottomNav() {
        return $this->isMobile;
    }
    
    /**
     * Get bottom navigation items
     * 
     * @return array
     */
    public function getBottomNavItems() {
        return [
            [
                'name' => 'Home',
                'url' => '/',
                'icon' => 'home',
                'active' => $_SERVER['REQUEST_URI'] === '/'
            ],
            [
                'name' => 'Search',
                'url' => '/search',
                'icon' => 'search',
                'active' => strpos($_SERVER['REQUEST_URI'], '/search') === 0
            ],
            [
                'name' => 'Bookings',
                'url' => '/booking',
                'icon' => 'calendar',
                'active' => strpos($_SERVER['REQUEST_URI'], '/booking') === 0
            ],
            [
                'name' => 'Favorites',
                'url' => '/favorites',
                'icon' => 'heart',
                'active' => strpos($_SERVER['REQUEST_URI'], '/favorites') === 0
            ],
            [
                'name' => 'Profile',
                'url' => '/profile',
                'icon' => 'user',
                'active' => strpos($_SERVER['REQUEST_URI'], '/profile') === 0
            ]
        ];
    }
    
    /**
     * Get swipe gesture configuration
     * 
     * @return array
     */
    public function getSwipeConfig() {
        return [
            'enabled' => $this->isMobile,
            'threshold' => 50,
            'restraint' => 100,
            'allowedTime' => 300
        ];
    }
    
    /**
     * Get mobile-specific JavaScript config
     * 
     * @return array
     */
    public function getMobileJSConfig() {
        return [
            'isMobile' => $this->isMobile,
            'isTablet' => $this->isTablet,
            'deviceType' => $this->getDeviceType(),
            'useBottomNav' => $this->useBottomNav(),
            'swipeConfig' => $this->getSwipeConfig(),
            'touchEnabled' => $this->isTouch()
        ];
    }
}
