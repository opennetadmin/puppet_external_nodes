CREATE TABLE IF NOT EXISTS `puppet_node_classes` (
  `host_id` int(10) NOT NULL COMMENT 'Host that this class relates to',
  `name` varchar(255) NOT NULL COMMENT 'The name of the class'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Store Puppet node classes';

CREATE TABLE IF NOT EXISTS `puppet_node_parameters` (
  `host_id` int(10) NOT NULL COMMENT 'Host that this class relates to',
  `name` varchar(255) NOT NULL COMMENT 'The name of the parameter',
  `value` text NOT NULL COMMENT 'The value of the parameter'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Store Puppet node parameters';

CREATE TABLE IF NOT EXISTS `puppet_node_environments` (
  `host_id` int(10) NOT NULL COMMENT 'Host that this environment relates to',
  `name` varchar(255) NOT NULL COMMENT 'The name of the environment'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Store Puppet node environments';
