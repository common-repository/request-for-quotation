<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once plugin_dir_path(__FILE__) . 'interface-rfqwp-form-item-dao.php';

class QuestionDaoImpl implements FormItem
{
    public $tables;
    public $questionItemTableClass;

    public function __construct($tables, $questionItemTableClass)
    {
        $this->tables = $tables;
        $this->questionItemTableClass = $questionItemTableClass;
    }

    public function getListByFormId($formId)
    {
        global $wpdb;
        $columnTableName = $wpdb->prefix . $this->tables->COLUMN;
        $cardTableName = $wpdb->prefix . $this->tables->CARD;
        $questionTableName = $wpdb->prefix . $this->tables->QUESTION;
        $questionList = $wpdb->get_results(
            $wpdb->prepare("
            SELECT 
                questionTable.id
                ,questionTable.rfqwp_form_card_id
                ,questionTable.app_id
                ,questionTable.title
                ,questionTable.type
                ,questionTable.required
                ,questionTable.required_text as 'requiredText'
                ,questionTable.price
                ,questionTable.discount
                ,questionTable.setting
                ,questionTable.after_input_note_only_email as 'afterInputNoteOnlyEmail'
                ,questionTable.after_input_note_only_form as 'afterInputNoteOnlyForm'
                ,questionTable.position
            FROM {$columnTableName} columnTable 
            INNER JOIN {$cardTableName} cardTable on columnTable.id=cardTable.rfqwp_form_column_id 
            INNER JOIN {$questionTableName} questionTable on cardTable.id=questionTable.rfqwp_form_card_id 
            WHERE columnTable.rfqwp_form_id=%d
            ORDER BY questionTable.position ASC"
            , $formId)
        );
        foreach ($questionList as $key => $row) {
            $questionList[$key]->{'setting'} = json_decode($questionList[$key]->{'setting'}, true);
        }
        return $questionList;
        // return json_encode($questionList, JSON_UNESCAPED_UNICODE);
    }
    
    public function delete($item)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->tables->QUESTION;
        $wpdb->get_results(
            $wpdb->prepare("DELETE FROM {$tablename} WHERE id=%d", $item['id'])
        );
        if(isset($item['itemList'] )){
            foreach ($item['itemList'] as $cardItem) {
                $this->questionItemTableClass->delete($cardItem);
            }
        }
    }

    public function deleteByFormId($formId)
    {
        $list = $this->getListByFormId($formId);
        foreach ($list as $item) {
            $item = (array) $item;
            $this->questionItemTableClass->deleteByFormId($formId);
            $this->delete($item);
        }
    }

    public function create($item)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->tables->QUESTION;
        $sql = $wpdb->prepare(
            "INSERT INTO `$tablename` (
		  `id`
		  , `rfqwp_form_card_id`
		  , `app_id`
		  , `title`
		  , `type`
		  , `required`
		  , `required_text`
		  , `price`
		  , `discount`
		  , `setting`
		  , `after_input_note_only_email`
		  , `after_input_note_only_form`
		  , `position`
		  ) VALUES (NULL, %d, %s, %s, %s,  %s, %s, %s, %s, %s,   %s, %s ,%s);",
            $item['rfqwp_form_card_id'],
            $item['app_id'],
            $item['title'],
            $item['type'],
            $item['required'],

            $item['requiredText'],
            $item['price'],
            $item['discount'],
            preg_replace("/\r|\n|\t|\s{2,}/", "", json_encode($item['setting'])),
            $item['afterInputNoteOnlyEmail'],

            $item['afterInputNoteOnlyForm'],
            $item['position']
        );
        $wpdb->query($sql);
        $insertedId = $wpdb->insert_id;
        $item['id'] = $insertedId;
        if(isset($item['itemList']) && !empty($item['itemList'])){
            $this->questionItemTableClass->requestList($item['id'], $item['itemList']);
        }
    }

    public function update($item)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->tables->QUESTION;
        $sql = $wpdb->prepare(
            "UPDATE $tablename  SET
		 		rfqwp_form_card_id= %d
		 		, app_id= %s
		 		, title= %s
		 		, type= %s
		 		, required= %s
		 		, required_text= %s
		 		, price= %s
		 		, discount= %s
		 		, setting= %s
		 		, after_input_note_only_email= %s
		 		, after_input_note_only_form= %s
				, position = %s				
			WHERE id = %d;",
            $item['rfqwp_form_card_id'],
            $item['app_id'],
            $item['title'],
            $item['type'],
            $item['required'],
            $item['requiredText'],
            $item['price'],
            $item['discount'],
            preg_replace("/\r|\n|\t|\s{2,}/", "", json_encode($item['setting'])),
            $item['afterInputNoteOnlyEmail'],
            $item['afterInputNoteOnlyForm'],
            $item['position'],
            $item['id']
        );
        $wpdb->query($sql);
        if(isset($item['itemList']) && !empty($item['itemList'])){
            $this->questionItemTableClass->requestList($item['id'], $item['itemList']);
        }
    }

    public function requestList($parentId, $itemList)
    {
        foreach ($itemList as $item) {
            $item['rfqwp_form_card_id'] = $parentId;
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
