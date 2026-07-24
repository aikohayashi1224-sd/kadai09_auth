<?php
$page_title = '人間関係';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$error   = '';

// 削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ? AND user_id = ?");
    $stmt->execute([(int)$_POST['delete_id'], $user_id]);
    header('Location: contacts.php');
    exit;

// 更新処理
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $data = [
        'id'           => (int)$_POST['edit_id'],
        'user_id'      => $user_id,
        'contact_type' => $_POST['contact_type'] ?? '',
        'name'         => $_POST['name'] ?? '',
        'relationship' => $_POST['relationship'] ?? '',
        'phone'        => $_POST['phone'] ?? '',
        'email'        => $_POST['email'] ?? '',
        'contact_note' => $_POST['contact_note'] ?? '',
    ];
    $stmt = $pdo->prepare("
        UPDATE contacts SET
            contact_type = :contact_type, name = :name, relationship = :relationship,
            phone = :phone, email = :email, contact_note = :contact_note
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute($data);
    header('Location: contacts.php');
    exit;

// 追加処理
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id'      => $user_id,
        'contact_type' => $_POST['contact_type'] ?? '',
        'name'         => $_POST['name'] ?? '',
        'relationship' => $_POST['relationship'] ?? '',
        'phone'        => $_POST['phone'] ?? '',
        'email'        => $_POST['email'] ?? '',
        'contact_note' => $_POST['contact_note'] ?? '',
    ];
    try {
        $stmt = $pdo->prepare("
            INSERT INTO contacts
                (user_id, contact_type, name, relationship, phone, email, contact_note)
            VALUES
                (:user_id, :contact_type, :name, :relationship, :phone, :email, :contact_note)
        ");
        $stmt->execute($data);
        header('Location: contacts.php');
        exit;
    } catch (PDOException $e) {
        $error = 'エラーが発生しました：' . $e->getMessage();
    }
}

// 編集対象データを取得
$edit_item = [];
$scroll_to_form = false;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ? AND user_id = ?");
    $stmt->execute([(int)$_GET['edit'], $user_id]);
    $edit_item = $stmt->fetch() ?: [];
    $scroll_to_form = true;
}

// 一覧取得
$stmt = $pdo->prepare("SELECT * FROM contacts WHERE user_id = ? ORDER BY contact_type, id");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <h1 class="section-heading">人間関係</h1>
        <p class="page-desc">訃報を伝えてほしい方、逆に知らせなくてよい方なども記録しておきましょう。</p>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <!-- 新規追加ボタン（常に最上部） -->
        <div style="text-align:right; margin-bottom:16px;">
            <a href="contacts.php" class="btn">＋ 新規追加</a>
        </div>

        <!-- 入力フォーム（ページ上部） -->
        <div class="card" id="form-area">
            <h2><?= $edit_item ? '内容を編集する' : '新しく追加する' ?></h2>

            <form method="post">
                <?php if ($edit_item): ?>
                    <input type="hidden" name="edit_id" value="<?= $edit_item['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>種別</label>
                    <select name="contact_type">
                        <?php foreach (['', '家族・親族', '友人・知人', '職場関係', '取引先', '訃報を知らせない'] as $t): ?>
                            <option value="<?= htmlspecialchars($t) ?>"
                                <?= ($edit_item['contact_type'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t === '' ? '選択してください' : htmlspecialchars($t) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>氏名</label>
                    <input type="text" name="name"
                        value="<?= htmlspecialchars($edit_item['name'] ?? '') ?>"
                        placeholder="例：山田花子">
                </div>
                <div class="form-group">
                    <label>続柄・関係</label>
                    <input type="text" name="relationship"
                        value="<?= htmlspecialchars($edit_item['relationship'] ?? '') ?>"
                        placeholder="例：母、大学時代の友人">
                </div>
                <div class="form-group">
                    <label>電話番号</label>
                    <input type="tel" name="phone"
                        value="<?= htmlspecialchars($edit_item['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>メールアドレス</label>
                    <input type="email" name="email"
                        value="<?= htmlspecialchars($edit_item['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>備考</label>
                    <textarea name="contact_note" placeholder="例：訃報は必ず本人に直接連絡してほしい"><?= htmlspecialchars($edit_item['contact_note'] ?? '') ?></textarea>
                </div>

                <?php if ($edit_item): ?>
                    <button type="submit">更新する</button>
                    <a href="contacts.php" class="btn btn-secondary" style="margin-left:12px;">キャンセル</a>
                <?php else: ?>
                    <button type="submit">追加する</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- 登録済み一覧（ページ下部） -->
        <div class="card">
            <h2>登録済み一覧</h2>

            <?php if (empty($items)): ?>
                <p style="color:#999;">まだ登録されていません。上のフォームから追加してください。</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>種別</th>
                            <th>氏名</th>
                            <th>続柄・関係</th>
                            <th>電話番号</th>
                            <th>メール</th>
                            <th>備考</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['contact_type']) ?></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['relationship']) ?></td>
                            <td><?= htmlspecialchars($item['phone']) ?></td>
                            <td><?= htmlspecialchars($item['email']) ?></td>
                            <td><?= htmlspecialchars($item['contact_note']) ?></td>
                            <td>
                                <a href="?edit=<?= $item['id'] ?>"
                                   class="btn btn-secondary"
                                   style="padding:4px 12px; font-size:0.8rem; display:block; margin-bottom:4px;">編集</a>
                                <form method="post" onsubmit="return confirm('削除しますか？')">
                                    <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-danger"
                                        style="padding:4px 12px; font-size:0.8rem;">削除</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php if ($scroll_to_form): ?>
<script>
    window.addEventListener('DOMContentLoaded', function () {
        document.getElementById('form-area').scrollIntoView({ behavior: 'smooth' });
    });
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>