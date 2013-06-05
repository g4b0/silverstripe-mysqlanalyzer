<?php
/**
 * MySQLCached connector class.
 *
 * Supported indexes for {@link requireTable()}:
 *
 * @package framework
 * @subpackage model
 */
class MySQLAnalyzer extends MySQLDatabase {
	
	public function __construct($parameters) { 
		parent::__construct($parameters);
	}
	
	public function query($sql, $errorLevel = E_USER_ERROR) {
		
		if(isset($_REQUEST['previewwrite']) && in_array(strtolower(substr($sql,0,strpos($sql,' '))),
				array('insert','update','delete','replace'))) {

			Debug::message("Will execute: $sql");
			return;
		}

		if(isset($_REQUEST['showqueries']) && Director::isDev(true)) {
			$starttime = microtime(true);
		}

		$handle = $this->dbConn->query($sql);
		
		if(isset($_REQUEST['showqueries']) && Director::isDev(true)) {
			$endtime = round(microtime(true) - $starttime,4);
			
			if (!isset($GLOBALS['totaltime'] )) {
				$GLOBALS['totaltime'] =0;
				$GLOBALS['totalquery'] =0;
			}
			
			$GLOBALS['totaltime'] += $endtime;
			$GLOBALS['totalquery'] += 1;			
			
			Debug::message("\n$sql\n{$endtime}s [{$GLOBALS['totaltime']}[{$GLOBALS['totalquery']}]\n", false);
		}
		
		if (isset($_REQUEST['collectqueries']) && Director::isDev(true)) {
			$fopen_mode = 'a';
			if (!isset($GLOBALS['append'] )) {
				$GLOBALS['append'] =1;
				$fopen_mode = 'w';
			}
			$h = fopen(BASE_PATH . '/' . MYSQL_ANALYZER_BASE . '/sql.txt', 'a');
			if ($h !== false) {
				fwrite($h, "$sql;||");
				fclose($h);
			}
		}
		
		if(!$handle && $errorLevel) {
			$this->databaseError("Couldn't run query: $sql | " . $this->dbConn->error, $errorLevel);
		}
		return new MySQLQuery($this, $handle);
	}

}


