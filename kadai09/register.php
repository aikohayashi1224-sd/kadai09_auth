<?php
require_once 'includes/db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');

    // 入力チェック
    if ($name === '' || $email === '' || $password === '') {
        $error = 'すべての項目を入力してください。';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'メールアドレスの形式が正しくありません。';
    } elseif (strlen($password) < 8) {
        $error = 'パスワードは8文字以上で入力してください。';
    } elseif ($password !== $password_confirm) {
        $error = 'パスワードが一致しません。';
    } else {
        // メールアドレスの重複チェック
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'このメールアドレスはすでに登録されています。';
        } else {
            // パスワードを暗号化して登録
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $hashed_password]);
            $success = '登録が完了しました。ログインしてください。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規登録 - エンディングノート</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>新規ユーザー登録</h1>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
            <p><a href="index.php">ログイン画面へ</a></p>
        <?php else: ?>
            <form method="post" action="">
                <div class="form-group">
                    <label>お名前</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>メールアドレス</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>パスワード（8文字以上）</label>
                    <input type="password" name="password">
                </div>
                <div class="form-group">
                    <label>パスワード（確認）</label>
                    <input type="password" name="password_confirm">
                </div>
                <button type="submit">登録する</button>
            </form>
            <p><a href="index.php">ログイン画面へ</a></p>
        <?php endif; ?>
    </div>
</body>
</html>