<?php


// Lets do some initial install related stuff
if (file_exists(dirname(__FILE__)."/install.php")) {
    printmsg("DEBUG => Found install file for ".basename(dirname(__FILE__))." plugin.", 1);
    include(dirname(__FILE__)."/install.php");
} else {

// Place initial popupwindow content here if this plugin uses one.





$window['title'] = "Puppet Node Maint";

$window['js'] .= <<<EOL
    /* Put a minimize icon in the title bar */
    el('{$window_name}_title_r').innerHTML =
        '&nbsp;<a onClick="toggle_window(\'{$window_name}\');" title="Minimize window" style="cursor: pointer;"><img src="{$images}/icon_minimize.gif" border="0" /></a>' +
        el('{$window_name}_title_r').innerHTML;

    /* Put a help icon in the title bar */
    el('{$window_name}_title_r').innerHTML =
        '&nbsp;<a href="{$_ENV['help_url']}{$window_name}" target="null" title="Help" style="cursor: pointer;"><img src="{$images}/silk/help.png" border="0" /></a>' +
        el('{$window_name}_title_r').innerHTML;

    /* using the list interfaces form is kinda kludgy but it works for now */
    xajax_window_submit('{$window_name}', xajax.getFormValues('list_interfaces_filter_form'), 'edit_puppet_ext_node');
EOL;

global $conf, $base;

$window['html'] .= <<<EOL
    <div id='{$window_name}_content_id'>
        {$conf['loading_icon']}
    </div>
EOL;



}




// GUI edit form for external nodes
function ws_edit_puppet_ext_node($window_name, $form) {
    global $conf, $self, $onadb;
    global $font_family, $color, $style, $images;


    // Check permissions
    if (!auth('puppet_ext_node_admin')) {
        $response = new xajaxResponse();
        $response->addScript("alert('Permission denied!');");
        return($response->getXML());
    }

    // If the group supplied an array in a string, build the array and store it in $form
    $form = parse_options_string($form);

    // check that we found a host_id
    if (!$form['host_id']) {
        $response = new xajaxResponse();
        $response->addAssign("{$window_name}_content_id", "innerHTML", "ERROR => Unable to find host id.");
        return($response->getXML());
    }

    // get host info
    list($status, $rows, $host) = ona_get_host_record(array('id' => $form['host_id']));

    list($status, $rows, $env) = db_get_record($onadb, 'puppet_node_environments', array('host_id' => $host['id']));
    list($status, $classrows, $classes) = db_get_records($onadb, 'puppet_node_classes', array('host_id' => $host['id']), 'name ASC');
    list($status, $parmrows, $parms) = db_get_records($onadb, 'puppet_node_parameters', array('host_id' => $host['id']), 'name ASC');

    $html = <<<EOL

    <div style="background-color: {$color['window_content_bg']};" >
        <div style="padding: 10px 20px 10px 20px; "><b><u>Puppet Node: {$host['fqdn']}</u></b></div>
        <div style="padding-left: 20px; padding-right: 20px; text-align: right;">Environment
    
          <!-- Puppet EXT Node Edit Form -->
          <form id="{$window_name}_edit_form" onSubmit="return false;">
            <input type="hidden" name="host_id" value="{$host['id']}">
            <input type="hidden" name="host" value="{$host['id']}">
            <input type="hidden" name="module" value="puppet_node_env_set">
            <input type="hidden" name="js" value="{$form['js']}">
            <input
                   id="set_pen_env"
                   name="set_pen_env"
                   alt="Environment"
                   value="{$env['name']}"
                   class="edit"
                   type="text"
                   size="20" maxlength="64"
            >
          </form>
            <a onClick="xajax_window_submit('{$window_name}', xajax.getFormValues('{$window_name}_edit_form'), 'save');"
               title="Set environment"
               style="cursor: pointer;">
                   <img src="{$images}/silk/disk.png" border="0" />
            </a>
        </div>
    
        <div style="padding-left: 20px; padding-right: 20px; text-align: right;border-bottom: 1px solid #aaaaaa;">Classes
    
          <form id="{$window_name}_edit_class_form" onSubmit="return false;">
            <input type="hidden" name="host" value="{$host['id']}">
            <input type="hidden" name="module" value="puppet_node_class_add">
            <input type="hidden" name="js" value="{$form['js']}">
              <input
                        id="set_pen_class"
                        name="set_pen_class"
                        alt="Class"
                        value=""
                        class="edit"
                        type="text"
                        size="20" maxlength="64"
                    >
          </form>
            <a onClick="
                        var newdiv = document.createElement('div');
                        var clname = el('set_pen_class').value;
                        var rmname = 'class_'+clname;
                        var parent = el('edit_puppet_node_class_list');
                        newdiv.innerHTML = '<div id=\'class_' +clname+ '\' style=\'padding-left: 20px; padding-right: 20px;\'> <a title=\'Remove class\' style=\'cursor: pointer;border-right: 1px solid #aaaaaa;\' \
                                            onClick=xajax_window_submit(\'{$window_name}\',\'module=puppet_node_class_del,host={$host['id']},class='+clname+'\',\'delete\');removeElement(\''+rmname+'\');><img src=\'{$images}/silk/delete.png\' border=\'0\' /></a> '+clname+'</div> ';
                        newdiv.setAttribute('id','class_'+clname);
                        parent.insertBefore(newdiv,parent.firstChild);
                        xajax_window_submit('{$window_name}', xajax.getFormValues('{$window_name}_edit_class_form'), 'save');el('set_pen_class').value='';"
               title="Add class"
               style="cursor: pointer;">
                   <img src="{$images}/silk/disk.png" border="0" />
            </a>
        </div>
        <div id="edit_puppet_node_class_list" style="margin-left: 20px; margin-right: 20px; max-height: 100px;overflow-y: auto;overflow-x: hidden;">
EOL;

    if ($classrows) {
        foreach($classes as $class) {
            $html .= <<<EOL
        <div id="db_class_{$class['name']}" style="padding-left: 20px; padding-right: 20px; ">
            <a onClick="xajax_window_submit('{$window_name}', 'module=>puppet_node_class_del,host=>{$host['id']},class=>{$class['name']}', 'delete');removeElement('db_class_{$class['name']}');"
               title="Remove class"
               style="cursor: pointer;border-right: 1px solid #aaaaaa;">
                   <img src="{$images}/silk/delete.png" border="0" /></a>
            {$class['name']}
        </div>
EOL;
        }
    }



        // puppet parm entries

        $html .= <<<EOL
        </div>
        <div style="padding: 10px 20px 0px 20px; text-align: right;border-bottom: 1px solid #aaaaaa;border-top: 1px solid #aaaaaa;">Parms
    
          <form id="{$window_name}_edit_parm_form" onSubmit="return false;">
            <input type="hidden" name="host" value="{$host['id']}">
            <input type="hidden" name="module" value="puppet_node_parm_add">
            <input type="hidden" name="js" value="{$form['js']}">
              <input
                        id="set_pen_parm"
                        name="set_pen_parm"
                        alt="Parm"
                        title="Name"
                        value=""
                        class="edit"
                        type="text"
                        size="20" maxlength="64"
                    >=><input
                        id="set_pen_value"
                        name="set_pen_value"
                        alt="Value"
                        title="Value"
                        value=""
                        class="edit"
                        type="text"
                        size="20" maxlength="64"
                    >
          </form>
            <a onClick="
                        var newdiv = document.createElement('div');
                        var pname = el('set_pen_parm').value;
                        var pval = el('set_pen_value').value;
                        var rmname = 'parm_'+pname;
                        var parent = el('edit_puppet_node_parm_list');
                        newdiv.innerHTML = '<div id=\'parm_' +pname+ '\' style=\'padding-left: 20px; padding-right: 20px;\'> <a title=\'Remove parm\' style=\'cursor: pointer;border-right: 1px solid #aaaaaa;\' \
                                            onClick=xajax_window_submit(\'{$window_name}\',\'module=puppet_node_parm_del,host={$host['id']},parm='+pname+'\',\'delete\');removeElement(\''+rmname+'\');><img src=\'{$images}/silk/delete.png\' border=\'0\' /></a> '+pname+' => '+pval+'</div>';
                        newdiv.setAttribute('id','parm_'+pname);
                        parent.insertBefore(newdiv,parent.firstChild);
                        xajax_window_submit('{$window_name}', xajax.getFormValues('{$window_name}_edit_parm_form'), 'save');el('set_pen_parm').value='';el('set_pen_value').value='';"
               title="Add parm"
               style="cursor: pointer;">
                   <img src="{$images}/silk/disk.png" border="0" />
             </a>
        </div>
        <div id="edit_puppet_node_parm_list" style="margin-left: 20px; margin-right: 20px; max-height: 100px;overflow-y: auto;overflow-x: hidden;">
EOL;

    // If we have existing parms, loop through and print them
    if ($parmrows) {
        foreach($parms as $parm) {
            $html .= <<<EOL
        <div id="db_parm_{$parm['name']}" style="padding-left: 20px; padding-right: 20px; ">
            <a onClick="xajax_window_submit('{$window_name}', 'module=>puppet_node_parm_del,host=>{$host['id']},parm=>{$parm['name']}', 'delete');removeElement('db_parm_{$parm['name']}');"
               title="Remove parm"
               style="cursor: pointer;border-right: 1px solid #aaaaaa;">
                   <img src="{$images}/silk/delete.png" border="0" /></a>
            {$parm['name']} => {$parm['value']}
        </div>
EOL;
        }
    }




        $html .= <<<EOL
        </div>
        <div style="padding: 10px 20px 10px 20px; text-align: right;border-top: 1px solid #aaaaaa;">

            Copy from:<input id="move_hostname" name="host" type="text" class="edit" size="24" autocomplete="off" />
            <div id="suggest_move_hostname" class="suggest"></div>
            <input class="edit" type="button" name="copy" value="Copy" onClick="var from=el('move_hostname').value; if(from) {xajax_window_submit('{$window_name}', 'module=>puppet_node_copy,to=>{$host['id']},from=>'+from, 'save');xajax_window_submit('work_space', 'xajax_window_submit(\'display_host\', \'host_id=>{$host['id']}\', \'display\')');removeElement('{$window_name}');}">&nbsp;&nbsp;&nbsp;&nbsp;

            <input class="edit" type="button" name="close" value="Close" onClick="xajax_window_submit('work_space', 'xajax_window_submit(\'display_host\', \'host_id=>{$host['id']}\', \'display\')');removeElement('{$window_name}');">
        </div>
    </div>
EOL;

    $js .= <<<EOL
        suggest_setup('move_hostname', 'suggest_move_hostname');
EOL;

    // Insert the new table into the window
    // Instantiate the xajaxResponse object
    $response = new xajaxResponse();
    $response->addAssign("{$window_name}_content_id", "innerHTML", $html);
    $response->addScript($js);
    return($response->getXML());
}






//////////////////////////////////////////////////////////////////////////////
// Function:
//     Save Form
//
// Description:
//     Creates/updates an puppet node record.
//////////////////////////////////////////////////////////////////////////////
function ws_save($window_name, $form='') {
    global $include, $conf, $self, $onadb;

    // If the group supplied an array in a string, build the array and store it in $form
    $form = parse_options_string($form);


    // Check permissions (there is no interface_add, it's merged with host_add)
    if (!auth('puppet_ext_node_admin')) {
        $response = new xajaxResponse();
        $response->addScript("alert('Permission denied!');");
        return($response->getXML());
    }


    // Instantiate the xajaxResponse object
    $response = new xajaxResponse();
    $js = '';


    // Validate input
    if (($form['module'] == 'puppet_node_copy') and ($form['from'] == '')) {
        $response->addScript("alert('Please select a copy from host first!');");
        return($response->getXML());
    }

    // MP: maybe someday check to see if the values are the same and dont run the update process?

    $form['env'] = $form['set_pen_env'];
    $form['class'] = $form['set_pen_class'];
    $form['parm'] = $form['set_pen_parm'];
    $form['value'] = $form['set_pen_value'];

    // Run the module
    list($status, $output) = run_module($form['module'], $form);

    // If the module returned an error code display a popup warning
    if ($status)
        $js .= "removeElement('parm_{$form['parm']}');removeElement('class_{$form['class']}');alert('Save failed.\\n". preg_replace('/[\s\']+/', ' ', $self['error']) . "');";
    else {
        if ($form['js']) $js .= $form['js'];
    }

    // Insert the new table into the window
    $response->addScript($js);
    return($response->getXML());
}





//////////////////////////////////////////////////////////////////////////////
// Function:
//     delete Form
//
// Description:
//     deletes puppet node information as passed in from GUI
//////////////////////////////////////////////////////////////////////////////
function ws_delete($window_name, $form='') {
    global $include, $conf, $self, $onadb;

    // MP: stupid hack for javascript escaping problems I ran into
    if (!strpos($form,'>')) $form = str_replace('=','=>',$form);

    // If the group supplied an array in a string, build the array and store it in $form
    $form = parse_options_string($form);

    // Check permissions (there is no interface_add, it's merged with host_add)
    if (!auth('puppet_ext_node_admin')) {
        $response = new xajaxResponse();
        $response->addScript("alert('Permission denied!');");
        return($response->getXML());
    }

    // Instantiate the xajaxResponse object
    $response = new xajaxResponse();
    $js = '';

    // Run the module
    list($status, $output) = run_module($form['module'], $form);

    // If the module returned an error code display a popup warning
    if ($status)
        $js .= "alert('Delete failed.\\n". preg_replace('/[\s\']+/', ' ', $self['error']) . "');";
    else {
        if ($form['js']) $js .= $form['js'];
    }

    // Insert the new table into the window
    $response->addScript($js);
    return($response->getXML());

}






///////////////////////////////////////////////////////////////////////
//  Function: puppet_node_copy (string $options='')
//
//  Input Options:
//    $options = key=value pairs of options for this function.
//               multiple sets of key=value pairs should be separated
//               by an "&" symbol.
//
//  Output:
//    Returns a two part list:
//      1. The exit status of the function.  0 on success, non-zero on
//         error.  All errors messages are stored in $self['error'].
//      2. A textual message for display on the console or web interface.
//
//  Example: list($status, $result) = puppet_node_copy('host=test&type=something');
///////////////////////////////////////////////////////////////////////
function puppet_node_copy($options="") {
    global $conf, $self, $onadb;

    // Version - UPDATE on every edit!
    $version = '1.00';

    printmsg("DEBUG => puppet_node_copy({$options}) called", 3);

    // Parse incoming options string to an array
    $options = parse_options($options);

    // Return the usage summary if we need to
    if ($options['help'] or !($options['from'] and $options['to']) ) {
        // NOTE: Help message lines should not exceed 80 characters for proper display on a console
        $self['error'] = 'ERROR => Insufficient parameters';
        return(array(1,
<<<EOM

puppet_node_copy-v{$version}
Copy puppet external node values from one host to another

  Synopsis: puppet_node_copy [KEY=VALUE] ...

    from=(name|IP)       FQDN or ID of host to copy from
    to=(name|IP)         FQDN or ID of host to copy to


EOM
        ));
    }

    // Check permissions
    if (!auth('puppet_ext_node_admin')) {
        $self['error'] = "Permission denied!";
        printmsg($self['error'], 0);
        return(array(10, $self['error'] . "\n"));
    }


    // Find the hosts
    list($status, $rows, $from_host) = ona_find_host($options['from']);
    list($status, $rows, $to_host) = ona_find_host($options['to']);
    if (!$from_host['id'] or !$to_host['id']) {
        $self['error'] = "ERROR=> Unable to find the from OR to host";
        printmsg($self['error'], 0);
        return(array(20, $self['error'] . "\n"));
    }


    // gather data from the from host
    list($status, $ferows, $fenv) = db_get_record($onadb, 'puppet_node_environments', array('host_id' => $from_host['id']));
    list($status, $fcrows, $fclasses) = db_get_records($onadb, 'puppet_node_classes', array('host_id' => $from_host['id']));
    list($status, $fprows, $fparms) = db_get_records($onadb, 'puppet_node_parameters', array('host_id' => $from_host['id']));

    // gather info from the to host
    list($status, $terows, $tenv) = db_get_record($onadb, 'puppet_node_environments', array('host_id' => $to_host['id']));
    list($status, $tcrows, $tclasses) = db_get_record($onadb, 'puppet_node_classes', array('host_id' => $to_host['id']));
    list($status, $tprows, $tparms) = db_get_record($onadb, 'puppet_node_classes', array('host_id' => $to_host['id']));

    // Process environments.  update existing or add new ones
    list($status, $output) = run_module('puppet_node_env_set', array('host'=>$to_host['id'],'env'=>$fenv['name']));
    if ($status) {
        $self['error'] = "ERROR => puppet_node_copy() Unable to copy environment: " . $output;
        printmsg($self['error'], 0);
        return(array(6, $self['error'] . "\n"));
    }

    // Process classes
    // Delete all the classes from the to host if there are any
    list($status, $rows) = db_delete_records( $onadb, 'puppet_node_classes', array('host_id' => $to_host['id']));

    // loop through the from classes and run the add class module on them for the to host
    foreach($fclasses as $cl) {
        list($status, $output) = run_module('puppet_node_class_add', array('host'=>$to_host['id'],'class'=>$cl['name']));
        if ($status) {
            $self['error'] = "ERROR => puppet_node_copy() Unable to copy class: " . $output;
            printmsg($self['error'], 0);
            return(array(6, $self['error'] . "\n"));
        }
    }

    // Process parms
    // Delete all the parms from the to host if there are any
    list($status, $rows) = db_delete_records( $onadb, 'puppet_node_parameters', array('host_id' => $to_host['id']));

    // loop through the from parms and run the add parm module on them for the to host
    foreach($fparms as $par) {
        list($status, $output) = run_module('puppet_node_parm_add', array('host'=>$to_host['id'],'parm'=>$par['name'],'value'=>$par['value']));
        if ($status) {
            $self['error'] = "ERROR => puppet_node_copy() Unable to copy parm: " . $output;
            printmsg($self['error'], 0);
            return(array(6, $self['error'] . "\n"));
        }
    }
 


    // start an output message
    $text = "INFO => Puppet external node information copied from host '{$from_host['fqdn']}' to host '{$to_host['fqdn']}'.\n";
    printmsg($text,0);


    // Return the success notice
    return(array(0, $text));
}











///////////////////////////////////////////////////////////////////////
//  Function: puppet_node_env_set (string $options='')
//
//  Input Options:
//    $options = key=value pairs of options for this function.
//               multiple sets of key=value pairs should be separated
//               by an "&" symbol.
//
//  Output:
//    Returns a two part list:
//      1. The exit status of the function.  0 on success, non-zero on
//         error.  All errors messages are stored in $self['error'].
//      2. A textual message for display on the console or web interface.
//
//  Example: list($status, $result) = puppet_node_env_set('host=test&type=something');
///////////////////////////////////////////////////////////////////////
function puppet_node_env_set($options="") {
    global $conf, $self, $onadb;

    // Version - UPDATE on every edit!
    $version = '1.00';

    printmsg("DEBUG => puppet_node_env_set({$options}) called", 3);

    // Parse incoming options string to an array
    $options = parse_options($options);

    // Return the usage summary if we need to
    if ($options['help'] or !($options['host'] and $options['env']) ) {
        // NOTE: Help message lines should not exceed 80 characters for proper display on a console
        $self['error'] = 'ERROR => Insufficient parameters';
        return(array(1,
<<<EOM

puppet_node_env_set-v{$version}
Set the puppet node environment for a host 

  Synopsis: puppet_node_env_set [KEY=VALUE] ...

    host=(name|IP)       FQDN or ID of host to set environment to
    env=STRING           Name of the environment to use


EOM
        ));
    }

    // Check permissions
    if (!auth('puppet_ext_node_admin')) {
        $self['error'] = "Permission denied!";
        printmsg($self['error'], 0);
        return(array(10, $self['error'] . "\n"));
    }


    // Find the host
    list($status, $rows, $host) = ona_find_host($options['host']);
    if (!$host['id']) {
        $self['error'] = "ERROR=> Unable to find the host: {$options['host']}";
        printmsg($self['error'], 0);
        return(array(20, $self['error'] . "\n"));
    }


    // check to see if the host already has an environment
    list($status, $rows, $env) = db_get_record($onadb, 'puppet_node_environments', array('host_id' => $host['id']));

    if ($rows and ($options['env'] != $env['name'])) {
        // update the record
        list($status, $rows) = db_update_record(
            $onadb,
            'puppet_node_environments',
            array( 'host_id'    => $host['id']),
            array( 'name'       => $options['env'])
        );
    } else {
        // Add the record
        list($status, $rows) = db_insert_record(
            $onadb,
            'puppet_node_environments',
            array(
                'host_id'    => $host['id'],
                'name'       => $options['env']
            )
        );
    }

    // show an error if there was one
    if ($status or !$rows) {
        $self['error'] = "ERROR => puppet_node_env_set() SQL Query failed setting record: " . $self['error'];
        printmsg($self['error'], 0);
        return(array(6, $self['error'] . "\n"));
    }


    // start an output message
    $text = "INFO => Set puppet node environment '{$options['env']}' to host: {$host['fqdn']}.\n";
    printmsg($text,0);


    // Return the success notice
    return(array(0, $text));
}








///////////////////////////////////////////////////////////////////////
//  Function: puppet_node_parm_del (string $options='')
//
//  Input Options:
//    $options = key=value pairs of options for this function.
//               multiple sets of key=value pairs should be separated
//               by an "&" symbol.
//
//  Output:
//    Returns a two part list:
//      1. The exit status of the function.  0 on success, non-zero on
//         error.  All errors messages are stored in $self['error'].
//      2. A textual message for display on the console or web interface.
//
//  Example: list($status, $result) = puppet_node_parm_del('host=test&type=something');
///////////////////////////////////////////////////////////////////////
function puppet_node_parm_del($options="") {
    global $conf, $self, $onadb;

    // Version - UPDATE on every edit!
    $version = '1.00';

    printmsg("DEBUG => puppet_node_parm_del({$options}) called", 3);

    // Parse incoming options string to an array
    $options = parse_options($options);

    // Return the usage summary if we need to
    if ($options['help'] or !($options['host'] and $options['parm']) ) {
        // NOTE: Help message lines should not exceed 80 characters for proper display on a console
        $self['error'] = 'ERROR => Insufficient parameters';
        return(array(1,
<<<EOM

puppet_node_parm_del-v{$version}
Delete a parm from a puppet node

  Synopsis: puppet_node_parm_del [KEY=VALUE] ...

    host=(name|IP)       FQDN or ID of host to remove parm from
    parm=STRING         Name of the parm to remove


EOM
        ));
    }

    // Check permissions
    if (!auth('puppet_ext_node_admin')) {
        $self['error'] = "Permission denied!";
        printmsg($self['error'], 0);
        return(array(10, $self['error'] . "\n"));
    }


    // Find the host
    list($status, $rows, $host) = ona_find_host($options['host']);
    if (!$host['id']) {
        $self['error'] = "ERROR=> Unable to find the host: {$options['host']}";
        printmsg($self['error'], 0);
        return(array(20, $self['error'] . "\n"));
    }


    // check to see if the host has this parm
    list($status, $rows, $parm) = db_get_record($onadb, 'puppet_node_parameters', array('host_id' => $host['id'], 'name' => $options['parm']));

    if (!$rows) {
        printmsg("DEBUG => The parm {$options['parm']} does not exist on {$host['fqdn']}!",3);
        $self['error'] = "ERROR => The parm {$options['parm']} does not exist on {$host['fqdn']}!";
        return(array(3, $self['error'] . "\n"));
    } else {
        // Add the record
        list($status, $rows) = db_delete_records(
            $onadb,
            'puppet_node_parameters',
            array(
                'host_id'    => $host['id'],
                'name'       => $options['parm']
            )
        );
    }

    // show an error if there was one
    if ($status or !$rows) {
        $self['error'] = "ERROR => puppet_node_parm_del() SQL Query failed deleting record: " . $self['error'];
        printmsg($self['error'], 0);
        return(array(6, $self['error'] . "\n"));
    }


    // start an output message
    $text = "INFO => Removed puppet node parm '{$options['parm']}' from host: {$host['fqdn']}.\n";
    printmsg($text,0);


    // Return the success notice
    return(array(0, $text));
}










///////////////////////////////////////////////////////////////////////
//  Function: puppet_node_class_del (string $options='')
//
//  Input Options:
//    $options = key=value pairs of options for this function.
//               multiple sets of key=value pairs should be separated
//               by an "&" symbol.
//
//  Output:
//    Returns a two part list:
//      1. The exit status of the function.  0 on success, non-zero on
//         error.  All errors messages are stored in $self['error'].
//      2. A textual message for display on the console or web interface.
//
//  Example: list($status, $result) = puppet_node_class_del('host=test&type=something');
///////////////////////////////////////////////////////////////////////
function puppet_node_class_del($options="") {
    global $conf, $self, $onadb;

    // Version - UPDATE on every edit!
    $version = '1.00';

    printmsg("DEBUG => puppet_node_class_del({$options}) called", 3);

    // Parse incoming options string to an array
    $options = parse_options($options);

    // Return the usage summary if we need to
    if ($options['help'] or !($options['host'] and $options['class']) ) {
        // NOTE: Help message lines should not exceed 80 characters for proper display on a console
        $self['error'] = 'ERROR => Insufficient parameters';
        return(array(1,
<<<EOM

puppet_node_class_del-v{$version}
Delete a class from a puppet node

  Synopsis: puppet_node_class_del [KEY=VALUE] ...

    host=(name|IP)       FQDN or ID of host to remove class from
    class=STRING         Name of the class to remove


EOM
        ));
    }

    // Check permissions
    if (!auth('puppet_ext_node_admin')) {
        $self['error'] = "Permission denied!";
        printmsg($self['error'], 0);
        return(array(10, $self['error'] . "\n"));
    }


    // Find the host
    list($status, $rows, $host) = ona_find_host($options['host']);
    if (!$host['id']) {
        $self['error'] = "ERROR=> Unable to find the host: {$options['host']}";
        printmsg($self['error'], 0);
        return(array(20, $self['error'] . "\n"));
    }


    // check to see if the host has this class
    list($status, $rows, $class) = db_get_record($onadb, 'puppet_node_classes', array('host_id' => $host['id'], 'name' => $options['class']));

    if (!$rows) {
        printmsg("DEBUG => The class {$options['class']} does not exist on {$host['fqdn']}!",3);
        $self['error'] = "ERROR => The class {$options['class']} does not exist on {$host['fqdn']}!";
        return(array(3, $self['error'] . "\n"));
    } else {
        // Add the record
        list($status, $rows) = db_delete_records(
            $onadb,
            'puppet_node_classes',
            array(
                'host_id'    => $host['id'],
                'name'       => $options['class']
            )
        );
    }

    // show an error if there was one
    if ($status or !$rows) {
        $self['error'] = "ERROR => puppet_node_class_del() SQL Query failed deleting record: " . $self['error'];
        printmsg($self['error'], 0);
        return(array(6, $self['error'] . "\n"));
    }


    // start an output message
    $text = "INFO => Removed puppet node class '{$options['class']}' from host: {$host['fqdn']}.\n";
    printmsg($text,0);


    // Return the success notice
    return(array(0, $text));

}









///////////////////////////////////////////////////////////////////////
//  Function: puppet_node_parm_add (string $options='')
//
//  Input Options:
//    $options = key=value pairs of options for this function.
//               multiple sets of key=value pairs should be separated
//               by an "&" symbol.
//
//  Output:
//    Returns a two part list:
//      1. The exit status of the function.  0 on success, non-zero on
//         error.  All errors messages are stored in $self['error'].
//      2. A textual message for display on the console or web interface.
//
//  Example: list($status, $result) = puppet_node_parm_add('host=test&type=something');
///////////////////////////////////////////////////////////////////////
function puppet_node_parm_add($options="") {
    global $conf, $self, $onadb;

    // Version - UPDATE on every edit!
    $version = '1.00';

    printmsg("DEBUG => puppet_node_parm_add({$options}) called", 3);

    // Parse incoming options string to an array
    $options = parse_options($options);

    // Return the usage summary if we need to
    if ($options['help'] or !($options['host'] and $options['parm'] and $options['value']) ) {
        // NOTE: Help message lines should not exceed 80 characters for proper display on a console
        $self['error'] = 'ERROR => Insufficient parameters';
        return(array(1,
<<<EOM

puppet_node_parm_add-v{$version}
Assign a parm to a puppet node

  Synopsis: puppet_node_parm_add [KEY=VALUE] ...

    host=(name|IP)       FQDN or ID of host to assign parm to
    parm=STRING          Name of the parm to assign
    value=STRING         Value of the parm to assign


EOM
        ));
    }

    // Check permissions
    if (!auth('puppet_ext_node_admin')) {
        $self['error'] = "Permission denied!";
        printmsg($self['error'], 0);
        return(array(10, $self['error'] . "\n"));
    }


    // Find the host
    list($status, $rows, $host) = ona_find_host($options['host']);
    if (!$host['id']) {
        $self['error'] = "ERROR=> Unable to find the host: {$options['host']}";
        printmsg($self['error'], 0);
        return(array(20, $self['error'] . "\n"));
    }


    // check to see if the host already has this parm
    list($status, $rows, $env) = db_get_record($onadb, 'puppet_node_parameters', array('host_id' => $host['id'], 'name' => $options['parm']));

    if ($rows) {
        printmsg("DEBUG => The parm {$options['parm']} already exists on {$host['fqdn']}!",3);
        $self['error'] = "ERROR => The parm {$options['parm']} already exists on {$host['fqdn']}!";
        return(array(3, $self['error'] . "\n"));
    } else {
        // Add the record
        list($status, $rows) = db_insert_record(
            $onadb,
            'puppet_node_parameters',
            array(
                'host_id'    => $host['id'],
                'name'       => $options['parm'],
                'value'      => $options['value']
            )
        );
    }

    // show an error if there was one
    if ($status or !$rows) {
        $self['error'] = "ERROR => puppet_node_parm_add() SQL Query failed setting record: " . $self['error'];
        printmsg($self['error'], 0);
        return(array(6, $self['error'] . "\n"));
    }


    // start an output message
    $text = "INFO => Added puppet node parm '{$options['parm']}:{$options['value']}' to host: {$host['fqdn']}.\n";
    printmsg($text,0);


    // Return the success notice
    return(array(0, $text));

}












///////////////////////////////////////////////////////////////////////
//  Function: puppet_node_class_add (string $options='')
//
//  Input Options:
//    $options = key=value pairs of options for this function.
//               multiple sets of key=value pairs should be separated
//               by an "&" symbol.
//
//  Output:
//    Returns a two part list:
//      1. The exit status of the function.  0 on success, non-zero on
//         error.  All errors messages are stored in $self['error'].
//      2. A textual message for display on the console or web interface.
//
//  Example: list($status, $result) = puppet_node_class_add('host=test&type=something');
///////////////////////////////////////////////////////////////////////
function puppet_node_class_add($options="") {
    global $conf, $self, $onadb;

    // Version - UPDATE on every edit!
    $version = '1.00';

    printmsg("DEBUG => puppet_node_class_add({$options}) called", 3);

    // Parse incoming options string to an array
    $options = parse_options($options);

    // Return the usage summary if we need to
    if ($options['help'] or !($options['host'] and $options['class']) ) {
        // NOTE: Help message lines should not exceed 80 characters for proper display on a console
        $self['error'] = 'ERROR => Insufficient parameters';
        return(array(1,
<<<EOM

puppet_node_class_add-v{$version}
Assign a class to a puppet node

  Synopsis: puppet_node_class_add [KEY=VALUE] ...

    host=(name|IP)       FQDN or ID of host to assign class to
    class=STRING           Name of the class to assign


EOM
        ));
    }

    // Check permissions
    if (!auth('puppet_ext_node_admin')) {
        $self['error'] = "Permission denied!";
        printmsg($self['error'], 0);
        return(array(10, $self['error'] . "\n"));
    }


    // Find the host
    list($status, $rows, $host) = ona_find_host($options['host']);
    if (!$host['id']) {
        $self['error'] = "ERROR=> Unable to find the host: {$options['host']}";
        printmsg($self['error'], 0);
        return(array(20, $self['error'] . "\n"));
    }


    // check to see if the host already has an environment
    list($status, $rows, $env) = db_get_record($onadb, 'puppet_node_classes', array('host_id' => $host['id'], 'name' => $options['class']));

    if ($rows) {
        printmsg("DEBUG => The class {$options['class']} already exists on {$host['fqdn']}!",3);
        $self['error'] = "ERROR => The class {$options['class']} already exists on {$host['fqdn']}!";
        return(array(3, $self['error'] . "\n"));
    } else {
        // Add the record
        list($status, $rows) = db_insert_record(
            $onadb,
            'puppet_node_classes',
            array(
                'host_id'    => $host['id'],
                'name'       => $options['class']
            )
        );
    }

    // show an error if there was one
    if ($status or !$rows) {
        $self['error'] = "ERROR => puppet_node_class_add() SQL Query failed setting record: " . $self['error'];
        printmsg($self['error'], 0);
        return(array(6, $self['error'] . "\n"));
    }


    // start an output message
    $text = "INFO => Added puppet node class '{$options['class']}' to host: {$host['fqdn']}.\n";
    printmsg($text,0);


    // Return the success notice
    return(array(0, $text));

}






///////////////////////////////////////////////////////////////////////
//  Function: puppet_external_node (string $options='')
//
//  Input Options:
//    $options = key=value pairs of options for this function.
//               multiple sets of key=value pairs should be separated
//               by an "&" symbol.
//
//  Output:
//    Returns a two part list:
//      1. The exit status of the function.  0 on success, non-zero on
//         error.  All errors messages are stored in $self['error'].
//      2. A textual message for display on the console or web interface.
//
//  Example: list($status, $result) = puppet_external_node('host=test&type=something');
///////////////////////////////////////////////////////////////////////
function puppet_external_node($options="") {
    global $conf, $self, $onadb;

    // Version - UPDATE on every edit!
    $version = '1.01';
    $exstatus = 0;

    printmsg("DEBUG => puppet_external_node({$options}) called", 3);

    // Parse incoming options string to an array
    $options = parse_options($options);

    // Return the usage summary if we need to
    if ($options['help']) {
        // NOTE: Help message lines should not exceed 80 characters for proper display on a console
        $self['error'] = 'ERROR => Insufficient parameters';
        return(array(1,
<<<EOM

puppet_external_node-v{$version}
Extract YAML formatted puppet external node data from ONA

  Synopsis: puppet_external_node [KEY=VALUE] ...

  Required:
    host=FQDN|IP       Display external node data for host
    or
    FQDN|IP            Simply pass an IP or FQDN

If a host is found in ONA but it has no puppet node information
this tool will exit with a non zero status so puppet will respond
as if there is no node defined.

Configure your puppet.conf file with something like:

 [main]
 external_nodes = /usr/local/bin/dcm.pl -u http://onaserver.example.com/ona/dcm.php -r puppet_external_node
 node_terminus = exec

This example provides direct URL path and username.  You can use
a dcm.conf file if you wish for these values.

EOM
        ));
    }


    // Test that we have a yaml parser
    if (!function_exists('yaml_emit')) {
        $self['error'] = "ERROR=> Unable to find the YAML parser function 'yaml_emit'!";
        printmsg($self['error'], 0);
        return(array(15, $self['error'] . "\n"));
    }

    // Check permissions
    if (!auth('puppet_ext_node_view')) {
        $self['error'] = "Permission denied!";
        printmsg($self['error'], 0);
        return(array(10, $self['error'] . "\n"));
    }

    // see if we can figure out the host info if no host was passed in specifically
    if (!$options['host']) {
        foreach (array_keys($options) as $key) {
            // loop through the option keys and find the first one with a dot in it
            if (strpos($key, '.')) $options['host'] = $key; 
        }
    }

    $options['host'] = trim($options['host']);

    // If we have a view.. add it to the name
    // MP: FIXME: VIEWS are currently a problem.. it will return the default view even if it is not a valid view
    if ($options['view']) {
        $options['host'] = $options['view'].'/'.$options['host'];
    }

    // If they provided a hostname / ID let's look it up
    if ($options['host']) {
        list($status, $rows, $host) = ona_find_host($options['host']);

        if ($host['id']==0) {
            $self['error'] = "ERROR => puppet_external_node: Host [{$options['host']}] not found.";
            printmsg($self['error'], 0);
            return(array(5, $self['error'] . "\n"));
        }

        // Gather data for this host

        // ENV
        list($status, $rows, $env) = db_get_record($onadb, 'puppet_node_environments', array('host_id' => $host['id']));

        list($status, $rows, $classes) = db_get_records($onadb, 'puppet_node_classes', array('host_id' => $host['id']));

        list($status, $rows, $parms) = db_get_records($onadb, 'puppet_node_parameters', array('host_id' => $host['id']));


        // load the data into an array in the "right" format for external nodes
        // More info can be found here http://docs.puppetlabs.com/guides/external_nodes.html
        // TODO: need to adjust formatting to allow for parameterized classes
        $extnode = array();
        if ($env['name']) $extnode['environment'] = $env['name'];
        $i=0;
        foreach ($classes as $class) {
            $extnode['classes'][$i] = $class['name'];
            $i++;
        }

        $i=0;
        foreach ($parms as $parm) {
            $extnode['parameters'][$parm['name']] = $parm['value'];
            $i++;
        }

        // If we still have an empty array, dont print it and exit with an error status
        if (!empty($extnode)) {
            // dump yaml data
            $text .= yaml_emit($extnode);
        } else {
            $exstatus++;
        }

    }


    // Return the success notice
    return(array($exstatus, $text));
}





?>
