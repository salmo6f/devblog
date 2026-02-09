<?php
require_once __DIR__ . '/../src/bootstrap.php';
render_header('Features');
?>

<div class="row g-4 align-items-start">
    <div class="col-lg-7">
        <h1 class="h2 fw-bold mb-2">Features built for schools</h1>
        <p class="text-muted mb-4">A modern course registration workflow: terms, sections, capacity, waitlist, and secure portals.</p>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card app-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="soft-icon mb-3"><i class="bi bi-calendar3"></i></div>
                        <div class="fw-bold mb-1">Terms / Semesters</div>
                        <div class="text-muted small">Set an active term and manage offerings per term.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card app-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="soft-icon mb-3"><i class="bi bi-grid-3x3-gap"></i></div>
                        <div class="fw-bold mb-1">Sections</div>
                        <div class="text-muted small">Instructor, schedule, room, and capacity per section.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card app-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="soft-icon mb-3"><i class="bi bi-people"></i></div>
                        <div class="fw-bold mb-1">Admin portal</div>
                        <div class="text-muted small">Manage courses, terms, sections, students, and enrollments.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card app-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="soft-icon mb-3"><i class="bi bi-shield-check"></i></div>
                        <div class="fw-bold mb-1">Security</div>
                        <div class="text-muted small">CSRF protection, prepared statements, safe escaping, hashed passwords.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="hero p-4 shadow-sm">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <div class="fw-bold">Ready to try?</div>
                    <div class="text-white-50 small">Login or create a student account.</div>
                </div>
                <span class="badge text-bg-light text-dark app-pill">School-ready</span>
            </div>
            <div class="d-grid gap-2">
                <a class="btn btn-light btn-lg fw-semibold" href="<?php echo h(url_for('public/login.php')); ?>"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a>
                <a class="btn btn-outline-light btn-lg" href="<?php echo h(url_for('public/register.php')); ?>"><i class="bi bi-person-plus me-2"></i>Create student account</a>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>
