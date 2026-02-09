<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('admin');

$role = 'student';
$stmt = $conn->prepare('SELECT id, name, email FROM users WHERE role=? ORDER BY name ASC');
$stmt->bind_param('s', $role);
$stmt->execute();
$students = $stmt->get_result();

render_header('Students');
?>
<?php render_portal_nav('students'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Students</h1>
        <div class="text-muted">All registered student accounts.</div>
    </div>
    <a href="<?php echo h(url_for('admin/dashboard.php')); ?>" class="btn btn-outline-primary">Back</a>
</div>

<div class="card app-card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:80px;">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th class="text-end" style="width:220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($s = $students->fetch_assoc()) : ?>
                        <tr>
                            <td class="text-muted"><?php echo h($s['id']); ?></td>
                            <td class="fw-semibold"><?php echo h($s['name']); ?></td>
                                <td><?php echo h($s['email']); ?></td>
                                <td class="text-end">
                                <?php if (table_exists($conn, 'sections')) : ?>
                                    <a class="btn btn-sm btn-outline-primary" href="<?php echo h(url_for('admin/sections.php')); ?>">View Sections</a>
                                <?php else : ?>
                                    <a class="btn btn-sm btn-outline-primary" href="<?php echo h(url_for('admin/student-courses.php?student_id=' . (int) $s['id'])); ?>">View Courses</a>
                                <?php endif; ?>
                                </td>
                            </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_footer(); ?>
