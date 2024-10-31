<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class Class_rfqwp_Public
{
	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// call: /wp-json/custom-form-api/config
		add_action('rest_api_init', function () {
			register_rest_route('custom-form-api', 'config', [
				'methods' => 'GET',
				'callback' => array($this, 'get_request_config_json')
			]);
		});
		// call: /wp-json/custom-form-api/new-form-request
		add_action('rest_api_init', function () {
			register_rest_route('custom-form-api', 'new-form-request', [
				'methods' => 'POST',
				'callback' => array($this, 'newFormRequest')
			]);
		});
	}


	function get_request_config_json()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-rfqwp-config-json.php';
		$class_rfqwp_config_json = new Class_rfqwp_config_json();
		echo json_encode($class_rfqwp_config_json->getPublicRequestConfigJson());
		exit;
	}

	function config_json($rfqId)
	{
		global $wpdb;
		$tablename = $wpdb->prefix . "rfqwp_config_json";

		$result = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM {$tablename} WHERE id=%d", $rfqId)
		);
		return $result[0];
	}

	function newFormRequest($data)
	{

		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-rfqwp-form-submit.php';
		$class_rfqwp_form_submit = new Class_rfqwp_form_submit();
		return $class_rfqwp_form_submit->newFormRequest($data->get_json_params());
	}

	public function enqueue_styles()
	{
		$pluginName = basename(plugin_dir_path(dirname(__FILE__, 1)));
		wp_enqueue_style('styles', plugins_url($pluginName . '/public/app/styles.css'), array(), '1.0', 'all');
	}

	public function enqueue_scripts()
	{
		add_shortcode('request_for_quotation_wp_plugin_form', array($this, 'load_custom_form_index'));
		add_shortcode('request_for_quotation_wp_plugin_email_response_page', array($this, 'email_response_page'));
	}

	public function load_custom_form_index($atts)
	{
		$pluginName = basename(plugin_dir_path(dirname(__FILE__, 1)));

		extract(shortcode_atts(array(
			'rfq_id' => 1,
		), $atts));

		// print form id
		wp_register_script('form_id-script', '');
		wp_add_inline_script('form_id-script', '<script> var customFormId="' . $rfq_id . '"; console.log("' . $rfq_id . '")</script>');
		wp_enqueue_script('form_id-script');

		// angular app
		wp_enqueue_script('main-es5', plugins_url($pluginName . '/public/app/main-es5.js'), array('jquery'), '', false);
		wp_enqueue_script('polyfills-es5', plugins_url($pluginName . '/public/app/polyfills-es5.js'), array('jquery'), '', false);
		wp_enqueue_script('runtime-es5', plugins_url($pluginName . '/public/app/runtime-es5.js'), array('jquery'), '', false);


		//material icons
		wp_enqueue_style(
			'material-icons',
			'https://fonts.googleapis.com/icon?family=Material+Icons'
		);


		$html = '   
			<base href="./">  <meta name="viewport" content="width=device-width, initial-scale=1">		
			<app-root></app-root>
			';
		return $html;
	}

	public function email_response_page($atts)
	{
		extract(shortcode_atts(array(
			'enable_response_text' => true,
			'accept_text' => 'Thank you for your response. We will contact you as soon as possible.',
			'reject_text' => ' We are sad to see you reject our offer.',
		), $atts));

		if ($_GET['response'] == ('accept' || 'reject') && $_GET['email'] && $_GET['token']) {
			$email = $_GET['email'];
			$pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

			if (preg_match($pattern, $email) === 1) {
				global $wpdb;
				$tablename = $wpdb->prefix . "rfqwp_user_email";
				$result = $wpdb->get_results(
					$wpdb->prepare("SELECT `id`
					,`rfqwp_config_json_id`
					,`first_name` 
					,`last_name` 
					,`email` 
					,`telephone` 
					,`price` 
					,`discount` 
					,`discount_price` 
					,`date`
					, `token` FROM {$tablename} WHERE email=%d", $email)
				);
				foreach ($result as $row) {

					if ($row->{'token'} == $_GET['token']) {

						$fSuccess = $wpdb->update(
							$tablename,
							array(
								'email_response' => $_GET['response']
							),
							array('id' => $row->{'id'})
						);

						if ($enable_response_text == 'true') {
							$this->adminEmailAlert($wpdb, $row, $_GET['response']);
							if ($_GET['response'] == 'accept') {
								echo $accept_text;
							} else {
								echo $reject_text;
							}
						}
					}
				}
			} else {
				echo ("$email is not a valid email address");
			}
		} else {
			echo "I have not got enough parameter, please contact us!";
		}
	}

	function adminEmailAlert($wpdb, $userEmailRow, $response)
	{
		$json_table_result = $this->config_json($userEmailRow->{'rfqwp_config_json_id'});
		if ($json_table_result->{'send_admin_email_user_response_accept'} == 'true' && $response == 'accept') {
			$this->sendEmailAlert($wpdb, $json_table_result->{'admin_email'}, $json_table_result->{'sender_name'} . '<' . $json_table_result->{'sender_email'} . '>', $userEmailRow, 'admin_alert_accept');
		}
		if ($json_table_result->{'send_admin_email_user_response_reject'} == 'true' && $response == 'reject') {
			$this->sendEmailAlert($wpdb, $json_table_result->{'admin_email'},  $json_table_result->{'sender_name'} . '<' . $json_table_result->{'sender_email'} . '>', $userEmailRow, 'admin_alert_reject');
		}
	}

	function sendEmailAlert($wpdb, $adminEmail, $senderEmail, $userEmailRow, $emailType)
	{

		$tablename = $wpdb->prefix . "rfqwp_system_email";
		$systemEmails = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM {$tablename} WHERE `rfqwp_config_json`=%d AND `type`=%s", 1, $emailType)
		);
		$content = '
		<html>
		<head>
		</head>
		<body>
		<div style="max-width:800px">
			' . $systemEmails[0]->{'content'} . '
		</div>
		</body>
		</html>
		';
		$title = $systemEmails[0]->{'title'};

		$title  = str_replace('{{user_email}}', $userEmailRow->{'email'}, $title);
		$title  = str_replace('{{user_first_name}}', $userEmailRow->{'first_name'}, $title);
		$title  = str_replace('{{user_last_name}}', $userEmailRow->{'last_name'}, $title);
		$title  = str_replace('{{user_telephone}}', $userEmailRow->{'telephone'}, $title);
		$title  = str_replace('{{email_price}}', $userEmailRow->{'price'}, $title);
		$title  = str_replace('{{email_discount}}', $userEmailRow->{'discount'}, $title);
		$title  = str_replace('{{email_discount_price}}', $userEmailRow->{'discount_price'}, $title);
		$title  = str_replace('{{email_date}}', $userEmailRow->{'date'}, $title);


		$content  = str_replace('{{user_email}}', $userEmailRow->{'email'}, $content);
		$content  = str_replace('{{user_first_name}}', $userEmailRow->{'first_name'}, $content);
		$content  = str_replace('{{user_last_name}}', $userEmailRow->{'last_name'}, $content);
		$content  = str_replace('{{user_telephone}}', $userEmailRow->{'telephone'}, $content);
		$content  = str_replace('{{email_price}}', $userEmailRow->{'price'}, $content);
		$content  = str_replace('{{email_discount}}', $userEmailRow->{'discount'}, $content);
		$content  = str_replace('{{email_discount_price}}', $userEmailRow->{'discount_price'}, $content);
		$content  = str_replace('{{email_date}}', $userEmailRow->{'date'}, $content);


		$email_subject = $title;
		$email_body = $content;
		$email_to = $adminEmail;
		$headers = ['From: ' . $senderEmail, 'Content-Type: text/html; charset=UTF-8'];

		wp_mail($email_to, $email_subject, $email_body, $headers);
	}
}
