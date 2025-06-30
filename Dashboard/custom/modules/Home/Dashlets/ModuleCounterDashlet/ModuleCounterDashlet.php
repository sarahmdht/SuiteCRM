<?php

require_once('include/Dashlets/Dashlet.php');

class ModuleCounterDashlet extends Dashlet
{
    public function __construct($id, $def = null)
    {
        parent::__construct($id, $def);
        $this->title = '📊 Module Record Counters';
    }

    public function display()
    {
        global $db;

        // Modules and their colors
        $modules = [
            'Accounts' => 'linear-gradient(135deg, rgba(74,144,226,0.85), rgba(100,181,246,0.85))',
            'Tele_Customers' => 'linear-gradient(135deg, rgba(244,180,0,0.85), rgba(255,213,79,0.85))',
            'Opportunities' => 'linear-gradient(135deg, rgba(229,57,53,0.85), rgba(244,67,54,0.85))',
            'Contacts' => 'linear-gradient(135deg, rgba(67,160,71,0.85), rgba(129,199,132,0.85))',
        ];

        // Wrapper container with wrapping
        $output = '<div style="display: flex; flex-wrap: wrap; gap: 1rem; padding: 10px;">';

        foreach ($modules as $module => $color) {
            $bean = BeanFactory::newBean($module);
            if (!$bean) {
                continue;
            }

            $table = $bean->getTableName();
            $query = "SELECT COUNT(*) AS count FROM $table WHERE deleted = 0";
            $result = $db->query($query);
            $row = $db->fetchByAssoc($result);
            $count = $row['count'] ?? 0;

            $output .= <<<HTML
                <div style="
                    flex: 1 1 calc(50% - 1rem);
                    background: $color;
                    padding: 1rem;
                    border-radius: 10px;
                    text-align: center;
                    color: white;
                    box-shadow: 1px 1px 5px rgba(0,0,0,0.2);
                ">
                    <h3 style="margin: 0; font-size: 18px;">{$GLOBALS['app_list_strings']['moduleList'][$module]}</h3>

                    <p style="font-size: 28px; font-weight: bold; margin-top: 8px;">{$count}</p>
                </div>
            HTML;
        }

        $output .= '</div>';
        return parent::display() . $output;
    }
}
