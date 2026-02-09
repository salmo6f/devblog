<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_role('admin');
require_modern_schema($conn);

$message = '';

if (isset($_POST['create_department'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $code = strtoupper(trim((string) ($_POST['code'] ?? '')));

        if ($name === '' || $code === '') {
            $message = 'Name and code are required.';
        } else {
            try {
                $stmt = $conn->prepare('INSERT INTO departments (name, code) VALUES (?, ?)');
                $stmt->bind_param('ss', $name, $code);
                $stmt->execute();
                flash_set('success', 'Department created.');
                header('Location: ' . url_for('admin/departments.php'));
                exit;
            } catch (Throwable $e) {
                $message = 'Failed to create department (name/code must be unique).';
            }
        }
    }
}

if (isset($_POST['delete_department'])) {
    if (!checkToken($_POST['csrf_token'] ?? '')) {
        flash_set('danger', 'Invalid CSRF token.');
        header('Location: ' . url_for('admin/departments.php'));
        exit;
    }

    $id = (int) ($_POST['department_id'] ?? 0);
    if ($id > 0) {
        try {
            $del = $conn->prepare('DELETE FROM departments WHERE id=?');
            $del->bind_param('i', $id);
            $del->execute();
            flash_set('info', 'Department deleted.');
        } catch (Throwable $e) {
            flash_set('danger', 'Failed to delete department.');
        }
    }
    header('Location: ' . url_for('admin/departments.php'));
    exit;
}

$departments = $conn->query('SELECT id, name, code FROM departments ORDER BY code ASC');

render_header('Departments');
?>
<?php render_portal_nav('departments'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Departments</h1>
        <div class="text-muted">Create departments like CS, IT, Business.</div>
    </div>
    <a href="<?php echo h(url_for('admin/dashboard.php')); ?>" class="btn btn-outline-primary">Back</a>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card app-card shadow-sm">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">Add department</h2>
                <?php if ($message) : ?>
                    <div class="alert alert-danger" role="alert"><?php echo h($message); ?></div>
                <?php endif; ?>
                <form method="POST" class="vstack gap-3">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                    <div>
                        <label class="form-label">Name</label>
                        <input name="name" class="form-control" placeholder="Computer Science" required>
                    </div>
                    <div>
                        <label class="form-label">Code</label>
                        <input name="code" class="form-control" placeholder="CS" required>
                    </div>
                    <button class="btn btn-primary" name="create_department">Create</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card app-card shadow-sm">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">All departments</h2>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:90px;">Code</th>
                                <th>Name</th>
                                <th class="text-end" style="width:160px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($d = $departments->fetch_assoc()) : ?>
                                <tr>
                                    <td><span class="badge text-bg-light border app-pill"><?php echo h($d['code']); ?></span></td>
                                    <td class="fw-semibold"><?php echo h($d['name']); ?></td>
                                    <td class="text-end">
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this department?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo h(generateToken()); ?>">
                                            <input type="hidden" name="department_id" value="<?php echo h((int) $d['id']); ?>">
                                            <button class="btn btn-sm btn-outline-danger" name="delete_department">Delete</button>
                                        </form>
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
