<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Class_rfqwp_Activator
{
	public static function activate()
	{
		require_once plugin_dir_path(__FILE__) . 'class-install-databases.php';
		$angular_Config =  new Class_Angular_Config();
		$angular_Config->startConfig();
	}
}
