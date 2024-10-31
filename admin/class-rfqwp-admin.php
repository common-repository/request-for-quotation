<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class Class_rfqwp_Admin
{

	private $plugin_name;

	private $version;

	public function __construct($plugin_name = '', $version = '')
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		if (is_admin()) {
			add_action('admin_menu', array($this, 'theme_options_panel'));
			$this->loadAdminDependencies();
		}
	}

	private function loadAdminDependencies()
	{
		require plugin_dir_path(__FILE__) . './class-rfqwp-mail.php';
		new Class_rfqwp_Mail();

		require plugin_dir_path(__FILE__) . './form/class-rfqwp-form-ajax-service.php';
		new FormAjaxService();

		require plugin_dir_path(__FILE__) . './class-rfqwp-admin-email-alert.php';
		new Class_rfqwp_Admin_email_alert();

		// require_once plugin_dir_path(__FILE__) . './form/class-rfqwp-form-dao.php';
		// $FormDaoImpl =new FormDaoImpl();
		// $FormDaoImpl->deleteForm(6);
	}

	function theme_options_panel()
	{
		add_menu_page(
			null,
			'RFQWP',
			null,
			'theme-options',
			null,
			plugins_url(basename(plugin_dir_path(dirname(__FILE__, 1))) . '/images/admin-icon.png'),
			6
		);
		add_submenu_page('theme-options', 'Email', 'Email', 'manage_options', 'rfqwp-email', array($this, 'getAdminApp'));
		add_submenu_page('theme-options', 'Forms', 'Forms', 'manage_options', 'rfqwp-form', array($this, 'getAdminApp'));
	}

	function getAdminApp()
	{
		// load premium application instead of basic application
		if (function_exists('getAdminAppPro')) {
			getAdminAppPro();
			return;
		}


		echo "<h1>Basic version</h1>";


		$pluginName = basename(plugin_dir_path(dirname(__FILE__, 1)));

		// get language
		$wpLocaleLanguage = get_bloginfo("language");
		$language = 'en';
		if (strpos($wpLocaleLanguage, 'hu') !== false) {
			$language = 'hu';
		} else if (strpos($wpLocaleLanguage, 'en')  !== false) {
			$language = 'en';
		}
		// else if (strpos($locale, 'de')) {
		// 	$language = 'de';
		// }
		// echo plugins_url(dirname(__FILE__, 1)); 

		// print wp language and files path
		$rootPath =  plugins_url() . '/' . $pluginName;
		wp_register_script('language-script', '');
		wp_add_inline_script('language-script', '<script> 
			var language="' . $language . '"; 
			var i18nRootPath ="' . $rootPath . '";
			var imagesPath ="' . $rootPath . '/admin/app";
			</script>');
		wp_enqueue_script('language-script');

		//material icons
		wp_enqueue_style(
			'material-icons',
			'https://fonts.googleapis.com/icon?family=Material+Icons'
		);

		// angular app
		wp_enqueue_script('main-es5', plugins_url($pluginName . '/admin/app/main-es5.js'), array('jquery'), '', false);
		wp_enqueue_script('polyfills-es5', plugins_url($pluginName . '/admin/app/polyfills-es5.js'), array('jquery'), '', false);
		wp_enqueue_script('runtime-es5', plugins_url($pluginName . '/admin/app/runtime-es5.js'), array('jquery'), '', false);
		wp_enqueue_style('styles', plugins_url($pluginName . '/admin/app/styles.css'), array(), '1.0', 'all');

		$html = '
			<base href="./">  
			<meta name="viewport" content="width=device-width, initial-scale=1">		
			<app-root></app-root>			
			';
		echo $html;
		return;
	}
}
