<?php
$dbn  = 'mysql:dbname=green_beans;charset=utf8mb4;port=3306;host=localhost';
$user = 'root';
$pwd  = 'root';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // エラーを例外で投げる
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // 取得結果を連想配列に
    PDO::ATTR_EMULATE_PREPARES   => false,                    // 本物のプリペアドステートメント
];
try {
    $pdo  = new PDO($dbn, $user, $pwd);
} catch (PDOException $e) {
    exit('DB接続失敗: ' . $e->getMessage());
}
