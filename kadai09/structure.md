kadai09/
│
├── index.php                   # トップ・ログイン画面
├── register.php                # 新規ユーザー登録画面
├── logout.php                  # ログアウト処理
│
├── user/                       # ユーザー画面
│   ├── dashboard.php           # ダッシュボード・開示設定
│   ├── profile.php             # 基本情報
│   ├── medical.php             # 医療・健康
│   ├── assets.php              # 財産
│   ├── digital.php             # デジタル
│   ├── work.php                # 仕事
│   ├── housing.php             # 賃貸・契約
│   ├── belongings.php          # 動産・コレクション
│   ├── funeral.php             # 葬儀
│   ├── contacts.php            # 人間関係
│   ├── will.php                # 遺言・形見
│   ├── messages.php            # 引受人へのメッセージ
│   ├── trustees.php            # 引受人設定・権限設定
│   └── settings.php            # アカウント設定
│
├── trustee/                    # 引受人画面
│   ├── login.php               # 引受人ログイン
│   ├── dashboard.php           # 閲覧トップ（開示チェック含む）
│   ├── view.php                # 各カテゴリ閲覧画面
│   └── logout.php              # 引受人ログアウト
│
├── includes/                   # 共通部品
│   ├── db.php                  # DB接続
│   ├── auth.php                # ログイン認証チェック
│   ├── header.php              # 共通ヘッダー
│   ├── footer.php              # 共通フッター
│   └── sidebar.php             # サイドバーナビゲーション
│
├── css/
│   └── style.css               # スタイルシート
│
├── js/
│   └── main.js                 # JavaScript
│
└── sql/
    └── create_tables.sql       # テーブル作成SQL