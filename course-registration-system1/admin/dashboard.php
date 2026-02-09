<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('admin');

$courseCount = 0;
$studentCount = 0;
$registrationCount = 0;

try {
    $courseCount = (int) $conn->query('SELECT COUNT(*) AS c FROM courses')->fetch_assoc()['c'];
    $role = 'student';
    $st = $conn->prepare('SELECT COUNT(*) AS c FROM users WHERE role=?');
    $st->bind_param('s', $role);
    $st->execute();
    $studentCount = (int) $st->get_result()->fetch_assoc()['c'];
    $registrationCount = (int) $conn->query('SELECT COUNT(*) AS c FROM student_courses')->fetch_assoc()['c'];
} catch (Throwable $e) {
    // keep zeros; flash is optional
}
?>

<?php render_header('Admin Dashboard'); ?>
<?php render_portal_nav('dashboard'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Admin Dashboard</h1>
        <div class="text-muted">Manage courses and registrations.</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo h(url_for('admin/add-course.php')); ?>" class="btn btn-primary">Add Course</a>
        <a href="<?php echo h(url_for('admin/courses.php')); ?>" class="btn btn-outline-primary">Courses</a>
        <a href="<?php echo h(url_for('admin/sections.php')); ?>" class="btn btn-outline-primary">Sections</a>
        <a href="<?php echo h(url_for('admin/terms.php')); ?>" class="btn btn-outline-primary">Terms</a>
        <a href="<?php echo h(url_for('admin/students.php')); ?>" class="btn btn-outline-primary">Students</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card app-card shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Courses</div>
                <div class="display-6 fw-bold"><?php echo h($courseCount); ?></div>
                <a class="link-primary small" href="<?php echo h(url_for('admin/courses.php')); ?>">View courses →</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card app-card shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Students</div>
                <div class="display-6 fw-bold"><?php echo h($studentCount); ?></div>
                <a class="link-primary small" href="<?php echo h(url_for('admin/students.php')); ?>">View students →</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card app-card shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Registrations</div>
                <div class="display-6 fw-bold"><?php echo h($registrationCount); ?></div>
                <a class="link-primary small" href="<?php echo h(url_for('admin/registrations.php')); ?>">View registrations →</a>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>
