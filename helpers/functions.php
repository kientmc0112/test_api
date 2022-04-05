<?php 

if(!function_exists('createLog')) {
	function createLog($message = 'local.INFO ', $input = [], $level = 3) {
		$date = date("Y-m-d");
		$now = date("Y-m-d H:i:s");
		$logPath = LOG_PATH . DIRECTORY_SEPARATOR . "app-{$date}.log";
       	error_log( "$now: {$message}: ", 3, $logPath );

        if( empty($input) )  return;
        $array = is_array($input) ? $input : [$input];
    	$str = print_r( json_encode($array), true ) ."\r\n";
    	error_log( $str, $level, $logPath ) ;
	}
}

if(!function_exists('dump')) {
	function dump(...$args) {
		foreach($args as $k => $arg) {
			echo '<pre>';
			var_dump($arg);
			echo '</pre>';
		}
	}
}

if(!function_exists('dd')) {
	function dd(...$args) {
		dump(...$args);
		die;
	}
}

if(!function_exists('addQuotes')) {
	function addQuotes($str) {
	    return sprintf("'%s'", $str);
	}
}

if(!function_exists('unique')) {
	function unique(&$a, $b) {
	    return $a ? array_merge(array_diff($a, $b), array_diff($b, $a)) : $b;
	}
}