<?php
require_once('../framework/core/Core.php');
global $databaseConfig;

$conn = mysql_connect($databaseConfig['server'], $databaseConfig['username'], $databaseConfig['password']);
mysql_select_db($databaseConfig['database'], $conn);

//mysql_query("RESET QUERY CACHE");

// Colleziono i dati relativi alla cache prima dell'esecuzione
$res = mysql_query('show status like "Qcache_hits" ');
$hit_start = mysql_result($res, 0, 1);
$res = mysql_query('show status like "Qcache_not_cached" ');
$nohit_start = mysql_result($res, 0, 1);
$res = mysql_query('show status like "Qcache_inserts" ');
$insert_start = mysql_result($res, 0, 1);

// Recupero le query dal file di dump
$sql_txt = '';
$h = fopen('sql.txt', 'r');
if ($h !== false) {
	while ($str = fread($h, 2048)) {
		$sql_txt .= $str;
	}
}
$sql_arr_tmp = explode('||', $sql_txt);

// Trovo le query duplicate e conto le query totali
$c = 0;
$sql_arr = array();
foreach ($sql_arr_tmp as $sql) {
	if (strlen(trim($sql)) > 0) {
		$c++;
		if (isset($sql_arr[$sql])) {
			$sql_arr[$sql]['cnt']++;
		} else {
			$sql_arr[$sql]['cnt'] = 1;
		}
	}
}
//echo count($sql_arr);
//print_r($sql_arr);
//exit;

//// Ripulisco l'array dai doppioni e trimmo le query
//foreach ($sql as $k => $row) {
//	$sql[$k] = trim($row);
//}
//$sql = array_unique($sql);

$hit_cnt = 0;
$nohit_cnt = 0;
$sql_arr_tmp = array();
foreach ($sql_arr as $sql => $data) {
	
	$res = mysql_query("show status like 'Qcache_hits' ");
	$hit_start2 = mysql_result($res, 0, 1);
	$res = mysql_query("show status like 'Qcache_not_cached' ");
	$nohit_start2 = mysql_result($res, 0, 1);
	$res = mysql_query("show status like 'Qcache_inserts' ");
	$insert_start2 = mysql_result($res, 0, 1);
	
	// Ripulisco eventuali query sporche
	$sql = trim(str_replace("\"", "`", $sql));
	//$sql = preg_replace("/^SELECT /si", "SELECT SQL_CACHE ", $sql);
	$r = mysql_query($sql, $conn);
	
	if (!$r) {
		trigger_error(mysql_error() . " - $sql", E_USER_ERROR);
	}
	
	$res = mysql_query("show status like 'Qcache_hits' ");
	$hit_stop2 = mysql_result($res, 0, 1);
	$res = mysql_query("show status like 'Qcache_not_cached' ");
	$nohit_stop2 = mysql_result($res, 0, 1);
	$res = mysql_query("show status like 'Qcache_inserts' ");
	$insert_stop2 = mysql_result($res, 0, 1);
	
	if ($nohit_stop2 - $nohit_start2 > 0) {
		$data['cache_hit'] = false;
		$nohit_cnt++;
	} else {
		$data['cache_hit'] = true;
		$hit_cnt++;
	}
	
	// aggiorno i dati
	$sql_arr_tmp[$sql] = $data;
}
$sql_arr = $sql_arr_tmp;

//print_r($sql_arr);

// Colleziono i dati relativi alla cache dopo l'esecuzione
$res = mysql_query("show status like 'Qcache_hits' ");
$hit_stop = mysql_result($res, 0, 1);
$res = mysql_query("show status like 'Qcache_not_cached' ");
$nohit_stop = mysql_result($res, 0, 1);
$res = mysql_query("show status like 'Qcache_inserts' ");
$insert_stop = mysql_result($res, 0, 1);
$res = mysql_query("show status like 'Qcache_free_memory' ");
$free_mem = mysql_result($res, 0, 1);

// Calcolo le hit
$hit = $hit_stop -$hit_start;
$nohit = $nohit_stop - $nohit_start;
$insert = $insert_stop - $insert_start;

echo "TOTAL QUERY: $c\n";
echo "UNIQUE QUERY: " .count($sql_arr) . "\n";
echo "CACHE HIT: $hit\n";
echo "CACHE MISS: $nohit\n";
echo "CACHE INSERT: $insert\n";
echo "FREE CACHE MEM: " . $free_mem/1024/1024 . " MB\n";

echo "\nDUPLICATED QUERY: \n";
echo "occ.\tc. hit\tsql\n";

// Ordino l'array per numero di occorrenze delle query
uasort($sql_arr, function($a, $b) {
    return $b['cnt'] - $a['cnt'];
});

foreach ($sql_arr as $sql => $data) {
	echo '[' . $sql_arr[$sql]['cnt'] . ']' . "\t[" . $sql_arr[$sql]['cache_hit'] . "]\t" . "$sql\n"; 
}

echo "\nCACHE HIT QUERY [$hit_cnt]: \n";

// Ordino l'array per per query alfabeticamente
ksort($sql_arr);

foreach ($sql_arr as $sql => $data) {
	if ($data['cache_hit']) {
		echo "$sql\n"; 
	}
}

echo "\nNO CACHE HIT QUERY [$nohit_cnt]: \n";

// Ordino l'array per per query alfabeticamente
ksort($sql_arr);

foreach ($sql_arr as $sql => $data) {
	if (!$data['cache_hit']) {
		echo "$sql\n"; 
	}
}


