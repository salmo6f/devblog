<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('admin');

$message = '';

if (isset($_POST['add_course'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
    } else {
        $name = trim((string) ($_POST['course_name'] ?? ''));
        $code = trim((string) ($_POST['course_code'] ?? ''));

        if ($name === '' || $code === '') {
            $message = 'All fields are required.';
        } else {
            try {
                $stmt = $conn->prepare('INSERT INTO courses (course_name, course_code) VALUES (?, ?)');
                $stmt->bind_param('ss', $name, $code);
                $stmt->execute();

                flash_set('success', 'Course added successfully.');
                header('Location: ' . url_for('admin/courses.php'));
                exit;
            } catch (Throwable $e) {
                $message = 'Failed to add course (course code may already exist).';
            }
        }
    }
}
?>

<?php render_header('Add Course'); ?>
<?php render_portal_nav('courses'); ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <h1 class="h3 fw-bold mb-1">Add Course</h1>
                <div class="text-muted">Create a new course for students to register.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo h(url_for('admin/courses.php')); ?>" class="btn btn-outline-primary">Back</a>
            </div>
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
                        <input type="text" name="course_name" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Course Code</label>
                        <input type="text" name="course_code" class="form-control" required>
                        <div class="form-text">Example: CS101</div>
                    </div>
                    <button name="add_course" class="btn btn-primary">Add Course</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>
