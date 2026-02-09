<?php
require_once __DIR__ . '/../src/bootstrap.php';

$message = '';
if (isset($_POST['send'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $body = trim((string) ($_POST['message'] ?? ''));

        if ($name === '' || $email === '' || $body === '') {
            $message = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email address.';
        } else {
            flash_set('success', 'Message received! (Demo: no email is sent.)');
            header('Location: ' . url_for('public/contact.php'));
            exit;
        }
    }
}

render_header('Contact');
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="row g-3">
            <div class="col-lg-5">
                <div class="hero p-4 h-100 shadow-sm">
                    <h1 class="h3 fw-bold mb-2">Contact</h1>
                    <p class="text-white-50 mb-4">Questions about setup, features, or using it in your school? Send a message.</p>
                    <div class="glass-card p-3">
                        <div class="d-flex gap-3 align-items-center mb-2">
                            <div class="icon-badge"><i class="bi bi-envelope"></i></div>
                            <div>
                                <div class="fw-semibold">Email</div>
                                <div class="text-white-50 small">demo@example.com</div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <div class="icon-badge"><i class="bi bi-geo-alt"></i></div>
                            <div>
                                <div class="fw-semibold">Office</div>
                                <div class="text-white-50 small">School IT Department</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card app-card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Send a message</h2>
                        <?php if ($message) : ?>
                            <div class="alert alert-danger" role="alert"><?php echo h($message); ?></div>
                        <?php endif; ?>
                        <form method="POST" class="vstack gap-3">
                            <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                            <div>
                                <label class="form-label">Name</label>
                                <input name="name" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Email</label>
                                <input name="email" type="email" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="5" required></textarea>
                            </div>
                            <button class="btn btn-primary" name="send"><i class="bi bi-send me-2"></i>Send</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>

