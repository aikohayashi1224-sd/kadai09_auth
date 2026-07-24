<?php

$page_title = 'ダッシュボード';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$user_id = $_SESSION['user_id'];

// 引受人ごとの開示切り替え処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trustee_id = (int)($_POST['trustee_id'] ?? 0);
    $disclose   = $_POST['disclose'] ?? '';

    if ($trustee_id && ($disclose === '1' || $disclose === '0')) {
        $stmt = $pdo->prepare("UPDATE trustees SET is_disclosed = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([(int)$disclose, $trustee_id, $user_id]);
    }
}

// 引受人一覧と開示状態を取得
$stmt = $pdo->prepare("SELECT id, name, relationship, is_disclosed FROM trustees WHERE user_id = ? ORDER BY priority");
$stmt->execute([$user_id]);
$trustees = $stmt->fetchAll();

// 生まれてから何日目か計算
$birth_days = null;
$stmt = $pdo->prepare("SELECT birth_date FROM profile WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile_data = $stmt->fetch();

if (!empty($profile_data['birth_date'])) {
    $birth = new DateTime($profile_data['birth_date']);
    $today = new DateTime('today');
    $diff  = $today->diff($birth);
    $birth_days = (int)$diff->days + 1;
}

// 入力進捗チェック
$sections = [
    'profile'    => ['label' => '基本情報',           'multi' => false],
    'medical'    => ['label' => '医療・健康',           'multi' => false],
    'assets'     => ['label' => '財産',               'multi' => false],
    'digital'    => ['label' => 'デジタル',            'multi' => false],
    'work'       => ['label' => '仕事',               'multi' => false],
    'housing'    => ['label' => '賃貸・契約',           'multi' => false],
    'belongings' => ['label' => '動産・コレクション',    'multi' => true],
    'funeral'    => ['label' => '葬儀',               'multi' => false],
    'contacts'   => ['label' => '人間関係',            'multi' => true],
    'will'       => ['label' => '遺言・形見',           'multi' => false],
    'messages'   => ['label' => '引受人へのメッセージ', 'multi' => true],
];

// 1件テーブル：代表カラムに値があるかで判定
$check_columns = [
    'profile'  => 'last_name',
    'medical'  => 'doctor_name',
    'assets'   => 'bank_name',
    'digital'  => 'email_address',
    'work'     => 'company_name',
    'housing'  => 'housing_type',
    'funeral'  => 'funeral_type',
    'will'     => 'will_exists',
];

$filled = 0;
foreach ($sections as $key => &$sec) {
    if ($sec['multi']) {
        // 複数行テーブル：行が1件以上あればOK
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$key} WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $sec['done'] = (int)$stmt->fetchColumn() > 0;
    } else {
        // 1件テーブル：代表カラムが空でないかチェック
        $col  = $check_columns[$key];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$key} WHERE user_id = ? AND {$col} != ''");
        $stmt->execute([$user_id]);
        $sec['done'] = (int)$stmt->fetchColumn() > 0;
    }
    if ($sec['done']) $filled++;
}
unset($sec);


$total   = count($sections);
$percent = (int)round($filled / $total * 100);

require_once '../includes/header.php';
?>

<div class="layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <h2>ようこそ、<?= htmlspecialchars($_SESSION['user_name']) ?>さん</h2>
            <p>左のメニューから各項目を入力・編集できます。</p>
        </div>

        <!-- 今日で何日 -->
        <?php if ($birth_days): ?>
        <div class="card" style="background: linear-gradient(135deg, #fde8f0, #e8f4fd); border: none;">
        <p style="font-size: 0.85rem; color: #999; margin-bottom: 4px;">今日のあなた</p>
        <p style="font-size: 2rem; font-weight: bold; color: #c0788a; margin: 0;">
            生まれてから <span style="font-size: 2.5rem;"><?= number_format($birth_days) ?></span> 日目
        </p>
        <p style="font-size: 0.9rem; color: #a0b8c8; margin-top: 8px;">
             人生はたくさんの思い出と共に。
        </p>
    </div>
    
    <?php endif; ?>


        <!-- 入力進捗 -->
        <div class="card">
            <h3>入力進捗</h3>
            <p style="font-size:1.1rem; margin-bottom:8px;">
                <strong><?= $filled ?> / <?= $total ?> カテゴリ入力済み（<?= $percent ?>%）</strong>
            </p>

            <!-- プログレスバー -->
            <div style="background:#e0e0e0; border-radius:8px; overflow:hidden; height:18px; margin-bottom:20px;">
                <div style="width:<?= $percent ?>%; background:#c5a059; height:100%; border-radius:8px; transition:width 0.4s;"></div>
            </div>

            <!-- カテゴリ別一覧 -->
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap:10px;">
                <?php foreach ($sections as $key => $sec): ?>
                <a href="<?= $key ?>.php" style="text-decoration:none;">
                    <div style="
                        padding:10px 14px;
                        border-radius:6px;
                        border:1px solid <?= $sec['done'] ? '#c5a059' : '#ddd' ?>;
                        background:<?= $sec['done'] ? '#fdf6e9' : '#fafafa' ?>;
                        color:<?= $sec['done'] ? '#7a5c1e' : '#999' ?>;
                        font-size:0.88rem;
                        display:flex;
                        align-items:center;
                        gap:8px;
                    ">
                        <span><?= $sec['done'] ? '✓' : '○' ?></span>
                        <span><?= htmlspecialchars($sec['label']) ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 引受人への開示設定 -->
        <div class="card">
            <h3>引受人への開示設定</h3>
            <p style="color:#777; font-size:0.9rem; margin-bottom:16px;">
                開示を許可すると、その引受人が登録情報を閲覧できるようになります。
            </p>

            <?php if (empty($trustees)): ?>
                <p style="color:#999;">
                    引受人が登録されていません。
                    <a href="trustees.php">引受人設定</a>から登録してください。
                </p>
            <?php else: ?>
                <?php foreach ($trustees as $t): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 0; border-bottom:1px solid #eee;">
                    <div>
                        <strong><?= htmlspecialchars($t['name']) ?>さん</strong>
                        <span style="color:#777; font-size:0.9rem;">（<?= htmlspecialchars($t['relationship']) ?>）</span><br>
                        <?php if ($t['is_disclosed']): ?>
                            <span style="color:#27ae60; font-size:0.85rem;">開示中</span>
                        <?php else: ?>
                            <span style="color:#999; font-size:0.85rem;">非開示</span>
                        <?php endif; ?>
                    </div>
                    <form method="post">
                        <input type="hidden" name="trustee_id" value="<?= $t['id'] ?>">
                        <?php if ($t['is_disclosed']): ?>
                            <input type="hidden" name="disclose" value="0">
                            <button type="submit" class="btn btn-secondary"
                                onclick="return confirm('<?= htmlspecialchars($t['name']) ?>さんの開示を取り消しますか？')"
                                style="padding:8px 16px; font-size:0.85rem;">
                                取り消す
                            </button>
                        <?php else: ?>
                            <input type="hidden" name="disclose" value="1">
                            <button type="submit"
                                onclick="return confirm('<?= htmlspecialchars($t['name']) ?>さんへの開示を許可しますか？')"
                                style="padding:8px 16px; font-size:0.85rem;">
                                開示を許可する
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>