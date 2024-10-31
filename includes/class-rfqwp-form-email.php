<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class Class_rfqwp_form_email
{
    public function __construct()
    {
    }

    public function generateEmail($formConfiguration, $requestData, $token)
    {

        $formStyle = $formConfiguration['settings']['styles'];
        $uniqueCounter = $formConfiguration['settings']['uniqueCounter'];
        $hasAndCountPrice = $formConfiguration['settings']['hasAndCountPrice'];
        $uniqueCounterItemList = $formConfiguration['settings']['uniqueCounterItemList'];
        $couponList = $formConfiguration['settings']['couponList'];
        $price = 0;
        $discount = 0;
        $htmlContent = '
        <html> <head> <meta http-equiv="content-type" content="text/html; charset=UTF-8">';
        $htmlContent .= ' </head><body><div class="email-body" style="background-color:#ffffff;"> ';

        $htmlContent .= '<div class="content-header" style="
        max-width:800px;
        margin:auto;
        margin-bottom:20px;
        ">';

        $genderEmailValue = '';
        foreach ($formConfiguration['columnList'] as $column) {
            foreach ($column['cardList'] as $card) {
                foreach ($card['questionList'] as $question) {
                    // $htmlContent .= '<div>'. $question['type']. '</div>';
                    if ($question['app_id'] == 'gender') {
                        // print_r($question);
                        foreach ($question['itemList'] as $item) {
                            if ($item['value'] == $requestData['gender']) {
                                $genderEmailValue = $item['email_value'];
                            }
                        }
                    }
                }
            }
        }

        $header = $formConfiguration['settings']['emailConfiguration']['email_header'];
        $header  = str_replace('{{gender_text}}', $genderEmailValue, $header);
        // $header  = str_replace('{{gender_text}}', $requestData['gender'], $header);
        $header  = str_replace('{{user_first_name}}', $requestData['first_name'], $header);
        $header  = str_replace('{{user_last_name}}', $requestData['last_name'], $header);
        $htmlContent .= $header;
        $htmlContent .= '</div>';

        $htmlContent .= '<div class="main-content" style="
        max-width:800px;
        margin:auto;
        margin-bottom:20px;
        color: ' . $formConfiguration['settings']['styles']['question']['color'] . '
        ">';
        foreach ($formConfiguration['columnList'] as $column) {
            foreach ($column['cardList'] as $card) {

                if (
                    $card['required'] == 'true'  || $requestData[$card['app_id']] == (true || 'true')
                    || $card['required'] == 'input' &&  $card['requiredInputValue'] == $requestData[$card['requiredInputAppId']]
                ) {
                    $cardPrice = 0;
                    $cardDiscountPercentage = 0;


                    $htmlContent .= '<div class="form-table" style="
                        width: 100%;
                        background-color:' . $formStyle['card']['backgroundColor'] . ';
                        padding:5px;
                        margin:5px;
                        margin-top:20px; 
                        box-shadow: 1px 1px 5px ' . $formStyle['card']['color'] . ';
                    ">';
                    if ($card['headTitle']) {
                        $htmlContent .= '<div class="form-header" style="
                            font-size:18px;
                            padding:10px;   
                            margin: -5px -5px 5px -5px;                                           
                            background-color:' . $formStyle['card']['headerBackgroundColor'] . ';
                            color:' . $formStyle['card']['titleColor'] . ';
                        ">' . $card['headTitle'] . '</div>';
                    }

                    foreach ($card['questionList'] as $question) {

                        if ($question['type'] == 'address') {
                            $htmlContent .= $this->addRequestRow($formStyle, $requestData, $question['setting']['zip']);
                            $htmlContent .= $this->addRequestRow($formStyle, $requestData, $question['setting']['city']);
                            $htmlContent .= $this->addRequestRow($formStyle, $requestData, $question['setting']['street']);
                            $htmlContent .= $this->addRequestRow($formStyle, $requestData, $question['setting']['number']);
                            $htmlContent .= $this->addRequestRow($formStyle, $requestData, $question['setting']['floor']);
                            $htmlContent .= $this->addRequestRow($formStyle, $requestData, $question['setting']['door']);
                        } else if ($question['type'] == 'check') {
                            $htmlContent .= $this->addRequestRow($formStyle, $requestData, $question);
                            if ($hasAndCountPrice == 'true' && $requestData[$question['app_id']] == true) {
                                if ($question['price']) {
                                    $cardPrice += $question['price'];
                                    // unique counter collect items 
                                    if ($uniqueCounter == 'true' || $uniqueCounter == true) {
                                        $this->collectUniqueItems(
                                            $uniqueCounterItemList,
                                            $question['app_id'],
                                            'price',
                                            $question['price']
                                        );
                                    }
                                } else if ($question['discount']) {
                                    $cardDiscountPercentage += $question['discount'];
                                    // unique counter collect items 
                                    if ($uniqueCounter == 'true' || $uniqueCounter == true) {
                                        $this->collectUniqueItems(
                                            $uniqueCounterItemList,
                                            $question['app_id'],
                                            'discount',
                                            $question['discount']
                                        );
                                    }
                                }
                            }
                        } else if ($question['type'] == 'select' || $question['type'] == 'ratio' || $question['type'] == 'range') {
                            if ($requestData[$question['app_id']] != null) {
                                $htmlContent .= $this->addRequestRow($formStyle, $requestData, $question);
                                if ($hasAndCountPrice) {
                                    foreach ($question['itemList'] as $item) {
                                        if ($item['price'] && $item['value'] ==  $requestData[$question['app_id']]) {
                                            $cardPrice += $item['price'];
                                            // unique counter collect items 
                                            if ($uniqueCounter == 'true' || $uniqueCounter == true) {
                                                $this->collectUniqueItems(
                                                    $uniqueCounterItemList,
                                                    $question['app_id'],
                                                    'price',
                                                    $item['price']
                                                );
                                            }
                                        }
                                        if ($item['discount'] && $item['value'] ==  $requestData[$question['app_id']]) {
                                            $cardDiscountPercentage += $item['discount'];
                                            // unique counter collect items 
                                            if ($uniqueCounter == 'true' || $uniqueCounter == true) {
                                                $this->collectUniqueItems(
                                                    $uniqueCounterItemList,
                                                    $question['app_id'],
                                                    'discount',
                                                    $item['discount']
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                        } else if ($question['type'] == 'pickedDate') {
                            $date =  $requestData[$question['app_id']];
                            if (isset($date)) {
                                $htmlContent .= $this->addRequestRow($formStyle, $requestData, $question, $formConfiguration['settings']['emailDateForm']);
                                $dayOfWeek = date("w", strtotime($date));
                                if ($dayOfWeek == 1 && isset($question['setting']['mondayPrice'])) {
                                    $cardPrice += $question['setting']['mondayPrice'];
                                    if ($uniqueCounter == 'true' || $uniqueCounter == true) {
                                        $this->collectUniqueItems($uniqueCounterItemList, $question['app_id'], 'price', $question['setting']['mondayPrice']);
                                    }
                                }
                                if ($dayOfWeek == 2 && isset($question['setting']['tuesdayPrice'])) {
                                    $cardPrice += $question['setting']['tuesdayPrice'];
                                    if ($uniqueCounter == 'true' || $uniqueCounter == true) {
                                        $this->collectUniqueItems($uniqueCounterItemList, $question['app_id'], 'price', $question['setting']['tuesdayPrice']);
                                    }
                                }
                                if ($dayOfWeek == 3 && isset($question['setting']['wednesdayPrice'])) {
                                    $cardPrice += $question['setting']['wednesdayPrice'];
                                    if ($uniqueCounter == 'true' || $uniqueCounter == true) {
                                        $this->collectUniqueItems($uniqueCounterItemList, $question['app_id'], 'price', $question['setting']['wednesdayPrice']);
                                    }
                                }
                                if ($dayOfWeek == 4 && isset($question['setting']['thursdayPrice'])) {
                                    $cardPrice += $question['setting']['thursdayPrice'];
                                    if ($uniqueCounter == 'true' || $uniqueCounter == true) {
                                        $this->collectUniqueItems($uniqueCounterItemList, $question['app_id'], 'price', $question['setting']['thursdayPrice']);
                                    }
                                }
                                if ($dayOfWeek == 5 && isset($question['setting']['fridayPrice'])) {
                                    $cardPrice += $question['setting']['fridayPrice'];
                                    if ($uniqueCounter == 'true' || $uniqueCounter == true) {
                                        $this->collectUniqueItems($uniqueCounterItemList, $question['app_id'], 'price', $question['setting']['fridayPrice']);
                                    }
                                }
                                if ($dayOfWeek == 6 && isset($question['setting']['saturdayPrice'])) {
                                    $cardPrice += $question['setting']['saturdayPrice'];
                                    if ($uniqueCounter == 'true' || $uniqueCounter == true) {
                                        $this->collectUniqueItems($uniqueCounterItemList, $question['app_id'], 'price', $question['setting']['saturdayPrice']);
                                    }
                                }
                                if ($dayOfWeek == 0 && isset($question['setting']['sundayPrice'])) {
                                    $cardPrice += $question['setting']['sundayPrice'];
                                    if ($uniqueCounter == 'true' || $uniqueCounter == true) {
                                        $this->collectUniqueItems($uniqueCounterItemList, $question['app_id'], 'price', $question['setting']['sundayPrice']);
                                    }
                                }
                            }
                        } else {
                            $htmlContent .= $this->addRequestRow($formStyle, $requestData, $question);
                            if ($question['app_id'] == 'coupon' && isset($couponList) && $hasAndCountPrice == 'true') {

                                foreach ($couponList as $coupon) {
                                    //  if the coupon is exists and the it is valid
                                    if ($coupon['name'] == $requestData['coupon'] && strtotime($coupon['expiredDate']) > strtotime('now')) {
                                        $cardDiscountPercentage += $coupon['discount'];
                                        // set unique coupon
                                        if ($uniqueCounter == 'true') {
                                            $this->collectUniqueItems($uniqueCounterItemList, $question['app_id'], 'discount', $coupon['discount']);
                                        }
                                    }
                                }
                            }
                        }
                    }


                    // print if does not have unique counter 
                    if ($uniqueCounter == 'false' || $uniqueCounter == false || $uniqueCounter == null) {
                        if ($hasAndCountPrice == 'true' && $cardPrice != 0) {
                            $htmlContent .= $this->printPriceWithMarker(
                                $formConfiguration['settings'],
                                $cardPrice,
                                'font-size: 20px; padding: 15px; color: ' . $formConfiguration['settings']['styles']['card']['color'] . '; ',
                                $card['afterCardPriceNote'],
                                null
                            );
                        }
                        if ($hasAndCountPrice  == 'true' && $cardDiscountPercentage != 0) {
                            $htmlContent .= $this->printDiscountPercentage(
                                $cardDiscountPercentage,
                                'font-size: 20px; padding: 15px; color: ' . $formConfiguration['settings']['styles']['card']['color'] . ';',
                                $card['afterCardDiscountNote'],
                                null
                            );
                        }
                    }
                    $discount += $cardDiscountPercentage;
                    $price += $cardPrice;
                    $htmlContent .= '</div>';
                }
            }
        }

        // print price
        if ($hasAndCountPrice  == 'true') {


            // basic price counter
            if ($uniqueCounter == 'false' || $uniqueCounter == null) {
                if ($price != 0 && $discount != 0) {
                    $htmlContent .= '<div class="all-price-row" style="color: ' . $formConfiguration['settings']['styles']['formTitle']['color'] . '; margin:20px;padding:10px;">';
                    $htmlContent .= '<div style="text-decoration: line-through">';
                    $htmlContent .= $this->printPriceWithMarker(
                        $formConfiguration['settings'],
                        $price,
                        'font-size:24px;',
                        '',
                        $formConfiguration['settings']['allPriceText']
                    );
                    $htmlContent .= '</div>';

                    $htmlContent .= '<div >';
                    $htmlContent .= $this->printDiscountPercentage(
                        $discount,
                        'font-size:24px;',
                        $formConfiguration['settings']['afterCounterDiscountNote'],
                        $formConfiguration['settings']['allDiscountText']
                    );
                    $htmlContent .= '</div>';

                    $htmlContent .= '<div >';
                    $htmlContent .= $this->printPriceWithMarker(
                        $formConfiguration['settings'],
                        (round(($price - ($price * $discount / 100)), 2)),
                        'font-size:24px;',
                        $formConfiguration['settings']['afterCounterPriceNote'],
                        $formConfiguration['settings']['allDiscountedPriceText']
                    );
                    $htmlContent .= '</div>';
                    $htmlContent .= '</div>';
                } else if ($price != 0) {
                    $htmlContent .= '<div class="all-price-row" style="color: ' . $formConfiguration['settings']['styles']['formTitle']['color'] . '; margin:20px;padding:10px;">';
                    $htmlContent .= $this->printPriceWithMarker(
                        $formConfiguration['settings'],
                        $price,
                        'font-size:24px;',
                        $formConfiguration['settings']['afterCounterPriceNote'],
                        $formConfiguration['settings']['allPriceText']
                    );
                    $htmlContent .= '</div>';
                }
            }

            // unique price counter
            if ($uniqueCounter == 'true') {
                $discount = 0;
                $priceFgv = '';
                $price = '';
                $printPrice = '';
                try {
                    $priceFgv = $this->priceCountWithUniqueCounter($uniqueCounterItemList, $requestData);
                    $price = round(eval('return ' . $priceFgv . ';'), 2);
                } catch (ParseError $e) {
                }
                $printPrice = $price;
                if ($formConfiguration['settings']['printUniqueCounterFunction'] == 'true') {
                    $printPrice = $priceFgv . ' = ' . $printPrice;
                }

                $htmlContent .= '<div class="all-price-row" style="color: ' . $formConfiguration['settings']['styles']['formTitle']['color'] . '; margin:20px;padding:10px;">';
                $htmlContent .= $this->printPriceWithMarker(
                    $formConfiguration['settings'],
                    $printPrice,
                    'font-size:24px;',
                    $formConfiguration['settings']['afterUniqueCounterNote'],
                    $formConfiguration['settings']['allPriceText']
                );
                $htmlContent .= '</div>';
            }
        } else {
            $price = 0;
            $discount = 0;
        }
        // email response 
        if ($formConfiguration['settings']['emailConfiguration']['email_response_enable'] == 'true') {
            $htmlContent .= $this->getEmailResponseRow($formConfiguration, $requestData['email'] ? $requestData['email'] : '', $token);
        }
        $htmlContent .= ' </div>';


        $htmlContent .= '<div class="content-footer" style="
            max-width:800px;
            margin:auto;
            margin-bottom:20px;
        ">' . $formConfiguration['settings']['emailConfiguration']['email_footer'] . '</div>';

        $htmlContent .= ' </body></html>';
        $htmlContent =  preg_replace("/\r|\n|\t|\s{2,}/", "", $htmlContent);


        $returnData = array(
            "htmlEmailContent" => $htmlContent,
            "price" => $price,
            "discount" => $discount
        );
        // print_r($htmlContent);
        // exit;
        return $returnData;
    }

    private function addRequestRow($style, $requestData, $question, $emailDateForm = null)
    {
        $htmlContent = '<div class="question-row" style="
            background-color:' . $style['question']['backgroundColor'] . ';
            border-bottom: 1px solid ' . $style['formTitle']['color'] . ';
            padding:5px;
        ">';
        $htmlContent .= '<div class="form-question" style="
             padding:5px;
            display: inline-block;	
            margin-left:10px;
            width:40%;
            min-width:200px;
        "><strong>';
        $htmlContent .= $question['title'];
        $htmlContent .= "</strong></div>";
        $htmlContent .= '<div class="form-value" style="
            padding:5px;
            display: inline-block;	
            padding-left: 15px !important;
            text-align: start;
        ">';

        if ($question['type'] == 'check' && $requestData[$question['app_id']] == true && $question['setting']['checkedEmailText']) {
            $htmlContent .= $question['setting']['checkedEmailText'];
        } else if ($question['type'] == 'check' && $requestData[$question['app_id']] != true && $question['setting']['uncheckedEmailText']) {
            $htmlContent .= $question['setting']['uncheckedEmailText'];
        } else if ($question['type'] == 'acceptAndSend' && $question['setting']['emailAcceptText']) {
            $htmlContent .= $question['setting']['emailAcceptText'];
        } else if ($question['type'] == 'pickedDate' && $requestData[$question['app_id']]) {
            $htmlContent .= date($emailDateForm, strtotime($requestData[$question['app_id']]));
        } else {
            $htmlContent .= strlen($requestData[$question['app_id']]) > 0 ? $requestData[$question['app_id']] : '';
        }

        $htmlContent .= "</div>";
        if ($question['afterInputNoteOnlyEmail']) {
            $htmlContent .= '<div class="input-note" style="
            font-size:16px; 
            padding-left: 16px;
            ">' . $question['afterInputNoteOnlyEmail'] . '</div>';
        }
        $htmlContent .= '</div>';
        return $htmlContent;
    }

    function printPriceWithMarker($settings, $price, $price_style, $afterCardPriceNote, $beforePriceNote)
    {
        $htmlContent = '<div style="' . $price_style . '">';
        if ($beforePriceNote) {
            $htmlContent .= '<span style="display:inline-block; width:300px">' . $beforePriceNote . '</span>';
        }
        $htmlContent .= '<span style="display: inline-block;">';
        $htmlContent .= '' . ($settings['priceTypePlace'] == 'front') ? $settings['priceType'] : '';
        $htmlContent .= ' ' . $price . ' ';
        $htmlContent .= '' . ($settings['priceTypePlace'] == 'back') ? $settings['priceType'] : '';
        $htmlContent .=   '<span style="font-size:16px;">' . $afterCardPriceNote . '</span>';
        $htmlContent .= '</span>';
        $htmlContent .= '</div>';
        return $htmlContent;
    }

    function printDiscountPercentage($discount, $price_style, $afterCardDiscountNote, $beforePriceNote)
    {
        $htmlContent = '<div style="' . $price_style . '">';
        if ($beforePriceNote) {
            $htmlContent .= '<span style="display:inline-block; width:300px">' . $beforePriceNote . '</span>';
        }
        $htmlContent .= '<span style ="display: inline-block;">';
        $htmlContent .= ' -' . $discount . '% ';
        $htmlContent .=  '<span style="font-size:16px;">' . $afterCardDiscountNote . '</span>';
        $htmlContent .= '</span>';
        $htmlContent .= '</div>';
        return $htmlContent;
    }

    function getEmailResponseRow($formConfiguration, $email, $token)
    {
        $htmlContent = '			
			<div style="margin:15px;">
                <a class="reject" style ="
                padding:10px;
				padding-left:30px;
				padding-right:30px;
				font-size:20px;
				margin:15px;
				cursor:pointer;
				border: 1px solid ' . $formConfiguration['settings']['styles']['button']['backgroundColor'] . '; 
                text-decoration: none !important;
                display:inline-block;
                color: ' . $formConfiguration['settings']['styles']['button']['backgroundColor'] . '; 
                // background-color: transparent; 
                " href="' . $formConfiguration['settings']['emailConfiguration']['email_response_page_url'] . '?response=reject&email=' . $email . '&token=' . $token . '">'
            . $formConfiguration['settings']['emailConfiguration']['email_refuse_button_text'] . '</a>				
                <a class="accept" style ="
                padding:10px;
				padding-left:30px;
				padding-right:30px;
				font-size:20px;
				margin:15px;
				cursor:pointer; 
                text-decoration: none !important;
                display:inline-block;
				color: ' . $formConfiguration['settings']['styles']['button']['color'] . ';
				background-color: ' . $formConfiguration['settings']['styles']['button']['backgroundColor'] . ';
                " href="' . $formConfiguration['settings']['emailConfiguration']['email_response_page_url'] . '?response=accept&email=' . $email . '&token=' . $token . '">'
            . $formConfiguration['settings']['emailConfiguration']['email_accept_button_text'] . '</a>				
			</div>
		';
        return $htmlContent;
    }

    function collectUniqueItems(&$uniqueCounterItemList, $app_id, $type, $value)
    {
        foreach ($uniqueCounterItemList as &$uniqueItem) {
            if ($uniqueItem['type'] == 'question' && $uniqueItem['question']['app_id'] == $app_id) {
                if ($type == $uniqueItem['question']['type']) {
                    $uniqueItem['value'] = $value && $value ? $value : 0;
                }
            } else if ($uniqueItem['type'] == 'bracket') {
                $this->collectUniqueItems($uniqueItem['content'], $app_id, $type, $value);
            } else if ($uniqueItem['type'] == 'condition') {
                $this->collectUniqueItems($uniqueItem['leftCondition'], $app_id, $type, $value);
                $this->collectUniqueItems($uniqueItem['rightCondition'], $app_id, $type, $value);
                $this->collectUniqueItems($uniqueItem['content'], $app_id, $type, $value);
            }
        }
    }

    function priceCountWithUniqueCounter($uniqueCounterItemList, $requestData)
    {
        return ($this->countContainerContent($uniqueCounterItemList, $requestData));
    }
    function countContainerContent($list, $requestData)
    {
        $result = '';
        foreach ($list as $item) {
            if ($item['type'] == 'text') {
                $result .= $item['name'];
            } else if ($item['type'] == 'math') {
                $result .= $item['name'];
            } else if ($item['type'] == 'bracket') {
                $result .= '(' . $this->countContainerContent($item['content'], $requestData) . ')';
            } else if ($item['type'] == 'question') {
                $result .= isset($item['value']) ? $item['value'] : 0;
            } else if ($item['type'] == 'condition') {
                $leftCondition = eval("return " . $this->countContainerContent($item['leftCondition'], $requestData) . ";");
                $rightCondition = eval("return " . $this->countContainerContent($item['rightCondition'], $requestData) . ";");

                if ($item['relation'] == 'equal' && $leftCondition == $rightCondition) {
                    $result .=  $this->countContainerContent($item['content'], $requestData);
                }

                if ($item['relation'] == 'notEqual' && $leftCondition != $rightCondition) {
                    $result .= $this->countContainerContent($item['content'], $requestData);
                }

                if ($item['relation'] == 'greater' && $leftCondition > $rightCondition) {
                    $result .= $this->countContainerContent($item['content'], $requestData);
                }

                if ($item['relation'] == 'less' && $leftCondition < $rightCondition) {
                    $result .=  $this->countContainerContent($item['content'], $requestData);
                }
            }
        }
        return $result;
    }
}
