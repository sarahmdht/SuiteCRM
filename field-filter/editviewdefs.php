<?php
// For example: 
// If a contact field is a relate field in "Accounts" module, that searches contacts,
// but we need it to be filtered to show only the contacts that relate to that account by default.
//A filtering parameter is sent using “initial_filter” from:
//custom/modules/Accounts/metadata/editviewdefs.php definition.

'displayParams' => array( 'initial_filter' => '&account_name_advanced={$fields.name.value}', ),

