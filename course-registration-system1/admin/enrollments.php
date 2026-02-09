<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('admin');
require_modern_schema($conn);

$sectionId = (int) ($_GET['section_id'] ?? 0);
if ($sectionId <= 0) {
    flash_set('warning', 'Select a section first.');
    header('Location: ' . url_for('admin/sections.php'));
    exit;
}

if (isset($_POST['set_status'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        flash_set('danger', 'Invalid CSRF token.');
        header('Location: ' . url_for('admin/enrollments.php?section_id=' . $sectionId));
        exit;
    }

    $enrollmentId = (int) ($_POST['enrollment_id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');
    $allowed = array('enrolled', 'waitlisted', 'dropped');
    if ($enrollmentId > 0 && in_array($status, $allowed, true)) {
        if ($status === 'enrolled') {
            $capStmt = $conn->prepare('SELECT capacity FROM sections WHERE id=?');
            $capStmt->bind_param('i', $sectionId);
            $capStmt->execute();
            $cap = (int) $capStmt->get_result()->fetch_assoc()['capacity'];

            $cnt = $conn->prepare('SELECT COUNT(*) AS c FROM enrollments WHERE section_id=? AND status=\'enrolled\'');
            $cnt->bind_param('i', $sectionId);
            $cnt->execute();
            $enrolled = (int) $cnt->get_result()->fetch_assoc()['c'];
            if ($enrolled >= $cap) {
                flash_set('warning', 'No seats left to set as enrolled.');
                header('Location: ' . url_for('admin/enrollments.php?section_id=' . $sectionId));
                exit;
            }
        }

        $up = $conn->prepare('UPDATE enrollments SET status=? WHERE id=?');
        $up->bind_param('si', $status, $enrollmentId);
        $up->execute();
        flash_set('success', 'Enrollment updated.');
    }
    header('Location: ' . url_for('admin/enrollments.php?section_id=' . $sectionId));
    exit;
}

$sec = $conn->prepare(
    'SELECT s.id, s.section_code, s.capacity, c.course_name, c.course_code, t.name AS term_name
     FROM sections s
     JOIN courses c ON c.id=s.course_id
     JOIN terms t ON t.id=s.term_id
     WHERE s.id=?'
);
$sec->bind_param('i', $sectionId);
$sec->execute();
$section = $sec->get_result()->fetch_assoc();

if (!$section) {
    flash_set('danger', 'Section not found.');
    header('Location: ' . url_for('admin/sections.php'));
    exit;
}

$cnt = $conn->prepare('SELECT COUNT(*) AS c FROM enrollments WHERE section_id=? AND status=\'enrolled\'');
$cnt->bind_param('i', $sectionId);
$cnt->execute();
$enrolledCount = (int) $cnt->get_result()->fetch_assoc()['c'];

$rows = $conn->prepare(
    'SELECT e.id, e.status, e.created_at, u.name, u.email
     FROM enrollments e
     JOIN users u ON u.id=e.student_id
     WHERE e.section_id=?
     ORDER BY FIELD(e.status, \'enrolled\', \'waitlisted\', \'dropped\'), e.created_at ASC'
);
$rows->bind_param('i', $sectionId);
$rows->execute();
$enrollments = $rows->get_result();

render_header('Enrollments');
?>
<?php render_portal_nav('sections'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Enrollments</h1>
        <div class="text-muted">
            <?php echo h($section['term_name']); ?> • <?php echo h($section['course_name'] . ' (' . $section['course_code'] . ')'); ?> • Section <?php echo h($section['section_code']); ?>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo h(url_for('admin/sections.php')); ?>" class="btn btn-outline-primary">Back</a>
        <span class="badge text-bg-light border app-pill align-self-center"><?php echo h($enrolledCount . '/' . (int) $section['capacity'] . ' enrolled'); ?></span>
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
                            <th>Student</th>
                            <th>Email</th>
                            <th style="width:140px;">Status</th>
                            <th class="text-end" style="width:220px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($e = $enrollments->fetch_assoc()) : ?>
                            <tr>
                                <td class="fw-semibold"><?php echo h($e['name']); ?></td>
                                <td><?php echo h($e['email']); ?></td>
                                <td>
                                    <?php
                                    $status = (string) $e['status'];
                                    $badge = 'light';
                                    if ($status === 'enrolled') $badge = 'success';
                                    if ($status === 'waitlisted') $badge = 'warning';
                                    if ($status === 'dropped') $badge = 'secondary';
                                    ?>
                                    <span class="badge text-bg-<?php echo h($badge); ?> app-pill"><?php echo h($status); ?></span>
                                </td>
                                <td class="text-end">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                                        <input type="hidden" name="enrollment_id" value="<?php echo h((int) $e['id']); ?>">
                                        <input type="hidden" name="status" value="enrolled">
                                        <button class="btn btn-sm btn-outline-success" name="set_status">Enroll</button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                                        <input type="hidden" name="enrollment_id" value="<?php echo h((int) $e['id']); ?>">
                                        <input type="hidden" name="status" value="waitlisted">
                                        <button class="btn btn-sm btn-outline-warning" name="set_status">Waitlist</button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                                        <input type="hidden" name="enrollment_id" value="<?php echo h((int) $e['id']); ?>">
                                        <input type="hidden" name="status" value="dropped">
                                        <button class="btn btn-sm btn-outline-secondary" name="set_status">Drop</button>
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
