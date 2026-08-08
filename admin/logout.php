<?php
/**
 * ZEBIR LIBAS – Admin Logout
 */
require_once __DIR__ . '/../includes/bootstrap.php';

unset($_SESSION['admin_id'], $_SESSION['admin_name']);
redirectTo('admin/login');
