<?
include('../FX/FX.php');
include('../FX/server_data.php');
include('../common/Db.php');

set_time_limit(0);
ini_set('memory_limit', -1);

class RemoteUpdate extends Db {
	private $date;

	public function __construct() {
		parent::__construct();

		$this->date = (isset($_GET['ask'])) ? $_GET['ask'] : date('m/d/Y',strtotime("-1 days")); //'08/28/2013';
		$this->writeLog('aktiveres'); //

		$records = $this->getModifiedRecords($this->date);
		
		$this->writelog('Datoen er '.$this->date);
		$this->writelog('---------------------------------------');
		$this->writelog(count($records).' poster returneret via AtlasSQLModifiedTimeStamp');
		//$this->debug($records);

		foreach ($records as $record) {
			$this->processRecord($record);
		}

	}

	private function writeLog($text) {
		$now = new DateTime();
		$timestamp=$now->format('Y-m-d H:i:s')."\t";
		$file = "log.txt";
		$text='['.$this->date.'] '.$text;
		$fh = fopen($file, 'a') or die("can't open file");
		fwrite($fh, $timestamp.$text."\n");
		fclose($fh);
	}

	private function reportError() {
		if (mysql_error()!='') {
			$this->writeLog(mysql_error());
		}
	}
		
	private function getModifiedRecords($date) {
		global $serverIP, $webCompanionPort;
		$result=array();
		$soeg = new FX($serverIP, $webCompanionPort);
		//$soeg->setDBData('Svampefund.fp7', 'SQLtransfer', 'all');
		$soeg->setDBData('Svampefund.fmp12', 'SQLtransfer', 'all');
		$soeg->setDBPassword('soegpass','soeguser');
		$soeg->addDBParam('AtlasSQLModifiedTimeStamp', $date);
		$recordFind = $soeg->FMFind();
		foreach ($recordFind['data'] as $key=>$recordliste) {
			$result[]=$recordliste['AtlasIDnummer'][0];
		}
		return $result;
	}

	private function getFileMakerData($AtlasIDnummer) {
		global $serverIP, $webCompanionPort;
		$soeg=new FX($serverIP,$webCompanionPort);  
		//$soeg->SetDBData('Svampefund.fp7','wwwFungi', '1');
		$soeg->SetDBData('Svampefund.fmp12','wwwFungi', '1');  
		$soeg->SetDBPassword('logpass','loguser');
		$soeg->AddDBParam('AtlasIDnummer', $AtlasIDnummer);
		$result = $soeg->FMFind();

		$FFID=key($result['data']);
		$row=array();
		foreach ($result['data'] as $row=>$record);
		return $record;
	}		

	private function processRecord($AtlasIDnummer) {
		$record=$this->getFileMakerData($AtlasIDnummer);
		$SQL='select count(*) as c from atlasFund where AtlasIDnummer="'.$AtlasIDnummer.'"';
		$row=$this->getRow($SQL);

		if ($row['c']>0) {
			$this->updateAtlasFund($record);
			$this->writeLog($AtlasIDnummer.' opdateret.');
		} else {
			$this->insertAtlasFund($record);
			$this->writeLog('Ny record '.$AtlasIDnummer.' indsat.');
		}

		$this->updateAtlasForum($record);
		$this->updateAtlasPerson($record);
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
			'_UTM10,'.
			'VegType,'.
			'associatedOrganism,'.
			'Substrate,'.
			'AtlasGeoRef,'.
			'AtlasUserPrec,'.
			'Det,'.
			'CollNr) values ('.
			$this->q($record['AtlasIDnummer'][0]).
			$this->q($record['AtlasUserLati'][0]).
			$this->q($record['AtlasUserLatiNum'][0]).
			$this->q($record['AtlasUserLong'][0]).
			$this->q($record['AtlasUserLongNum'][0]).
			$this->q($record['date_day'][0]).
			$this->q($record['date_month'][0]).
			$this->q($record['date_year'][0]).
			$this->q($record['AtlasPic'][0]).
			$this->q($record['AtlasLNR'][0]).
			$this->q($record['AtlasForumVali'][0]).
			$this->q($record['AtlasForumValue'][0]).
			$this->q($record['AtlasLocID'][0]).
			$this->q($record['AtlasLegInit'][0]).
			//$this->q($record['DkIndexNumber'][0]). //DKIndex
			$this->q($record['MycoTaxon current use link::DKIndex'][0]). //DKIndex
			$this->q($record['UTM10'][0]).
			$this->q($record['VegType'][0]).
			$this->q($record['associatedOrganism'][0]).
			$this->q($record['Substrate'][0]).
			$this->q($record['AtlasGeoRef'][0]).
			$this->q($record['AtlasUserPrec'][0]).
			$this->q($record['Det'][0]).
			$this->q($record['CollNr'][0], false).
			')';
		//$this->debug($SQL);
		$this->exec($SQL);
		$this->reportError($SQL);
	}

	private function updateAtlasFund($record) {
		$SQL='update atlasFund set '.
			'AtlasUserlati='.$this->q($record['AtlasUserLati'][0]).
			'AtlasUserlatiNum='.$this->q($record['AtlasUserLatiNum'][0]).
			'AtlasUserLong='.$this->q($record['AtlasUserLong'][0]).
			'AtlasUserLongNum='.$this->q($record['AtlasUserLongNum'][0]).
			'date_day='.$this->q($record['date_day'][0]).
			'date_month='.$this->q($record['date_month'][0]).
			'date_year='.$this->q($record['date_year'][0]).
			'AtlasPic='.$this->q($record['AtlasPic'][0]).
			'AtlasLNR='.$this->q($record['AtlasLNR'][0]).
			'AtlasForumVali='.$this->q($record['AtlasForumVali'][0]).
			'AtlasForumValue='.$this->q($record['AtlasForumValue'][0]).
			'_AtlasLocID='.$this->q($record['AtlasLocID'][0]).
			'_AtlaslegInit='.$this->q($record['AtlasLegInit'][0]).
			//'_DKIndex='.$this->q($record['DkIndexNumber'][0]). //DKIndex
			'_DKIndex='.$this->q($record['MycoTaxon current use link::DKIndex'][0]).
			'_UTM10='.$this->q($record['UTM10'][0]).
			'VegType='.$this->q($record['VegType'][0]).
			'associatedOrganism='.$this->q($record['associatedOrganism'][0]).
			'Substrate='.$this->q($record['Substrate'][0]).
			'AtlasGeoRef='.$this->q($record['AtlasGeoRef'][0]).
			'AtlasUserPrec='.$this->q($record['AtlasUserPrec'][0]).
			'Det='.$this->q($record['Det'][0]).
			'CollNr='.$this->q($record['CollNr'][0], false).
			
			' where AtlasIDnummer="'.$record['AtlasIDnummer'][0].'"';
		//$this->debug($SQL);
		$this->exec($SQL);
		$this->reportError($SQL);
	}

	private function updateAtlasForum($record) {
		$SQL='select count(*) as c from atlasForum where AtlasIDnummer='.$this->q($record['AtlasIDnummer'][0], false);
		//$this->debug($SQL);
		$row=$this->getRow($SQL);
		if ($row['c']==0) {
			$SQL='insert into atlasForum ('.
				'AtlasIDnummer,'.
				'AtlasForumLastComment,'.
				'AtlasForumreply) values('.
				$this->q($record['AtlasIDnummer'][0]).
				$this->q($record['AtlasForumLastComment'][0]).
				$this->q($record['AtlasForumreply'][0], false).
				')';
		} else {
			$SQL='update atlasForum set '.
				'AtlasForumLastComment='.$this->q($record['AtlasForumLastComment'][0]).
				'AtlasForumreply='.$this->q($record['AtlasForumreply'][0], false).' '.
				'where '.
				'AtlasIDnummer='.$this->q($record['AtlasIDnummer'][0], false);
		}			
		$this->exec($SQL);
		$this->reportError($SQL);
	}

	private function updateAtlasPerson($record) {
		//we really dont actually need to check if the person already exists
		$SQL='insert into atlasPerson ('.
			'AtlaslegInit,'.
			'Leg) values('.
			$this->q($record['AtlasLegInit'][0]).
			$this->q($record['Leg'][0], false).
			')';
		$this->exec($SQL);
		//dont rePortError since it most likely contain error
		//$this->reportError($SQL);
	}

}

$update = new RemoteUpdate();
?>
