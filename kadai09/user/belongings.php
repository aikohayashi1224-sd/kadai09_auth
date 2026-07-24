<?php
$page_title = '動産・コレクション';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$error   = '';

// 削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM belongings WHERE id = ? AND user_id = ?");
    $stmt->execute([(int)$_POST['delete_id'], $user_id]);
    header('Location: belongings.php');
    exit;

// 更新処理
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $data = [
        'id'               => (int)$_POST['edit_id'],
        'user_id'          => $user_id,
        'category'         => $_POST['category'] ?? '',
        'item_name'        => $_POST['item_name'] ?? '',
        'brand'            => $_POST['brand'] ?? '',
        'purchase_price'   => (int)str_replace(',', '', $_POST['purchase_price'] ?? ''),
        'storage_location' => $_POST['storage_location'] ?? '',
        'disposal'         => $_POST['disposal'] ?? '',
        'disposal_person'  => $_POST['disposal_person'] ?? '',
        'item_note'        => $_POST['item_note'] ?? '',
    ];
    $stmt = $pdo->prepare("
        UPDATE belongings SET
            category = :category, item_name = :item_name, brand = :brand,
            purchase_price = :purchase_price, storage_location = :storage_location,
            disposal = :disposal, disposal_person = :disposal_person, item_note = :item_note
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute($data);
    header('Location: belongings.php');
    exit;

// 追加処理
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id'          => $user_id,
        'category'         => $_POST['category'] ?? '',
        'item_name'        => $_POST['item_name'] ?? '',
        'brand'            => $_POST['brand'] ?? '',
        'purchase_price'   => (int)str_replace(',', '', $_POST['purchase_price'] ?? ''),
        'storage_location' => $_POST['storage_location'] ?? '',
        'disposal'         => $_POST['disposal'] ?? '',
        'disposal_person'  => $_POST['disposal_person'] ?? '',
        'item_note'        => $_POST['item_note'] ?? '',
    ];
    try {
        $stmt = $pdo->prepare("
            INSERT INTO belongings
                (user_id, category, item_name, brand, purchase_price, storage_location, disposal, disposal_person, item_note)
            VALUES
                (:user_id, :category, :item_name, :brand, :purchase_price, :storage_location, :disposal, :disposal_person, :item_note)
        ");
        $stmt->execute($data);
        header('Location: belongings.php');
        exit;
    } catch (PDOException $e) {
        $error = 'エラーが発生しました：' . $e->getMessage();
    }
}

// 編集対象データを取得
$edit_item = [];
$scroll_to_form = false;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM belongings WHERE id = ? AND user_id = ?");
    $stmt->execute([(int)$_GET['edit'], $user_id]);
    $edit_item = $stmt->fetch() ?: [];
    $scroll_to_form = true;
}

// 一覧取得
$stmt = $pdo->prepare("SELECT * FROM belongings WHERE user_id = ? ORDER BY category, id");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <h1 class="section-heading">動産・コレクション</h1>
        <p class="page-desc">貴重品やコレクションの品目・保管場所・処分方法を記録しておきましょう。</p>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <!-- 新規追加ボタン（常に最上部） -->
        <div style="text-align:right; margin-bottom:16px;">
            <a href="belongings.php" class="btn">＋ 新規追加</a>
        </div>

        <!-- 入力フォーム（ページ上部） -->
        <div class="card" id="form-area">
            <h2><?= $edit_item ? '内容を編集する' : '新しく追加する' ?></h2>

            <form method="post">
                <?php if ($edit_item): ?>
                    <input type="hidden" name="edit_id" value="<?= $edit_item['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>カテゴリ</label>
                    <select name="category">
                        <?php
                        $categories = ['', '宝飾品・時計', 'カメラ・スポーツ', '楽器・レコード', '書籍・マンガ', 'その他'];
                        foreach ($categories as $cat):
                        ?>
                            <option value="<?= htmlspecialchars($cat) ?>"
                                <?= ($edit_item['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                <?= $cat === '' ? '選択してください' : htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>品名</label>
                    <input type="text" name="item_name"
                        value="<?= htmlspecialchars($edit_item['item_name'] ?? '') ?>"
                        placeholder="例：ダイヤモンドリング">
                </div>

                <div class="form-group">
                    <label>ブランド・メーカー</label>
                    <input type="text" name="brand"
                        value="<?= htmlspecialchars($edit_item['brand'] ?? '') ?>"
                        placeholder="例：Tiffany & Co.">
                </div>

                <div class="form-group">
                    <label>購入価格（円）</label>
                    <input type="text" name="purchase_price"
                        value="<?= !empty($edit_item['purchase_price']) ? number_format($edit_item['purchase_price']) : '' ?>"
                        placeholder="例：150,000" inputmode="numeric">
                </div>

                <div class="form-group">
                    <label>保管場所</label>
                    <input type="text" name="storage_location"
                        value="<?= htmlspecialchars($edit_item['storage_location'] ?? '') ?>"
                        placeholder="例：寝室クローゼット上段">
                </div>

                <div class="form-group">
                    <label>処分方法</label>
                    <select name="disposal">
                        <?php foreach (['', '特定の人に渡す', '売却する', '寄付する', '処分・廃棄', '家族に任せる'] as $d): ?>
                            <option value="<?= htmlspecialchars($d) ?>"
                                <?= ($edit_item['disposal'] ?? '') === $d ? 'selected' : '' ?>>
                                <?= $d === '' ? '選択してください' : htmlspecialchars($d) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>渡す相手（「特定の人に渡す」の場合）</label>
                    <input type="text" name="disposal_person"
                        value="<?= htmlspecialchars($edit_item['disposal_person'] ?? '') ?>"
                        placeholder="例：姉（山田花子）">
                </div>

                <div class="form-group">
                    <label>備考</label>
                    <textarea name="item_note"><?= htmlspecialchars($edit_item['item_note'] ?? '') ?></textarea>
                </div>

                <?php if ($edit_item): ?>
                    <button type="submit">更新する</button>
                    <a href="belongings.php" class="btn btn-secondary" style="margin-left:12px;">キャンセル</a>
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
                            <th>カテゴリ</th>
                            <th>品名</th>
                            <th>ブランド</th>
                            <th>購入価格</th>
                            <th>処分方法</th>
                            <th>渡す相手</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['category']) ?></td>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= htmlspecialchars($item['brand']) ?></td>
                            <td><?= $item['purchase_price'] ? number_format($item['purchase_price']) . '円' : '-' ?></td>
                            <td><?= htmlspecialchars($item['disposal']) ?></td>
                            <td><?= htmlspecialchars($item['disposal_person']) ?></td>
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