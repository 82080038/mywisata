<?php

/**
 * MyWisata Application - Desa Wisata Controller
 *
 * Handles Desa Wisata (Village Tourism) listings and details.
 *
 * @version 1.0.0
 * @since 2026-07-19
 */
class DesaWisataController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Index - List all desa wisata destinations
     */
    public function index()
    {
        $db = Database::getInstance();
        $city = $this->get('city');
        $search = $this->get('search');

        $where = ["d.is_active = 1 AND d.is_village_tourism = 1"];
        $params = [];

        if (!empty($city)) {
            $where[] = "(d.city LIKE :city OR d.village_name LIKE :city2)";
            $params['city'] = "%{$city}%";
            $params['city2'] = "%{$city}%";
        }

        if (!empty($search)) {
            $where[] = "(d.name LIKE :search OR d.description LIKE :search2 OR d.village_name LIKE :search3)";
            $params['search'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
        }

        $sql = "SELECT d.*, c.name as category_name, c.slug as category_slug,
                COALESCE(AVG(r.rating), 0) as rating_avg,
                COUNT(r.id) as review_count
                FROM destinations d
                LEFT JOIN destination_categories c ON d.category_id = c.id
                LEFT JOIN reviews r ON r.reviewable_type = 'destination' AND r.reviewable_id = d.id AND r.is_published = 1
                WHERE " . implode(' AND ', $where) . "
                GROUP BY d.id
                ORDER BY d.eco_score DESC, d.rating_avg DESC";

        $destinations = $db->query($sql, $params)->fetchAll();

        // Get cities for filter
        $cities = $db->query("SELECT DISTINCT city FROM destinations WHERE is_active = 1 AND is_village_tourism = 1 AND city IS NOT NULL AND city != '' ORDER BY city")->fetchAll();

        $data = [
            'title' => 'Desa Wisata - MyWisata',
            'destinations' => $destinations,
            'cities' => $cities,
            'filters' => ['city' => $city, 'search' => $search],
        ];

        $this->view('desawisata/index', $data);
    }

    /**
     * Detail - Show desa wisata details
     */
    public function detail($id = null)
    {
        if (!$id) {
            $id = $this->get('id');
        }

        $db = Database::getInstance();

        $sql = "SELECT d.*, c.name as category_name,
                COALESCE(AVG(r.rating), 0) as rating_avg,
                COUNT(r.id) as review_count
                FROM destinations d
                LEFT JOIN destination_categories c ON d.category_id = c.id
                LEFT JOIN reviews r ON r.reviewable_type = 'destination' AND r.reviewable_id = d.id AND r.is_published = 1
                WHERE d.id = :id AND d.is_village_tourism = 1
                GROUP BY d.id";

        $destination = $db->query($sql, ['id' => $id])->fetch();

        if (!$destination) {
            Session::flash('error', 'Desa Wisata tidak ditemukan');
            $this->redirect('desawisata');
        }

        // Get reviews
        $reviews = $db->query(
            "SELECT r.*, u.name as user_name, u.avatar as user_avatar FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.reviewable_type = 'destination' AND r.reviewable_id = :id AND r.is_published = 1 ORDER BY r.created_at DESC LIMIT 10",
            ['id' => $id]
        )->fetchAll();

        // Get images
        $images = $db->query("SELECT * FROM destination_images WHERE destination_id = :id ORDER BY is_primary DESC, sort_order ASC", ['id' => $id])->fetchAll();

        // Get nearby UMKM products
        $umkmProducts = $db->query(
            "SELECT p.* FROM products p WHERE p.is_active = 1 AND p.is_approved = 1 ORDER BY p.created_at DESC LIMIT 6"
        )->fetchAll();

        // Get facilities
        $facilityModel = new Facility();
        $facilities = $facilityModel->getEntityFacilitiesGrouped('destination', $id);

        // Get videos
        $videoModel = new VideoGallery();
        $videos = $videoModel->getVideos('destination', $id);

        $data = [
            'title' => $destination['name'] . ' - Desa Wisata - MyWisata',
            'destination' => $destination,
            'reviews' => $reviews,
            'images' => $images,
            'umkmProducts' => $umkmProducts,
            'facilities' => $facilities,
            'facilityModel' => $facilityModel,
            'videos' => $videos,
            'entityType' => 'destination',
            'entityId' => $id,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('desawisata/detail', $data);
    }
}
