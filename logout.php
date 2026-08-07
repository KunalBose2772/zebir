<?php
/**
 * ZEBIR LIBAS – Logout Script
 */
require_once __DIR__ . '/includes/bootstrap.php';

unset($_SESSION['customer_id'], $_SESSION['customer_name']);
setFlash('info', 'You have been signed out.');
redirectTo('index.php');
