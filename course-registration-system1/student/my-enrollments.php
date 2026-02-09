<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('student');
require_modern_schema($conn);

$studentId = (int) current_user()['id'];

if (isset($_POST['drop'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        flash_set('danger', 'Invalid CSRF token.');
        header('Location: ' . url_for('student/my-enrollments.php'));
        exit;
    }

    $enrollmentId = (int) ($_POST['enrollment_id'] ?? 0);
    if ($enrollmentId > 0) {
        $up = $conn->prepare('UPDATE enrollments SET status=\'dropped\' WHERE id=? AND student_id=?');
        $up->bind_param('ii', $enrollmentId, $studentId);
        $up->execute();
        flash_set('info', 'Enrollment dropped.');
    }
    header('Location: ' . url_for('student/my-enrollments.php'));
    exit;
}

$rows = $conn->prepare(
    'SELECT e.id, e.status, e.created_at,
            s.section_code, s.days, s.start_time, s.end_time, s.room,
            c.course_name, c.course_code,
            t.name AS term_name
     FROM enrollments e
     JOIN sections s ON s.id=e.section_id
     JOIN courses c ON c.id=s.course_id
     JOIN terms t ON t.id=s.term_id
     WHERE e.student_id=? AND e.status <> \'dropped\'
     ORDER BY t.is_active DESC, t.id DESC, c.course_name ASC'
);
$rows->bind_param('i', $studentId);
$rows->execute();
$enrollments = $rows->get_result();

render_header('My Enrollments');
?>
<?php render_portal_nav('enrollments'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">My Enrollments</h1>
        <div class="text-muted">Your enrolled and waitlisted sections.</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo h(url_for('student/dashboard.php')); ?>" class="btn btn-outline-primary">Back</a>
        <a href="<?php echo h(url_for('student/sections.php')); ?>" class="btn btn-primary">Browse Sections</a>
    </div>
</div>

<div class="card app-card shadow-sm">
    <div class="card-body">
        <?php if ($enrollments->num_rows === 0) : ?>
            <div class="text-muted">No enrollments yet.</div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th>Course</th>
                            <th style="width:90px;">Sec</th>
                            <th style="width:200px;">Schedule</th>
                            <th style="width:140px;">Status</th>
                            <th class="text-end" style="width:160px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($e = $enrollments->fetch_assoc()) : ?>
                            <?php
                            $status = (string) $e['status'];
                            $badge = $status === 'enrolled' ? 'success' : ($status === 'waitlisted' ? 'warning' : 'secondary');
                            ?>
                            <tr>
                                <td class="text-muted small"><?php echo h($e['term_name']); ?></td>
                                <td class="fw-semibold"><?php echo h($e['course_name']); ?><div class="text-muted small"><?php echo h($e['course_code']); ?></div></td>
                                <td><span class="badge text-bg-light border app-pill"><?php echo h($e['section_code']); ?></span></td>
                                <td class="text-muted small">
                                    <?php
                                    $sched = trim(($e['days'] ?? '') . ' ' . ($e['start_time'] ?? '') . '-' . ($e['end_time'] ?? ''));
                                    echo h($sched !== '-' ? $sched : '—');
                                    ?>
                                    <?php if (!empty($e['room'])) : ?>
                                        <div class="text-muted small"><?php echo h($e['room']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge text-bg-<?php echo h($badge); ?> app-pill"><?php echo h($status); ?></span></td>
                                <td class="text-end">
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Drop this enrollment?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                                        <input type="hidden" name="enrollment_id" value="<?php echo h((int) $e['id']); ?>">
                                        <button class="btn btn-sm btn-outline-danger" name="drop">Drop</button>
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
