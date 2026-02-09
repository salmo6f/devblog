<?php
require_once __DIR__ . '/../src/bootstrap.php';

if (is_logged_in()) {
    $user = current_user();
    $to = ($user && $user['role'] === 'admin') ? url_for('admin/dashboard.php') : url_for('student/dashboard.php');
    header('Location: ' . $to);
    exit;
}

$message = '';

if (isset($_POST['register'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            $message = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email address.';
        } elseif (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters.';
        } else {
            try {
                $stmt = $conn->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
                $stmt->bind_param('s', $email);
                $stmt->execute();

                if ($stmt->get_result()->fetch_assoc()) {
                    $message = 'Email already exists. Please use another email.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $role = 'student';
                    $ins = $conn->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
                    $ins->bind_param('ssss', $name, $email, $hash, $role);
                    $ins->execute();

                    flash_set('success', 'Account created. Please login.');
                    header('Location: ' . url_for('public/login.php'));
                    exit;
                }
            } catch (Throwable $e) {
                $message = 'Registration failed. Please try again.';
            }
        }
    }
}

render_header('Register');
?>

<div class="row justify-content-center g-3 align-items-stretch">
    <div class="col-lg-10 col-xl-9">
        <div class="row g-3 align-items-stretch">
            <div class="col-lg-6 order-lg-2">
                <div class="auth-visual p-4 p-md-5 h-100 shadow-sm">
                    <span class="badge text-bg-light text-dark app-pill mb-3"><i class="bi bi-person-plus me-2"></i>Student Signup</span>
                    <h1 class="h3 fw-bold mb-2">Create your account</h1>
                    <p class="muted mb-4">Browse sections, enroll instantly, and track your enrollment or waitlist status.</p>

                    <div class="glass-card p-3">
                        <div class="d-flex gap-3 align-items-center mb-2">
                            <div class="icon-badge"><i class="bi bi-search"></i></div>
                            <div>
                                <div class="fw-semibold">Find sections</div>
                                <div class="text-white-50 small">Search by course and instructor</div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <div class="icon-badge"><i class="bi bi-check2-square"></i></div>
                            <div>
                                <div class="fw-semibold">Track status</div>
                                <div class="text-white-50 small">Enrolled or waitlisted</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 order-lg-1">
                <div class="card app-card shadow-sm h-100">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h2 class="h4 fw-bold mb-0">Create student account</h2>
                            <a class="btn btn-sm btn-outline-secondary" href="<?php echo h(url_for('public/index.php')); ?>"><i class="bi bi-house me-1"></i>Home</a>
                        </div>

                        <?php if ($message) : ?>
                            <div class="alert alert-danger" role="alert"><?php echo h($message); ?></div>
                        <?php endif; ?>

                        <form method="POST" class="vstack gap-3">
                            <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                            <div>
                                <label class="form-label">Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="name" class="form-control" placeholder="Your name" required>
                                </div>
                            </div>
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
                                    <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                                </div>
                                <div class="form-text">Minimum 6 characters.</div>
                            </div>
                            <button name="register" class="btn btn-primary btn-lg"><i class="bi bi-person-plus me-2"></i>Create account</button>
                        </form>

                        <div class="text-muted small mt-3">
                            Already have an account? <a href="<?php echo h(url_for('public/login.php')); ?>">Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>

