<?php
require_once __DIR__ . '/func.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    redirect('login.php');
}
