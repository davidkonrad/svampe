<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('../../common/Db.php');

class InsertCSV extends Db {
	private $csv;

	public function __construct() {
		parent::__construct();
		if (isset($_GET['csv'])) {
			$this->csv=$_GET['csv'];
			$this->insertCSV();
		} else {
			echo 'Fejl - CSV mangler ..';
		}
	}

	protected function insertCSV() {
		$SQL='insert into replicate_storage (csv) values ('.$this->q($_GET['csv'], false).')';
		$this->exec($SQL);
	}
}

new InsertCSV();

?>
