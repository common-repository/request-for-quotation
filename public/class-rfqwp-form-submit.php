<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class Class_rfqwp_form_submit
{

    public function __construct()
    {
    }

    function newFormRequest($requestData)
    {
        // escape special characters
        // $requestData = $requestData->get_json_params();
        foreach ($requestData as $key => $value) {
            $requestData[$key] = sanitize_text_field(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
        }

        //check custom form id
        if (!isset($requestData['customFormId']) && is_numeric($requestData['customFormId'])) {
            return array('state' => 'error');
        }


        // get form 
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-rfqwp-config-json.php';
        $class_rfqwp_config_json = new Class_rfqwp_config_json();
        $formConfiguration = $class_rfqwp_config_json->getFormById($requestData['customFormId']);
        $formConfiguration = json_decode(json_encode($formConfiguration), true);
        $emailConfiguration = $formConfiguration['settings']['emailConfiguration'];
        // check form data
        if (!isset($formConfiguration['id'])) {
            return array('state' => 'error');
        }

        // check user email
        $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';
        if (
            !isset($requestData['email'])
            || strlen($requestData['email']) < 3
            || preg_match($pattern, $requestData['email']) !== 1
        ) {
            return array('state' => 'error');
        }

        // generate email response token
        $token = sha1(rand(1, 90000) . 'SALT' . (($requestData['email']) ? $requestData['email'] : 'kacsa'));

        //generate email
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rfqwp-form-email.php';
        $class_rfqwp_form_email = new Class_rfqwp_form_email();
        $generatedResult = $class_rfqwp_form_email->generateEmail($formConfiguration, $requestData, $token);
        $price = 0;
        $discount = 0;
        $htmlContent =  $generatedResult['htmlEmailContent'];
        $price =  $generatedResult['price'];
        $discount =  $generatedResult['discount'];

        if (strlen($htmlContent) < 25000) {
            $this->uploadGeneratedEmailToDatabase(
                $formConfiguration['id'],
                isset($requestData['first_name']) ? $requestData['first_name'] : '',
                isset($requestData['last_name']) ? $requestData['last_name'] : '',
                isset($requestData['email']) ? $requestData['email'] : '',
                isset($requestData['telephone']) ? $requestData['telephone'] : '',
                $htmlContent,
                json_encode($requestData),
                $price,
                $discount,
                round(($price - ($price * $discount / 100)), 2),
                date("Y-m-d H:i:s"),
                'Not tried',
                $token,
                isset($requestData['newsletter']) ? 'yes' : 'no'
            );
        }
        $headers;
        if (filter_var($emailConfiguration['sender_email'], FILTER_VALIDATE_EMAIL)) {
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $emailConfiguration['sender_name'] . ' <' . $emailConfiguration['sender_email'] . '>'
            );
        } else {
            $headers = array('Content-Type: text/html; charset=UTF-8');
        }

        if ($emailConfiguration['send_email'] == 'true') {
            $email_subject = $emailConfiguration['title'];
            $email_to = $requestData['email'];
           
            wp_mail($email_to, $email_subject, $htmlContent, $headers);
        }


        if ($emailConfiguration['send_admin_email'] == 'true') {
            $email_subject = $emailConfiguration['title'];
            $email_to = $emailConfiguration['admin_email'];
            $headers[] = 'Reply-To: '.$requestData['email'].''; 
            wp_mail($email_to, $email_subject, $htmlContent, $headers);
        }

        return array('state' => 'success');
    }

    function uploadGeneratedEmailToDatabase(
        $rfqwp_config_json_id,
        $firstName,
        $lastName,
        $email,
        $telephone,
        $content,
        $variables,
        $price,
        $discount,
        $discountPrice,
        $date,
        $email_success,
        $token,
        $newsletter
    ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rfqwp_user_email';
        $sql = $wpdb->prepare(
            "INSERT INTO `$table_name` (`id`, `rfqwp_config_json_id`,`first_name`, `last_name`, `email`, `telephone`, `content`,`variables`, `price`, `discount`, `discount_price`, `date`, `email_success`, `token`, `newsletter`) 
		VALUES (NULL, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
            $rfqwp_config_json_id,
            $firstName,
            $lastName,
            $email,
            $telephone,
            $content,
            $variables,
            $price,
            $discount,
            $discountPrice,
            $date,
            $email_success,
            $token,
            $newsletter
        );

        $wpdb->query($sql);
    }
}
