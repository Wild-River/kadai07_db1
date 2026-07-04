<?php
require_once '../config/func.php';
session_start();
$_SESSION = [];         // 中身を空にする
session_destroy();      // セッション自体を破棄
redirect('login.php');
