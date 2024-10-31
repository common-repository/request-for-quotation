<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class FormDaoImpl
{
    public $dbTables;
    public $columnTableClass;
    public $cardTableClass;
    public $questionTableClass;
    public $questionItemTableClass;

    public function __construct()
    {
        require_once plugin_dir_path(realpath(dirname(__FILE__) . '/..')) . 'includes/enum-rfqwp-tables.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'form/class-rfqwp-column-dao.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'form/class-rfqwp-card-dao.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'form/class-rfqwp-question-dao.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'form/class-rfqwp-question-item-dao.php';

        $this->dbTables = new Enum_rfqwp_database_tables();
        $this->questionItemTableClass = new QuestionItemDaoImpl($this->dbTables);
        $this->questionTableClass = new QuestionDaoImpl($this->dbTables, $this->questionItemTableClass);
        $this->cardTableClass = new CardDaoImpl($this->dbTables, $this->questionTableClass);
        $this->columnTableClass = new ColumnDaoImpl($this->dbTables, $this->cardTableClass);
    }

    public function updateForm($configuration)
    {
        // if(!$configuration['settings']){
        //     echo json_encode(array('state' => 'success'));
        //     exit;
        // }
        global $wpdb;
        $tablename = $wpdb->prefix . $this->dbTables->FORM;
        $sql = $wpdb->prepare(
            "UPDATE $tablename  SET
				name = %s
		 		, settings= %s	
			WHERE id = %d;",
            $configuration['name'],
            $configuration['settings']? $configuration['settings']:$this->getDefaultFormSettings(),
            // preg_replace("/\r|\n|\t|\s{2,}/", "", json_encode($configuration['settings'])),
            $configuration['id']
        );
        $wpdb->query($sql);

        $this->columnTableClass->requestList($configuration['id'], $configuration['columnList']);
        echo json_encode(array('state' => 'success'));
        exit;
    }

    public function findPageFormList($from, $limit)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->dbTables->FORM;
        $all_row_result = $wpdb->get_results("SELECT COUNT(*) AS 'all_row_number' FROM  {$tablename} ");
        $allRowNumber = $all_row_result[0]->{"all_row_number"};
        $result = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$tablename} limit %d,%d;", $from, $limit)
        );
        $response['all'] = $allRowNumber;
        $response['form'] = $result;
        return $response;
    }

    public function getFormById($configJsonId)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->dbTables->FORM;
        $form = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$tablename} WHERE id=%d", $configJsonId)
        );
        $ResponseFormWidthColumnsAndCardsAndQuestions = $form[0];
        $ResponseFormWidthColumnsAndCardsAndQuestions->{'settings'} = json_decode($ResponseFormWidthColumnsAndCardsAndQuestions->{'settings'}, true);

        $columnList = $this->columnTableClass->getListByFormId($configJsonId);
        $cardList = $this->cardTableClass->getListByFormId($configJsonId);
        $questionList = $this->questionTableClass->getListByFormId($configJsonId);
        $questionItemList = $this->questionItemTableClass->getListByFormId($configJsonId);

        foreach ($columnList as $column) {
            $column->{'cardList'} = array();

            foreach ($cardList as $card) {
                if ($column->{'id'} == $card->{'rfqwp_form_column_id'}) {
                    $card->{'questionList'} = array();

                    foreach ($questionList as $question) {
                        if ($card->{'id'} == $question->{'rfqwp_form_card_id'}) {
                            $question->{'itemList'} = array();

                            foreach ($questionItemList as $item) {
                                if ($question->{'id'} == $item->{'rfqwp_form_question_id'}) {
                                    array_push($question->{'itemList'}, $item);
                                }
                            }
                            array_push($card->{'questionList'}, $question);
                        }
                    }
                    array_push($column->{'cardList'}, $card);
                }
            }
        }
        $ResponseFormWidthColumnsAndCardsAndQuestions->{'columnList'} = array();
        $ResponseFormWidthColumnsAndCardsAndQuestions->{'columnList'} = $columnList;
        return $ResponseFormWidthColumnsAndCardsAndQuestions;
    }

    public function createDefaultFormByName($newFormName)
    {
        $defaultSettings = $this->getDefaultFormSettings();
        $admin_alert_accept = array(
            'type' => 'admin_alert_accept',
            'title' => 'Accept email title - {{user_first_name}} - {{user_last_name}} - {{user_email}}',
            'content' => 'Accept email content - {{user_first_name}} - {{user_last_name}} - {{user_email}}'
        );
        $admin_alert_reject = array(
            'type' => 'admin_alert_reject',
            'title' => 'Reject email title - {{user_first_name}} - {{user_last_name}} - {{user_email}}',
            'content' => 'Reject email content - {{user_first_name}} - {{user_last_name}} - {{user_email}}'
        );
        $settings = preg_replace("/\r|\n|\t|\s{2,}/", "", json_encode(json_decode($defaultSettings, true)));
        $form = array(
            'systemEmail' => array(
                'admin_alert_accept' => $admin_alert_accept,
                'admin_alert_reject' => $admin_alert_reject
            ),
            'name' => $newFormName,
            'settings' => $settings,
            'columnList' => array()
        );
        $this->createForm($form);
        exit;
    }

    public function createForm($form)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . $this->dbTables->FORM;
        $sql = $wpdb->prepare(
            "INSERT INTO `$tablename` (
		  `id`
		  , `name`
		  , `settings`
		  ) VALUES (NULL,
            %s, %s);",
            $form['name'],
            // preg_replace("/\r|\n|\t|\s{2,}/", "", json_encode($form['settings'])),
            $form['settings']
        );
        // print_r(json_encode( (array)$form['settings']));
        $wpdb->query($sql);
        $insertedId = $wpdb->insert_id;
        if (isset($insertedId) && $insertedId != null) {
            $this->createSystemDefaultEmail(
                $wpdb,
                $wpdb->prefix . $this->dbTables->SYSTEM_EMAIL,
                $form['systemEmail']['admin_alert_accept'],
                $insertedId
            );
            $this->createSystemDefaultEmail(
                $wpdb,
                $wpdb->prefix . $this->dbTables->SYSTEM_EMAIL,
                $form['systemEmail']['admin_alert_reject'],
                $insertedId
            );
            if (isset($form['columnList'])) {
                $this->columnTableClass->requestList($insertedId, $form['columnList']);
            }
        }
        echo true;
    }

    public function deleteForm($formId)
    {
        //delete column, card, question, item
        $this->columnTableClass->deleteByFormId($formId);

        // delete emails
        global $wpdb;
        $tablename = $wpdb->prefix . $this->dbTables->EMAIL;
        $result = $wpdb->get_results(
            $wpdb->prepare("DELETE FROM {$tablename} WHERE rfqwp_config_json_id LIKE %s", $formId)
        );

        //system elmails
        $tablename = $wpdb->prefix . $this->dbTables->SYSTEM_EMAIL;
        $result = $wpdb->get_results(
            $wpdb->prepare("DELETE FROM {$tablename} WHERE rfqwp_config_json LIKE %s", $formId)
        );

        // delete form
        $tablename = $wpdb->prefix . $this->dbTables->FORM;
        $result = $wpdb->get_results(
            $wpdb->prepare("DELETE FROM {$tablename} WHERE id LIKE %d", $formId)
        );

        echo json_encode(array('state' => 'success'));
        exit;
    }

    public function createSystemDefaultEmail($wpdb, $tablename, $email, $parentId)
    {
        $sql = $wpdb->prepare(
            "INSERT INTO `$tablename` (
            `id`
            , `rfqwp_config_json`
            , `type`
            , `title`
            , `content`
            ) VALUES (NULL, %d, %s, %s, %s);",
            $parentId,
            $email['type'],
            $email['title'],
            $email['content']
        );
        $wpdb->query($sql);
    }

    function getDefaultFormSettings()
    {
        return '{
            "title": "Request for quotation",
            "emailConfiguration":{
                "title": "Request for quotation",
                "admin_email": "admin_email@email.com",
                "sender_name": "XYZ company",
                "sender_email": "sender.email@email.com",
                "send_admin_email": "true",
                "send_email": "true",
                "email_header": "Header {{gender_text}} {{user_first_name}} {{user_last_name}}",
                "email_footer": "Footer",
                "email_response_enable": "false",
                "email_accept_button_text": "Accept",
                "email_refuse_button_text": "Reject",
                "email_response_page_url": "https://yourdomain/email-response_page",
                "send_admin_email_user_response_accept": "false",
                "send_admin_email_user_response_reject": "false"
              },
            "hasAndCountPrice": true,
            "uniqueCounter": false,
            "uniqueCounterItemList":[],
            "priceType": "€",
            "priceTypePlace": "front",
            "allPriceText": "All price: ",
            "allDiscountText": "All discount: ",
            "allDiscountedPriceText": "All discounted price: ",
            "afterCounterPriceNote": "after counted price note",
            "afterCounterDiscountNote": "after counted discount note",
            "afterCardDiscountNote": "after counted discount note",
            "formResponseTextSuccess": "Thank you!",
            "formResponseTextError": "Something went wrong",
            "emailDateForm": "d-m-Y",
            "formDateForm": "d-M-y",
            "styles": {
                "button": {
                  "color": "#000",
                  "backgroundColor": "#10c285"
                },
                "card": {
                  "color": "#000",
                  "backgroundColor": "#f4f4f4",
                  "headerBackgroundColor": "#f4f4f4",
                  "titleColor": "#000"
                },
                "formTitle": {
                  "color": "#10c285"
                },
                "formResponse": {
                  "color": "#10c285"
                },
                "question": {
                  "color": "#000",
                  "backgroundColor": "#ffffff",
                  "errorColor": "red",
                  "caretColor": "#000",
                  "underlineColor": "#f4f4f4"
                },
                "datepicker": {
                  "iconColor": "#10c285",
                  "buttonColor": "#ffffff",
                  "buttonBackgroundColor": "#10c285"
                },
                "rangeSlider": {
                  "trackBeforeColor": "#10c285",
                  "trackAfterColor": "#000",
                  "thumbBackgroundColor": "#000",
                  "thumbLabelBackgroundColor": "#10c285",
                  "thumbLabelColor": "#000"
                }
            }
        }';
    }
}
