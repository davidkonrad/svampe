<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('../../common/Db.php');

class CSVToStorage extends Db {
	private $CSVFile='../../svampeatlas-totalgodkendt.tab';

	public function __construct() {
		parent::__construct();
		$this->loadCSVAsChunks();
	}

	private function loadCSVAsChunks() {
		if (($handle = fopen($this->CSVFile, "r")) !== false) {
			$this->fieldNames = fgetcsv($handle, 1000, ";");

			echo '<pre>';
			print_r($this->fieldNames);
			echo '</pre>';
			//exit('test');

			$SQL='delete from replicate_storage';
			$this->exec($SQL);

			$total=0;
			while (($record = fgets($handle, 1000)) !== false) {
				$SQL='insert into replicate_storage (csv) values ('.$this->q($record, false).')';
				$this->exec($SQL);
				$total=$total+1;
			}
			echo $total.' records inserted<br>';
		}
	}

}

$import = new CSVToStorage();

?>
