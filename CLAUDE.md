# CLAUDE.md

このファイルは Claude Code がプロジェクトを正しく理解するためのコンテキストファイル。Claude Code を起動するたびに自動的に読み込まれる。

---

## プロジェクト概要

**名称**: Bank of Art（バンク・オブ・アート）公式WEBサイト リニューアル

**事業概要**: アート作品を「資産」として法人・事業者に提供するサービス。減価償却・即時償却の税制を活用し、若手画家を全作品買取制で支援。最大70%のリセール買取保証付き。

**ターゲット**: 中小企業、個人事業主等の事業家

**キャッチコピー**: 絵描きの明日を創出する。

**URL**: https://bankof-art.com（既存サイトを完全リプレース）

**運営**: 株式会社シクミーズ

---

## 技術スタック

### サーバー・インフラ
- **本番サーバー**: ConoHa WING
- **ドメイン**: bankof-art.com（取得済み・運用中）
- **ローカル開発環境**: Local by Flywheel

### CMS・フレームワーク
- **WordPress**（最新安定版、PHP 8.1以上推奨）
- **オリジナルテーマ**（既存テーマのカスタマイズではなく、ゼロから構築）
- **Meta Box AIO**（使い放題プラン契約済み）
  - MB Custom Post Type（CPT登録）
  - MB Relationships（作品⇔画家⇔企業の関連）
  - MB Frontend Submission（公開フォーム用）
  - MB Views（テンプレート補助）
  - MB Builder（管理画面UI構築）

### バージョン管理
- **GitHub**: https://github.com/sikumys809/bankofart
- **デプロイ**: 開発はLocal、本番はConoHa WING（手動FTP or Git pull想定）

### 開発ツール
- **エディタ**: VS Code + Claude Code拡張
- **コード規約**: WordPress Coding Standards準拠
- **言語**: PHP / HTML / CSS / JavaScript（Vanilla）

---

## デザインシステム

### カラートークン
```css
--brand: #01ae84;        /* ブランドメインカラー（緑） */
--brand-deep: #018c6a;   /* ホバー時の濃い緑 */
--brand-soft: rgba(1,174,132,.08);  /* 緑の薄いアクセント */
--ink: #212121;          /* メインテキスト（黒） */
--paper: #f5f0ea;        /* 背景ベージュ */
--dark: #212121;         /* ダークセクション背景 */
--mid: #6e6862;          /* サブテキスト */
--border: rgba(33,33,33,.18);
--border-soft: rgba(33,33,33,.08);
```

### フォント
```css
--f-display: 'Cormorant SC', serif;     /* 英大文字ディスプレイ */
--f-deco: 'Cinzel', serif;              /* 英字ラベル・小見出し */
--f-jp: 'Shippori Mincho B1', serif;    /* 日本語全般・数字 */
```

**Google Fonts読み込み**:
```html
<link href="https://fonts.googleapis.com/css2?family=Cormorant+SC:wght@400;500;700&family=Cinzel:wght@500;700&family=Shippori+Mincho+B1:wght@400;500;700;800&display=swap" rel="stylesheet">
```

### 厳格なテキストルール（Notion仕様準拠）
- **英字フォントはすべて大文字統一**（HEADER, MENU, ARTISTなど）
- **斜体（italic）使用禁止**（`font-style: italic` 全箇所で禁止）
- **字間（letter-spacing）は最大3px**
- **数字はすべて Shippori Mincho B1 を使用**（Cormorant SC は英字専用）
- **最小フォントサイズ**: 18px（モバイル375px以下は10px）
- **見出しサイズ**: 40px（モバイル24px）

### レスポンシブ基準
- **PC**: 1280px以上
- **タブレット**: 980px以下で1カラム化
- **モバイル**: 375px完全対応必須

### デザイン参考
- パリ、美術館、英字新聞の印象
- Louvre公式サイト、Louis Vuitton日本サイト

---

## ファイル構成

```
bankofart/
├── .git/
├── .gitignore
├── README.md
├── CLAUDE.md                    # ← このファイル
├── docs/                        # ドキュメント
│   ├── theme-structure.md       # テーマ構造詳細
│   ├── meta-box-fields.md       # Meta Boxフィールド定義
│   ├── notion-spec.md           # Notion仕様書のローカルコピー
│   └── implementation/          # 4つの実装指示書
│       ├── booking-system.md
│       ├── resale-waitlist.md
│       ├── document-request.md  # 資料請求フォーム
│       └── data-model.md        # 関連形態DB設計
├── mockups/                     # 元HTML（参照用、本番では使わない）
│   ├── index.html
│   ├── about.html
│   ├── artist.html
│   ├── ...
│   └── matching-issue.html
└── wp-content/
    └── themes/
        └── bankofart/           # オリジナルテーマ本体
            ├── style.css
            ├── functions.php
            ├── header.php
            ├── footer.php
            ├── front-page.php    # TOPページ（index.htmlベース）
            ├── page-about.php
            ├── page-recruit.php
            ├── page-matching-purpose.php
            ├── page-matching-issue.php
            ├── archive-artist.php
            ├── single-artist.php
            ├── archive-art.php
            ├── single-art.php
            ├── archive-collector.php
            ├── single-collector.php
            ├── archive-news.php
            ├── single-news.php
            ├── archive-journal.php
            ├── single-journal.php
            ├── 404.php
            ├── inc/              # PHP分割ファイル
            │   ├── post-types.php       # CPT登録（Meta Box補助）
            │   ├── taxonomies.php       # タクソノミー
            │   ├── meta-box-fields.php  # Meta Boxフィールド定義
            │   ├── relationships.php    # MB Relationships定義
            │   ├── enqueue.php          # アセット読み込み
            │   ├── customizer.php       # WP Customizer
            │   ├── shortcodes.php       # ショートコード
            │   └── helpers.php          # ヘルパー関数
            ├── template-parts/   # パーシャル
            │   ├── header-main.php
            │   ├── footer-main.php
            │   ├── card-artist.php
            │   ├── card-art.php
            │   ├── card-collector.php
            │   ├── card-news.php
            │   ├── matching-banner.php  # 診断バナー
            │   ├── for-artists-banner.php
            │   └── cta-contact.php
            ├── assets/
            │   ├── css/
            │   │   ├── reset.css
            │   │   ├── tokens.css       # デザイントークン
            │   │   ├── base.css
            │   │   ├── header.css
            │   │   ├── footer.css
            │   │   ├── components.css
            │   │   ├── pages/           # ページ別CSS
            │   │   │   ├── front.css
            │   │   │   ├── about.css
            │   │   │   ├── artist.css
            │   │   │   └── ...
            │   │   └── style.css        # 統合エントリ
            │   ├── js/
            │   │   ├── main.js
            │   │   ├── header.js        # ハンバーガーメニュー
            │   │   ├── simulator.js     # 税制シミュレーター
            │   │   ├── matching-purpose.js
            │   │   ├── matching-issue.js
            │   │   ├── filter.js        # ARTフィルター
            │   │   └── reveal.js        # スクロールリビール
            │   ├── img/                 # 固定画像（テーマ同梱）
            │   │   ├── logo/
            │   │   │   ├── boa-logo.svg
            │   │   │   ├── boa-09.png   # メインビジュアル系
            │   │   │   ├── boa-01.png 〜 boa-16.png
            │   │   │   └── char-01.png 〜 char-05.png
            │   │   ├── collector-logos/
            │   │   │   ├── SBS.png
            │   │   │   ├── AMASSY.png
            │   │   │   ├── ...
            │   │   │   └── BOAicon-15.png
            │   │   ├── icons/
            │   │   │   └── *.svg
            │   │   └── og-image.png
            │   └── fonts/               # ローカルホスト用（任意）
            └── languages/
                └── bankofart-ja.po
```

---

## カスタム投稿タイプ（CPT）

Meta BoxのCustom Post Typeで登録。`functions.php`から`inc/post-types.php`を読み込む。

| CPTスラッグ | 表示名 | URL構造 | 主用途 |
|---|---|---|---|
| `artist` | アーティスト | `/artist/{slug}/` | 公認画家・登録画家のプロフィール |
| `art` | 作品 | `/art/{slug}/` | 個別作品 |
| `collector` | 画家応援企業 | `/collector/{slug}/` | 導入企業の事例 |
| `news` | NEWS | `/news/{slug}/` | 最新記事（受賞・展示・メディア掲載） |
| `journal` | JOURNAL | `/journal/{slug}/` | 読み物（コラム・インタビュー） |

### 補足
- 通常の固定ページ：TOP, ABOUT, MATCHING各種, JOIN US, プライバシーポリシー
- 通常の `post`（投稿）は使わない

---

## カスタムタクソノミー

| タクソノミー | 対象CPT | 値 |
|---|---|---|
| `artist_status` | artist | 公認画家 / 登録画家 |
| `art_status` | art | AVAILABLE / OWNED |
| `art_genre` | art | 具象 / 抽象 / ポップアート / ストリートアート |
| `art_technique` | art | 油彩 / アクリル / 水彩 / 墨 / 日本画材 / ミクストメディア |
| `art_form` | art | 平面 / 立体 / 半立体 |
| `art_size` | art | 10号 / 20号 / 30号 / その他 |
| `art_color` | art | 赤 / 橙 / 黄 / 緑 / 青 / 紫 / 茶 / 白 / 黒 / 金 / 銀 / その他 |
| `artist_genre` | artist | 具象 / 抽象 / ストリートアート / ポップアート / 日本画 / 油彩 / アクリル / ミクストメディア |
| `collector_issue` | collector | モチベーション / 他社との差別化 / 取引先への印象 / 企業理念浸透 / 空間の活性化 |
| `news_category` | news | 受賞 / 展示 / メディア掲載 / お知らせ |
| `journal_category` | journal | コラム / インタビュー |

詳細フィールドは `docs/meta-box-fields.md` を参照。

---

## MB Relationships（関連付け）

Meta Box の MB Relationships を使う。

```php
MB_Relationships_API::register([
    'id'   => 'artist_to_art',
    'from' => [ 'object_type' => 'post', 'post_type' => 'artist' ],
    'to'   => [ 'object_type' => 'post', 'post_type' => 'art' ],
]);
MB_Relationships_API::register([
    'id'   => 'art_to_collector',
    'from' => [ 'object_type' => 'post', 'post_type' => 'art' ],
    'to'   => [ 'object_type' => 'post', 'post_type' => 'collector' ],
]);
MB_Relationships_API::register([
    'id'   => 'collector_to_art',  // 1社が複数作品所有
    'from' => [ 'object_type' => 'post', 'post_type' => 'collector' ],
    'to'   => [ 'object_type' => 'post', 'post_type' => 'art' ],
]);
```

詳細は `docs/implementation/data-model.md` を参照。

---

## 4つの追加システム（順次実装）

| # | システム | ステータス | 指示書 |
|---|---|---|---|
| 1 | オンライン説明会予約システム | 着手予定 | `docs/implementation/booking-system.md` |
| 2 | リセール待機リスト | 着手予定 | `docs/implementation/resale-waitlist.md` |
| 3 | 資料請求フォーム | 着手予定 | `docs/implementation/document-request.md` |
| 4 | 画家プロフィール承認システム | 着手予定 | `docs/implementation/artist-profile-approval.md` |

実装順序の推奨：資料請求 → オンライン説明会予約 → リセール待機リスト → 画家プロフィール承認

**重要：画家プロフィール承認システムは「公開=手動」の二段階フロー必須**。本名公開事故を防ぐため、承認しても自動で公開せず、管理者がWP標準の「公開」ボタンを別途クリックする設計を厳守する。

---

## コーディング規約

### PHP
- WordPress Coding Standards 準拠
- インデント: タブ
- 関数名: `bankofart_` プレフィックス必須（テーマ全体で名前空間衝突を避ける）
  - 例: `bankofart_get_artist_works()`, `bankofart_enqueue_assets()`
- セキュリティ:
  - 出力時は必ず `esc_html()` `esc_attr()` `esc_url()` でエスケープ
  - 入力時は `sanitize_text_field()` `wp_kses_post()` 等でサニタイズ
  - フォーム送信には `wp_nonce_field()` + `wp_verify_nonce()` 必須
- データベース操作:
  - `$wpdb` 直叩きは原則禁止（できる限り `WP_Query` 等を使う）
  - 必要な場合は `$wpdb->prepare()` を必ず使用

### CSS
- BEM風命名（過度に厳格にはしない）
- カラー・フォントは必ず CSS変数経由で参照（ハードコード禁止）
- メディアクエリは末尾にまとめず、各セレクタ近くに記述してもOK
- `!important` は最終手段

### JavaScript
- Vanilla JS（jQuery禁止）
- ES6+構文OK（const/let、アロー関数、async/await）
- IIFEで namespace 汚染を避ける `(function(){ 'use strict'; ... })();`
- イベントリスナーは `DOMContentLoaded` 以降に登録

### HTML
- `<script>` `<style>` のインライン記述は基本的に避け、外部ファイル化
- ただし `front-page.php` 等の単体ページに閉じた小規模なものは可

---

## アセット読み込みルール

`inc/enqueue.php` に集約。条件分岐で必要なページにだけ読み込む。

```php
function bankofart_enqueue_assets() {
    $ver = wp_get_theme()->get('Version');

    // 全ページ共通
    wp_enqueue_style('bankofart-fonts', 'https://fonts.googleapis.com/...');
    wp_enqueue_style('bankofart-base', get_stylesheet_directory_uri() . '/assets/css/style.css', [], $ver);
    wp_enqueue_script('bankofart-main', get_stylesheet_directory_uri() . '/assets/js/main.js', [], $ver, true);

    // ABOUT ページのみ
    if (is_page('about')) {
        wp_enqueue_script('bankofart-simulator', get_stylesheet_directory_uri() . '/assets/js/simulator.js', [], $ver, true);
    }

    // ART一覧のみ
    if (is_post_type_archive('art')) {
        wp_enqueue_script('bankofart-filter', get_stylesheet_directory_uri() . '/assets/js/filter.js', [], $ver, true);
    }

    // マッチング診断
    if (is_page('matching-purpose')) {
        wp_enqueue_script('bankofart-matching-purpose', get_stylesheet_directory_uri() . '/assets/js/matching-purpose.js', [], $ver, true);
    }
}
add_action('wp_enqueue_scripts', 'bankofart_enqueue_assets');
```

---

## 重要な開発ルール

### 禁止事項

❌ **既存テーマ（WordPressデフォルト等）をベースにしない**  
- 必ずゼロからオリジナルテーマとして構築

❌ **jQuery、Bootstrap、Tailwindの導入禁止**  
- Vanilla JS + Vanilla CSS のみ

❌ **無闇なプラグイン追加禁止**  
- 必須プラグイン以外はクライアントに相談してから追加

❌ **Meta Box以外のカスタムフィールドプラグイン併用禁止**  
- ACFやPodsは使わない（Meta Box AIOで完結）

❌ **直接FTP本番アップでの即時反映禁止**  
- 必ずローカルで動作確認 → GitHubコミット → デプロイ

❌ **画家の本名・住所等の非公開情報をテンプレートに出力しない**  
- 非公開フィールドは `is_user_logged_in()` & 権限チェック必須

### 推奨事項

✅ **`docs/` 内のドキュメントを常に最新に保つ**  
- 仕様変更時は対応するMDファイルも同時更新

✅ **コミットメッセージは日本語OK、ただし内容を明確に**  
- 例: `feat: ARTISTカスタム投稿タイプ登録` / `fix: フッターtaglineを修正`

✅ **PR/コミット単位を小さく**  
- 1コミット1機能が理想

✅ **CSSは page-別に分離**  
- 1つの巨大style.cssに全部詰め込まない

---

## 必須プラグイン

| プラグイン名 | 用途 | 必須/推奨 |
|---|---|---|
| Meta Box AIO | カスタムフィールド全般 | **必須** |
| WP All Import Pro | アーティスト・作品データCSV投入 | **必須** |
| WP Mail SMTP | メール送信安定化（ConoHaのSMTP設定） | **必須** |
| All in One SEO Pack または Yoast SEO | SEO対策 | 推奨 |
| BackWPup | バックアップ自動化 | 推奨 |
| Wordfence Security | セキュリティ | 推奨 |

---

## データ初期投入（CSV）

20名のアーティスト、各作品データはCSVインポートで初期投入。

### CSVファイル設計

#### `imports/artists.csv`
```csv
post_title,post_name,artist_name_en,catch_phrase,theme_short,theme_long,bio,status,genre,instagram,x,facebook,youtube,other_url,thumbnail
東 樹生,azuma-jusei,AZUMA JUSEI,ADRENALINE ARTIST,身体で叩き込む唯一無二の表現,神戸芸術工科大学で学んだ東は...,1998年神戸生まれ。神戸芸術工科大学卒...,公認画家,具象;油彩,https://...,,,,,/wp-content/uploads/2026/artist-azuma.jpg
```

#### `imports/arts.csv`
```csv
post_title,post_name,artist_slug,year,medium,size_label,form,genre,technique,main_color,status,thumbnail
ADRENALINE ART 昇華 VOL.5,adrenaline-art-vol5,azuma-jusei,2023,アクリル/キャンバス,F10,平面,抽象,アクリル,赤,AVAILABLE,/wp-content/uploads/2026/art-adrenaline-vol5.jpg
```

WP All Import Pro でこれらを取り込み、Meta Box フィールドにマッピング。詳細は `docs/meta-box-fields.md`。

---

## Local by Flywheel セットアップ

```
サイト名: bankofart-local
ドメイン: bankofart.local
PHP: 8.1
Webサーバー: nginx
データベース: MySQL 8.0
WordPress: 最新版
```

### 開発フロー

```bash
# Local の Site Shell で
cd ~/Local Sites/bankofart-local/app/public/wp-content/themes
git clone https://github.com/sikumys809/bankofart.git
cd bankofart

# Claude Code起動
claude
```

---

## GitHub運用

### ブランチ戦略
- `main`: 本番反映ブランチ
- `develop`: 開発統合ブランチ
- `feature/*`: 機能ブランチ
- `fix/*`: バグ修正ブランチ

### .gitignore に必ず入れるもの
```
/wp-config.php
/wp-content/uploads/
/wp-content/cache/
/wp-content/upgrade/
/wp-content/backup-db/
/wp-content/debug.log
/.DS_Store
/node_modules/
.env
*.local
```

---

## デプロイ手順（暫定）

```
1. ローカルで動作確認
2. git add . && git commit -m "..."
3. git push origin main
4. ConoHa WING のテーマフォルダにて git pull
   （or 手動FTP）
5. WP管理画面でキャッシュクリア
6. 本番サイトで動作確認
```

将来的には GitHub Actions による自動デプロイ導入を検討。

---

## 質問・相談時のお作法

Claude Code に質問する際は、以下のコンテキストを意識する：

- **どのファイルの**話なのか
- **何を達成したい**のか（ユーザー視点で）
- **既に試したこと**があれば共有
- **エラーログ**があれば貼る

例:
```
✅ Good: `single-artist.php`でMB Relationshipsの作品リストを取得して表示したい。MB_Relationships_API::get_connected()を試したが空配列が返る。コードはこれ：[code]
❌ Bad: アーティストの作品が表示されない
```

---

## 用語集

| 用語 | 意味 |
|---|---|
| BOA | Bank of Art の略称 |
| 公認画家 | BOAと専属契約を結んだ代表作家 |
| 登録画家 | BOAと作品ごとの買取契約を結ぶ画家（応募窓口） |
| コレクト | 企業がアートを取得すること |
| リセール | コレクト済み作品をBOAが買い戻すこと（30〜70%） |
| OWNED | 既に企業が所有している作品ステータス |
| AVAILABLE | 販売可能ステータス |
| 即時償却 | 1点40万円未満を取得年に全額経費計上できる特例 |
| 減価償却 | 1年〜8年で経費計上する通常の処理 |
| マッチング | パーパス診断・課題逆引き診断の総称 |

---

## 過去の経緯

このプロジェクトは Claude.ai でのモックアップ制作フェーズ（HTML 13ページ＋シミュレーター＋診断機能）を経て、本格実装フェーズに入った。  

過去のNotion仕様書（マスター）と HTMLモックアップ（実装版）の間で表記揺れが発生しているため、**HTMLモックアップ（`mockups/` 配下）を正とする**。差分があった場合は HTMLモックアップに従う。

主な仕様変更（HTMLが正）:
- TOPキャッチコピー: 「絵描きの明日を創出する。」
- 4 STEPS のタイトル: 「作品を預ける」「企業が購入」（Notion: 「コレクト」「減価償却」）
- 「公認アーティスト一覧」（Notion: 「公認画家一覧」）
- 「絵画償却制度のルール」（Notion: 「絵画減価償却のルール」）
- MAIN COLOR: 「金/銀/その他」（Notion: 「ゴールド/シルバー/カラフル」）
- フッターtagline: 「絵描きの明日を創出する。/ 減価償却 × 画家応援」

---

## 連絡先

**運営**: 株式会社シクミーズ  
**代表**: 水野 永吉  
**GitHub**: https://github.com/sikumys809/bankofart
