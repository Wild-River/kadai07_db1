<?php
require_once '../config/auth.php';
require_once '../config/db.php';

$id = $_POST['id'];   // どの行を消すか受け取る

$sql = 'DELETE FROM stock_movements WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

// 早期リターンでエラー処理 →index.php へリダイレクト
if (!$status) {
    $error = $stmt->errorInfo();
    exit('送信エラー:' . $error[2]);
}
header('Location:movement_create.php');
exit;
