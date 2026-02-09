<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('admin');
require_modern_schema($conn);

$message = '';

if (isset($_POST['create_section'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
    } else {
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $termId = (int) ($_POST['term_id'] ?? 0);
        $sectionCode = strtoupper(trim((string) ($_POST['section_code'] ?? '')));
        $instructor = trim((string) ($_POST['instructor_name'] ?? ''));
        $days = trim((string) ($_POST['days'] ?? ''));
        $startTime = trim((string) ($_POST['start_time'] ?? ''));
        $endTime = trim((string) ($_POST['end_time'] ?? ''));
        $room = trim((string) ($_POST['room'] ?? ''));
        $capacity = (int) ($_POST['capacity'] ?? 30);

        if ($courseId <= 0 || $termId <= 0 || $sectionCode === '') {
            $message = 'Course, term and section code are required.';
        } elseif ($capacity <= 0) {
            $message = 'Capacity must be greater than 0.';
        } else {
            try {
                $stmt = $conn->prepare(
                    'INSERT INTO sections (course_id, term_id, section_code, instructor_name, days, start_time, end_time, room, capacity)
                     VALUES (?,?,?,?,?, NULLIF(?, \'\'), NULLIF(?, \'\'), ?, ?)'
                );
                $stmt->bind_param('iissssssi', $courseId, $termId, $sectionCode, $instructor, $days, $startTime, $endTime, $room, $capacity);
                $stmt->execute();
                flash_set('success', 'Section created.');
                header('Location: ' . url_for('admin/sections.php'));
                exit;
            } catch (Throwable $e) {
                $message = 'Failed to create section (course+term+code must be unique).';
            }
        }
    }
}

$courses = $conn->query('SELECT id, course_name, course_code FROM courses ORDER BY course_name ASC');
$terms = $conn->query('SELECT id, name, is_active FROM terms ORDER BY is_active DESC, id DESC');

$sections = $conn->query(
    'SELECT s.id, s.section_code, s.instructor_name, s.days, s.start_time, s.end_time, s.room, s.capacity,
            c.course_name, c.course_code, t.name AS term_name,
            (SELECT COUNT(*) FROM enrollments e WHERE e.section_id=s.id AND e.status=\'enrolled\') AS enrolled_count
     FROM sections s
     JOIN courses c ON c.id=s.course_id
     JOIN terms t ON t.id=s.term_id
     ORDER BY t.is_active DESC, t.id DESC, c.course_name ASC, s.section_code ASC'
);

render_header('Sections');
?>
<?php render_portal_nav('sections'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Sections</h1>
        <div class="text-muted">Create offerings per term with instructor, schedule and capacity.</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo h(url_for('admin/dashboard.php')); ?>" class="btn btn-outline-primary">Back</a>
        <a href="<?php echo h(url_for('admin/terms.php')); ?>" class="btn btn-outline-primary">Terms</a>
        <a href="<?php echo h(url_for('admin/departments.php')); ?>" class="btn btn-outline-primary">Departments</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-4">
        <div class="card app-card shadow-sm">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">Add section</h2>
                <?php if ($message) : ?>
                    <div class="alert alert-danger" role="alert"><?php echo h($message); ?></div>
                <?php endif; ?>
                <form method="POST" class="vstack gap-3">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                    <div>
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Select course...</option>
                            <?php while ($c = $courses->fetch_assoc()) : ?>
                                <option value="<?php echo h((int) $c['id']); ?>"><?php echo h($c['course_name'] . ' (' . $c['course_code'] . ')'); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Term</label>
                        <select name="term_id" class="form-select" required>
                            <option value="">Select term...</option>
                            <?php while ($t = $terms->fetch_assoc()) : ?>
                                <option value="<?php echo h((int) $t['id']); ?>"><?php echo h($t['name'] . ((int) $t['is_active'] === 1 ? ' (Active)' : '')); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col">
                            <label class="form-label">Section code</label>
                            <input name="section_code" class="form-control" placeholder="A" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Capacity</label>
                            <input type="number" name="capacity" class="form-control" value="30" min="1" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Instructor</label>
                        <input name="instructor_name" class="form-control" placeholder="Mr. Ahmed">
                    </div>
                    <div class="row g-2">
                        <div class="col">
                            <label class="form-label">Days</label>
                            <input name="days" class="form-control" placeholder="Mon/Wed">
                        </div>
                        <div class="col">
                            <label class="form-label">Room</label>
                            <input name="room" class="form-control" placeholder="B-12">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col">
                            <label class="form-label">Start</label>
                            <input type="time" name="start_time" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">End</label>
                            <input type="time" name="end_time" class="form-control">
                        </div>
                    </div>
                    <button class="btn btn-primary" name="create_section">Create section</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card app-card shadow-sm">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">All sections</h2>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Term</th>
                                <th>Course</th>
                                <th style="width:90px;">Sec</th>
                                <th style="width:180px;">Schedule</th>
                                <th style="width:150px;">Instructor</th>
                                <th class="text-end" style="width:140px;">Seats</th>
                                <th class="text-end" style="width:120px;">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($s = $sections->fetch_assoc()) : ?>
                                <?php
                                $enrolled = (int) $s['enrolled_count'];
                                $cap = (int) $s['capacity'];
                                $seats = $cap - $enrolled;
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
                                        <div class="text-muted small"><?php echo h($seats > 0 ? ($seats . ' seats left') : 'Full'); ?></div>
                                    </td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary" href="<?php echo h(url_for('admin/enrollments.php?section_id=' . (int) $s['id'])); ?>">Enrollments</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>
