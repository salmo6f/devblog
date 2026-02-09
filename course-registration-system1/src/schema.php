<?php

function table_exists(mysqli $conn, $table)
{
    $table = (string) $table;
    $stmt = $conn->prepare('SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name=? LIMIT 1');
    $stmt->bind_param('s', $table);
    $stmt->execute();
    return (bool) $stmt->get_result()->fetch_assoc();
}

function require_modern_schema(mysqli $conn)
{
    if (!table_exists($conn, 'sections') || !table_exists($conn, 'enrollments') || !table_exists($conn, 'terms')) {
        $msg = 'Modern school features are not installed yet. Run: powershell -ExecutionPolicy Bypass -File scripts/setup-db.ps1 -Modern';
        flash_set('warning', $msg);
        header('Location: ' . url_for('public/index.php'));
        exit;
    }
}

