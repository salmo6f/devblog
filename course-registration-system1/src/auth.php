<?php

require_once __DIR__ . '/url.php';
require_once __DIR__ . '/flash.php';

function current_user()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function is_logged_in()
{
    return !empty($_SESSION['user']);
}

function login_user($user)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);

    $_SESSION['user'] = array(
        'id' => (int) $user['id'],
        'name' => (string) $user['name'],
        'email' => (string) $user['email'],
        'role' => (string) $user['role'],
    );
}

function logout_user()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function require_login()
{
    if (!is_logged_in()) {
        flash_set('warning', 'Please login to continue.');
        header('Location: ' . url_for('public/login.php'));
        exit;
    }
}

function require_role($role)
{
    require_login();
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== $role) {
        flash_set('danger', 'You are not authorized to access that page.');
        header('Location: ' . url_for('public/index.php'));
        exit;
    }
}

