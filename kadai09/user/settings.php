<?php
$page_title = 'アカウント設定';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

// 現在のユーザー情報を取得
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 保存処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // プロフィール更新
    if ($action === 'update_profile') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!$name || !$email) {
            $error = '氏名とメールアドレスは必須です。';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'メールアドレスの形式が正しくありません。';
        } else {
            // メールアドレス重複チェック（自分以外）
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->execute([$email, $user_id]);
            if ($check->fetch()) {
                $error = 'このメールアドレスはすでに使用されています。';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $user_id]);
                    $_SESSION['user_name'] = $name;
                    $success = 'プロフィールを更新しました。';
                    $user['name']  = $name;
                    $user['email'] = $email;
                } catch (PDOException $e) {
                    $error = 'エラーが発生しました：' . $e->getMessage();
                }
            }
        }
    }

    // パスワード変更
    if ($action === 'change_password') {
        $current  = $_POST['current_password'] ?? '';
        $new      = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (!$current || !$new || !$confirm) {
            $error = 'すべての項目を入力してください。';
        } elseif (!password_verify($current, $user['password'])) {
            $error = '現在のパスワードが正しくありません。';
        } elseif (mb_strlen($new) < 8) {
            $error = '新しいパスワードは8文字以上で設定してください。';
        } elseif ($new !== $confirm) {
            $error = '新しいパスワードと確認用パスワードが一致しません。';
        } else {
            try {
                $hashed = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $user_id]);
                $success = 'パスワードを変更しました。';
            } catch (PDOException $e) {
                $error = 'エラーが発生しました：' . $e->getMessage();
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">

        <!-- プロフィール更新 -->
        <div class="card">
            <h2>アカウント設定</h2>

            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
            <?php if ($error):   ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

            <h3 class="section-heading">基本情報</h3>
            <form method="post">
                <input type="hidden" name="action" value="update_profile">
                <div class="form-group">
                    <label>氏名</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>">
                </div>
                <div class="form-group">
                    <label>メールアドレス</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                </div>
                <button type="submit">更新する</button>
            </form>
        </div>

        <!-- パスワード変更 -->
        <div class="card">
            <h3 class="section-heading">パスワード変更</h3>
            <form method="post">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label>現在のパスワード</label>
                    <input type="password" name="current_password">
                </div>
                <div class="form-group">
                    <label>新しいパスワード（8文字以上）</label>
                    <input type="password" name="new_password">
                </div>
                <div class="form-group">
                    <label>新しいパスワード（確認）</label>
                    <input type="password" name="confirm_password">
                </div>
                <button type="submit">パスワードを変更する</button>
            </form>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>