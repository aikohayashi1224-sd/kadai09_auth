<?php
require_once 'includes/db.php';
session_start();

// すでにログイン済みならダッシュボードへ
if (isset($_SESSION['user_id'])) {
    header('Location: user/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'メールアドレスとパスワードを入力してください。';
    } else {
        // DBからユーザーを取得
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // ログイン成功
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
              $stmt = $pdo->prepare("UPDATE users SET last_checked_at = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            header('Location: user/dashboard.php');
            exit;
        } else {
            $error = 'メールアドレスまたはパスワードが正しくありません。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン - エンディングノート</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>ログイン</h1>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label>メールアドレス</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>パスワード</label>
                <input type="password" name="password">
            </div>
            <button type="submit">ログインする</button>
        </form>
        <p><a href="register.php">新規登録はこちら</a></p>
    </div>
</body>
</html>