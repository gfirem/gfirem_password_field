<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFireMPasswordManager {

	public function __construct() {

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'fs_is_submenu_visible_' . GFireMPassword::getSlug(), array( $this, 'handle_sub_menu' ), 10, 2 );

		require_once 'class-gfirem-password-logs.php';
		new GFireMPasswordLogs();

		try {
			//Check formidable pro
			if ( class_exists( 'FrmAppHelper' ) && method_exists( 'FrmAppHelper', 'pro_is_installed' )
			     && FrmAppHelper::pro_is_installed() ) {
				if ( GFireMPassword::getFreemius()->is_paying() ) {
					//Implements here
				}
			} else {
				add_action( 'admin_notices', array( $this, 'required_formidable_pro' ) );
			}
		} catch ( Exception $ex ) {
			GFireMPasswordLogs::log( array(
				'action'         => get_class( $this ),
				'object_type'    => GFireMPassword::getSlug(),
				'object_subtype' => 'loading_dependency',
				'object_name'    => $ex->getMessage(),
			) );
		}
	}

	public function required_formidable_pro() {
		require GFireMPassword::$view . 'formidable_notice.php';
	}

	public static function load_plugins_dependency() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	public static function is_formidable_active() {
		self::load_plugins_dependency();

		return is_plugin_active( 'formidable/formidable.php' );
	}

	/**
	 * Handle freemius menus visibility
	 *
	 * @param $is_visible
	 * @param $menu_id
	 *
	 * @return bool
	 */
	public function handle_sub_menu( $is_visible, $menu_id ) {
		if ( $menu_id == 'account' ) {
			$is_visible = false;
		}

		return $is_visible;
	}

	/**
	 * Adding the Admin Page
	 */
	public function admin_menu() {
		add_menu_page( __( 'Password', 'gfirem_password-locale' ), __( 'Password', 'gfirem_password-locale' ), 'manage_options', GFireMPassword::getSlug(), array( $this, 'screen' ), 'dashicons-post-status' );
	}

	/**
	 * Screen to admin page
	 */
	public function screen() {
		GFireMPassword::getFreemius()->get_logger()->entrance();
		GFireMPassword::getFreemius()->_account_page_load();
		GFireMPassword::getFreemius()->_account_page_render();
	}
}