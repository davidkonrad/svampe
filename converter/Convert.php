<?php

//debug
error_reporting(E_ALL);
ini_set('display_errors', '1');

//CSV structure so far, 02.07.2012
/*
Array
(
    [0] => AtlasArteriAtlasPeriod
    [1] => AtlasForumLastComment
    [2] => AtlasForumreply
    [3] => AtlasForumVali
    [4] => AtlasForumValue
    [5] => AtlasGeoRef
    [6] => AtlasIDnummer
    [7] => AtlasIkon
    [8] => AtlasLegInit
    [9] => AtlasLNR
    [10] => AtlasPic
    [11] => AtlasRecInclude
    [12] => AtlasreelArt
    [13] => AtlasUserLati
    [14] => AtlasUserLatiNum
    [15] => AtlasUserLong
    [16] => AtlasUserLongNum
    [17] => date_day
    [18] => date_month
    [19] => date_year
    [20] => Leg
    [21] => localityBasicString
    [22] => UTM10
    [23] => GooglePolygonFill
    [24] => DKIndex
    [25] => DKName
    [26] => GenSpec
    [27] => MycoKeyIDDKWebLink
    [28] => Status
    [29] => StatusTal
)
*/
include('../common/Db.php');

class AtlasCSVToMySQL extends Db {
	//private $CSVFile = 'svampeatlas-eksempel.csv';
	//private $CSVFile = '../svampeatlas-totalgodkendt.tab';
	//private $CSVFile = 'udtraek/Gyrodon.mer';
	//private $CSVFile = 'udtraek/svampefundTotal14-08-12.mer';
	//private $CSVFile = 'udtraek/udtræksep2012.tab';
	//private $CSVFile = 'udtraek/svampeatlasTotaltilNov.tab';
	private $CSVFile = 'udtraek/Totalsvampeatlas2012.tab';

	private $fieldNames = array();
	private $records = array();

	public function __construct() {
		parent::__construct();
		set_time_limit(0); //!!
		ini_set('memory_limit',-1);
		ini_set("auto_detect_line_endings", true);
	}

	public function run() {
		if (isset($_GET['from'])) {
			$this->splObject();
		}
	}

	// Kun test (da testudtræk manglede AtlasLocID) - ser om localityBasicString findes
	// i atlasGaz, hvis den gør returneres AtlasLocID, ellers returneres recordcount+1
	private function getDummyAtlasLocID($localityBasicString) {
		$SQL='select * from atlasGaz where localityBasicString="'.$localityBasicString.'"';
		if ($this->hasData($SQL)) {
			$rec=$this->getRow($SQL);
			return $rec['AtlasLocID'];
		} else {
			$AtlasLocID=$this->getRecCount('atlasGaz');
			return $AtlasLocID+1;
		}
	}		

	private function splObject() {
		$separator=';';
		if (($handle = fopen($this->CSVFile, "r")) !== false) {
			$this->fieldNames = fgetcsv($handle, 1000, $separator);
			fclose($handle);
		}		
		
		$this->records=array();

		$file = new SplFileObject($this->CSVFile);
		$file->seek($_GET['from']);
		for ($count=0;$count<30000;$count++) {
			$record=$file->fgetcsv($separator);
			//print_r($record);
			$array = array();
			$index = 0;
			foreach ($this->fieldNames as $fieldName) {
				$array[$fieldName] = $record[$index];
				$index++;
			}
			$this->records[]=$array;
		}
		$this->insertData();
	}


	private function emptyTables() {
		$this->exec('delete from atlasFund');
		$this->exec('delete from atlasForum');
		$this->exec('delete from atlasPerson');
		//$this->exec('delete from atlasTaxon');
		//$this->exec('delete from atlasGaz');
		//$this->exec('delete from atlasUTM');
	}

//AtlasArteriAtlasPeriod;AtlasForumLastComment;AtlasForumreply;AtlasForumVali;
//AtlasForumValue;AtlasGeoRef;AtlasIDnummer;AtlasIkon;AtlasLegInit;AtlasLNR;AtlasPic;
//AtlasRecInclude;AtlasreelArt;AtlasUserLati;AtlasUserLatiNum;AtlasUserLong;AtlasUserLongNum;
//date_day;date_month;date_year;Leg;localityBasicString;UTM10;GooglePolygonFill;DKIndex;
//DKName;GenSpec;MycoKeyIDDKWebLink;Status;StatusTal

	private function loadCSVAsChunks() {
		if (($handle = fopen($this->CSVFile, "r")) !== false) {
			$this->fieldNames = fgetcsv($handle, 1000, ',');

			echo '<pre>';
			print_r($this->fieldNames);
			echo '</pre>';
			//exit('test');

			$total=0;
			$count=0;
			
			while (($record = fgetcsv($handle, 1000, ',')) !== false) {
				$array = array();
				$index = 0;
				foreach ($this->fieldNames as $fieldName) {
					$array[$fieldName] = $record[$index];
					$index++;
				}
				$this->records[]=$array;
				$count++;
				if ($count>=1000) {
					echo 'inserting row '.$total.' -> '.$count.'<br>';
					$total=$total+$count;
					$count=0;
					$this->insertData();
					unset($this->records);
					$this->records=array();
				}
			}
		}
	}

/*
	private function seekAndInsert() {
		if (($handle = fopen($this->CSVFile, "r")) !== false) {
			$this->fieldNames = fgetcsv($handle, 1000, ',');
			
			echo '<pre>';
			print_r($this->fieldNames);
			echo '</pre>';
			//exit('test');

			$total=0;
			$count=0;
			for ($count=0;$count<$_GET['from'];$count++) {
				$x=fgetcsv($handle, 1000, ',');
			}

			$count=0;
			while (($record = fgetcsv($handle, 1000, ',')) !== false) {
				$array = array();
				$index = 0;
				foreach ($this->fieldNames as $fieldName) {
					$array[$fieldName] = $record[$index];
					$index++;
				}
				$this->records[]=$array;
				$count++;
				if ($count==1000) {
					$this->insertData();
				}
			}
		}
	}
*/

	private function loadCSV() {
		if (($handle = fopen($this->CSVFile, "r")) !== false) {
			$this->fieldNames = fgetcsv($handle, 1000, ",");
			$this->debug($this->fieldNames);
			while (($record = fgetcsv($handle, 1000, ",")) !== false) {
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

	private function insertData() {
		$count=0;
		foreach($this->records as $record) {

			//test - find eller dan et AtlasLocID
			/*
			$record['AtlasLocID']=$this->getDummyAtlasLocID($record['localityBasicString']);

			//test - korriger AtlasPic, er '' i udtræk hvor det burde være 0
			if ($record['AtlasPic']=='') $record['AtlasPic']=0;
			*/
			//$this->debug($record);
			//$count++;
			//echo $count.'<br>';

			$this->insertAtlasFund($record);
			$this->insertAtlasForum($record);
			$this->insertAtlasPerson($record);
		}
	}

	private function insertAtlasFund($record) {
		$SQL='insert into atlasFund ('.
			'AtlasIDnummer,'.
			'AtlasUserlati,'.
			'AtlasUserlatiNum,'.
			'AtlasUserLong,'.
			'AtlasUserLongNum,'.
			'date_day,'.
			'date_month,'.
			'date_year,'.
			'AtlasPic,'.
			'AtlasLNR,'.
			'AtlasForumVali,'.
			'AtlasForumValue,'.
			'_AtlasLocID,'.
			'_AtlaslegInit,'.
			'_DKIndex,'.
			'_UTM10) values ('.
			$this->q($record['AtlasIDnummer']).
			
			//$this->q($record['AtlasUserlati']).
			$this->q($record['AtlasUserLati']).

			//$this->q($record['AtlasUserlatiNum']).
			$this->q($record['AtlasUserLatiNum']).

			$this->q($record['AtlasUserLong']).
			$this->q($record['AtlasUserLongNum']).
			$this->q($record['date_day']).
			$this->q($record['date_month']).
			$this->q($record['date_year']).
			$this->q($record['AtlasPic']).
			$this->q($record['AtlasLNR']).
			$this->q($record['AtlasForumVali']).
			$this->q($record['AtlasForumValue']).
			$this->q($record['AtlasLocID']).

			//$this->q($record['AtlaslegInit']).
			$this->q($record['AtlasLegInit']).

			$this->q($record['DKIndex']).
			$this->q($record['UTM10'], false).
			')';
		$this->exec($SQL);
	}

	private function insertAtlasForum($record) {
		$SQL='insert into atlasForum ('.
			'AtlasIDnummer,'.
			'AtlasForumLastComment,'.
			'AtlasForumreply) values('.
			$this->q($record['AtlasIDnummer']).
			$this->q($record['AtlasForumLastComment']).
			$this->q($record['AtlasForumreply'], false).
			')';
		$this->exec($SQL);
	}

	private function insertAtlasPerson($record) {
		$SQL='insert into atlasPerson ('.
			'AtlaslegInit,'.
			'Leg) values('.
			
			//$this->q($record['AtlaslegInit']).
			$this->q($record['AtlasLegInit']).

			$this->q($record['Leg'], false).
			')';
		$this->exec($SQL);
	}

	private function insertAtlasGaz($record) {
		$SQL='insert into atlasGaz ('.
			'AtlasLocID,'.
			'localityBasicString,'.
			'UTM10) values('.
			$this->q($record['AtlasLocID']).
			$this->q($record['localityBasicString']).
			$this->q($record['UTM10'], false).
			')';
		$this->exec($SQL);
	}

	private function insertAtlasTaxon($record) {
		$SQL='insert into atlasTaxon ('.
			'DKIndex,'.
			'DKName,'.
			'GenSpec,'.
			'MycokeyIDDKWebLink,'.
			'Status,'.
			'StatusTal) values('.
			$this->q($record['DKIndex']).
			$this->q($record['DKName']).
			$this->q($record['GenSpec']).

			//$this->q($record['MycokeyIDDKWebLink']).
			$this->q($record['MycoKeyIDDKWebLink']).

			$this->q($record['Status']).
			$this->q($record['StatusTal'], false).
			')';
		$this->exec($SQL);
	}

	private function insertAtlasUTM($record) {
		$SQL='insert into atlasUTM ('.
			'UTM10,'.
			'GooglePolygonFill) values('.
			$this->q($record['UTM10']).
			$this->q($record['GooglePolygonFill'], false).
			')';
		$this->exec($SQL);
	}

}

$test = new AtlasCSVToMySQL();
$test->run();

?>
