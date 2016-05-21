<?php

//debug
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('../common/Db.php');

class Ecology extends Db {
	private $delimiter = ';';
	private $CSVFile = 'ecologysvampeatlas.tab';
	private $fieldNames = array();

	public function __construct() {
		parent::__construct();
		ini_set("auto_detect_line_endings", true);
		$this->loadCSV();
		//echo $this->getCreateTableSQL('atlasEcology');				
		$this->insertData();
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
		foreach($this->records as $record) {
			$SQL='insert into atlasEcology (DKIndexNumber,FuldeNavnFraFUN,Naturtyper) values('.
				$this->q($record['DKIndexNumber']).
				$this->q($record['FuldeNavnFraFUN']).
				$this->q($record['Naturtyper'], false).
			')';
			$this->query($SQL);
		}
	}

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

$ecology = new Ecology();

?>

