<?

//debug
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('../common/Db.php');

class convertBase extends Db {
	protected $CSVFile = ''; // 
	protected $delimiter = ',';
	protected $fieldNames = array();
	protected $records = array();
	
	public function __construct($CSVFile) {
		parent::__construct();
		$this->CSVFile=$CSVFile;
	}

/*
	protected function loadCSV() {
		
		if (($handle = fopen($this->CSVFile, "r")) !== false) {
			$line=fgets($handle, 1000);
			echo $line;
			$this->fieldNames=explode(',',$line);
			//$this->fieldNames = fgetcsv($handle, 1000, $this->delimiter);

			while (($record = fgetcsv($handle, 1000, $this->delimiter)) !== false) {
				$array = array();
				$index = 0;
				foreach ($this->fieldNames as $fieldName) {
					$array[$fieldName] = $record[$index];
					$index++;
				}
				$this->records[]=$array;

				$this->debug($array);

			}
		}
	}
*/

	//generates a simple 'create table xxx based on field names
	protected function getCreateTableSQL($tablename) {
		$count=0;
		$SQL='create table '.$tablename.'(';
		foreach ($this->fieldNames as $fieldName) {
			$SQL.=$fieldName.' varchar(20)';
			$count++;
			if ($count<count($this->fieldNames)) $SQL.=',';
			$SQL.="\n";
		}
		$SQL.=')';
		return $SQL;
	}
}

?>
