<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('../../common/Db.php');

//ex
//AtlasArteriAtlasPeriod;AtlasForumLastComment;AtlasForumreply;AtlasForumVali;AtlasForumValue;AtlasGeoRef;AtlasIDnummer;AtlasIkon;AtlasLegInit;AtlasLNR;AtlasPic;AtlasRecInclude;AtlasreelArt;AtlasUserLati;AtlasUserLatiNum;AtlasUserLong;AtlasUserLongNum;date_day;date_month;date_year;Leg;localityBasicString;UTM10;GooglePolygonFill;DKIndex;DKName;GenSpec;MycoKeyIDDKWebLink;Status;StatusTal
//"1";"785";"OK";"Godkendt";"1";"0";"CL2009-428";"Red";"CL";"428";"";"1";"1";"55.704065";"55,704065";"12.4846905";"12,4846905";"01";"03";"2009";"Christian Lange";"Brønshøj";"UB47";"var UB47= new GPolygon([new GLatLng(55.649451,12.457234),new GLatLng(55.739216,12.451398),new GLatLng(55.742422,12.610525),new GLatLng(55.652646,12.615998),new GLatLng(55.649451,12.457234),], ""#cc0033"", 0.5, 0.5, ""#cc0033"", 0.7); map.addOverlay(UB47);";"13002";"ege-labyrintsvamp";"Daedalea quercina";"http://www.mycokey.org/speciesindex/592/language=dk/LocalLanguage=dk";"LC";"9"

class Update extends Db {
	private $records = array();
	private $fields = array (
		    0 => 'AtlasArteriAtlasPeriod',
		    1 => 'AtlasForumLastComment',
		    2 => 'AtlasForumreply',
		    3 => 'AtlasForumVali',
		    4 => 'AtlasForumValue',
		    5 => 'AtlasGeoRef',
		    6 => 'AtlasIDnummer',
		    7 => 'AtlasIkon',
		    8 => 'AtlasLegInit',
		    9 => 'AtlasLNR',
		    10 => 'AtlasPic',
		    11 => 'AtlasRecInclude',
		    12 => 'AtlasreelArt',
		    13 => 'AtlasUserLati',
		    14 => 'AtlasUserLatiNum',
		    15 => 'AtlasUserLong',
		    16 => 'AtlasUserLongNum',
		    17 => 'date_day',
		    18 => 'date_month',
		    19 => 'date_year',
		    20 => 'Leg',
		    21 => 'localityBasicString',
		    22 => 'UTM10',
		    23 => 'GooglePolygonFill',
		    24 => 'DKIndex',
		    25 => 'DKName',
		    26 => 'GenSpec',
		    27 => 'MycoKeyIDDKWebLink',
		    28 => 'Status',
		    29 => 'StatusTal'
	);

	public function __construct() {
		parent::__construct();
		$this->run();
	}

	private function run() {
		$SQL='select * from replicate_storage limit 1000';
		$result=$this->query($SQL);
		while($row = mysql_fetch_array($result)) {
			$record = str_getcsv($row['csv'], ";");
			$array = array();
			$index = 0;
			foreach ($this->fields as $fieldName) {
				$array[$fieldName] = $record[$index];
				$index++;
			}
			$this->records[]=$array;
			
			echo '<pre>';
			print_r($array);
			echo '</pre>';

			//delete the record
			$SQL='delete from replicate_storage where import_id='.$row['import_id'];
			$this->exec($SQL);
		}
		$this->processData();
	}

	private function processData() {
		foreach($this->records as $record) {
			$SQL='select AtlasIDnummer from atlasFund where AtlasIDnummer="'.$record['AtlasIDnummer'].'"';
			$result = $this->query($SQL);
			if (mysql_num_rows($result)<=0) {
				$this->insertData($record);
				$this->updateLog($record['AtlasIDnummer'],'I');
			} else {
				$this->updateData($record);
				$this->updateLog($record['AtlasIDnummer'],'U');
			}
			unset($result);
		}
	}

	private function updateLog($AtlasIDnummer, $action) {
		$SQL='insert into replicate_log(AtlasIDnummer, import_action) values('.
			$this->q($AtlasIDnummer).
			$this->q($action, false).')';
		$this->exec($SQL);
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

	//update atlasFund and/or atlasGaz only (??)
	private function updateData($record) {
		$record['AtlasLocID']=$this->getDummyAtlasLocID($record['localityBasicString']);
		$SQL='update atlasFund set '.
			'AtlasUserLati='.$this->q($record['AtlasUserLati']).
			'AtlasUserLatiNum='.$this->q($record['AtlasUserLatiNum']).
			'AtlasUserLong='.$this->q($record['AtlasUserLong']).
			'AtlasUserLongNum='.$this->q($record['AtlasUserLongNum']).
			'date_day='.$this->q($record['date_day']).
			'date_month='.$this->q($record['date_month']).
			'date_year='.$this->q($record['date_year']).
			'AtlasPic='.$this->q($record['AtlasPic']).
			'AtlasLNR='.$this->q($record['AtlasLNR']).
			'AtlasForumVali='.$this->q($record['AtlasForumVali']).
			'AtlasForumValue='.$this->q($record['AtlasForumValue']).
			'AtlasLocID='.$this->q($record['AtlasLocID']).
			'AtlasLegInit='.$this->q($record['AtlasLegInit']).
			'DKIndex='.$this->q($record['DKIndex']).
			'UTM10='.$this->q($record['UTM10'], false).
			'where AtlasIDnummer='.$this->q($record['AtlasIDnummer'], false);
		$SQL.'<br>';
		$this->exec($SQL);
	}
		
	private function insertData($record) {
		//test - find or create an AtlasLocID
		$record['AtlasLocID']=$this->getDummyAtlasLocID($record['localityBasicString']);
		//test - correct AtlasPic when it is '' in exports where it should be '0'
		if ($record['AtlasPic']=='') $record['AtlasPic']=0;

		$this->insertAtlasFund($record);
		$this->insertAtlasGaz($record);
		$this->insertAtlasForum($record);
		$this->insertAtlasPerson($record);
		$this->insertAtlasTaxon($record);
		$this->insertAtlasUTM($record);
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

$update = new Update();

?>
