<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('admin');

// If modern schema exists, this legacy page redirects to sections (enrollments are managed per section).
if (table_exists($conn, 'sections')) {
    header('Location: ' . url_for('admin/sections.php'));
    exit;
}

$rows = $conn->query(
    "SELECT sc.student_id, u.name AS student_name, u.email AS student_email,
            c.id AS course_id, c.course_name, c.course_code
     FROM student_courses sc
     JOIN users u ON u.id=sc.student_id
     JOIN courses c ON c.id=sc.course_id
     ORDER BY u.name ASC, c.course_name ASC"
);

render_header('Registrations');
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Registrations</h1>
        <div class="text-muted">All student-course registrations.</div>
    </div>
    <a href="<?php echo h(url_for('admin/dashboard.php')); ?>" class="btn btn-outline-primary">Back</a>
</div>

<div class="card app-card shadow-sm">
    <div class="card-body">
        <?php if ($rows->num_rows === 0) : ?>
            <div class="text-muted">No registrations yet.</div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th style="width:160px;">Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = $rows->fetch_assoc()) : ?>
                            <tr>
                                <td class="fw-semibold"><?php echo h($r['student_name']); ?></td>
                                <td><?php echo h($r['student_email']); ?></td>
                                <td><?php echo h($r['course_name']); ?></td>
                                <td><span class="badge text-bg-light border app-pill"><?php echo h($r['course_code']); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php render_footer(); ?>
