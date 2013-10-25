<?php
global $wsmenuitem,$images,$base;

$pluginpath = str_replace($base,'',dirname(__FILE__));

$wsmenuitem[1] = array(
// Display on what workspace names
'displayon' => array('display_host'),

// This is the title that shows in the menu itself, it is also the "alt" name.
'title' => 'Manage Puppet External Node',

// The builtin silkicon image path would be /images/silk/<imagename>.png
// OR
// To provide your own 16x16 image use the following: {$pluginpath}/menu_item_image.png
// where menu_item_image.png is the name of the file in this plugin directory.
'image' => "/images/silk/tag_blue.png",

// The type of menuitem call to be made.
//   work_space:  this will do an ajax call to a work_space with the same name as your plugin. it will run the "ws_display" function
//   window:      this opens a floating window only
'type' => 'window',

// Defines the permission type to use for this item to appear.  Comment out for open access to this menu item.
'authname' => 'puppet_ext_node_admin',

// The name of the function that should be called by either the workspace or window defined in type above
'function_name' => 'puppet_ext_node_add'

);

?>
