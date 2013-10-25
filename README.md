Puppet External Nodes
=====================
This plugin allows you to use ONA as a Puppet External Node classifier. You can read more about that ability here: <http://docs.puppetlabs.com/guides/external_nodes.html>

Install
=======

  * Unzip the archive and place it in your $ONABASE/www/local/plugins directory
        `tar -C /opt/ona/www/local/plugins -zxvf pluginname.tar.gz`
  * Click _Plugins->Manage Plugins_ while logged in as an admin user
  * Click the install icon for the plugin which should be listed by the plugin name
  * Follow any instructions it prompts you with.  They may or may not include the following (some user/group names may differ on various platforms):
    * chown -R www-data /opt/ona/www/local/plugins
    * if you have not already, run the following command _echo '/opt/ona' > /etc/onabase_.  This assumes you installed ONA into /opt/ona
  * Ensure you have prerequisites installed:
    * A Puppetmaster
    * dcm.pl ONA command line tool (must be installed on the puppet master)
    * The php YAML module

Usage
=====

  * Click _Plugins->Puppet External Nodes_
  * There are now new dcm.pl modules to use from the CLI as well:
    * ...._add

  * Add the following to ``/etc/puppet/puppet.conf``
   
```
external_nodes = /opt/ona/bin/dcm.pl -u https://ona.example.com/dcm.php -r puppet_external_node
node_terminus = exec
```
  * You must add a default node to your nodes.pp file.  Typically this default node statement would not have anything in it as those settings would come from ONA.  For example:
  
```
# Must have a default node defined so the external node function works.
# The default node currently has nothing in it as we expect
# our nodes to have some sort of definition either externally or in nodes.pp.
node default {
}
```
  * You should be able to also place regular node statements in nodes.pp that are not pulled from ONA.


FUTURE
======
In the future support for parameterized classes and some of the newer external node classifier options should be supported.
