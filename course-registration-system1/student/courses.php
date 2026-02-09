<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('student');

// Modern system uses sections; keep this file for backwards-compatible links.
if (table_exists($conn, 'sections')) {
    header('Location: ' . url_for('student/sections.php'));
    exit;
}

// Legacy fallback (old schema)
$studentId = (int) current_user()['id'];

if (isset($_POST['register_course'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        flash_set('danger', 'Invalid CSRF token.');
        header('Location: ' . url_for('student/courses.php'));
        exit;
    }

    $courseId = (int) ($_POST['course_id'] ?? 0);
    if ($courseId <= 0) {
        flash_set('danger', 'Invalid course.');
        header('Location: ' . url_for('student/courses.php'));
        exit;
    }

    try {
        $ins = $conn->prepare('INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)');
        $ins->bind_param('ii', $studentId, $courseId);
        $ins->execute();
        flash_set('success', 'Course registered successfully.');
    } catch (Throwable $e) {
        flash_set('warning', 'You are already registered for that course.');
    }

    header('Location: ' . url_for('student/courses.php'));
    exit;
}

$stmt = $conn->prepare(
    'SELECT c.id, c.course_name, c.course_code,
            CASE WHEN sc.id IS NULL THEN 0 ELSE 1 END AS registered
     FROM courses c
     LEFT JOIN student_courses sc ON sc.course_id=c.id AND sc.student_id=?
     ORDER BY c.course_name ASC'
);
$stmt->bind_param('i', $studentId);
$stmt->execute();
$courses = $stmt->get_result();
?>

<?php render_header('Available Courses'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Available Courses</h1>
        <div class="text-muted">Register for a course in one click.</div>
    </div>
    <a href="<?php echo h(url_for('student/dashboard.php')); ?>" class="btn btn-outline-primary">Back</a>
</div>

<div class="card app-card shadow-sm">
    <div class="card-body">
        <?php if ($courses->num_rows === 0) : ?>
            <div class="text-muted">No courses available yet.</div>
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
                                    <?php if ((int) $row['registered'] === 1) : ?>
                                        <span class="badge text-bg-success app-pill">Registered</span>
                                    <?php else : ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                                            <input type="hidden" name="course_id" value="<?php echo h((int) $row['id']); ?>">
                                            <button class="btn btn-sm btn-primary" name="register_course">Register</button>
                                        </form>
                                    <?php endif; ?>
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
