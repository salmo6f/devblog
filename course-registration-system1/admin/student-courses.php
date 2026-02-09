<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('admin');

$studentId = (int) ($_GET['student_id'] ?? 0);
if ($studentId <= 0) {
    flash_set('danger', 'No student selected.');
    header('Location: ' . url_for('admin/students.php'));
    exit;
}

if (isset($_POST['remove_registration'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        flash_set('danger', 'Invalid CSRF token.');
        header('Location: ' . url_for('admin/student-courses.php?student_id=' . $studentId));
        exit;
    }

    $courseId = (int) ($_POST['course_id'] ?? 0);
    if ($courseId > 0) {
        $del = $conn->prepare('DELETE FROM student_courses WHERE student_id=? AND course_id=?');
        $del->bind_param('ii', $studentId, $courseId);
        $del->execute();
        flash_set('success', 'Registration removed.');
    }

    header('Location: ' . url_for('admin/student-courses.php?student_id=' . $studentId));
    exit;
}

$s = $conn->prepare('SELECT id, name, email FROM users WHERE id=? AND role=? LIMIT 1');
$role = 'student';
$s->bind_param('is', $studentId, $role);
$s->execute();
$student = $s->get_result()->fetch_assoc();

if (!$student) {
    flash_set('danger', 'Student not found.');
    header('Location: ' . url_for('admin/students.php'));
    exit;
}

$c = $conn->prepare(
    'SELECT c.id, c.course_name, c.course_code
     FROM student_courses sc
     JOIN courses c ON c.id=sc.course_id
     WHERE sc.student_id=?
     ORDER BY c.course_name ASC'
);
$c->bind_param('i', $studentId);
$c->execute();
$courses = $c->get_result();

render_header('Student Courses');
?>
<?php render_portal_nav('students'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Student Courses</h1>
        <div class="text-muted"><?php echo h($student['name']); ?> • <?php echo h($student['email']); ?></div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo h(url_for('admin/students.php')); ?>" class="btn btn-outline-primary">Back</a>
    </div>
</div>

<div class="card app-card shadow-sm">
    <div class="card-body">
        <?php if ($courses->num_rows === 0) : ?>
            <div class="text-muted">No registrations yet.</div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th style="width:180px;">Code</th>
                            <th class="text-end" style="width:200px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $courses->fetch_assoc()) : ?>
                            <tr>
                                <td class="fw-semibold"><?php echo h($row['course_name']); ?></td>
                                <td><span class="badge text-bg-light border app-pill"><?php echo h($row['course_code']); ?></span></td>
                                <td class="text-end">
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Remove this registration?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                                        <input type="hidden" name="course_id" value="<?php echo h((int) $row['id']); ?>">
                                        <button class="btn btn-sm btn-outline-danger" name="remove_registration">Remove</button>
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
