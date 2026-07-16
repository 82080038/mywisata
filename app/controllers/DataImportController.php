<?php
/**
 * MyWisata Application - Data Import Controller
 * 
 * Handles importing location-based data from external sources.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class DataImportController extends Controller {
    
    private $locationData;
    
    public function __construct() {
        parent::__construct();
        $this->locationData = new LocationData();
    }
    
    /**
     * Show data import dashboard
     */
    public function index() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        $sources = $this->locationData->getAvailableSources();
        $stats = $this->locationData->getImportStats();
        
        $data = [
            'title' => 'Import Data Destinasi - MyWisata',
            'sources' => $sources,
            'stats' => $stats,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('data_import/index', $data);
    }
    
    /**
     * Import data from source (AJAX)
     */
    public function import() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        if (!$this->isAjax()) {
            $this->redirect('data-import');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $source = $this->post('source');
        $filters = [
            'province' => $this->post('province'),
            'category' => $this->post('category'),
            'query' => $this->post('query'),
            'location' => $this->post('location'),
            'radius' => $this->post('radius'),
            'bbox' => $this->post('bbox'),
            'endpoint' => $this->post('endpoint')
        ];
        
        $results = $this->locationData->importDestinations($source, $filters);
        
        Logger::audit('IMPORT_DESTINATIONS', 'destinations', "Imported destinations from {$source}", [], [
            'source' => $source,
            'results' => $results
        ]);
        
        $this->json([
            'status' => 'success',
            'message' => 'Import data selesai',
            'data' => $results
        ]);
    }
    
    /**
     * Preview data from source before import
     */
    public function preview() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        if (!$this->isAjax()) {
            $this->redirect('data-import');
        }
        
        $source = $this->post('source');
        $filters = [
            'province' => $this->post('province'),
            'category' => $this->post('category'),
            'query' => $this->post('query'),
            'limit' => 10
        ];
        
        $data = [];
        
        switch ($source) {
            case 'sisparnas':
                $data = $this->locationData->fetchSisparnasData(
                    $filters['province'] ?? null,
                    $filters['category'] ?? null
                );
                break;
                
            case 'google':
                $data = $this->locationData->searchGooglePlaces(
                    $filters['query'] ?? 'wisata Indonesia',
                    null,
                    50000
                );
                break;
                
            case 'osm':
                $data = $this->locationData->searchOpenStreetMap(
                    $filters['query'] ?? 'wisata'
                );
                break;
        }
        
        $this->json([
            'status' => 'success',
            'data' => array_slice($data, 0, 10)
        ]);
    }
    
    /**
     * Get import statistics (AJAX)
     */
    public function getStats() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        if (!$this->isAjax()) {
            $this->redirect('data-import');
        }
        
        $stats = $this->locationData->getImportStats();
        
        $this->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
    
    /**
     * Clear cache
     */
    public function clearCache() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        if (!$this->isAjax()) {
            $this->redirect('data-import');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $cacheDir = ROOT_PATH . '/storage/cache/location_data';
        $files = glob($cacheDir . '/*.json');
        $deleted = 0;
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $deleted++;
            }
        }
        
        Logger::audit('CLEAR_CACHE', 'location_data_cache', "Cleared {$deleted} cache files");
        
        $this->json([
            'status' => 'success',
            'message' => "Cache dibersihkan ({$deleted} file)",
            'data' => ['deleted' => $deleted]
        ]);
    }
}
