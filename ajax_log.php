<?
//debug
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('common/Db.php');

class Log extends Db {
	private $log_id;

	public function __construct() {
		parent::__construct();
		$this->createLog();
		$this->insertRequests();
		//return log_id as response, caller may use it to something ...
		echo $this->log_id;
	}

	private function createLog() {
		$SQL='insert into userLog (_timestamp) values (CURRENT_TIMESTAMP)';
		$this->exec($SQL);
		$this->log_id=mysql_insert_id();
	}

	private function insertRequests() {
		foreach($_POST as $param => $value) {
			if (($value!='') && ($param!='_')) {
				$SQL='insert into userLogRequest (log_id, param, value) values('.
					$this->q($this->log_id).
					$this->q($param).
					$this->q($value, false).
				')';
				$this->exec($SQL);
			}
		}
	}
}

$log = new Log();

?>
