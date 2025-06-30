<?php
$hook_array = array();
// Custom logic hook for Contract regeneration after signature image handling
$hook_array['after_save'][] = array(
    20, // Priority of the hook
    'Regenerate PDF when contract is signed', // Name/Description of the hook
    'custom/modules/Custom_Contract/logic_hooks/RegenerateSignedPDF.php', // File where the logic is defined
    'GenerateSignedCustomContractPDF', // Class name (should match the class inside the PHP file)
    'generateSignedPDF' // Method to call in the class
);

//Custom logic hook for custom css for dashboard layout
$hook_array['after_ui_frame'][] = Array(
    1,
    'Inject custom CSS for dashboard',
    'custom/modules/Home/logic_hooks/CustomDashboardStyle.php',
    'CustomDashboardStyle',
    'injectCSS'
);
