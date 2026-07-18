<?php
/**
 * Social Share Buttons Partial
 * Usage: include with $shareUrl, $shareTitle, $shareText variables
 */
$shareUrl = $shareUrl ?? (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
$shareTitle = $shareTitle ?? '';
$shareText = $shareText ?? $shareTitle;
$encodedUrl = urlencode($shareUrl);
$encodedTitle = urlencode($shareTitle);
$encodedText = urlencode($shareText);
?>
<div class="social-share d-flex gap-2 align-items-center">
    <span class="text-muted small fw-bold me-1">Bagikan:</span>
    
    <!-- WhatsApp -->
    <a href="https://wa.me/?text=<?= $encodedText . '%20' . $encodedUrl ?>" 
       target="_blank" rel="noopener noreferrer"
       class="btn btn-sm btn-success" title="Bagikan ke WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>
    
    <!-- Facebook -->
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl ?>" 
       target="_blank" rel="noopener noreferrer"
       class="btn btn-sm btn-primary" title="Bagikan ke Facebook">
        <i class="fab fa-facebook-f"></i>
    </a>
    
    <!-- Instagram Story -->
    <a href="https://www.instagram.com/" 
       target="_blank" rel="noopener noreferrer"
       class="btn btn-sm btn-gradient-instagram" title="Bagikan ke Instagram"
       style="background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); color: white;">
        <i class="fab fa-instagram"></i>
    </a>
    
    <!-- TikTok -->
    <a href="https://www.tiktok.com/" 
       target="_blank" rel="noopener noreferrer"
       class="btn btn-sm btn-dark" title="Bagikan ke TikTok">
        <i class="fab fa-tiktok"></i>
    </a>
    
    <!-- Twitter/X -->
    <a href="https://twitter.com/intent/tweet?text=<?= $encodedText ?>&url=<?= $encodedUrl ?>" 
       target="_blank" rel="noopener noreferrer"
       class="btn btn-sm btn-dark" title="Bagikan ke X">
        <i class="fab fa-x-twitter"></i>
    </a>
    
    <!-- Telegram -->
    <a href="https://t.me/share/url?url=<?= $encodedUrl ?>&text=<?= $encodedText ?>" 
       target="_blank" rel="noopener noreferrer"
       class="btn btn-sm btn-info" title="Bagikan ke Telegram">
        <i class="fab fa-telegram"></i>
    </a>
    
    <!-- Copy Link -->
    <button type="button" class="btn btn-sm btn-outline-secondary" title="Salin Link" onclick="copyShareLink('<?= htmlspecialchars($shareUrl, ENT_QUOTES) ?>', this)">
        <i class="fas fa-link"></i>
    </button>
</div>

<script>
function copyShareLink(url, btn) {
    navigator.clipboard.writeText(url).then(function() {
        var icon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(function() { btn.innerHTML = icon; }, 2000);
    });
}
</script>
