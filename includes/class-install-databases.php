<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class Class_Angular_Config
{

  public function startConfig()
  {
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/enum-rfqwp-tables.php';
	
    $dbTables = new Enum_rfqwp_database_tables(); 
    global $wpdb;
    
    $this->insertEmailTable($wpdb,$dbTables);
    $this->insertSystemDefaultEmailsTable($wpdb, $dbTables);
    $this->insertFormTable($wpdb, $dbTables);
    $this->insertFormColumnTable($wpdb, $dbTables);
    $this->insertFormCardTable($wpdb, $dbTables);
    $this->insertFormQuestionTable($wpdb, $dbTables);
    $this->insertFormQuestionItemsTable($wpdb, $dbTables);

  }


  private function insertEmailTable($wpdb, $dbTables)
  {
    $table_name = $wpdb->prefix . $dbTables->EMAIL;

    $sql = "CREATE TABLE `$table_name`  (
      `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
      `rfqwp_config_json_id` int(11) NOT NULL,
      `first_name` varchar(150) NOT NULL,
      `last_name` varchar(150) NOT NULL,
      `email` varchar(150) NOT NULL,
      `telephone` varchar(50) NOT NULL,
      `variables` varchar(30000) NOT NULL,
      `content` longtext NOT NULL,
      `price` varchar(11) NOT NULL,
      `discount` varchar(11) NOT NULL,
      `discount_price` varchar(11) NOT NULL,
      `date` DATETIME NOT NULL,
      `email_success` int(11) NOT NULL,
      `email_response` varchar(50) NOT NULL,
      `token` varchar(500) NOT NULL,
      `newsletter` varchar(11) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  private function insertSystemDefaultEmailsTable($wpdb, $dbTables)
  {
    $tablename = $wpdb->prefix . $dbTables->SYSTEM_EMAIL;;

    $sql = "CREATE TABLE `$tablename`  (
      `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
      `rfqwp_config_json` int(11) NOT NULL,
      `type` varchar(150) NOT NULL,
      `title` varchar(500) NOT NULL,
      `content` varchar(3000) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
  

  private function insertFormTable($wpdb, $dbTables)
  {

    $table_name = $wpdb->prefix  . $dbTables->FORM;
    $sql = "CREATE TABLE `$table_name` (
			`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`name` varchar(100) NOT NULL,
			`settings` varchar(25000) NOT NULL
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);   
  }

  private function insertFormColumnTable($wpdb, $dbTables)
  {
    $table_name = $wpdb->prefix  . $dbTables->COLUMN;
    $sql = "CREATE TABLE `$table_name` (
			`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`rfqwp_form_id` int(11) NOT NULL,
      `app_id` varchar(200) NOT NULL,
			`settings` varchar(5000) NOT NULL,
			`size_s`int(11) NOT NULL,
			`size_m` int(11) NOT NULL,
			`size_l` int(11) NOT NULL,
			`position` int(11) NOT NULL
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);   
  }

  private function insertFormCardTable($wpdb, $dbTables)
  {
    $table_name = $wpdb->prefix  . $dbTables->CARD;
    $sql = "CREATE TABLE `$table_name` (
			`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`rfqwp_form_column_id` int(11) NOT NULL ,
			`app_id` varchar(200) NOT NULL,
			`head_title` varchar(100) NOT NULL,
			`size_s`int(11) NOT NULL,
			`size_m` int(11) NOT NULL,
			`size_l` int(11) NOT NULL,
			`required` varchar(20) NOT NULL,
			`requiredInputAppId` varchar(100) NOT NULL,
			`requiredInputValue` varchar(100) NOT NULL,
			`after_price_note` varchar(200) NOT NULL,
			`after_discount_note` varchar(200) NOT NULL,
			`position` int(11) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
      
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);   
  }

  private function insertFormQuestionTable($wpdb, $dbTables)
  {
    $table_name = $wpdb->prefix  . $dbTables->QUESTION;
    $sql = "CREATE TABLE `$table_name` (
			`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`rfqwp_form_card_id` int(11) NOT NULL ,
      `app_id` varchar(200) NOT NULL,
			`title` varchar(100) NOT NULL,
			`type` varchar(100) NOT NULL,
			`required` varchar(100) NOT NULL,
			`required_text` varchar(200) NOT NULL,
			`price` varchar(50) NOT NULL,
			`discount` varchar(50) NOT NULL,	
			`setting` varchar(5000) NOT NULL,
			`after_input_note_only_email` varchar(200) NOT NULL,
			`after_input_note_only_form` varchar(200) NOT NULL,
			`position` int(11) NOT NULL
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);   
  }

  private function insertFormQuestionItemsTable($wpdb, $dbTables)
  {
    $table_name = $wpdb->prefix  . $dbTables->QUESTION_ITEM;
    $sql = "CREATE TABLE `$table_name` (
			`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`rfqwp_form_question_id` int(11) NOT NULL,
      `app_id` varchar(200) NOT NULL,
			`email_value` varchar(100) NOT NULL,
			`checked` varchar(100) NOT NULL,
			`value` varchar(200) NOT NULL,
			`price` varchar(200) NOT NULL,
			`discount` varchar(200) NOT NULL,	
			`settings` varchar(5000) NOT NULL,
			`position` int(11) NOT NULL
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);   
  }
}
