<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('student');

$courseCount = 0;
$myCount = 0;
$studentId = (int) current_user()['id'];

try {
    $courseCount = (int) $conn->query('SELECT COUNT(*) AS c FROM courses')->fetch_assoc()['c'];
    $st = $conn->prepare('SELECT COUNT(*) AS c FROM student_courses WHERE student_id=?');
    $st->bind_param('i', $studentId);
    $st->execute();
    $myCount = (int) $st->get_result()->fetch_assoc()['c'];
} catch (Throwable $e) {
    // ignore
}
?>

<?php render_header('Student Dashboard'); ?>
<?php render_portal_nav('dashboard'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Student Dashboard</h1>
        <div class="text-muted">Browse courses and manage your registrations.</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (table_exists($conn, 'sections')) : ?>
            <a href="<?php echo h(url_for('student/sections.php')); ?>" class="btn btn-primary">Browse Sections</a>
            <a href="<?php echo h(url_for('student/my-enrollments.php')); ?>" class="btn btn-outline-primary">My Enrollments</a>
        <?php else : ?>
            <a href="<?php echo h(url_for('student/courses.php')); ?>" class="btn btn-primary">Browse Courses</a>
            <a href="<?php echo h(url_for('student/my-courses.php')); ?>" class="btn btn-outline-primary">My Courses</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card app-card shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Available courses</div>
                <div class="display-6 fw-bold"><?php echo h($courseCount); ?></div>
                <a class="link-primary small" href="<?php echo h(url_for('student/courses.php')); ?>">View courses →</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card app-card shadow-sm">
            <div class="card-body">
                <div class="text-muted small">My registrations</div>
                <div class="display-6 fw-bold"><?php echo h($myCount); ?></div>
                <a class="link-primary small" href="<?php echo h(url_for('student/my-courses.php')); ?>">View my courses →</a>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>
