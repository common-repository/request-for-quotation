<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Class_rfqwp_Mail
{

	public function __construct()
	{
		add_action('wp_ajax_rfqwpFindPageEmailList', array($this, 'rfqwpFindPageEmailListFunc'));
		add_action('wp_ajax_rfqwpGetEmailById', array($this, 'rfqwpGetEmailByIdFunc'));
		add_action('wp_ajax_rfqwpDeleteEmailsByIds', array($this, 'rfqwpDeleteEmailsByIdsFunc'));
	}

	function rfqwpDeleteEmailsByIdsFunc()
	{
		$emailIds = sanitize_text_field($_POST['emailIds']);
		$emailIds = json_decode(preg_replace("/\r|\n|\t|\s{2,}/", "", stripslashes($emailIds)), true);
		$sqlEmailStatement = '%s';
		foreach ($emailIds as $id) {
			$sqlEmailStatement .= ', %s';
		}
		global $wpdb;
		$table_name = $wpdb->prefix . 'rfqwp_user_email';
		$result = $wpdb->get_results(
			$wpdb->prepare("DELETE FROM {$table_name} WHERE id IN (" . $sqlEmailStatement . ")", array_merge([0], $emailIds))
		);
		echo 'true';
		exit;
	}

	public function rfqwpFindPageEmailListFunc()
	{
		require_once plugin_dir_path(realpath(dirname(__FILE__))) . 'includes/enum-rfqwp-tables.php';
		$dbTables = new Enum_rfqwp_database_tables();
		global $wpdb;

		$formTableName = $wpdb->prefix . $dbTables->FORM;

		$from = sanitize_text_field($_POST['pageIndex']);
		$limit = sanitize_text_field($_POST['pageSize']);
		$searchEmail = sanitize_text_field($_POST['searchEmail']);
		$selectedFormConfigurationId = sanitize_text_field($_POST['selectedFormConfiguration']);
		$newsletter = sanitize_text_field($_POST['newsletter']);

		$EmailTablename = $wpdb->prefix . "rfqwp_user_email";
		// $all_row_result = $wpdb->get_results("SELECT COUNT(*) AS 'all_row_number' FROM  {$EmailTablename} ");
		$all_row_result = $wpdb->get_results(
			$wpdb->prepare("SELECT 
			COUNT({$formTableName}.name) AS 'all_row_number'			
			 FROM {$formTableName},{$EmailTablename} WHERE {$formTableName}.id = {$EmailTablename}.rfqwp_config_json_id 
			 AND {$EmailTablename}.email LIKE %s  
			 AND {$formTableName}.id LIKE %s  
			 AND {$EmailTablename}.newsletter LIKE %s 
			 ORDER BY {$EmailTablename}.id DESC",
			  '%' . $wpdb->esc_like($searchEmail) . '%', 
			  '%' . $wpdb->esc_like($selectedFormConfigurationId) . '%', 
			  '%' . $wpdb->esc_like($newsletter) . '%')
		);
		$allRowNumber = $all_row_result[0]->{"all_row_number"};

		$result = $wpdb->get_results(
			$wpdb->prepare("SELECT 
			{$formTableName}.name,
			{$EmailTablename}.*
			 FROM {$formTableName},{$EmailTablename} WHERE {$formTableName}.id = {$EmailTablename}.rfqwp_config_json_id 
			 AND {$EmailTablename}.email LIKE %s 
			 AND {$formTableName}.id LIKE %s 
			 AND {$EmailTablename}.newsletter LIKE %s 
			 ORDER BY {$EmailTablename}.id DESC limit %d,%d;",
			  '%' . $wpdb->esc_like($searchEmail) . '%', 
			  '%' . $wpdb->esc_like($selectedFormConfigurationId) . '%', 
			  '%' . $wpdb->esc_like($newsletter) . '%', 
			  $from, 
			  $limit)
		);

		$emailResultRemap = [];
		foreach($result as $email) {
			$email->{'variables'} = json_decode($email->{'variables'});
			array_push($emailResultRemap, $email);
		}


		// echo $wpdb->last_query;
		// exit;
		$response['all'] = $allRowNumber;
		$response['emailList'] = $result;


		echo json_encode($response);
		exit;
	}

	public function rfqwpGetEmailByIdFunc()
	{
		require_once plugin_dir_path(realpath(dirname(__FILE__))) . 'includes/enum-rfqwp-tables.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rfqwp-form-email.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/form/class-rfqwp-form-dao.php';
		$dbTables = new Enum_rfqwp_database_tables();
		$class_rfqwp_form_email = new Class_rfqwp_form_email();
		$formDaoInstance = new FormDaoImpl();

		$emailId = sanitize_text_field($_POST['emailId']);
		// get email 
		global $wpdb;
		$table_name = $wpdb->prefix . $dbTables->EMAIL;
		$result = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM {$table_name} WHERE id=%d", $emailId)
		);
		$response = $result[0];
		// print_r($result[0]->{'content'});
		// exit();
		// get form configuration
		// $json_table_result=  $formDaoInstance->getFormById($response->{'rfqwp_config_json_id'});
		// if (!isset($json_table_result->{'id'})) {
		// 	return '';
		// }

		// generate email
		// require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rfqwp-form-email.php';
		// $class_rfqwp_form_email = new Class_rfqwp_form_email();
		// $generatedHtml = $class_rfqwp_form_email->generateEmail(
		// 	json_decode(json_encode($json_table_result),true),
		// 	json_decode($response->{'content'}, true),
		// 	$response->{'token'}
		// );

		$response->{'generatedHtml'} = $result[0]->{'content'};
		$response->{'content'} = $this->generateContentFromDbContent(json_decode($result[0]->{'variables'}, true));
		// $response->{'generatedHtml'} = 	$generatedHtml['htmlEmailContent'];

		echo json_encode($response);
		exit;
	}

	function generateContentFromDbContent($dbContent)
	{
		$content = [];
		foreach ($dbContent as $key => $value) {
			array_push($content, array('id' => $key, 'value' => $value));
		}
		return $content;
	}
}
