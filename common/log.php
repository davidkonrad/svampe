<?

class Log {

	public static function accessLog() {

		$time = '['.date("F j, Y, g:i a").']'; 
		$file = $_SERVER["REQUEST_URI"]; //__FILE__;
		$remote = $_SERVER['REMOTE_ADDR'];

		$logstr = $time."\t".$remote."\t".$file."\n";
		//echo $logstr;

		$fh = fopen('accesslog.txt', 'a') or die();
		fwrite($fh, $logstr);
		fclose($fh);
	}
}

?>
