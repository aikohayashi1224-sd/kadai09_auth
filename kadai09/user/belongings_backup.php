<?php
$page_title = '動産・コレクション';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

// 削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM belongings WHERE id = ? AND user_id = ?");
    $stmt->execute([(int)$_POST['delete_id'], $user_id]);
    $success = '削除しました。';

// 更新処理
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $fields = ['category', 'item_name', 'brand', 'storage_location', 'disposal', 'disposal_person', 'item_note'];
    $data = ['id' => (int)$_POST['edit_id'], 'user_id' => $user_id];
    foreach ($fields as $f) {
        $data[$f] = $_POST[$f] ?? '';
    }
    $data['purchase_price'] = (int)str_replace(',', '', $_POST['purchase_price'] ?? '');

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
    $fields = ['category', 'item_name', 'brand', 'purchase_price', 'storage_location', 'disposal', 'disposal_person', 'item_note'];
    $data = ['user_id' => $user_id];
    foreach ($fields as $f) {
        $data[$f] = $_POST[$f] ?? '';
    }
    // 金額は数値のみ（空なら0）
$data['purchase_price'] = (int)str_replace(',', '', $data['purchase_price']);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO belongings
                (user_id, category, item_name, brand, purchase_price, storage_location, disposal, disposal_person, item_note)
            VALUES
                (:user_id, :category, :item_name, :brand, :purchase_price, :storage_location, :disposal, :disposal_person, :item_note)
        ");
        $stmt->execute($data);
        $success = '追加しました。';
    } catch (PDOException $e) {
        $error = 'エラーが発生しました：' . $e->getMessage();
    }
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

        <!-- 登録済み一覧 -->
        <div class="card">
            <h2>動産・コレクション</h2>
            <p class="page-desc">宝飾品・ブランド品・コレクションなど、引き継ぎや処分の希望を記録しておきましょう。</p>

            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
            <?php if ($error):   ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

            <?php if (empty($items)): ?>
                <p style="color:#999;">まだ登録されていません。下のフォームから追加してください。</p>
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
                                <form method="post" onsubmit="return confirm('削除しますか？')">
                                    <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                                    <a href="?edit=<?= $item['id'] ?>" class="btn btn-secondary" style="padding:4px 12px; font-size:0.8rem; margin-bottom:4px; display:block;">編集</a>
                                    <button type="submit" class="btn btn-danger" style="padding:4px 12px; font-size:0.8rem;">削除</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- 追加フォーム -->
        <div class="card">
            <?php
            // 編集対象のデータを取得
            $edit_item = [];
            if (isset($_GET['edit'])) {
                $stmt = $pdo->prepare("SELECT * FROM belongings WHERE id = ? AND user_id = ?");
                $stmt->execute([(int)$_GET['edit'], $user_id]);
                $edit_item = $stmt->fetch() ?: [];
            }
            ?>

            <h3><?= $edit_item ? '編集する' : '新しく追加する' ?></h3>
            <form method="post">
                <?php if ($edit_item): ?>
                    <input type="hidden" name="edit_id" value="<?= $edit_item['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>カテゴリ</label>
                    <select name="category">
                        <?php foreach (['', '宝飾品・時計', 'カメラ・スポーツ', '楽器・レコード', '書籍・マンガ', 'その他'] as $t): ?>
                    <option value="<?= $t ?>" <?= ($edit_item['category'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t === '' ? '選択してください' : $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>品名</label>
                    <input type="text" name="item_name" value="<?= htmlspecialchars($edit_item['item_name'] ?? '') ?>" placeholder="例：ダイヤモンドリング">
                </div>
                <div class="form-group">
                    <label>ブランド・メーカー</label>
                    <input type="text" name="brand" value="<?= htmlspecialchars($edit_item['brand'] ?? '') ?>" placeholder="例：Tiffany & Co.">
                </div>
                <div class="form-group">
                    <label>購入価格（円）</label>
                    <input type="text" name="purchase_price" value="<?= !empty($edit_item['purchase_price']) ? number_format($edit_item['purchase_price']) : '' ?>" placeholder="例：150,000" inputmode="numeric">
                </div>
                <div class="form-group">
                    <label>保管場所</label>
                    <input type="text" name="storage_location" value="<?= htmlspecialchars($edit_item['storage_location'] ?? '') ?>" placeholder="例：寝室クローゼット上段">
                </div>
                <div class="form-group">
                    <label>処分方法</label>
                    <select name="disposal">
                        <?php foreach (['', '特定の人に渡す', '売却する', '寄付する', '処分・廃棄', '家族に任せる'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($edit_item['disposal'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t === '' ? '選択してください' : $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>渡す相手（「特定の人に渡す」の場合）</label>
                    <input type="text" name="disposal_person" value="<?= htmlspecialchars($edit_item['disposal_person'] ?? '') ?>" placeholder="例：姉（山田花子）">
                </div>
                <div class="form-group">
                    <label>備考</label>
                    <textarea name="item_note"><?= htmlspecialchars($edit_item['item_note'] ?? '') ?></textarea>
                </div>

                <?php if ($edit_item): ?>
                    <button type="submit">更新する</button>
                    <a href="belongings.php" class="btn btn-secondary" style="margin-left:10px;">キャンセル</a>
                <?php else: ?>
                    <button type="submit">追加する</button>
                <?php endif; ?>
            </form>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>