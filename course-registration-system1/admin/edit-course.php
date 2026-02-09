<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('admin');

$message = '';
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    flash_set('danger', 'No course selected.');
    header('Location: ' . url_for('admin/courses.php'));
    exit;
}

$stmt = $conn->prepare('SELECT id, course_name, course_code FROM courses WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    flash_set('danger', 'Course not found.');
    header('Location: ' . url_for('admin/courses.php'));
    exit;
}

if (isset($_POST['update'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
    } else {
        $name = trim((string) ($_POST['course_name'] ?? ''));
        $code = trim((string) ($_POST['course_code'] ?? ''));

        if ($name === '' || $code === '') {
            $message = 'All fields are required.';
        } else {
            try {
                $up = $conn->prepare('UPDATE courses SET course_name=?, course_code=? WHERE id=?');
                $up->bind_param('ssi', $name, $code, $id);
                $up->execute();
                flash_set('success', 'Course updated successfully.');
                header('Location: ' . url_for('admin/courses.php'));
                exit;
            } catch (Throwable $e) {
                $message = 'Error updating course (course code may already exist).';
            }
        }
    }
}
?>

<?php render_header('Edit Course'); ?>
<?php render_portal_nav('courses'); ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <h1 class="h3 fw-bold mb-1">Edit Course</h1>
                <div class="text-muted">Update course details.</div>
            </div>
            <a href="<?php echo h(url_for('admin/courses.php')); ?>" class="btn btn-outline-primary">Back</a>
        </div>

        <div class="card app-card shadow-sm">
            <div class="card-body">
                <?php if ($message) : ?>
                    <div class="alert alert-danger" role="alert"><?php echo h($message); ?></div>
                <?php endif; ?>
                <form method="POST" class="vstack gap-3">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                    <div>
                        <label class="form-label">Course Name</label>
                        <input type="text" name="course_name" class="form-control" value="<?php echo h($course['course_name']); ?>" required>
                    </div>
                    <div>
                        <label class="form-label">Course Code</label>
                        <input type="text" name="course_code" class="form-control" value="<?php echo h($course['course_code']); ?>" required>
                    </div>
                    <button class="btn btn-primary" name="update">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>
