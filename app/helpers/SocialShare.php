<?php
/**
 * MyWisata Application - Social Share Helper
 * 
 * Handles social media sharing functionality.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class SocialShare {
    
    /**
     * Generate share URL for Facebook
     * 
     * @param string $url URL to share
     * @param string $title Share title
     * @return string
     */
    public static function facebook($url, $title = '') {
        $encodedUrl = urlencode($url);
        $encodedTitle = urlencode($title);
        return "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}&t={$encodedTitle}";
    }
    
    /**
     * Generate share URL for Twitter/X
     * 
     * @param string $url URL to share
     * @param string $text Share text
     * @param string $hashtags Comma-separated hashtags
     * @return string
     */
    public static function twitter($url, $text = '', $hashtags = '') {
        $encodedUrl = urlencode($url);
        $encodedText = urlencode($text);
        $encodedHashtags = urlencode($hashtags);
        return "https://twitter.com/intent/tweet?url={$encodedUrl}&text={$encodedText}&hashtags={$encodedHashtags}";
    }
    
    /**
     * Generate share URL for WhatsApp
     * 
     * @param string $url URL to share
     * @param string $text Share text
     * @return string
     */
    public static function whatsapp($url, $text = '') {
        $encodedUrl = urlencode($url);
        $encodedText = urlencode($text . ' ' . $url);
        return "https://api.whatsapp.com/send?text={$encodedText}";
    }
    
    /**
     * Generate share URL for LinkedIn
     * 
     * @param string $url URL to share
     * @param string $title Share title
     * @param string $summary Share summary
     * @return string
     */
    public static function linkedin($url, $title = '', $summary = '') {
        $encodedUrl = urlencode($url);
        $encodedTitle = urlencode($title);
        $encodedSummary = urlencode($summary);
        return "https://www.linkedin.com/shareArticle?mini=true&url={$encodedUrl}&title={$encodedTitle}&summary={$encodedSummary}";
    }
    
    /**
     * Generate share URL for Pinterest
     * 
     * @param string $url URL to share
     * @param string $media Image URL
     * @param string $description Share description
     * @return string
     */
    public static function pinterest($url, $media = '', $description = '') {
        $encodedUrl = urlencode($url);
        $encodedMedia = urlencode($media);
        $encodedDescription = urlencode($description);
        return "https://pinterest.com/pin/create/button/?url={$encodedUrl}&media={$encodedMedia}&description={$encodedDescription}";
    }
    
    /**
     * Generate share URL for Telegram
     * 
     * @param string $url URL to share
     * @param string $text Share text
     * @return string
     */
    public static function telegram($url, $text = '') {
        $encodedUrl = urlencode($url);
        $encodedText = urlencode($text . ' ' . $url);
        return "https://t.me/share/url?url={$encodedUrl}&text={$encodedText}";
    }
    
    /**
     * Generate share URL for Email
     * 
     * @param string $url URL to share
     * @param string $subject Email subject
     * @param string $body Email body
     * @return string
     */
    public static function email($url, $subject = '', $body = '') {
        $encodedSubject = urlencode($subject);
        $encodedBody = urlencode($body . "\n\n" . $url);
        return "mailto:?subject={$encodedSubject}&body={$encodedBody}";
    }
    
    /**
     * Generate all share URLs
     * 
     * @param string $url URL to share
     * @param string $title Share title
     * @param string $description Share description
     * @param string $image Image URL
     * @return array
     */
    public static function generateAll($url, $title = '', $description = '', $image = '') {
        return [
            'facebook' => self::facebook($url, $title),
            'twitter' => self::twitter($url, $title, 'MyWisata'),
            'whatsapp' => self::whatsapp($url, $title),
            'linkedin' => self::linkedin($url, $title, $description),
            'pinterest' => self::pinterest($url, $image, $description),
            'telegram' => self::telegram($url, $title),
            'email' => self::email($url, $title, $description)
        ];
    }
    
    /**
     * Generate Open Graph meta tags
     * 
     * @param string $title Page title
     * @param string $description Page description
     * @param string $image Image URL
     * @param string $url Page URL
     * @return string
     */
    public static function generateMetaTags($title, $description, $image, $url) {
        $tags = [];
        
        // Basic meta
        $tags[] = '<meta name="title" content="' . htmlspecialchars($title) . '">';
        $tags[] = '<meta name="description" content="' . htmlspecialchars($description) . '">';
        
        // Open Graph
        $tags[] = '<meta property="og:title" content="' . htmlspecialchars($title) . '">';
        $tags[] = '<meta property="og:description" content="' . htmlspecialchars($description) . '">';
        $tags[] = '<meta property="og:image" content="' . htmlspecialchars($image) . '">';
        $tags[] = '<meta property="og:url" content="' . htmlspecialchars($url) . '">';
        $tags[] = '<meta property="og:type" content="website">';
        
        // Twitter Card
        $tags[] = '<meta name="twitter:card" content="summary_large_image">';
        $tags[] = '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">';
        $tags[] = '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">';
        $tags[] = '<meta name="twitter:image" content="' . htmlspecialchars($image) . '">';
        
        return implode("\n", $tags);
    }
    
    /**
     * Generate share count (mock implementation)
     * 
     * @param string $url URL to check
     * @param string $platform Platform name
     * @return int
     */
    public static function getShareCount($url, $platform) {
        // This is a mock implementation
        // In production, you would use API calls to get actual share counts
        // For now, return 0 to avoid API rate limits
        return 0;
    }
}
