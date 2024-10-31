<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Class_rfqwp
{

	protected $loader;
	protected $plugin_name;
	protected $version;
	public function __construct()
	{
		
		if (defined('PLUGIN_NAME_VERSION')) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'request-for-quotation';

		$this->load_dependencies();
		// $this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies()
	{	
		
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rfqwp-loader.php';
		$this->loader = new Class_rfqwp_Loader();

		// /**
		//  * The class responsible for defining internationalization functionality
		//  * of the plugin.
		//  */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-plugin-name-i18n.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-rfqwp-admin.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-rfqwp-public.php';

	}


	// private function set_locale() {
	// 	$plugin_i18n = new Plugin_Name_i18n();
	// 	$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	// }

	public function writeName()
	{
		echo 'name';
	}

	private function define_admin_hooks()
	{
		$plugin_admin = new Class_rfqwp_Admin($this->get_plugin_name(), $this->get_version());
	}

	private function define_public_hooks()
	{
		$plugin_public = new Class_rfqwp_Public($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
	}

	public function run()
	{
		$this->loader->run();
	}

	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	public function get_loader()
	{
		return $this->loader;
	}

	public function get_version()
	{
		return $this->version;
	}
}
