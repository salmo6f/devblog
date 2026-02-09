<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('admin');
require_modern_schema($conn);

$message = '';

if (isset($_POST['create_term'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $start = trim((string) ($_POST['start_date'] ?? ''));
        $end = trim((string) ($_POST['end_date'] ?? ''));
        $active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            $message = 'Name is required.';
        } else {
            try {
                if ($active === 1) {
                    $conn->query('UPDATE terms SET is_active=0');
                }
                $stmt = $conn->prepare('INSERT INTO terms (name, start_date, end_date, is_active) VALUES (?, NULLIF(?, \'\'), NULLIF(?, \'\'), ?)');
                $stmt->bind_param('sssi', $name, $start, $end, $active);
                $stmt->execute();
                flash_set('success', 'Term created.');
                header('Location: ' . url_for('admin/terms.php'));
                exit;
            } catch (Throwable $e) {
                $message = 'Failed to create term (name must be unique).';
            }
        }
    }
}

if (isset($_POST['set_active'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        flash_set('danger', 'Invalid CSRF token.');
        header('Location: ' . url_for('admin/terms.php'));
        exit;
    }
    $id = (int) ($_POST['term_id'] ?? 0);
    if ($id > 0) {
        $conn->query('UPDATE terms SET is_active=0');
        $st = $conn->prepare('UPDATE terms SET is_active=1 WHERE id=?');
        $st->bind_param('i', $id);
        $st->execute();
        flash_set('info', 'Active term updated.');
    }
    header('Location: ' . url_for('admin/terms.php'));
    exit;
}

$terms = $conn->query('SELECT id, name, start_date, end_date, is_active FROM terms ORDER BY is_active DESC, id DESC');

render_header('Terms');
?>
<?php render_portal_nav('terms'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Academic Terms</h1>
        <div class="text-muted">Semesters/terms (e.g., Spring 2026).</div>
    </div>
    <a href="<?php echo h(url_for('admin/dashboard.php')); ?>" class="btn btn-outline-primary">Back</a>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card app-card shadow-sm">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">Add term</h2>
                <?php if ($message) : ?>
                    <div class="alert alert-danger" role="alert"><?php echo h($message); ?></div>
                <?php endif; ?>
                <form method="POST" class="vstack gap-3">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                    <div>
                        <label class="form-label">Name</label>
                        <input name="name" class="form-control" placeholder="Fall 2026" required>
                    </div>
                    <div class="row g-2">
                        <div class="col">
                            <label class="form-label">Start date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">End date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active">
                        <label class="form-check-label" for="is_active">Set as active term</label>
                    </div>
                    <button class="btn btn-primary" name="create_term">Create</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card app-card shadow-sm">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">All terms</h2>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Term</th>
                                <th style="width:220px;">Dates</th>
                                <th style="width:120px;">Status</th>
                                <th class="text-end" style="width:160px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($t = $terms->fetch_assoc()) : ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo h($t['name']); ?></td>
                                    <td class="text-muted small">
                                        <?php
                                        $dates = array();
                                        if (!empty($t['start_date'])) $dates[] = $t['start_date'];
                                        if (!empty($t['end_date'])) $dates[] = $t['end_date'];
                                        echo h($dates ? implode(' → ', $dates) : '—');
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ((int) $t['is_active'] === 1) : ?>
                                            <span class="badge text-bg-success app-pill">Active</span>
                                        <?php else : ?>
                                            <span class="badge text-bg-light border app-pill">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ((int) $t['is_active'] === 0) : ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                                                <input type="hidden" name="term_id" value="<?php echo h((int) $t['id']); ?>">
                                                <button class="btn btn-sm btn-outline-primary" name="set_active">Set active</button>
                                            </form>
                                        <?php else : ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
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
