<?php
/**
 * 資料請求フォーム：管理画面（一覧・検索・ステータス変更・詳細）仕様§8
 *
 * @package bankofart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 管理メニュー追加。
 *
 * @return void
 */
function bankofart_doc_request_admin_menu() {
	add_menu_page(
		'資料請求管理',
		'資料請求管理',
		'manage_options',
		'boa-document-requests',
		'bankofart_doc_request_admin_page',
		'dashicons-media-document',
		25
	);
}
add_action( 'admin_menu', 'bankofart_doc_request_admin_menu' );

/**
 * 申込一覧ページ。
 *
 * @return void
 */
function bankofart_doc_request_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( '権限がありません。', 'bankofart' ) );
	}

	global $wpdb;
	$table    = bankofart_doc_request_table();
	$statuses = bankofart_doc_request_statuses();

	// ステータス更新／メモ更新（POST）。
	$updated = false;
	if ( isset( $_POST['boa_dr_update'] ) ) {
		check_admin_referer( 'boa_dr_update', 'boa_dr_update_nonce' );
		$row_id     = isset( $_POST['row_id'] ) ? absint( $_POST['row_id'] ) : 0;
		$new_status = isset( $_POST['new_status'] ) ? sanitize_text_field( wp_unslash( $_POST['new_status'] ) ) : '';
		$notes      = isset( $_POST['admin_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['admin_notes'] ) ) : '';
		if ( $row_id && isset( $statuses[ $new_status ] ) ) {
			$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$table,
				array(
					'status'      => $new_status,
					'admin_notes' => $notes,
					'updated_at'  => current_time( 'mysql' ),
				),
				array( 'id' => $row_id ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);
			$updated = true;
		}
	}

	// 検索条件。
	$s        = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 読み取り専用検索.
	$f_status = isset( $_GET['fstatus'] ) ? sanitize_text_field( wp_unslash( $_GET['fstatus'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$where  = 'WHERE 1=1';
	$params = array();
	if ( '' !== $s ) {
		$like    = '%' . $wpdb->esc_like( $s ) . '%';
		$where  .= ' AND ( company_name LIKE %s OR contact_name LIKE %s OR email LIKE %s OR industry LIKE %s )';
		$params  = array( $like, $like, $like, $like );
	}
	if ( isset( $statuses[ $f_status ] ) ) {
		$where  .= ' AND status = %s';
		$params[] = $f_status;
	}

	$per_page = 30;
	$paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$offset   = ( $paged - 1 ) * $per_page;

	$count_sql = "SELECT COUNT(*) FROM {$table} {$where}";
	$total     = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : $wpdb->get_var( $count_sql ) ); // phpcs:ignore WordPress.DB

	$list_sql    = "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d";
	$list_params = array_merge( $params, array( $per_page, $offset ) );
	$rows        = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_params ) ); // phpcs:ignore WordPress.DB
	$pages       = max( 1, (int) ceil( $total / $per_page ) );

	$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=boa_doc_request_export' ), 'boa_dr_export', 'boa_dr_export_nonce' );
	?>
	<div class="wrap">
		<h1>資料請求管理 <span class="title-count theme-count"><?php echo esc_html( (string) $total ); ?></span></h1>
		<?php if ( $updated ) : ?>
			<div class="notice notice-success is-dismissible"><p>更新しました。</p></div>
		<?php endif; ?>

		<form method="get" style="margin:12px 0;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
			<input type="hidden" name="page" value="boa-document-requests">
			<input type="search" name="s" value="<?php echo esc_attr( $s ); ?>" placeholder="会社名・氏名・メール・業種">
			<select name="fstatus">
				<option value="">すべてのステータス</option>
				<?php foreach ( $statuses as $val => $label ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $f_status, $val ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="button">検索</button>
			<a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary">CSVダウンロード</a>
		</form>

		<table class="widefat striped">
			<thead>
				<tr>
					<th>申込日時</th><th>会社名</th><th>担当者</th><th>業種</th><th>興味度</th>
					<th>連絡先</th><th>DL</th><th>ステータス / メモ</th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="8">該当する申込はありません。</td></tr>
			<?php else : ?>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row->created_at ); ?></td>
						<td><strong><?php echo esc_html( $row->company_name ); ?></strong></td>
						<td><?php echo esc_html( $row->contact_name ); ?><br><span style="color:#888;"><?php echo esc_html( $row->contact_name_kana ); ?></span></td>
						<td><?php echo esc_html( $row->industry ); ?></td>
						<td><?php echo esc_html( $row->interest_level ); ?></td>
						<td><?php echo esc_html( $row->email ); ?><br><?php echo esc_html( $row->phone ); ?></td>
						<td><?php echo $row->pdf_download_count > 0 ? 'DL済(' . esc_html( (string) $row->pdf_download_count ) . ')' : '未DL'; ?></td>
						<td>
							<form method="post" style="display:flex;flex-direction:column;gap:6px;min-width:220px;">
								<?php wp_nonce_field( 'boa_dr_update', 'boa_dr_update_nonce' ); ?>
								<input type="hidden" name="row_id" value="<?php echo esc_attr( (string) $row->id ); ?>">
								<select name="new_status">
									<?php foreach ( $statuses as $val => $label ) : ?>
										<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $row->status, $val ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<textarea name="admin_notes" rows="2" placeholder="管理者メモ"><?php echo esc_textarea( (string) $row->admin_notes ); ?></textarea>
								<?php if ( $row->message ) : ?>
									<details><summary>ご質問・ご要望</summary><div style="white-space:pre-wrap;color:#555;"><?php echo esc_html( $row->message ); ?></div></details>
								<?php endif; ?>
								<button type="submit" name="boa_dr_update" value="1" class="button button-small">更新</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $pages > 1 ) : ?>
			<div class="tablenav"><div class="tablenav-pages">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'base'    => add_query_arg( 'paged', '%#%' ),
							'format'  => '',
							'current' => $paged,
							'total'   => $pages,
						)
					)
				);
				?>
			</div></div>
		<?php endif; ?>
	</div>
	<?php
}
