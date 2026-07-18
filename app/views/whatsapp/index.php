<?php require_once APP_PATH . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fab fa-whatsapp me-2"></i>
                        WhatsApp Integration
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Register Contact -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Register WhatsApp Contact</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="/whatsapp/register">
                                        <div class="mb-3">
                                            <label class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" name="phone_number" placeholder="+6281234567890" required>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-plus me-2"></i>Register Contact
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Message Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-3">
                                            <div class="display-6 text-primary"><?php echo $data['stats']['total'] ?? 0; ?></div>
                                            <small>Total</small>
                                        </div>
                                        <div class="col-3">
                                            <div class="display-6 text-success"><?php echo $data['stats']['sent'] ?? 0; ?></div>
                                            <small>Sent</small>
                                        </div>
                                        <div class="col-3">
                                            <div class="display-6 text-info"><?php echo $data['stats']['delivered'] ?? 0; ?></div>
                                            <small>Delivered</small>
                                        </div>
                                        <div class="col-3">
                                            <div class="display-6 text-warning"><?php echo $data['stats']['read'] ?? 0; ?></div>
                                            <small>Read</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Send Message -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Send Message</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="/whatsapp/send">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Contact</label>
                                                <select class="form-select" name="contact_id" required>
                                                    <option value="">Select contact</option>
                                                    <?php if (!empty($data['contacts'])): ?>
                                                        <?php foreach ($data['contacts'] as $contact): ?>
                                                            <option value="<?php echo $contact['id']; ?>">
                                                                <?php echo htmlspecialchars($contact['phone_number']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Message Type</label>
                                                <select class="form-select" name="message_type" required>
                                                    <option value="custom">Custom</option>
                                                    <option value="booking_confirmation">Booking Confirmation</option>
                                                    <option value="payment_reminder">Payment Reminder</option>
                                                    <option value="review_request">Review Request</option>
                                                    <option value="promotion">Promotion</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Message Content</label>
                                            <textarea class="form-control" name="content" rows="4" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fab fa-whatsapp me-2"></i>Send Message
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/app/views/layouts/footer.php'; ?>
