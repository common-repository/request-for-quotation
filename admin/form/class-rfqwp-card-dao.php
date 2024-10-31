<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once plugin_dir_path(__FILE__) . 'interface-rfqwp-form-item-dao.php';

class CardDaoImpl implements FormItem
{
    public $tables;
    public $questionTableClass;
    public function __construct($tables, $questionTableClass)
    {
        $this->tables = $tables;
        $this->questionTableClass = $questionTableClass;
    }

    public function getListByFormId($formId)
    {
        global $wpdb;
        $columnTableName = $wpdb->prefix . $this->tables->COLUMN;
        $cardTableName = $wpdb->prefix . $this->tables->CARD;
        $cardList = $wpdb->get_results(
            $wpdb->prepare("
            SELECT 
            cardTable.id 
            ,cardTable.rfqwp_form_column_id 
            ,cardTable.app_id 
            ,cardTable.head_title as 'headTitle'
            ,cardTable.size_s
            ,cardTable.size_m
            ,cardTable.size_l
            ,cardTable.required 
            ,cardTable.requiredInputAppId 
            ,cardTable.requiredInputValue 
            ,cardTable.after_price_note as 'afterCardPriceNote'
            ,cardTable.after_discount_note as 'afterCardDiscountNote'
            ,cardTable.position 
            FROM {$columnTableName} columnTable 
            LEFT JOIN {$cardTableName} cardTable on columnTable.id=cardTable.rfqwp_form_column_id 
            WHERE columnTable.rfqwp_form_id=%d
            ORDER BY cardTable.position ASC", $formId)
        );
        return $cardList;
    }
    public function delete($item)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->tables->CARD;
        $wpdb->get_results(
            $wpdb->prepare("DELETE FROM {$tablename} WHERE id=%d", $item['id'])
        );
        if(isset($item['questionList'] )){
            foreach ($item['questionList'] as $card) {
                $this->questionTableClass->delete($card);
            }
        }
    }

    public function deleteByFormId($formId)
    {
        $list = $this->getListByFormId($formId);
        foreach ($list as $item) {
            $item = (array) $item;
            $this->questionTableClass->deleteByFormId($formId);
            $this->delete($item);
        }
    }

    public function create($item)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->tables->CARD;
        $sql = $wpdb->prepare(
            "INSERT INTO `$tablename` (
		  `id`
		  , `rfqwp_form_column_id`
		  , `app_id`
		  , `head_title`
		  , `size_s`
		  , `size_m`
		  , `size_l`
		  , `required`
		  , `requiredInputAppId`
		  , `requiredInputValue`
		  , `after_price_note`
		  , `after_discount_note`
		  , `position`
		  ) VALUES (NULL, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d);",
            $item['rfqwp_form_column_id'],
            $item['app_id'],
            $item['headTitle'],
            $item['size_s'],
            $item['size_m'],
            $item['size_l'],
            $item['required'],
            $item['requiredInputAppId'],
            $item['requiredInputValue'],
            $item['afterCardPriceNote'],
            $item['afterCardDiscountNote'],
            $item['position']
        );
        $wpdb->query($sql);
        $insertedId = $wpdb->insert_id;
        $item['id'] = $insertedId;   
        if(isset($item['questionList']) && !empty($item['questionList'])){
            $this->questionTableClass->requestList($item['id'], $item['questionList']);
        }
    }

    public function update($item)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->tables->CARD;
        $sql = $wpdb->prepare(
            "UPDATE $tablename  SET
		 		rfqwp_form_column_id= %d
		 		, app_id= %s
		 		, head_title= %s
		 		, size_s= %s
		 		, size_m= %s
		 		, size_l= %s
		 		, required= %s
		 		, requiredInputAppId= %s
		 		, requiredInputValue= %s
		 		, after_price_note= %s
		 		, after_discount_note= %s
				, position = %d				
			WHERE id = %d;",
            $item['rfqwp_form_column_id'],
            $item['app_id'],
            $item['headTitle'],
            $item['size_s'],
            $item['size_m'],
            $item['size_l'],
            $item['required'],
            $item['requiredInputAppId'],
            $item['requiredInputValue'],
            $item['afterCardPriceNote'],
            $item['afterCardDiscountNote'],
            $item['position'],
            $item['id']
        );
        $wpdb->query($sql);
        if(isset($item['questionList']) && !empty($item['questionList'])){
            $this->questionTableClass->requestList($item['id'], $item['questionList']);
        }
    }
    
    public function requestList($parentId, $itemList)
    {
        foreach ($itemList as $item) {
            $item['rfqwp_form_column_id'] = $parentId;
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
