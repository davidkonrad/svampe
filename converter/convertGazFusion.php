<?
include('../common/Db.php');

class convertGazFusion extends Db {
	private $fieldNames = array();
	private $CSVFile = 'udtraek/gazfusion3.mer';
	private $delimiter = ';';
	private $records = array();

	public function __construct() {
		parent::__construct();
		$this->loadCSV();

		/*
		$this->pre($this->fieldNames);
		foreach ($this->records as $record) {
			$this->pre($record);
		}
		*/

		$this->insertData();
	}

	private function pre($a) {
		echo '<pre>';
		print_r($a);
		echo '</pre>';
	}

	protected function removeLastChar($s) {
		return substr_replace($s ,"", -1);
	}

	protected function loadCSV() {
		if (($handle = fopen($this->CSVFile, "r")) !== false) {
			$this->fieldNames = fgetcsv($handle, 1000, $this->delimiter);

			while (($record = fgetcsv($handle, 1000, $this->delimiter)) !== false) {
				$array = array();
				$index = 0;
				foreach ($this->fieldNames as $fieldName) {
					$array[$fieldName] = $record[$index];
					$index++;
				}
				$this->records[]=$array;
			}

		}
	}

	protected function insertData() {
		$SQL='delete from atlasGaz';
		$this->exec($SQL);

		foreach($this->records as $record) {
			$SQL='insert into atlasGaz set '.
				'AtlasLocID='.$this->q($record['LocID']).
				'localitybasicString='.$this->q($record['LocName']).
				'kommune='.$this->q($record['Kommune']).
				'UTM10='.$this->q($record['UTM10']).
				'Latitude='.$this->q($record['Latitude']).
				'Longitude='.$this->q($record['Longitude']).
				'Medtages='.$this->q($record['include']).
				'Hovedlokalitet='.$this->q($record['hovedlokalitet'], false);
		
			//$this->removeLastChar($SQL);
			//echo '<br>'.$SQL;

			$this->query($SQL);
		}
	}
}


$convert = new convertGazFusion();
?>
