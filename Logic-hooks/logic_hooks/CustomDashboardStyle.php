<?php

class CustomDashboardStyle
{
    function injectCSS()
    {
        // echo '<link rel="stylesheet" type="text/css" href="custom/themes/SuiteP/css/custom-dashlet.css">';
        echo <<<STYLE
<style>
/* Target the recent opp dashlet table */
#dashlet_entire_e4a1baa1-6f3b-bbdc-2185-68628a686379{
    width: max-content !important;
    float: none !important;
    clear: both !important;
    display: block;
    margin: 10px auto;
}

#dashlet_e4a1baa1-6f3b-bbdc-2185-68628a686379 {
    width: max-content !important;
    float: none !important;
    clear: both !important;
    margin: 10px auto;
    display: block;
}

/* Optional: stretch the dashboard container to help */
#dashlets {
    display: block !important;
}

/* Prevent parent columns from constraining */
ul.noBullet {
    width: 100% !important;
    display: block !important;
}
</style>
STYLE;
    }
}
