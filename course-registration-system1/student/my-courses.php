<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('student');

$studentId = (int) current_user()['id'];

if (isset($_POST['drop_course'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        flash_set('danger', 'Invalid CSRF token.');
        header('Location: ' . url_for('student/my-courses.php'));
        exit;
    }

    $courseId = (int) ($_POST['course_id'] ?? 0);
    if ($courseId > 0) {
        $del = $conn->prepare('DELETE FROM student_courses WHERE student_id=? AND course_id=?');
        $del->bind_param('ii', $studentId, $courseId);
        $del->execute();
        flash_set('info', 'Course removed from your registrations.');
    }

    header('Location: ' . url_for('student/my-courses.php'));
    exit;
}

$stmt = $conn->prepare(
    'SELECT c.id, c.course_name, c.course_code
     FROM courses c
     JOIN student_courses sc ON c.id=sc.course_id
     WHERE sc.student_id=?
     ORDER BY c.course_name ASC'
);
$stmt->bind_param('i', $studentId);
$stmt->execute();
$courses = $stmt->get_result();
?>

<?php render_header('My Courses'); ?>
<?php render_portal_nav('enrollments'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">My Courses</h1>
        <div class="text-muted">Your registered courses.</div>
    </div>
    <a href="<?php echo h(url_for('student/dashboard.php')); ?>" class="btn btn-outline-primary">Back</a>
</div>

<div class="card app-card shadow-sm">
    <div class="card-body">
        <?php if ($courses->num_rows === 0) : ?>
            <div class="text-muted">You have not registered for any course yet.</div>
            <div class="mt-3">
                <a href="<?php echo h(url_for('student/courses.php')); ?>" class="btn btn-primary">Browse courses</a>
            </div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th style="width:180px;">Code</th>
                            <th class="text-end" style="width:220px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $courses->fetch_assoc()) : ?>
                            <tr>
                                <td class="fw-semibold"><?php echo h($row['course_name']); ?></td>
                                <td><span class="badge text-bg-light border app-pill"><?php echo h($row['course_code']); ?></span></td>
                                <td class="text-end">
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Drop this course?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                                        <input type="hidden" name="course_id" value="<?php echo h((int) $row['id']); ?>">
                                        <button class="btn btn-sm btn-outline-danger" name="drop_course">Drop</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php render_footer(); ?>
