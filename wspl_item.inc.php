<?php

$title_right_html = '';
$title_left_html  = '';
$modbodyhtml = '';

// if this is a display host screen then go ahead and make a puppet facts window
if ($extravars['window_name'] == 'display_host') {

// create workspace menu items
// This is where you list an array of menu items to display for this workspace
$modwsmenu[0]['menutitle'] = 'Puppet External Node Attributes';
$modwsmenu[0]['authname']  = 'advanced';
$modwsmenu[0]['commandjs'] = "toggle_window('puppet_external_nodes');";
$modwsmenu[0]['tooltip'] = 'Manage Puppet External node settings';


    // Get a list of node info
    list ($status, $crows, $pclasses) = db_get_records( $onadb, 'puppet_node_classes', "host_id = {$record['id']}", 'name ASC');
    list ($status, $erows, $penv) = db_get_record( $onadb, 'puppet_node_environments', "host_id = {$record['id']}");
    list ($status, $prows, $pparm) = db_get_records( $onadb, 'puppet_node_parameters', "host_id = {$record['id']}", 'name ASC');

    if ($crows or $erows or $prows) {
        $title_left_html .= <<<EOL
                    &nbsp;Puppet Node Info
EOL;

        $title_right_html .= <<<EOL
        <a title="Click to manage puppet node data"
           onClick="toggle_window('puppet_external_nodes');"
        ><img src="{$images}/silk/page_edit.png" border="0"></a>
EOL;

        $modbodyhtml .= <<<EOL
            <div style="max-height: 200px;overflow-y: auto;overflow-x: hidden;">
                <table width=100% cellspacing="0" border="0" cellpadding="0" style="margin-bottom: 8px;padding-right: 15px;">
                <tr onmouseover="this.className='row-highlight'" onmouseout="this.className='row-normal'">
                    <td align="right" nowrap="true" ><b>Env</b>&nbsp;</td>
                    <td nowrap="true" class="padding" align="left" style="border-left: 1px solid #aaaaaa;">{$penv['name']}</td>
                </tr>

EOL;

        // Print out some of the info
        $i=0;
        foreach($pclasses as $class) {
	    $sty = $titlething='';
            if ($i === 0) {$sty='border-top: 1px solid #aaaaaa;'; $titlething='<b>Class</b>';}
            $modbodyhtml .= <<<EOL
                <tr onmouseover="this.className='row-highlight'" onmouseout="this.className='row-normal'" {$sty}>
                    <td align="right" nowrap="true" style="{$sty}">{$titlething}</td>
                    <td nowrap="true" class="padding" align="left" style="border-left: 1px solid #aaaaaa;{$sty}">{$class['name']}</td>
                </tr>

EOL;
            $i++;
    }

        // Print out some of the info
        $i=0;
        foreach($pparm as $parm) {
	    $sty = $titlething='';
            if ($i === 0) {$sty='border-top: 1px solid #aaaaaa;'; $titlething='<b>Parms</b>';}
            $modbodyhtml .= <<<EOL
                <tr onmouseover="this.className='row-highlight'" onmouseout="this.className='row-normal'">
                    <td align="right" nowrap="true" style="{$sty}">{$titlething}</td>
                    <td nowrap="true" class="padding" align="left" style="border-left: 1px solid #aaaaaa;{$sty}">{$parm['name']} => {$parm['value']}</td>
                </tr>

EOL;
            $i++;
    }


        $modbodyhtml .= <<<EOL
              </table>
            <div>
EOL;
    }
}


?>
