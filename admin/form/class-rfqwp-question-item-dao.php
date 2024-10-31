<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once plugin_dir_path(__FILE__) . 'interface-rfqwp-form-item-dao.php';
class QuestionItemDaoImpl implements FormItem
{
    public $tables;
    public function __construct($tables)
    {
        $this->tables = $tables;
    }

    public function getListByFormId($formId)
    {
        global $wpdb;
        $columnTableName = $wpdb->prefix . $this->tables->COLUMN;
        $cardTableName = $wpdb->prefix . $this->tables->CARD;
        $questionTableName = $wpdb->prefix . $this->tables->QUESTION;
        $questionItemTableName = $wpdb->prefix . $this->tables->QUESTION_ITEM;
        $questionItemList = $wpdb->get_results(
            $wpdb->prepare("
            SELECT questionItemTable.* 
            FROM {$columnTableName} columnTable 
            INNER JOIN {$cardTableName} cardTable on columnTable.id=cardTable.rfqwp_form_column_id 
            INNER JOIN {$questionTableName} questionTable on cardTable.id=questionTable.rfqwp_form_card_id 
            INNER JOIN {$questionItemTableName} questionItemTable on questionTable.id=questionItemTable.rfqwp_form_question_id 
            WHERE columnTable.rfqwp_form_id=%d
            ORDER BY questionItemTable.position ASC", $formId)
        );
        foreach ($questionItemList as $key => $row) {
            $questionItemList[$key]->{'settings'} = json_decode($questionItemList[$key]->{'settings'}, true);
        }
        return $questionItemList;
    }

    public function delete($item)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->tables->QUESTION_ITEM;
        $wpdb->get_results(
            $wpdb->prepare("DELETE FROM {$tablename} WHERE id=%d", $item['id'])
        );
    }

    public function deleteByFormId($formId)
    {
        $list = $this->getListByFormId($formId);
        foreach ($list as $item) {
            $item = (array) $item;
            $this->delete($item);
        }
    }


    public function create($item)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->tables->QUESTION_ITEM;
        $sql = $wpdb->prepare(
            "INSERT INTO `$tablename` (
		  `id`
		  , `rfqwp_form_question_id`
		  , `app_id`
		  , `email_value`
		  , `checked`
		  , `value`
		  , `price`
		  , `discount`
		  , `settings`
		  , `position`
		  ) VALUES (NULL, %d, %s, %s, %s, %s, %s, %s, %s, %d);",
            $item['rfqwp_form_question_id'],
            $item['app_id'],
            $item['email_value'],
            $item['checked'],
            $item['value'],
            $item['price'],
            $item['discount'],
            preg_replace("/\r|\n|\t|\s{2,}/", "", json_encode($item['settings'])),
            $item['position']
        );
        $wpdb->query($sql);
    }

    public function update($item)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->tables->QUESTION_ITEM;
        $sql = $wpdb->prepare(
            "UPDATE $tablename  SET
		 		rfqwp_form_question_id= %d
		 		, app_id= %s
		 		, email_value= %s
		 		, checked= %s
				, value = %s				
				, price = %s				
				, discount = %s				
				, settings = %s				
				, position = %d				
			WHERE id = %d;",
            $item['rfqwp_form_question_id'],
            $item['app_id'],
            $item['email_value'],
            $item['checked'],
            $item['value'],
            $item['price'],
            $item['discount'],
            preg_replace("/\r|\n|\t|\s{2,}/", "", json_encode($item['settings'])),
            $item['position'],
            $item['id']
        );
        $wpdb->query($sql);
    }

    public function requestList($parentId, $itemList)
    {
        foreach ($itemList as $item) {
            $item['rfqwp_form_question_id'] = $parentId;
            if ($item['action'] == 'CREATE') {
                $this->create($item);
            } else if ($item['action'] == 'DELETE') {
                $this->delete($item);
            } else {
                $this->update($item);
            }
        }
    }
}
