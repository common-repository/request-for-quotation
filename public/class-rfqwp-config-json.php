<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class Class_rfqwp_config_json
{
	public function getPublicRequestConfigJson()
	{
		$rfqId = isset($_GET['customFormId']) ? $_GET['customFormId'] : 1;
		$json_table_result = $this->getFormById($rfqId);

		if (!isset($json_table_result->{'id'})) {
			return array('state' => 'not found');
		}

		$json = json_decode(json_encode($json_table_result), true);
		$returnColumns = array();
		foreach ($json['columnList'] as $column) {

			$cardList = array();
			foreach ($column['cardList'] as $questionGroup) {
				$returnQuestions = array();
				foreach ($questionGroup['questionList'] as $question) {
					$returnQuestion = array(
						'app_id' => $question['app_id'],
						'type' => $question['type'],
						'title' => $question['title'],
						'required' => $question['required'],
						'required_text' => $question['requiredText'],
						'afterInputNoteOnlyForm' => $question['afterInputNoteOnlyForm'],
						'setting' => $question['setting']
					);

					if ($question['itemList']) {
						$returnItems = array();
						foreach ($question['itemList'] as $itemsItem) {
							array_push($returnItems, array(
								'app_id' => $itemsItem['app_id'],
								'value' => $itemsItem['value'],
								'checked' => $itemsItem['checked'],
								'settings' => $itemsItem['settings']
							));
						}
						$returnQuestion['itemList'] = $returnItems;
					}
					array_push($returnQuestions, $returnQuestion);
				}
				$returnQuestionGroup = array(
					'headTitle' => $questionGroup['headTitle'],
					'app_id' => $questionGroup['app_id'],
					'size_s' => $questionGroup['size_s'],
					'size_m' => $questionGroup['size_m'],
					'size_l' => $questionGroup['size_l'],
					'required' => $questionGroup['required'],
					'requiredInputAppId' => $questionGroup['requiredInputAppId'] ? $questionGroup['requiredInputAppId'] : null,
					'requiredInputValue' => $questionGroup['requiredInputValue'] ? $questionGroup['requiredInputValue'] : null,
					'questionList' => $returnQuestions
				);
				array_push($cardList, $returnQuestionGroup);
			}
			$returnColumn = array(
				"size_l" => $column['size_l'],
				"size_m" => $column['size_m'],
				"size_s" => $column['size_s'],
				"settings" => $column['settings'],
				"cardList" => $cardList
			);
			array_push($returnColumns, $returnColumn);
		}
		$formSettings = $json['settings'];
		$returnJson = array(
			'settings' => array(
				"title" => $json['name'],
				"styles" => $formSettings['styles'],
				"formResponseTextSuccess" => $formSettings['formResponseTextSuccess'],
				"formResponseTextError" => $formSettings['formResponseTextError'],
				// "emailDateForm" => $formSettings['emailDateForm'],
				"formDateForm" => $formSettings['formDateForm'],
			),
			"columnList" => $returnColumns
		);
		return $returnJson;
	}

	function getFormById($configJsonId)
	{
		require_once plugin_dir_path(realpath(dirname(__FILE__) . '')) . 'admin/form/class-rfqwp-form-dao.php';
		$formDao = new FormDaoImpl();
		return $formDao->getFormById($configJsonId);
	}
}
