<?php

/**
 * MyWisata Application - Video Gallery Controller
 *
 * Handles user-generated video content uploads and display.
 *
 * @version 1.0.0
 */

require_once APP_ROOT . '/app/models/VideoGallery.php';

class VideoController extends Controller
{
    /**
     * Upload - Handle video submission (AJAX)
     */
    public function upload()
    {
        $userId = Session::get('user_id');

        if (!$userId) {
            $this->json(['status' => 'error', 'message' => 'Silakan login terlebih dahulu'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }

        // Verify CSRF
        $csrfToken = $this->post('csrf_token');
        if (!Middleware::verifyCsrf($csrfToken)) {
            $this->json(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
        }

        $entityType = $this->post('entity_type');
        $entityId = (int)$this->post('entity_id');
        $title = trim($this->post('title'));
        $description = trim($this->post('description') ?? '');
        $videoUrl = trim($this->post('video_url') ?? '');

        $validTypes = ['destination', 'hotel', 'restaurant', 'event'];
        if (!in_array($entityType, $validTypes)) {
            $this->json(['status' => 'error', 'message' => 'Tipe entitas tidak valid'], 400);
        }

        if (empty($title)) {
            $this->json(['status' => 'error', 'message' => 'Judul video wajib diisi'], 400);
        }

        if (empty($videoUrl) && empty($_FILES['video_file']['name'])) {
            $this->json(['status' => 'error', 'message' => 'URL video atau file video wajib diisi'], 400);
        }

        // Convert YouTube watch URL to embed URL
        if (!empty($videoUrl)) {
            if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
                $videoUrl = 'https://www.youtube.com/embed/' . $matches[1];
            } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
                $videoUrl = 'https://www.youtube.com/embed/' . $matches[1];
            }
        }

        $videoModel = new VideoGallery();

        $data = [
            'entity_id' => $entityId,
            'user_id' => $userId,
            'title' => $title,
            'description' => $description,
            'video_url' => $videoUrl,
            'video_file' => null,
            'thumbnail' => null,
        ];

        // Handle file upload if provided
        if (!empty($_FILES['video_file']['name'])) {
            $uploadDir = APP_ROOT . '/public/uploads/videos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['video_file']['name']);
            $targetPath = $uploadDir . $fileName;

            $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
            if (!in_array($_FILES['video_file']['type'], $allowedTypes)) {
                $this->json(['status' => 'error', 'message' => 'Format video tidak didukung. Gunakan MP4, WebM, atau OGG'], 400);
            }

            if ($_FILES['video_file']['size'] > 100 * 1024 * 1024) {
                $this->json(['status' => 'error', 'message' => 'Ukuran video maksimal 100MB'], 400);
            }

            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetPath)) {
                $data['video_file'] = $fileName;
            }
        }

        // Generate thumbnail from YouTube URL
        if (!empty($videoUrl) && preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
            $data['thumbnail'] = 'https://img.youtube.com/vi/' . $matches[1] . '/0.jpg';
        }

        $videoId = $videoModel->addVideo($entityType, $data);

        if ($videoId) {
            Logger::audit('UPLOAD_VIDEO', $entityType . '_videos', "User {$userId} uploaded video for {$entityType} #{$entityId}", [], $data);
            $this->json(['status' => 'success', 'message' => 'Video berhasil diupload dan menunggu persetujuan admin']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal mengupload video'], 500);
        }
    }
}
