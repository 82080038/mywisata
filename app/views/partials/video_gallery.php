<?php
// Video/UGC Gallery Partial
// Required variables: $entityType (destination|hotel|restaurant|event), $entityId, $videos (array)
// Optional: $csrf_token
?>
<?php if (!empty($videos)): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-video me-2"></i>Video Gallery
            <span class="badge bg-light text-dark ms-2"><?= count($videos) ?> video</span>
        </h5>
    </div>
    <div class="card-body">
        <!-- Featured Video -->
        <?php $featured = $videos[0]; ?>
        <div class="ratio ratio-16x9 mb-3 rounded overflow-hidden">
            <?php if (!empty($featured['video_url'])): ?>
            <iframe src="<?= View::e($featured['video_url']) ?>" 
                    title="<?= View::e($featured['title']) ?>" 
                    allowfullscreen></iframe>
            <?php elseif (!empty($featured['video_file'])): ?>
            <video controls poster="<?= View::e($featured['thumbnail'] ?? '') ?>">
                <source src="<?= View::asset('uploads/videos/' . $featured['video_file']) ?>" type="video/mp4">
            </video>
            <?php endif; ?>
        </div>
        <h6 class="mb-1"><?= View::e($featured['title']) ?></h6>
        <p class="small text-muted mb-2">
            <i class="fas fa-user me-1"></i><?= View::e($featured['user_name'] ?? 'Anonim') ?>
            <span class="ms-2"><i class="fas fa-eye me-1"></i><?= number_format($featured['view_count']) ?> views</span>
            <?php if (!empty($featured['description'])): ?>
            <br><?= View::e($featured['description']) ?>
            <?php endif; ?>
        </p>

        <!-- Video Thumbnails -->
        <?php if (count($videos) > 1): ?>
        <hr>
        <div class="row g-2">
            <?php foreach (array_slice($videos, 1) as $video): ?>
            <div class="col-md-4 col-6">
                <div class="card video-thumb-card" style="cursor:pointer;" 
                     data-video-url="<?= View::e($video['video_url'] ?? '') ?>" 
                     data-video-file="<?= View::e($video['video_file'] ?? '') ?>"
                     data-video-title="<?= View::e($video['title']) ?>"
                     data-video-desc="<?= View::e($video['description'] ?? '') ?>"
                     data-video-author="<?= View::e($video['user_name'] ?? 'Anonim') ?>"
                     data-video-views="<?= $video['view_count'] ?>"
                     onclick="switchVideo(this)">
                    <?php if (!empty($video['thumbnail'])): ?>
                    <img src="<?= View::e($video['thumbnail']) ?>" class="card-img-top" style="height:80px;object-fit:cover;">
                    <?php else: ?>
                    <div class="bg-dark d-flex align-items-center justify-content-center" style="height:80px;">
                        <i class="fas fa-play-circle text-white fa-2x"></i>
                    </div>
                    <?php endif; ?>
                    <div class="card-body p-2">
                        <p class="small mb-0 fw-bold text-truncate"><?= View::e($video['title']) ?></p>
                        <p class="small text-muted mb-0">
                            <i class="fas fa-eye me-1"></i><?= number_format($video['view_count']) ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Upload Button -->
        <?php if (Session::get('user_id')): ?>
        <hr>
        <button type="button" class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#videoUploadModal">
            <i class="fas fa-plus me-1"></i>Upload Video Anda
        </button>
        <?php else: ?>
        <hr>
        <a href="<?= View::url('auth/login') ?>" class="btn btn-outline-primary btn-sm w-100">
            <i class="fas fa-sign-in-alt me-1"></i>Login untuk Upload Video
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Video Upload Modal -->
<div class="modal fade" id="videoUploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-video me-2"></i>Upload Video</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="videoUploadForm">
                    <input type="hidden" name="csrf_token" value="<?= Middleware::csrfToken() ?>">
                    <input type="hidden" name="entity_type" value="<?= View::e($entityType) ?>">
                    <input type="hidden" name="entity_id" value="<?= $entityId ?>">
                    <div class="mb-3">
                        <label class="form-label">Judul Video</label>
                        <input type="text" class="form-control" name="title" required placeholder="Berikan judul untuk video Anda">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi (opsional)</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Ceritakan tentang video ini"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL Video YouTube</label>
                        <input type="url" class="form-control" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
                        <small class="text-muted">Atau embed URL: https://www.youtube.com/embed/VIDEO_ID</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-upload me-1"></i>Submit Video
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function switchVideo(card) {
    var url = card.dataset.videoUrl;
    var file = card.dataset.videoFile;
    var title = card.dataset.videoTitle;
    var desc = card.dataset.videoDesc;
    var author = card.dataset.videoAuthor;
    var views = card.dataset.videoViews;
    
    var container = card.closest('.card-body');
    var iframeContainer = container.querySelector('.ratio');
    
    if (url) {
        iframeContainer.innerHTML = '<iframe src="' + url + '" title="' + title + '" allowfullscreen></iframe>';
    } else if (file) {
        iframeContainer.innerHTML = '<video controls><source src="' + window.APP_URL + 'public/uploads/videos/' + file + '" type="video/mp4"></video>';
    }
    
    var titleEl = container.querySelector('h6');
    if (titleEl) titleEl.textContent = title;
    
    var descEl = container.querySelector('.small.text-muted');
    if (descEl) {
        descEl.innerHTML = '<i class="fas fa-user me-1"></i>' + author + 
            '<span class="ms-2"><i class="fas fa-eye me-1"></i>' + parseInt(views).toLocaleString() + ' views</span>' +
            (desc ? '<br>' + desc : '');
    }
}

if (document.getElementById('videoUploadForm')) {
    document.getElementById('videoUploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        fetch(window.APP_URL + 'video/upload', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(function() {
                    bootstrap.Modal.getInstance(document.getElementById('videoUploadModal')).hide();
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        })
        .catch(function() {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan koneksi' });
        });
    });
}
</script>
<?php endif; ?>
