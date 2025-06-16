<?php 
// To create a field manually for a custom module to match exactly the corresponding field in the a system module (There must be a relation between the two modules):
// The file should be put in the following path:
// suitecrm_data/custom/Extension/modules/Custom_Module/Ext/Vardefs/_override_sugarfield_lead_source.php

$dictionary['Custom_Module']['fields']['lead_source']['inline_edit']=true; 
$dictionary['Custom_Module']['fields']['lead_source']['comments']='Lead source (ex: Web, print)'; 
$dictionary['Custom_Module']['fields']['lead_source']['merge_filter']='disabled'; 
$dictionary['Custom_Module']['fields']['lead_source'] = array( 
  'name' => 'lead_source', 
  'vname' => 'LBL_LEAD_SOURCE', 
  'type' => 'enum',
  );                                                                                                                                                                                                                                                                                                         'options' => 'lead_source_dom', 'len' => '100', 'audited' => true, );
?>
