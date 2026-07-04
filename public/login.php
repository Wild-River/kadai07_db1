<?php
session_start();
require_once '../config/db.php';
require_once '../config/func.php';

// POSTで送られてきたら認証する
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 入力された値を受け取る
    $username = $_POST['username'];
    $password = $_POST['password'];

    // その username で DB を調べて、パスワードが合っているか照合する
    $sql = 'SELECT id, username, password_hash FROM admins WHERE username = :username';
    $stmt = $pdo->prepare($sql); //prepareに通すと「実行できる文」に変わる
    $stmt->bindValue(':username', $username, PDO::PARAM_STR); //bindValue で :username に値を紐付け
    $stmt->execute(); //実行
    $admin = $stmt->fetch(); //結果を $admin に受け取る

    // 成功 → $_SESSION に記録して index.php へリダイレクト
    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true); //ログイン成功時のみIDを再発行
        $_SESSION['admin_id'] = $admin['id'];
        redirect('index.php');
    } else {
        // 失敗 → エラーメッセージを変数に入れる
        $error = 'ログインに失敗しました';
    }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン | 生豆在庫管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="login-page">
    <div class="login-card">
        <h1 class="login-title">生豆在庫管理 ログイン</h1>
        <?php if (!empty($error)) : ?>
            <p class="error-message"><?= h($error) ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="username" class="form-label">
                    <i class="fas fa-user"></i>ユーザー名
                </label>
                <input type="text" id="username" name="username" class="form-input" required autocomplete="off">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i>パスワード
                </label>
                <input type="password" id="password" name="password" class="form-input" required autocomplete="off">
            </div>

            <button type="submit" class="submit-btn">
                ログイン
            </button>
        </form>
    </div>
</body>

</html>