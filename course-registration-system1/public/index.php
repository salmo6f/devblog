<?php
require_once __DIR__ . '/../src/bootstrap.php';

if (is_logged_in()) {
    $user = current_user();
    $to = ($user && $user['role'] === 'admin') ? url_for('admin/dashboard.php') : url_for('student/dashboard.php');
    header('Location: ' . $to);
    exit;
}

render_header('Welcome');
?>

<section class="hero shadow-sm">
    <div class="p-4 p-md-5">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <span class="badge text-bg-light text-dark app-pill mb-3"><i class="bi bi-stars me-2"></i>Modern • School-ready</span>
                <h1 class="display-6 fw-bold mb-3">A complete course registration website for your school</h1>
                <p class="lead text-white-50 mb-4">
                    Manage terms, sections, capacity, waitlists, and enrollments with secure Admin & Student portals.
                </p>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?php echo h(url_for('public/login.php')); ?>" class="btn btn-light btn-lg fw-semibold"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a>
                    <a href="<?php echo h(url_for('public/register.php')); ?>" class="btn btn-outline-light btn-lg"><i class="bi bi-person-plus me-2"></i>Student signup</a>
                </div>

                <div class="row g-2 mt-4">
                    <div class="col-md-4">
                        <div class="glass-card p-3">
                            <div class="fw-semibold">Terms</div>
                            <div class="text-white-50 small">Semesters & active term</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card p-3">
                            <div class="fw-semibold">Sections</div>
                            <div class="text-white-50 small">Schedule & capacity</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card p-3">
                            <div class="fw-semibold">Waitlist</div>
                            <div class="text-white-50 small">Full classes handled</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card app-card shadow-sm border-0 overflow-hidden">
                    <img
                        src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80"
                        class="w-100"
                        alt="Students studying"
                        loading="lazy"
                        referrerpolicy="no-referrer"
                    >
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold">How it works</div>
                                <div class="text-muted small">3 simple steps</div>
                            </div>
                            <span class="badge text-bg-primary app-pill">Fast setup</span>
                        </div>
                        <ol class="mb-0 mt-3 text-muted">
                            <li>Admin creates terms and sections.</li>
                            <li>Students browse sections and enroll.</li>
                            <li>Admin manages enrollments & waitlist.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mt-4">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="step-card p-3 h-100 shadow-sm">
                <div class="fw-bold mb-1"><i class="bi bi-speedometer2 me-2 text-primary"></i>Admin portal</div>
                <div class="text-muted small mb-3">Create terms, sections, and manage enrollments.</div>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo h(url_for('public/login.php')); ?>">Login</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="step-card p-3 h-100 shadow-sm">
                <div class="fw-bold mb-1"><i class="bi bi-search me-2 text-primary"></i>Student portal</div>
                <div class="text-muted small mb-3">Search sections, enroll, and track your status.</div>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo h(url_for('public/register.php')); ?>">Create account</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="step-card p-3 h-100 shadow-sm">
                <div class="fw-bold mb-1"><i class="bi bi-shield-check me-2 text-primary"></i>Secure by default</div>
                <div class="text-muted small mb-3">CSRF protection, prepared statements, safe output escaping.</div>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo h(url_for('public/features.php')); ?>">See features</a>
            </div>
        </div>
    </div>
</section>

<section class="mt-4">
    <div class="card app-card shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <h2 class="h3 fw-bold mb-2">Everything your school needs</h2>
                    <p class="text-muted mb-4">Modern schema: departments, terms, sections, and enrollments with waitlist support.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex gap-3">
                                <div class="icon-badge text-white" style="background: rgba(36,87,255,.20); border-color: rgba(36,87,255,.25);"><i class="bi bi-calendar3 text-white"></i></div>
                                <div>
                                    <div class="fw-semibold">Active term</div>
                                    <div class="text-muted small">Students register for the correct semester.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3">
                                <div class="icon-badge text-white" style="background: rgba(36,87,255,.20); border-color: rgba(36,87,255,.25);"><i class="bi bi-grid-3x3-gap text-white"></i></div>
                                <div>
                                    <div class="fw-semibold">Capacity & seats</div>
                                    <div class="text-muted small">Auto waitlist when a section is full.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3">
                                <div class="icon-badge text-white" style="background: rgba(36,87,255,.20); border-color: rgba(36,87,255,.25);"><i class="bi bi-people text-white"></i></div>
                                <div>
                                    <div class="fw-semibold">Admin tools</div>
                                    <div class="text-muted small">Manage enrollments per section.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3">
                                <div class="icon-badge text-white" style="background: rgba(36,87,255,.20); border-color: rgba(36,87,255,.25);"><i class="bi bi-shield-lock text-white"></i></div>
                                <div>
                                    <div class="fw-semibold">Secure accounts</div>
                                    <div class="text-muted small">Hashed passwords and session hardening.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="p-4 rounded-4 border bg-light">
                        <div class="fw-bold mb-2">Quick links</div>
                        <div class="d-grid gap-2">
                            <a class="btn btn-primary" href="<?php echo h(url_for('public/login.php')); ?>"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a>
                            <a class="btn btn-outline-primary" href="<?php echo h(url_for('public/register.php')); ?>"><i class="bi bi-person-plus me-2"></i>Register</a>
                            <a class="btn btn-outline-secondary" href="<?php echo h(url_for('public/features.php')); ?>"><i class="bi bi-stars me-2"></i>Features</a>
                        </div>
                        <div class="text-muted small mt-3">Tip: Import DB and enable modern mode using `scripts/setup-db.ps1 -Modern`.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php render_footer(); ?>
