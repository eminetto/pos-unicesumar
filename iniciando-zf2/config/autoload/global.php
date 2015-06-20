<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
    	),
    ),
	'db' => array(
		'driver'         => 'Pdo',
    	'dsn'            => 'mysql:dbname=iniciandozf2;host=127.0.0.1',
    	'username' => 'root',
        'password' => 'root',
    	'driver_options' => array(
        	PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
    	),
	)
);
