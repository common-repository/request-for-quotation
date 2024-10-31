<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once plugin_dir_path(__FILE__) . 'interface-rfqwp-form-item-dao.php';

class ColumnDaoImpl implements FormItem
{
    public $tables;
    public $cardTableClass;

    public function __construct($tables, $cardTableClass)
    {
        $this->tables = $tables;
        $this->cardTableClass = $cardTableClass;
    }

    public function getListByFormId($formId)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->tables->COLUMN;
        $columnList = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$tablename} WHERE rfqwp_form_id=%d", $formId)
        );

        foreach ($columnList as $key => $row) {
            $columnList[$key]->{'settings'} = json_decode($columnList[$key]->{'settings'}, true);
        }

        return $columnList;
    }

    public function delete($item)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->tables->COLUMN;
        $wpdb->get_results(
            $wpdb->prepare("DELETE FROM {$tablename} WHERE id=%d", $item['id'])
        );
        if (isset($item['cardList'])) {
            echo "delete card requestList";
            foreach ($item['cardList'] as $card) {
                $this->cardTableClass->delete($card);
            }
        }
    }

    public function deleteByFormId($formId)
    {
        $list = $this->getListByFormId($formId);
        foreach ($list as $item) {
            $item = (array) $item;
            $this->cardTableClass->deleteByFormId($formId);
            $this->delete($item);
        }
    }

    public function create($item)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->tables->COLUMN;
        $sql = $wpdb->prepare(
            "INSERT INTO `$tablename` (
		  `id`
		  , `rfqwp_form_id`
		  , `app_id`
		  , `settings`
		  , `size_s`
		  , `size_m`
		  , `size_l`
		  , `position`
		  ) VALUES (NULL, %d, %s, %s, %s, %s, %s, %s);",
            $item['rfqwp_form_id'],
            $item['app_id'],
            preg_replace("/\r|\n|\t|\s{2,}/", "", json_encode($item['settings'])),
            $item['size_s'],
            $item['size_m'],
            $item['size_l'],
            $item['position']
        );
        $wpdb->query($sql);
        $insertedId = $wpdb->insert_id;
        $item['id'] = $insertedId;
        if(isset($item['cardList']) && !empty($item['cardList'])){
            $this->cardTableClass->requestList($item['id'], $item['cardList']);
        }
    }

    public function update($item)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->tables->COLUMN;
        $sql = $wpdb->prepare(
            "UPDATE $tablename  SET
		 		rfqwp_form_id= %d
		 		, app_id= %s
		 		, settings= %s
		 		, size_s= %s
		 		, size_m= %s
		 		, size_l= %s
				, position = %s				
			WHERE id = %d;",
            $item['rfqwp_form_id'],
            $item['app_id'],
            preg_replace("/\r|\n|\t|\s{2,}/", "", json_encode($item['settings'])),
            $item['size_s'],
            $item['size_m'],
            $item['size_l'],
            $item['position'],
            $item['id']
        );
        $wpdb->query($sql);
        if(isset($item['cardList']) && !empty($item['cardList'])){
            $this->cardTableClass->requestList($item['id'], $item['cardList']);
        }
    }

    public function requestList($parentId, $itemList)
    {
        foreach ($itemList as $item) {
            $item['rfqwp_form_id'] = $parentId;
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
