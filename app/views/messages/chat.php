<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<?php
$userId = Session::get('user_id');
$otherName = $conversation['user1_id'] == $userId ? $conversation['user2_name'] : $conversation['user1_name'];
$otherRole = $conversation['user1_id'] == $userId ? $conversation['user1_role'] : $conversation['user2_role'];
$roleLabels = ['admin' => 'Admin', 'merchant' => 'Penjual Souvenir', 'tour_guide' => 'Tour Guide', 'wisatawan' => 'Wisatawan'];
$roleColors = ['admin' => 'danger', 'merchant' => 'success', 'tour_guide' => 'info', 'wisatawan' => 'secondary'];
$roleIcons = ['admin' => 'fa-cog', 'merchant' => 'fa-store', 'tour_guide' => 'fa-user-tie', 'wisatawan' => 'fa-user'];
$lastMsgId = 0;
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <!-- Chat Header -->
            <div class="card shadow-sm mb-0 rounded-bottom-0">
                <div class="card-body d-flex align-items-center py-3">
                    <a href="<?= View::url('messages') ?>" class="btn btn-sm btn-outline-secondary me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="bg-<?= $roleColors[$otherRole] ?? 'secondary' ?> bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; min-width: 45px;">
                        <i class="fas <?= $roleIcons[$otherRole] ?? 'fa-user' ?> text-<?= $roleColors[$otherRole] ?? 'secondary' ?> fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-0"><?= View::e($otherName) ?></h5>
                        <small class="text-muted">
                            <span class="badge bg-<?= $roleColors[$otherRole] ?? 'secondary' ?>"><?= $roleLabels[$otherRole] ?? 'User' ?></span>
                            <?php if (!empty($conversation['subject'])): ?>
                            — <?= View::e($conversation['subject']) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <span class="badge bg-success" id="onlineStatus"><i class="fas fa-circle me-1" style="font-size: 8px;"></i>Online</span>
                </div>
            </div>

            <!-- Chat Messages -->
            <div class="card shadow-sm border-top-0 rounded-top-0 rounded-bottom-0" style="height: 450px; overflow-y: auto;" id="chatBox">
                <div class="card-body p-3" id="messagesContainer">
                    <?php foreach ($messages as $msg): ?>
                    <?php $lastMsgId = $msg['id']; ?>
                    <div class="d-flex mb-3 <?= $msg['sender_id'] == $userId ? 'justify-content-end' : 'justify-content-start' ?>">
                        <?php if ($msg['sender_id'] != $userId): ?>
                        <div class="bg-<?= $roleColors[$msg['sender_role']] ?? 'secondary' ?> bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; min-width: 35px;">
                            <i class="fas <?= $roleIcons[$msg['sender_role']] ?? 'fa-user' ?> text-<?= $roleColors[$msg['sender_role']] ?? 'secondary' ?>"></i>
                        </div>
                        <?php endif; ?>
                        <div style="max-width: 65%;">
                            <div class="<?= $msg['sender_id'] == $userId ? 'bg-primary text-white' : 'bg-light' ?> rounded p-3 shadow-sm">
                                <p class="mb-0"><?= nl2br(View::e($msg['message'])) ?></p>
                            </div>
                            <small class="text-muted <?= $msg['sender_id'] == $userId ? 'd-block text-end' : '' ?>">
                                <?= date('H:i', strtotime($msg['created_at'])) ?>
                                <?php if ($msg['sender_id'] == $userId && $msg['is_read']): ?>
                                <i class="fas fa-check-double text-primary ms-1"></i>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($messages)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <p>Mulai percakapan dengan <?= View::e($otherName) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chat Input -->
            <div class="card shadow-sm border-top-0 rounded-top-0">
                <div class="card-body p-3">
                    <form id="chatForm" onsubmit="return sendMessage(event)">
                        <div class="input-group">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="conversation_id" value="<?= $conversation['id'] ?>">
                            <input type="text" name="message" id="messageInput" class="form-control" placeholder="Ketik pesan..." autocomplete="off" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>Kirim
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var lastMessageId = <?= $lastMsgId ?>;
var conversationId = <?= $conversation['id'] ?>;
var csrfToken = '<?= $csrf_token ?>';
var chatBox = document.getElementById('chatBox');
var messagesContainer = document.getElementById('messagesContainer');

// Scroll to bottom
chatBox.scrollTop = chatBox.scrollHeight;

function sendMessage(e) {
    e.preventDefault();
    var input = document.getElementById('messageInput');
    var message = input.value.trim();
    if (!message) return false;

    var formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('conversation_id', conversationId);
    formData.append('message', message);

    // Show message immediately
    var div = document.createElement('div');
    div.className = 'd-flex mb-3 justify-content-end';
    div.innerHTML = '<div style="max-width: 65%;">' +
        '<div class="bg-primary text-white rounded p-3 shadow-sm"><p class="mb-0">' + escapeHtml(message) + '</p></div>' +
        '<small class="text-muted d-block text-end">' + new Date().toTimeString().slice(0,5) + '</small>' +
        '</div>';
    messagesContainer.appendChild(div);
    chatBox.scrollTop = chatBox.scrollHeight;

    input.value = '';

    fetch(window.APP_URL + 'messages/send', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            lastMessageId = data.message_id;
        }
    });

    return false;
}

// Poll for new messages every 3 seconds
setInterval(function() {
    fetch(window.APP_URL + 'messages/poll?conversation_id=' + conversationId + '&last_id=' + lastMessageId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success' && data.messages.length > 0) {
            data.messages.forEach(function(msg) {
                lastMessageId = msg.id;
                var div = document.createElement('div');
                div.className = 'd-flex mb-3 ' + (msg.is_me ? 'justify-content-end' : 'justify-content-start');
                var bubbleClass = msg.is_me ? 'bg-primary text-white' : 'bg-light';
                var avatar = msg.is_me ? '' : '<div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; min-width: 35px;"><i class="fas fa-user text-info"></i></div>';
                div.innerHTML = avatar + '<div style="max-width: 65%;"><div class="' + bubbleClass + ' rounded p-3 shadow-sm"><p class="mb-0">' + escapeHtml(msg.message) + '</p></div><small class="text-muted">' + msg.time + '</small></div>';
                messagesContainer.appendChild(div);
            });
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    });
}, 3000);

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
