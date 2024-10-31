<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class FormAjaxService
{
    public $formDao;
    public function __construct()
    {
        require_once plugin_dir_path(__FILE__) . 'class-rfqwp-form-dao.php';
        $this->formDao = new FormDaoImpl();

        add_action('wp_ajax_rfqwpFindPageFormList', array($this, 'findPageFormListFunc'));
        add_action('wp_ajax_rfqwpCreateForm', array($this, 'createFormFunc'));
        add_action('wp_ajax_rfqwpGetFormById', array($this, 'getFormByIdFunc'));
        add_action('wp_ajax_rfqwpUpdateForm', array($this, 'updateFormFunc'));
        add_action('wp_ajax_rfqwpDeleteForm', array($this, 'deleteFormFunc'));
    }

    public function findPageFormListFunc()
    {
        $from = sanitize_text_field($_POST['pageIndex']);
        $limit = sanitize_text_field($_POST['pageSize']);
        echo json_encode($this->formDao->findPageFormList($from, $limit));
        exit;
    }

    function createFormFunc()
    {
        if (isset($_POST['name'])) {
            $newFormName = sanitize_text_field($_POST['name']);
            echo $this->formDao->createDefaultFormByName($newFormName);
        }

        if (isset($_POST['configuration'])) {

            $configuration = json_decode(
                preg_replace(
                    "/\r|\n|\t|\s{2,}/",
                    "",
                    stripslashes(urldecode($_POST['configuration']))
                ),
                true
            );
            $this->formDao->createForm($this->sanitizeForm($configuration));
        }
        exit;
    }

    public function getFormByIdFunc()
    {
        if (!isset($_POST['configJsonId'])) {
            die;
        }
        $configJsonId = sanitize_text_field($_POST['configJsonId']);
        echo json_encode($this->formDao->getFormById($configJsonId));
        exit;
    }

    public function updateFormFunc()
    {
        if (!isset($_POST['configuration'])) {
            die;
        }
        $configuration = json_decode(
            preg_replace(
                "/\r|\n|\t|\s{2,}/",
                "",
                stripslashes(urldecode($_POST['configuration']))
            ),
            true
        );
        $this->formDao->updateForm($this->sanitizeForm($configuration));
        exit;
    }

    public function deleteFormFunc()
    {
        if (!isset($_POST['formId'])) {
            die;
        }
        $formId = sanitize_text_field($_POST['formId']);
        $this->formDao->deleteForm($formId);
        exit;
    }

    public function sanitizeForm($form)
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

        $form['name'] = isset($form['name']) ? sanitize_text_field($form['name']) : null;
        // $form['admin_email'] = isset($form['admin_email']) ? sanitize_text_field($form['admin_email']) : null;
        // $form['sender_name'] = isset($form['sender_name']) ? sanitize_text_field($form['sender_name']) : null;
        // $form['sender_email'] = isset($form['sender_email']) ? sanitize_text_field($form['sender_email']) : null;
        // $form['send_admin_email'] = isset($form['send_admin_email']) ? sanitize_text_field($form['send_admin_email']) : null;
        // $form['send_admin_email'] = isset($form['send_admin_email']) ? sanitize_text_field($form['send_admin_email']) : null;

        // $form['send_email'] = isset($form['send_email']) ? sanitize_text_field($form['send_email']) : null;

        // $form['email_header'] = isset($form['email_header']) ? wp_kses($form['email_header'], $allowed_html) : null;
        // $form['email_footer'] = isset($form['email_footer']) ? wp_kses($form['email_footer'], $allowed_html) : null;

        // $form['email_response_enable'] = isset($form['email_response_enable']) ? sanitize_text_field($form['email_response_enable']) : null;
        // $form['email_accept_button_text'] = isset($form['email_accept_button_text']) ? sanitize_text_field($form['email_accept_button_text']) : null;

        // $form['email_refuse_button_text'] = isset($form['email_refuse_button_text']) ? sanitize_text_field($form['email_refuse_button_text']) : null;
        // $form['email_response_page_url'] = isset($form['email_response_page_url']) ? sanitize_text_field($form['email_response_page_url']) : null;
        // $form['send_admin_email_user_response_accept'] = isset($form['send_admin_email_user_response_accept']) ? sanitize_text_field($form['send_admin_email_user_response_accept']) : null;
        // $form['send_admin_email_user_response_reject'] = isset($form['send_admin_email_user_response_reject']) ? sanitize_text_field($form['send_admin_email_user_response_reject']) : null;


        /**
         * I sanitizing all form, except the email footer and header, because I have to allow some HTML codes, so I use a special pattern. 
         */

        $email_header = $form['settings']['emailConfiguration']['email_header'];
        $email_footer = $form['settings']['emailConfiguration']['email_footer'];

        $form['settings'] = isset($form['settings']) ?
            sanitize_textarea_field(preg_replace("/\r|\n|\t|\s{2,}/", "", json_encode($form['settings']))) :
            null;
        $form['settings'] = json_decode($form['settings'], true);

        $form['settings']['emailConfiguration']['email_header'] = isset($email_header) ? wp_kses($email_header, $allowed_html) : null;
        $form['settings']['emailConfiguration']['email_footer'] = isset($email_footer) ? wp_kses($email_footer, $allowed_html) : null;

        $form['settings'] = preg_replace("/\r|\n|\t|\s{2,}/", "", json_encode($form['settings']));
        return $form;
    }
}
