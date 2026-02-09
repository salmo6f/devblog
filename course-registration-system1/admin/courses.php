<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('admin');

if (table_exists($conn, 'sections')) {
    // Modern schema installed: show quick links.
    // (Course CRUD stays the same.)
}

if (isset($_POST['delete_course'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        flash_set('danger', 'Invalid CSRF token.');
        header('Location: ' . url_for('admin/courses.php'));
        exit;
    }

    $id = (int) ($_POST['course_id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $conn->prepare('DELETE FROM courses WHERE id=?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            flash_set('success', 'Course deleted.');
        } catch (Throwable $e) {
            flash_set('danger', 'Failed to delete course (it may have registrations).');
        }
    }

    header('Location: ' . url_for('admin/courses.php'));
    exit;
}

$courses = $conn->query('SELECT id, course_name, course_code FROM courses ORDER BY course_name ASC');
?>

<?php render_header('Courses'); ?>
<?php render_portal_nav('courses'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Courses</h1>
        <div class="text-muted">Add, edit, or delete courses.</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo h(url_for('admin/dashboard.php')); ?>" class="btn btn-outline-primary">Back</a>
        <a href="<?php echo h(url_for('admin/add-course.php')); ?>" class="btn btn-primary">Add Course</a>
        <?php if (table_exists($conn, 'sections')) : ?>
            <a href="<?php echo h(url_for('admin/sections.php')); ?>" class="btn btn-outline-primary">Manage Sections</a>
        <?php endif; ?>
    </div>
</div>

<div class="card app-card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:80px;">ID</th>
                        <th>Course Name</th>
                        <th style="width:180px;">Course Code</th>
                        <th style="width:220px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $courses->fetch_assoc()) : ?>
                        <tr>
                            <td class="text-muted"><?php echo h($row['id']); ?></td>
                            <td class="fw-semibold"><?php echo h($row['course_name']); ?></td>
                            <td><span class="badge text-bg-light border app-pill"><?php echo h($row['course_code']); ?></span></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="<?php echo h(url_for('admin/edit-course.php?id=' . (int) $row['id'])); ?>">Edit</a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this course?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                                    <input type="hidden" name="course_id" value="<?php echo h((int) $row['id']); ?>">
                                    <button type="submit" name="delete_course" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_footer(); ?>
