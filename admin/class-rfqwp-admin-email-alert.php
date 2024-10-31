<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Class_rfqwp_Admin_email_alert
{
	public $dbTables;
	public function __construct()
	{
		require_once plugin_dir_path(realpath(dirname(__FILE__))) . 'includes/enum-rfqwp-tables.php';
		$this->dbTables = new Enum_rfqwp_database_tables();

		add_action('wp_ajax_rfqwpGetAdminAlertEmail', array($this, 'getAdminAlertEmailFunc'));
		add_action('wp_ajax_rfqwpUpdateAdminAlertEmail', array($this, 'updateAdminAlertEmailFunc'));
	}

	public function updateAdminAlertEmailFunc()
	{

		$allowed_html = array(
			'a' => array(
				'href' => array(),
				'title' => array()
			),
			'br' => array(),
			'em' => array(),
			'strong' => array(),
			'span' => array(
				'style' => true
			),
			'div' => array(
				'style' => true
			),
			'img' => array(
				'alt' => true,
				'style' => true,
				'align' => true,
				'border' => true,
				'height' => true,
				'hspace' => true,
				'longdesc' => true,
				'vspace' => true,
				'src' => true,
				'usemap' => true,
				'width' => true
			),
			'table' => array(
				'style' => true,
				'align' => true,
				'bgcolor' => true,
				'border' => true,
				'cellpadding' => true,
				'cellspacing' => true,
				'dir' => true,
				'rules' => true,
				'summary' => true,
				'width' => true,
			),
			'tbody' => array(
				'style' => true,
				'align' => true,
				'char' => true,
				'charoff' => true,
				'valign' => true,
			),
			'td' => array(
				'style' => true,
				'abbr' => true,
				'align' => true,
				'axis' => true,
				'bgcolor' => true,
				'char' => true,
				'charoff' => true,
				'colspan' => true,
				'dir' => true,
				'headers' => true,
				'height' => true,
				'nowrap' => true,
				'rowspan' => true,
				'scope' => true,
				'valign' => true,
				'width' => true,
			),
			'th' => array(
				'style' => true,
				'abbr' => true,
				'align' => true,
				'axis' => true,
				'bgcolor' => true,
				'char' => true,
				'charoff' => true,
				'colspan' => true,
				'headers' => true,
				'height' => true,
				'nowrap' => true,
				'rowspan' => true,
				'scope' => true,
				'valign' => true,
				'width' => true,
			),
			'thead' => array(
				'style' => true,
				'align' => true,
				'char' => true,
				'charoff' => true,
				'valign' => true,
			),
			'title' => array(
				'style' => true
			),
			'tr' => array(
				'style' => true,
				'align' => true,
				'bgcolor' => true,
				'char' => true,
				'charoff' => true,
				'valign' => true,
			)



		);

		$id = sanitize_text_field($_POST['id']);
		
		$content = isset($_POST['content']) ? wp_kses($_POST['content'], $allowed_html) : null;
		$title = isset($_POST['title']) ? wp_kses($_POST['title'], $allowed_html) : null;

		global $wpdb;
		$tablename = $wpdb->prefix . $this->dbTables->SYSTEM_EMAIL;
		$fSuccess = $wpdb->update(
			$tablename,
			array(
				'title' => stripslashes($title),
				'content' => stripslashes($content)
			),
			array('id' => $id)
		);
		echo sprintf('%s', $fSuccess ? true : false);
	}

	public function getAdminAlertEmailFunc()
	{
		$formConfigId = sanitize_text_field($_POST['formConfigId']);
		$emailType = sanitize_textarea_field($_POST['emailType']);
		global $wpdb;
		$tablename = $wpdb->prefix . $this->dbTables->SYSTEM_EMAIL;
		$result = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM {$tablename} WHERE `rfqwp_config_json`=%d AND `type`=%s", $formConfigId, $emailType)
		);
		echo json_encode($result[0]);
		exit;
	}
}
