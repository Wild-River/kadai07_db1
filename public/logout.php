<?php
session_start();
$_SESSION = [];         // 中身を空にする
session_destroy();      // セッション自体を破棄
header('Location: login.php');
exit;
