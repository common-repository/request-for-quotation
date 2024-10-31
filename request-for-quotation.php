<?php
/**
 * Plugin Name: Request for quotation
 * Plugin URI: https://rfqwp.peternyirideveloper.com/
 * Description: This is a custom form plugin
 * Version: 1.2.2
 * Author: Nyíri Péter
 * Author URI: peternyirideveloper.com/
 */


if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Activate
// Deactivate  
// Uninstall 
register_activation_hook(__FILE__, 'activate_request_for_quotation_wp_plugin');
register_deactivation_hook(__FILE__, 'deactivate_request_for_quotation_wp_plugin');
register_uninstall_hook( __FILE__, 'my_fn_uninstall' );

function activate_request_for_quotation_wp_plugin()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-rfqwp-activator.php';
	Class_rfqwp_Activator::activate();
}
function deactivate_request_for_quotation_wp_plugin()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-rfqwp-deactivator.php';
	Class_rfqwp_Deactivator::deactivate();
}

function my_fn_uninstall()
{
	dropAllDatabases();
}
function dropAllDatabases()
{
	global $wpdb;
	require_once plugin_dir_path(__FILE__) . 'includes/enum-rfqwp-tables.php';
	$DbTables = new Enum_rfqwp_database_tables();

	dropDatabase($wpdb, $DbTables->SETTINGS);
	dropDatabase($wpdb, $DbTables->EMAIL);
	dropDatabase($wpdb, $DbTables->SYSTEM_EMAIL);
	dropDatabase($wpdb, $DbTables->FORM);
	dropDatabase($wpdb, $DbTables->COLUMN);
	dropDatabase($wpdb, $DbTables->CARD);
	dropDatabase($wpdb, $DbTables->QUESTION);
	dropDatabase($wpdb, $DbTables->QUESTION_ITEM);
}
function dropDatabase($wpdb, $databaseName)
{
	$tablename = $wpdb->prefix . $databaseName;
	$sql = "DROP TABLE IF EXISTS $tablename";
	$wpdb->query($sql);
}


// call plugin main
function run_Class_rfqwp()
{
	require plugin_dir_path(__FILE__) . 'includes/class-rfqwp.php';
	$plugin = new Class_rfqwp();
	$plugin->run();
}
run_Class_rfqwp();
