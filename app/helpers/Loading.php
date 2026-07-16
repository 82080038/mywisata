<?php
/**
 * MyWisata Application - Loading Helper
 * 
 * Handles loading indicators and progress feedback for user operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Loading {
    
    /**
     * Generate loading spinner HTML
     * 
     * @param string $size Size (small, medium, large)
     * @param string $color Color
     * @param string $message Optional loading message
     * @return string
     */
    public static function spinner($size = 'medium', $color = '#3b82f6', $message = null) {
        $sizes = [
            'small' => '20px',
            'medium' => '40px',
            'large' => '60px'
        ];
        
        $spinnerSize = $sizes[$size] ?? $sizes['medium'];
        
        $html = '<div class="loading-spinner" style="text-align: center;">';
        $html .= '<div class="spinner" style="';
        $html .= 'width: ' . $spinnerSize . '; ';
        $html .= 'height: ' . $spinnerSize . '; ';
        $html .= 'border: 3px solid #f3f3f3; ';
        $html .= 'border-top: 3px solid ' . $color . '; ';
        $html .= 'border-radius: 50%; ';
        $html .= 'animation: spin 1s linear infinite; ';
        $html .= 'margin: 0 auto;"></div>';
        
        if ($message) {
            $html .= '<p class="loading-message" style="margin-top: 10px; color: #666; font-size: 14px;">' . htmlspecialchars($message) . '</p>';
        }
        
        $html .= '</div>';
        
        $html .= '<style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>';
        
        return $html;
    }
    
    /**
     * Generate progress bar HTML
     * 
     * @param int $progress Progress percentage (0-100)
     * @param string $message Optional message
     * @param bool $showPercentage Show percentage
     * @return string
     */
    public static function progressBar($progress = 0, $message = null, $showPercentage = true) {
        $progress = min(100, max(0, $progress));
        
        $html = '<div class="progress-container" style="width: 100%;">';
        
        if ($message) {
            $html .= '<div class="progress-message" style="margin-bottom: 5px; font-size: 14px; color: #666;">';
            $html .= htmlspecialchars($message);
            if ($showPercentage) {
                $html .= ' <span class="progress-percentage">(' . $progress . '%)</span>';
            }
            $html .= '</div>';
        }
        
        $html .= '<div class="progress-bar" style="';
        $html .= 'width: 100%; ';
        $html .= 'height: 8px; ';
        $html .= 'background-color: #e0e0e0; ';
        $html .= 'border-radius: 4px; ';
        $html .= 'overflow: hidden;">';
        
        $html .= '<div class="progress-fill" style="';
        $html .= 'width: ' . $progress . '%; ';
        $html .= 'height: 100%; ';
        $html .= 'background: linear-gradient(90deg, #3b82f6, #8b5cf6); ';
        $html .= 'transition: width 0.3s ease; ';
        $html .= 'border-radius: 4px;"></div>';
        
        $html .= '</div></div>';
        
        return $html;
    }
    
    /**
     * Generate skeleton loader HTML
     * 
     * @param string $type Type (text, avatar, card, list)
     * @param int $count Number of items
     * @return string
     */
    public static function skeleton($type = 'text', $count = 1) {
        $html = '<div class="skeleton-loader">';
        
        for ($i = 0; $i < $count; $i++) {
            switch ($type) {
                case 'avatar':
                    $html .= '<div class="skeleton-avatar" style="';
                    $html .= 'width: 50px; height: 50px; ';
                    $html .= 'border-radius: 50%; ';
                    $html .= 'background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); ';
                    $html .= 'background-size: 200% 100%; ';
                    $html .= 'animation: shimmer 1.5s infinite; ';
                    $html .= 'margin-bottom: 10px;"></div>';
                    break;
                    
                case 'card':
                    $html .= '<div class="skeleton-card" style="';
                    $html .= 'width: 100%; height: 150px; ';
                    $html .= 'border-radius: 8px; ';
                    $html .= 'background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); ';
                    $html .= 'background-size: 200% 100%; ';
                    $html .= 'animation: shimmer 1.5s infinite; ';
                    $html .= 'margin-bottom: 15px;"></div>';
                    break;
                    
                case 'list':
                    $html .= '<div class="skeleton-list-item" style="';
                    $html .= 'display: flex; align-items: center; ';
                    $html .= 'margin-bottom: 15px;">';
                    $html .= '<div class="skeleton-avatar" style="';
                    $html .= 'width: 40px; height: 40px; ';
                    $html .= 'border-radius: 50%; ';
                    $html .= 'background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); ';
                    $html .= 'background-size: 200% 100%; ';
                    $html .= 'animation: shimmer 1.5s infinite; ';
                    $html .= 'margin-right: 15px;"></div>';
                    $html .= '<div class="skeleton-text" style="';
                    $html .= 'flex: 1; height: 20px; ';
                    $html .= 'border-radius: 4px; ';
                    $html .= 'background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); ';
                    $html .= 'background-size: 200% 100%; ';
                    $html .= 'animation: shimmer 1.5s infinite;"></div>';
                    $html .= '</div>';
                    break;
                    
                case 'text':
                default:
                    $html .= '<div class="skeleton-text" style="';
                    $html .= 'width: 100%; height: 20px; ';
                    $html .= 'border-radius: 4px; ';
                    $html .= 'background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); ';
                    $html .= 'background-size: 200% 100%; ';
                    $html .= 'animation: shimmer 1.5s infinite; ';
                    $html .= 'margin-bottom: 10px;"></div>';
                    break;
            }
        }
        
        $html .= '</div>';
        
        $html .= '<style>
            @keyframes shimmer {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }
        </style>';
        
        return $html;
    }
    
    /**
     * Generate toast notification HTML
     * 
     * @param string $message Message
     * @param string $type Type (success, error, warning, info)
     * @param int $duration Duration in milliseconds
     * @return string
     */
    public static function toast($message, $type = 'info', $duration = 3000) {
        $icons = [
            'success' => '✓',
            'error' => '✕',
            'warning' => '⚠',
            'info' => 'ℹ'
        ];
        
        $colors = [
            'success' => '#10b981',
            'error' => '#ef4444',
            'warning' => '#f59e0b',
            'info' => '#3b82f6'
        ];
        
        $icon = $icons[$type] ?? $icons['info'];
        $color = $colors[$type] ?? $colors['info'];
        
        $html = '<div class="toast-notification" style="';
        $html .= 'position: fixed; ';
        $html .= 'top: 20px; ';
        $html .= 'right: 20px; ';
        $html .= 'background: white; ';
        $html .= 'padding: 15px 20px; ';
        $html .= 'border-radius: 8px; ';
        $html .= 'box-shadow: 0 4px 12px rgba(0,0,0,0.15); ';
        $html .= 'display: flex; ';
        $html .= 'align-items: center; ';
        $html .= 'gap: 12px; ';
        $html .= 'z-index: 9999; ';
        $html .= 'animation: slideIn 0.3s ease; ';
        $html .= 'border-left: 4px solid ' . $color . ';">';
        
        $html .= '<div class="toast-icon" style="';
        $html .= 'width: 24px; height: 24px; ';
        $html .= 'background: ' . $color . '; ';
        $html .= 'color: white; ';
        $html .= 'border-radius: 50%; ';
        $html .= 'display: flex; ';
        $html .= 'align-items: center; ';
        $html .= 'justify-content: center; ';
        $html .= 'font-weight: bold;">' . $icon . '</div>';
        
        $html .= '<div class="toast-message" style="font-size: 14px; color: #333;">';
        $html .= htmlspecialchars($message);
        $html .= '</div>';
        
        $html .= '</div>';
        
        $html .= '<style>
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        </style>';
        
        $html .= '<script>
            setTimeout(function() {
                document.querySelector(".toast-notification").style.animation = "slideOut 0.3s ease";
                setTimeout(function() {
                    document.querySelector(".toast-notification").remove();
                }, 300);
            }, ' . $duration . ');
            
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        </script>';
        
        return $html;
    }
    
    /**
     * Generate inline loading overlay
     * 
     * @param string $message Message
     * @param bool $blurBackground Blur background
     * @return string
     */
    public static function overlay($message = 'Memuat...', $blurBackground = true) {
        $html = '<div class="loading-overlay" style="';
        $html .= 'position: fixed; ';
        $html .= 'top: 0; left: 0; ';
        $html .= 'width: 100%; height: 100%; ';
        $html .= 'background: rgba(255, 255, 255, 0.9); ';
        $html .= 'display: flex; ';
        $html .= 'flex-direction: column; ';
        $html .= 'align-items: center; ';
        $html .= 'justify-content: center; ';
        $html .= 'z-index: 9998; ';
        if ($blurBackground) {
            $html .= 'backdrop-filter: blur(5px); ';
        }
        $html .= '">';
        
        $html .= self::spinner('large', '#3b82f6', $message);
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate step progress indicator
     * 
     * @param array $steps Array of steps
     * @param int $currentStep Current step index
     * @return string
     */
    public static function stepProgress($steps, $currentStep) {
        $html = '<div class="step-progress" style="display: flex; align-items: center; justify-content: space-between; margin: 20px 0;">';
        
        foreach ($steps as $index => $step) {
            $isCompleted = $index < $currentStep;
            $isCurrent = $index === $currentStep;
            $isPending = $index > $currentStep;
            
            $html .= '<div class="step-item" style="';
            $html .= 'display: flex; ';
            $html .= 'flex-direction: column; ';
            $html .= 'align-items: center; ';
            $html .= 'flex: 1; ';
            $html .= 'position: relative;">';
            
            // Step circle
            $html .= '<div class="step-circle" style="';
            $html .= 'width: 40px; height: 40px; ';
            $html .= 'border-radius: 50%; ';
            $html .= 'display: flex; ';
            $html .= 'align-items: center; ';
            $html .= 'justify-content: center; ';
            $html .= 'font-weight: bold; ';
            $html .= 'margin-bottom: 8px; ';
            
            if ($isCompleted) {
                $html .= 'background: #10b981; color: white;';
            } elseif ($isCurrent) {
                $html .= 'background: #3b82f6; color: white;';
            } else {
                $html .= 'background: #e0e0e0; color: #999;';
            }
            $html .= '">';
            
            if ($isCompleted) {
                $html .= '✓';
            } else {
                $html .= ($index + 1);
            }
            
            $html .= '</div>';
            
            // Step label
            $html .= '<div class="step-label" style="';
            $html .= 'font-size: 12px; ';
            $html .= 'text-align: center; ';
            if ($isCurrent) {
                $html .= 'color: #3b82f6; font-weight: 600;';
            } elseif ($isCompleted) {
                $html .= 'color: #10b981;';
            } else {
                $html .= 'color: #999;';
            }
            $html .= '">';
            $html .= htmlspecialchars($step);
            $html .= '</div>';
            
            // Connector line (except for last step)
            if ($index < count($steps) - 1) {
                $html .= '<div class="step-connector" style="';
                $html .= 'position: absolute; ';
                $html .= 'top: 20px; ';
                $html .= 'left: 50%; ';
                $html .= 'width: 100%; ';
                $html .= 'height: 2px; ';
                $html .= 'background: ' . ($isCompleted ? '#10b981' : '#e0e0e0') . ';';
                $html .= '"></div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate loading button state
     * 
     * @param string $text Button text
     * @param bool $loading Loading state
     * @return string
     */
    public static function buttonState($text, $loading = false) {
        if ($loading) {
            return '<span class="btn-loading"><span class="btn-spinner"></span> Memuat...</span>';
        }
        return $text;
    }
}
