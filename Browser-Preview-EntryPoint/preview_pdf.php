<?php
if (!defined('sugarEntry')) define('sugarEntry', true);
require_once('include/entryPoint.php');

$id = $_GET['id'] ?? '';

$bean = BeanFactory::getBean('Contract', $id);
if (!$bean || empty($bean->id)) {
    echo "Invalid document ID.";
    exit;
}

// The file is stored in SuiteCRM as: upload/<id> (no extension)
$filepath = 'upload/' . $bean->id;
$filename = $bean->filename ?: ($bean->id . '.pdf');

if (file_exists($filepath)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"'); // replace "inline" with "attachment" to make it download
    readfile($filepath);
    exit;
} else {
    echo "PDF not found at: $filepath";
    exit;
}
