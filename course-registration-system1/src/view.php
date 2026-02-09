<?php

require_once __DIR__ . '/url.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/util.php';

function render_header($title)
{
    $user = current_user();
    $flash = flash_get();

    $dashboardUrl = null;
    if ($user && isset($user['role'])) {
        if ($user['role'] === 'admin') {
            $dashboardUrl = url_for('admin/dashboard.php');
        } elseif ($user['role'] === 'student') {
            $dashboardUrl = url_for('student/dashboard.php');
        }
    }

    echo '<!doctype html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . ' • Course Registration</title>';
    echo '<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/zephyr/bootstrap.min.css" rel="stylesheet">';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">';
    echo '<link href="' . h(url_for('public/assets/css/app.css')) . '" rel="stylesheet">';
    echo '</head>';
    echo '<body class="app-bg">';

    echo '<nav class="navbar navbar-expand-lg navbar-light app-navbar shadow-sm sticky-top">';
    echo '<div class="container">';
    echo '<a class="navbar-brand fw-semibold" href="' . h(url_for('public/index.php')) . '"><i class="bi bi-mortarboard-fill me-2 text-primary"></i>CourseReg</a>';
    echo '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">';
    echo '<span class="navbar-toggler-icon"></span>';
    echo '</button>';
    echo '<div class="collapse navbar-collapse" id="navMain">';
    echo '<ul class="navbar-nav me-auto mb-2 mb-lg-0">';
    echo '<li class="nav-item"><a class="nav-link" href="' . h(url_for('public/index.php')) . '">Home</a></li>';
    echo '<li class="nav-item"><a class="nav-link" href="' . h(url_for('public/features.php')) . '">Features</a></li>';
    echo '<li class="nav-item"><a class="nav-link" href="' . h(url_for('public/about.php')) . '">About</a></li>';
    echo '<li class="nav-item"><a class="nav-link" href="' . h(url_for('public/contact.php')) . '">Contact</a></li>';
    if ($dashboardUrl) {
        echo '<li class="nav-item"><a class="nav-link" href="' . h($dashboardUrl) . '">Dashboard</a></li>';
    }
    echo '</ul>';
    echo '<ul class="navbar-nav ms-auto mb-2 mb-lg-0">';
    if ($user) {
        echo '<li class="nav-item dropdown">';
        echo '<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-person-circle me-2"></i>' . h($user['name']) . '</a>';
        echo '<ul class="dropdown-menu dropdown-menu-end">';
        if ($dashboardUrl) {
            echo '<li><a class="dropdown-item" href="' . h($dashboardUrl) . '"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>';
        }
        echo '<li><hr class="dropdown-divider"></li>';
        echo '<li><a class="dropdown-item text-danger" href="' . h(url_for('public/logout.php')) . '"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>';
        echo '</ul>';
        echo '</li>';
    } else {
        echo '<li class="nav-item"><a class="nav-link" href="' . h(url_for('public/login.php')) . '">Login</a></li>';
        echo '<li class="nav-item"><a class="btn btn-sm btn-primary ms-lg-2" href="' . h(url_for('public/register.php')) . '">Get started</a></li>';
    }
    echo '</ul>';
    echo '</div>';
    echo '</div>';
    echo '</nav>';

    echo '<main class="container py-4">';
    if ($flash && !empty($flash['message'])) {
        $type = !empty($flash['type']) ? $flash['type'] : 'info';
        $allowed = array('primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark');
        if (!in_array($type, $allowed, true)) {
            $type = 'info';
        }
        echo '<div class="alert alert-' . h($type) . ' shadow-sm" role="alert">' . h($flash['message']) . '</div>';
    }
}

function render_footer()
{
    echo '</main>';
    echo '<footer class="py-4">';
    echo '<div class="container text-center text-muted small">';
    echo '© ' . date('Y') . ' Course Registration System • Built with PHP + MySQL';
    echo '</div>';
    echo '</footer>';
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>';
    echo '</body></html>';
}

function render_portal_nav($active)
{
    $user = current_user();
    if (!$user || empty($user['role'])) {
        return;
    }

    $links = array();
    if ($user['role'] === 'admin') {
        $links = array(
            'dashboard' => array('Admin', url_for('admin/dashboard.php'), 'bi-speedometer2'),
            'courses' => array('Courses', url_for('admin/courses.php'), 'bi-journal-bookmark'),
            'sections' => array('Sections', url_for('admin/sections.php'), 'bi-grid-3x3-gap'),
            'terms' => array('Terms', url_for('admin/terms.php'), 'bi-calendar3'),
            'departments' => array('Departments', url_for('admin/departments.php'), 'bi-diagram-3'),
            'students' => array('Students', url_for('admin/students.php'), 'bi-people'),
        );
    } elseif ($user['role'] === 'student') {
        $links = array(
            'dashboard' => array('Dashboard', url_for('student/dashboard.php'), 'bi-speedometer2'),
            'sections' => array('Browse Sections', url_for('student/sections.php'), 'bi-search'),
            'enrollments' => array('My Enrollments', url_for('student/my-enrollments.php'), 'bi-check2-square'),
        );
    }

    if (empty($links)) {
        return;
    }

    echo '<div class="card app-card shadow-sm mb-3">';
    echo '<div class="card-body py-2">';
    echo '<div class="d-flex flex-wrap gap-2">';
    foreach ($links as $key => $info) {
        $label = $info[0];
        $url = $info[1];
        $icon = $info[2];
        $cls = ($key === $active) ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline-primary';
        echo '<a class="' . h($cls) . '" href="' . h($url) . '"><i class="bi ' . h($icon) . ' me-1"></i>' . h($label) . '</a>';
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
