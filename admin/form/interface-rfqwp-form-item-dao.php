<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

interface FormItem
{
    public function getListByFormId($formId);
    public function delete($item);
    public function create($item);
    public function update($item);
    public function requestList($parentId, $itemList);
    public function deleteByFormId($parentId);
}
