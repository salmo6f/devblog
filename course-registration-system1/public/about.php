<?php
require_once __DIR__ . '/../src/bootstrap.php';
render_header('About');
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card app-card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <h1 class="h2 fw-bold mb-3">About this system</h1>
                <p class="text-muted mb-4">
                    This project is a modern course registration system built with core PHP and MySQL.
                    It is designed for school workflows: academic terms, course sections, capacity, waitlist, and role-based access.
                </p>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="step-card p-3 h-100">
                            <div class="fw-bold mb-1"><i class="bi bi-mortarboard me-2"></i>Students</div>
                            <div class="text-muted small">Browse sections, enroll, and track waitlist/enrollment status.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="step-card p-3 h-100">
                            <div class="fw-bold mb-1"><i class="bi bi-kanban me-2"></i>Administration</div>
                            <div class="text-muted small">Create terms, sections, and manage enrollments in a clean interface.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="step-card p-3 h-100">
                            <div class="fw-bold mb-1"><i class="bi bi-shield-lock me-2"></i>Security</div>
                            <div class="text-muted small">Secure sessions, CSRF protection, and password hashing.</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-primary" href="<?php echo h(url_for('public/features.php')); ?>">See features</a>
                    <a class="btn btn-outline-primary" href="<?php echo h(url_for('public/contact.php')); ?>">Contact</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>

