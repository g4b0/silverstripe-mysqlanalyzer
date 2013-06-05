<?php
define('MYSQL_ANALYZER_BASE', basename(dirname(__FILE__)));
define('MYSQL_ANALYZER_SLOW_QUERIES_THRESHOLD', 0.5); //ms

$enabled = true;

if ($enabled) {
	define('SS_DATABASE_CLASS', 'MySQLAnalyzer');

	DatabaseAdapterRegistry::register(
		array(
			'class' => 'MySQLAnalyzer',
			'title' => 'MySQL 5.0+',
			'helperPath' => '/framework/dev/install/MySQLDatabaseConfigurationHelper.php',
			'supported' => class_exists('MySQLi'),
		)
	);
}