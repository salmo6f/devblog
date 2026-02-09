<?php
require_once __DIR__ . '/../src/bootstrap.php';

if (is_logged_in()) {
    $user = current_user();
    $to = ($user && $user['role'] === 'admin') ? url_for('admin/dashboard.php') : url_for('student/dashboard.php');
    header('Location: ' . $to);
    exit;
}

$message = '';

if (isset($_POST['login'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $message = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email address.';
        } else {
            try {
                $stmt = $conn->prepare('SELECT id,name,email,password,role FROM users WHERE email=? LIMIT 1');
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();

                if (!$user) {
                    $message = 'Login failed: invalid email or password.';
                } else {
                    $stored = (string) $user['password'];
                    $ok = false;

                    // Legacy MD5 support (auto-upgrade to password_hash on success).
                    if (preg_match('/^[a-f0-9]{32}$/i', $stored) === 1) {
                        if (md5($password) === $stored) {
                            $ok = true;
                            $newHash = password_hash($password, PASSWORD_DEFAULT);
                            $up = $conn->prepare('UPDATE users SET password=? WHERE id=?');
                            $up->bind_param('si', $newHash, $user['id']);
                            $up->execute();
                        }
                    } else {
                        $ok = password_verify($password, $stored);
                        if ($ok && password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                            $newHash = password_hash($password, PASSWORD_DEFAULT);
                            $up = $conn->prepare('UPDATE users SET password=? WHERE id=?');
                            $up->bind_param('si', $newHash, $user['id']);
                            $up->execute();
                        }
                    }

                    if ($ok) {
                        login_user($user);
                        flash_set('success', 'Welcome back, ' . $user['name'] . '!');
                        $to = ($user['role'] === 'admin') ? url_for('admin/dashboard.php') : url_for('student/dashboard.php');
                        header('Location: ' . $to);
                        exit;
                    }

                    $message = 'Login failed: invalid email or password.';
                }
            } catch (Throwable $e) {
                $message = 'Login failed. Please try again.';
            }
        }
    }
}

render_header('Login');
?>

<div class="row justify-content-center g-3 align-items-stretch">
    <div class="col-lg-10 col-xl-9">
        <div class="row g-3 align-items-stretch">
            <div class="col-lg-6">
                <div class="auth-visual p-4 p-md-5 h-100 shadow-sm">
                    <span class="badge text-bg-light text-dark app-pill mb-3"><i class="bi bi-shield-check me-2"></i>Secure Portal</span>
                    <h1 class="h3 fw-bold mb-2">Welcome back</h1>
                    <p class="muted mb-4">Login to manage terms, sections, and enrollments (Admin) or register for sections (Student).</p>

                    <div class="glass-card p-3">
                        <div class="d-flex gap-3 align-items-center mb-2">
                            <div class="icon-badge"><i class="bi bi-calendar3"></i></div>
                            <div>
                                <div class="fw-semibold">Terms & Sections</div>
                                <div class="text-white-50 small">School-ready registration workflow</div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <div class="icon-badge"><i class="bi bi-people"></i></div>
                            <div>
                                <div class="fw-semibold">Role-based Access</div>
                                <div class="text-white-50 small">Admin and Student portals</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card app-card shadow-sm h-100">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h2 class="h4 fw-bold mb-0">Login</h2>
                            <a class="btn btn-sm btn-outline-secondary" href="<?php echo h(url_for('public/index.php')); ?>"><i class="bi bi-house me-1"></i>Home</a>
                        </div>

                        <?php if ($message) : ?>
                            <div class="alert alert-danger" role="alert"><?php echo h($message); ?></div>
                        <?php endif; ?>

                        <form method="POST" class="vstack gap-3">
                            <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                            <div>
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="Your password" required>
                                </div>
                            </div>
                            <button name="login" class="btn btn-primary btn-lg"><i class="bi bi-box-arrow-in-right me-2"></i>Login</button>
                        </form>

                        <div class="text-muted small mt-3">
                            Don't have an account? <a href="<?php echo h(url_for('public/register.php')); ?>">Register</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>

