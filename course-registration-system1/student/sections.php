<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('student');
require_modern_schema($conn);

$studentId = (int) current_user()['id'];

// Get active term by default (or selected term)
$termId = (int) ($_GET['term_id'] ?? 0);
if ($termId <= 0) {
    $t = $conn->query('SELECT id FROM terms WHERE is_active=1 ORDER BY id DESC LIMIT 1')->fetch_assoc();
    $termId = $t ? (int) $t['id'] : 0;
}

$q = trim((string) ($_GET['q'] ?? ''));

if (isset($_POST['enroll'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        flash_set('danger', 'Invalid CSRF token.');
        header('Location: ' . url_for('student/sections.php'));
        exit;
    }

    $sectionId = (int) ($_POST['section_id'] ?? 0);
    if ($sectionId <= 0) {
        flash_set('danger', 'Invalid section.');
        header('Location: ' . url_for('student/sections.php'));
        exit;
    }

    // Capacity check (enrolled only)
    $capStmt = $conn->prepare('SELECT capacity FROM sections WHERE id=?');
    $capStmt->bind_param('i', $sectionId);
    $capStmt->execute();
    $sec = $capStmt->get_result()->fetch_assoc();
    if (!$sec) {
        flash_set('danger', 'Section not found.');
        header('Location: ' . url_for('student/sections.php'));
        exit;
    }

    $cap = (int) $sec['capacity'];
    $cnt = $conn->prepare('SELECT COUNT(*) AS c FROM enrollments WHERE section_id=? AND status=\'enrolled\'');
    $cnt->bind_param('i', $sectionId);
    $cnt->execute();
    $enrolled = (int) $cnt->get_result()->fetch_assoc()['c'];

    $status = ($enrolled < $cap) ? 'enrolled' : 'waitlisted';

    try {
        $ins = $conn->prepare('INSERT INTO enrollments (student_id, section_id, status) VALUES (?, ?, ?)');
        $ins->bind_param('iis', $studentId, $sectionId, $status);
        $ins->execute();
        flash_set($status === 'enrolled' ? 'success' : 'warning', $status === 'enrolled' ? 'Enrolled successfully.' : 'Section is full. You have been waitlisted.');
    } catch (Throwable $e) {
        flash_set('info', 'You already have an enrollment record for this section.');
    }

    $back = url_for('student/sections.php' . ($termId ? ('?term_id=' . $termId) : ''));
    header('Location: ' . $back);
    exit;
}

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

$terms = $conn->query('SELECT id, name, is_active FROM terms ORDER BY is_active DESC, id DESC');

$like = '%' . $q . '%';
$stmt = $conn->prepare(
    'SELECT s.id, s.section_code, s.instructor_name, s.days, s.start_time, s.end_time, s.room, s.capacity,
            c.course_name, c.course_code, t.name AS term_name,
            (SELECT COUNT(*) FROM enrollments e WHERE e.section_id=s.id AND e.status=\'enrolled\') AS enrolled_count,
            (SELECT e2.status FROM enrollments e2 WHERE e2.section_id=s.id AND e2.student_id=? LIMIT 1) AS my_status
     FROM sections s
     JOIN courses c ON c.id=s.course_id
     JOIN terms t ON t.id=s.term_id
     WHERE (?=0 OR s.term_id=?)
       AND (c.course_name LIKE ? OR c.course_code LIKE ? OR s.instructor_name LIKE ?)
     ORDER BY t.is_active DESC, t.id DESC, c.course_name ASC, s.section_code ASC'
);
$stmt->bind_param('iiisss', $studentId, $termId, $termId, $like, $like, $like);
$stmt->execute();
$sections = $stmt->get_result();

render_header('Sections');
?>
<?php render_portal_nav('sections'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Sections</h1>
        <div class="text-muted">Choose a term, search, and enroll. Full sections add you to the waitlist.</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo h(url_for('student/dashboard.php')); ?>" class="btn btn-outline-primary">Back</a>
        <a href="<?php echo h(url_for('student/my-enrollments.php')); ?>" class="btn btn-primary">My Enrollments</a>
    </div>
</div>

<div class="card app-card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Term</label>
                <select name="term_id" class="form-select">
                    <option value="0">All terms</option>
                    <?php while ($t = $terms->fetch_assoc()) : ?>
                        <option value="<?php echo h((int) $t['id']); ?>" <?php echo ((int) $t['id'] === $termId) ? 'selected' : ''; ?>>
                            <?php echo h($t['name'] . ((int) $t['is_active'] === 1 ? ' (Active)' : '')); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Search</label>
                <input name="q" class="form-control" placeholder="Course name, code, instructor..." value="<?php echo h($q); ?>">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-outline-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card app-card shadow-sm">
    <div class="card-body">
        <?php if ($sections->num_rows === 0) : ?>
            <div class="text-muted">No sections found.</div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th>Course</th>
                            <th style="width:90px;">Sec</th>
                            <th style="width:200px;">Schedule</th>
                            <th style="width:150px;">Instructor</th>
                            <th class="text-end" style="width:140px;">Seats</th>
                            <th class="text-end" style="width:220px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($s = $sections->fetch_assoc()) : ?>
                            <?php
                            $enrolled = (int) $s['enrolled_count'];
                            $cap = (int) $s['capacity'];
                            $seats = $cap - $enrolled;
                            $myStatus = (string) ($s['my_status'] ?? '');
                            ?>
                            <tr>
                                <td class="text-muted small"><?php echo h($s['term_name']); ?></td>
                                <td class="fw-semibold"><?php echo h($s['course_name']); ?><div class="text-muted small"><?php echo h($s['course_code']); ?></div></td>
                                <td><span class="badge text-bg-light border app-pill"><?php echo h($s['section_code']); ?></span></td>
                                <td class="text-muted small">
                                    <?php
                                    $sched = trim(($s['days'] ?? '') . ' ' . ($s['start_time'] ?? '') . '-' . ($s['end_time'] ?? ''));
                                    echo h($sched !== '-' ? $sched : '—');
                                    ?>
                                    <?php if (!empty($s['room'])) : ?>
                                        <div class="text-muted small"><?php echo h($s['room']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?php echo h($s['instructor_name'] ?: '—'); ?></td>
                                <td class="text-end">
                                    <div class="fw-semibold"><?php echo h($enrolled . '/' . $cap); ?></div>
                                    <div class="text-muted small"><?php echo h($seats > 0 ? ($seats . ' left') : 'Full'); ?></div>
                                </td>
                                <td class="text-end">
                                    <?php if ($myStatus === 'enrolled') : ?>
                                        <span class="badge text-bg-success app-pill">Enrolled</span>
                                    <?php elseif ($myStatus === 'waitlisted') : ?>
                                        <span class="badge text-bg-warning app-pill">Waitlisted</span>
                                    <?php elseif ($myStatus === 'dropped') : ?>
                                        <span class="badge text-bg-secondary app-pill">Dropped</span>
                                    <?php else : ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                                            <input type="hidden" name="section_id" value="<?php echo h((int) $s['id']); ?>">
                                            <button class="btn btn-sm btn-primary" name="enroll">Enroll</button>
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
