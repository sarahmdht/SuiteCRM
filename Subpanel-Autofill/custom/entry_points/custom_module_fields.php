<?php

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/entryPoint.php');
require_once('data/BeanFactory.php');

$id = $_GET['id'];

$bean = BeanFactory::getBean('module_name', $id);

if (!$bean) {
echo json_encode(['status' => 'error', 'message' => 'Record not found']);
exit;
}

echo json_encode([
'status' => 'success',
'result' => [
'field_1_c' => $bean->field_1_c,
'field_2_c' => $bean->field_2_c,
'field_3_c' => $bean->field_3_c,
]
]);
exit;