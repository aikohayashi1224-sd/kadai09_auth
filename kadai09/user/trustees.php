<?php
$page_title = '引受人設定';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

// カテゴリ一覧（section => 表示名）
$sections = [
    'profile'    => '基本情報',
    'medical'    => '医療・健康',
    'assets'     => '財産',
    'digital'    => 'デジタル',
    'work'       => '仕事',
    'housing'    => '賃貸・契約',
    'belongings' => '動産・コレクション',
    'funeral'    => '葬儀',
    'contacts'   => '人間関係',
    'will'       => '遺言・形見',
    'messages'   => '引受人へのメッセージ',
];

// 削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM trustees WHERE id = ? AND user_id = ?");
    $stmt->execute([(int)$_POST['delete_id'], $user_id]);
    $success = '削除しました。';

// 権限保存処理
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_permissions'])) {
    $trustee_id = (int)$_POST['trustee_id'];

    // このtrusteesがこのユーザーのものか確認
    $check = $pdo->prepare("SELECT id FROM trustees WHERE id = ? AND user_id = ?");
    $check->execute([$trustee_id, $user_id]);
    if ($check->fetch()) {
        foreach ($sections as $section => $label) {
            $is_visible = isset($_POST['permissions'][$section]) ? 1 : 0;
            $stmt = $pdo->prepare("
                INSERT INTO trustees_permissions (trustee_id, section, item, is_visible)
                VALUES (:trustee_id, :section, :item, :is_visible)
                ON DUPLICATE KEY UPDATE is_visible = VALUES(is_visible)
            ");
            $stmt->execute([
                'trustee_id' => $trustee_id,
                'section'    => $section,
                'item'       => 'all',
                'is_visible' => $is_visible,
            ]);
        }
        $success = '権限を保存しました。';
    }

// 引受人追加処理
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM trustees WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $count = $stmt->fetchColumn();

    if ($count >= 3) {
        $error = '引受人は最大3人まで登録できます。';
    } else {
        $name         = trim($_POST['name'] ?? '');
        $relationship = trim($_POST['relationship'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $password     = $_POST['password'] ?? '';

        if (!$name || !$email || !$password) {
            $error = '氏名・メールアドレス・パスワードは必須です。';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'メールアドレスの形式が正しくありません。';
        } elseif (mb_strlen($password) < 8) {
            $error = 'パスワードは8文字以上で設定してください。';
        } else {
            try {
                $priority    = $count + 1;
                $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO trustees (user_id, priority, name, relationship, email, password)
                    VALUES (:user_id, :priority, :name, :relationship, :email, :password)
                ");
                $stmt->execute([
                    'user_id'      => $user_id,
                    'priority'     => $priority,
                    'name'         => $name,
                    'relationship' => $relationship,
                    'email'        => $email,
                    'password'     => $hashed_pass,
                ]);
                $success = '引受人を追加しました。';
            } catch (PDOException $e) {
                $error = 'エラーが発生しました：' . $e->getMessage();
            }
        }
    }
}

// 引受人一覧取得
$stmt = $pdo->prepare("SELECT * FROM trustees WHERE user_id = ? ORDER BY priority");
$stmt->execute([$user_id]);
$trustees = $stmt->fetchAll();

// 権限データを取得（trustee_id => section => is_visible）
$permissions = [];
if (!empty($trustees)) {
    $ids = implode(',', array_column($trustees, 'id'));
    $rows = $pdo->query("SELECT trustee_id, section, is_visible FROM trustees_permissions WHERE trustee_id IN ($ids)")->fetchAll();
    foreach ($rows as $row) {
        $permissions[$row['trustee_id']][$row['section']] = $row['is_visible'];
    }
}

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">

        <div class="card">
            <h2>引受人設定</h2>
            <p class="page-desc">
                あなたの情報を閲覧できる引受人を最大3人まで登録できます。<br>
                登録したメールアドレスとパスワードで引受人がログインします。
            </p>

            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
            <?php if ($error):   ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

            <?php if (empty($trustees)): ?>
                <p style="color:#999;">まだ引受人が登録されていません。</p>
            <?php else: ?>
                <?php foreach ($trustees as $t): ?>
                <div style="border:1px solid #eee; border-radius:6px; padding:20px; margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <div>
                            <strong><?= $t['priority'] ?>番目：<?= htmlspecialchars($t['name']) ?>さん</strong>
                            <span style="color:#777; font-size:0.9rem;">（<?= htmlspecialchars($t['relationship']) ?>）</span><br>
                            <small style="color:#999;"><?= htmlspecialchars($t['email']) ?></small>
                        </div>
                        <form method="post" onsubmit="return confirm('<?= htmlspecialchars($t['name']) ?>さんを削除しますか？')">
                            <input type="hidden" name="delete_id" value="<?= $t['id'] ?>">
                            <button type="submit" class="btn btn-danger" style="padding:4px 12px; font-size:0.8rem;">削除</button>
                        </form>
                    </div>

                    <!-- 閲覧権限設定 -->
                    <form method="post">
                        <input type="hidden" name="save_permissions" value="1">
                        <input type="hidden" name="trustee_id" value="<?= $t['id'] ?>">
                        <p style="font-size:0.9rem; font-weight:bold; margin-bottom:10px;">閲覧できるカテゴリ：</p>
                        <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
                            <?php foreach ($sections as $section => $label): ?>
                                <?php $checked = ($permissions[$t['id']][$section] ?? 0) ? 'checked' : ''; ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="permissions[<?= $section ?>]" value="1" <?= $checked ?>>
                                    <?= $label ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="btn btn-secondary" style="padding:8px 20px; font-size:0.9rem;">権限を保存する</button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 追加フォーム -->
        <?php if (count($trustees) < 3): ?>
        <div class="card">
            <h3>引受人を追加する</h3>
            <form method="post">
                <div class="form-group">
                    <label>氏名 <span style="color:#e74c3c;">*</span></label>
                    <input type="text" name="name" placeholder="例：山田花子">
                </div>
                <div class="form-group">
                    <label>続柄・関係</label>
                    <input type="text" name="relationship" placeholder="例：姉、親友">
                </div>
                <div class="form-group">
                    <label>メールアドレス <span style="color:#e74c3c;">*</span></label>
                    <input type="email" name="email">
                </div>
                <div class="form-group">
                    <label>ログインパスワード <span style="color:#e74c3c;">*</span></label>
                    <input type="password" name="password" placeholder="8文字以上">
                    <small style="color:#999;">引受人がログインに使うパスワードです。本人に直接伝えてください。</small>
                </div>
                <button type="submit">追加する</button>
            </form>
        </div>
        <?php else: ?>
        <div class="card">
            <p style="color:#999;">引受人が3人登録済みです。追加するには既存の引受人を削除してください。</p>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>