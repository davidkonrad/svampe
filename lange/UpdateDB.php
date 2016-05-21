<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('../common/Db.php');
include_once('../FX/FX.php'); 
include_once('../FX/server_data.php');


class UpdateDB extends Db {

	public function __construct() {
		parent::__construct();

		//$this->testDelete();
		$this->writeLog(date("Y-m-d H:i:s"));

		$run=true;
		$count=0;
		while ($run) {
			if ($count>5) break;
			$run=$this->run();
			if ($run) $this->checkMySQL($run);
			$count++;
		}
	}

	//hent næste post der skal opdateres/indsættes, returner false hvis der ikke er nogen
	private function run() {
		$AtlasIDnummer=$this->CronGetNextUpdate();
		return ($AtlasIDnummer!='') ? $AtlasIDnummer : false;
	}

	//slet alle matchende poster i atlasFund så der kan testes insert
	private function testDelete() {
		$SQL='select svampeatlas_IDtransfer from xxxID_transfer';
		$result=$this->query($SQL);
		while ($row = mysql_fetch_array($result)) {
			$SQL='delete from atlasFund where AtlasIDnummer="'.$row['svampeatlas_IDtransfer'].'"';
			$this->exec($SQL);
		}
	}

	//returner svampe_dk svampedk_cron ID_transfer link
	function getID_transfer_Link() {
		$this->writeLog('transfer');

		$database = 'svampedk_atlas';
		$hostname = 'dbp8.meebox.net';
		$username = 'svampedk_cron';
		$password = 'mysql666cron';

		$link=mysql_connect($hostname, $username, $password);
		if (!$link) return false;
		mysql_select_db($database, $link);

		$this->writeLog($link);

		return $link;
	}

	//log på svampe.dk's svampedk_atlas replikerings-database udenom Db
	//hent næste nummer i rækken, hvis der er et
	private function CronGetNextUpdate() {
		/*
		$database = 'svampedk_atlas';
		$hostname = 'dbp8.meebox.net';
		$username = 'svampedk_cron';
		$password = 'mysql666cron';

		$link=mysql_connect($hostname, $username, $password);
		if (!$link) return '';
		*/
		$link=$this->getID_transfer_Link();
		if (!$link) return '';

		//echo 'CronGetNextUpdate<br';

		//mysql_select_db($database, $link);
		$SQL='select svampeatlas_IDtransfer from ID_transfer order by rand() limit 1';
		$result=mysql_query($SQL, $link);

		$this->writeLog('select');

		if (mysql_numrows($result)==0) return '';
		$row=mysql_fetch_array($result);

		mysql_close($link);
		unset($link);
		return $row['svampeatlas_IDtransfer'];
	}

	//slet et færdigbehandlet svampeatlas_IDtransfer på svampe.dk's svampedk_atlas replikerings-database
	private function CronRemoveUpdate($AtlasIDnummer) {
		$link=$this->getID_transfer_Link();
		if (!$link) return false;

		$SQL='delete from ID_transfer where svampeatlas_IDtransfer="'.$AtlasIDnummer.'"';
		mysql_query($SQL, $link);

		$result=(mysql_affected_rows($link)==1);
		unset($link);
		return $result;
	}

	//hent FileMakers data for det aktuelle AtlasIDnummer
	private function getFileMakerData($AtlasIDnummer) {
		global $serverIP, $webCompanionPort;
		$soeg=new FX($serverIP,$webCompanionPort);  
		$soeg->SetDBData('Svampefund.fp7','wwwFungi', '1'); 
		$soeg->SetDBPassword('logpass','loguser');
		$soeg->AddDBParam('AtlasIDnummer', $AtlasIDnummer);
		$result = $soeg->FMFind();

		/*
		echo '<pre>';
		print_r($result);
		echo '</pre>';
		*/

		return $result;
	}		

	private function CronDeleteUpdate($AtlasODnummer) {
	}

	//check hvorvidt det aktuelle AtlasIDnummer er er helt nyt fund eller om det skal opdateres
	private function checkMySQL($AtlasIDnummer) {
		$this->writeLog('check');

		parent::__construct();

		$SQL='select AtlasIDnummer from atlasFund where AtlasIDnummer="'.$AtlasIDnummer.'"';
		if (!$this->hasData($SQL)) {
			$this->writeLog('check insert');
			$this->insertMySQL($AtlasIDnummer);
		} else {
			$this->updateMySQL($AtlasIDnummer);
		}

		$this->writeLog('check finished');
	}

	private function insertMySQL($AtlasIDnummer) {
		$result=$this->getFileMakerData($AtlasIDnummer);

		$FFID=key($result['data']);
		//$this->writeLog($FFID);

		$row=array();
		foreach ($result['data'] as $row=>$a);
		//$this->writeLog($a);		
		
		//echo $a['creation date'][0];

		$SQL='insert into atlasFund set '.
			'AtlasIDnummer="'.$AtlasIDnummer.'",'.
			'creationDate='.$this->q($a['creation date'][0]).
			'AtlasFirstRecmark='.$this->q($a['AtlasFirstRecMark'][0]).
			'AtlasPersFirstYear='.$this->q($a['AtlasPersFirstYear'][0]).
			'AtlasPersFirst='.$this->q($a['AtlasPersFirst'][0]).
			'AtlasJulianDay='.$this->q($a['AtlasJulianDay'][0]).
			'AtlasUTMFirst='.$this->q($a['AtlasUTMFirst'][0]).
			'AtlasForumValue='.$this->q($a['AtlasForumValue'][0]).

			'date_day='.$this->q($a['date_day'][0]).
			'date_month='.$this->q($a['date_month'][0]).
			'date_year='.$this->q($a['date_year'][0]).
			'AtlasPic='.$this->q($a['photoPresent'][0]).
			'LocalityFriText='.$this->q($a['LocalityFriText'][0]).
			'AtlasUserLong='.$this->q($a['AtlasUserLong'][0]).
			'AtlasUserLati='.$this->q($a['AtlasUserLati'][0]).
			'AtlasUserLatiNum='.$this->q($a['AtlasUserLatiNum'][0]).
			'AtlasUserLongNum='.$this->q($a['AtlasUserLongNum'][0]).
			'AtlasLNR='.$this->q($a['AtlasLNR'][0]).
			'AtlasForumVali='.$this->q($a['AtlasForumVali'][0]).
			'_AtlaslegInit='.$this->q($a['AtlasLegInit'][0]).
			'_AtlasLocID='.$this->q($a['AtlasLocID'][0]).
			'_UTM10='.$this->q($a['UTM10'][0]).
			'_DKIndex='.$this->q($a['DkIndexNumber'][0], false);

		//echo $SQL;

		$this->query($SQL);

		//echo $this->affected_rows();
		//echo mysql_error();

		$this->info('>>>>>>>>>> insert', $AtlasIDnummer, $FFID, $SQL);
	}

	//opdater en post i MySQL søge-databasen
	private function updateMySQL($AtlasIDnummer) {
		$result=$this->getFileMakerData($AtlasIDnummer);

		$FFID=key($result['data']);
		//$this->writeLog($FFID);

		$row=array();
		foreach ($result['data'] as $row=>$a);
		//$this->writeLog($a);		

		$SQL='update atlasFund set '.
			'date_day='.$this->q($a['date_day'][0]).
			'date_month='.$this->q($a['date_month'][0]).
			'date_year='.$this->q($a['date_year'][0]).
			'AtlasPic='.$this->q($a['photoPresent'][0]).
			'LocalityFriText='.$this->q($a['LocalityFriText'][0]).
			'AtlasUserLong='.$this->q($a['AtlasUserLong'][0]).
			'AtlasUserLati='.$this->q($a['AtlasUserLati'][0]).
			'AtlasUserLatiNum='.$this->q($a['AtlasUserLatiNum'][0]).
			'AtlasUserLongNum='.$this->q($a['AtlasUserLongNum'][0]).
			'AtlasLNR='.$this->q($a['AtlasLNR'][0]).
			'AtlasForumVali='.$this->q($a['AtlasForumVali'][0]).
			'_AtlaslegInit='.$this->q($a['Leg'][0]).
			'_AtlasLocID='.$this->q($a['AtlasLocID'][0]).
			'_UTM10='.$this->q($a['UTM10'][0]).
			'_DKIndex='.$this->q($a['DkIndexNumber'][0], false).
		' '.
		'where AtlasIDnummer="'.$AtlasIDnummer.'"';

		//echo $SQL;

		$this->query($SQL);

		//echo $this->affected_rows();
		//echo mysql_error();
		$this->info('<<<<<<<< update', $AtlasIDnummer, $FFID, $SQL);

		$ID=substr($FFID,0,strpos($FFID,'.'));
		echo $FFID.' -> '.$ID.'<br>';

		if ($this->CronRemoveUpdate($AtlasIDnummer)) {
			$this->writeLog('Update fjernet fra ID_transfer');
			$this->updateFileMaker($ID);
		} else {
			$this->writeLog('Remove på ID-transfer mislykkedes');
		}
	}

	private function updateFileMaker($FFID) {
		echo $FFID;
		global $serverIP, $webCompanionPort;
		$timestamp=date("d.m.Y H:i:s");
		echo '<br/>'.$timestamp.'<br/>';
		$soeg=new FX($serverIP,$webCompanionPort);  
		$soeg->SetDBData('Svampefund.fp7','wwwFungi');//, '1'); 
		$soeg->SetDBPassword('logpass','loguser');
		$soeg->AddDBParam('-recid', $FFID);
		$soeg->AddDBParam('AtlasSQLupdatedTimeStamp', $timestamp);
		//$soeg->AddDBParam('recordType', $timestamp);
		$test=$soeg->FMEdit();
		
		echo '<pre>';
		print_r($test);
		echo '</pre>';
	}

	//debug, hvad er ID'erne, den kørte SQL og hvad skete der?
	private function info($action, $AtlasIDnummer, $FFID, $SQL) {
		$this->writeLog($action);
		$this->writeLog($AtlasIDnummer);
		$this->writeLog($FFID);
		$this->writeLog($SQL);
		$this->writeLog('Affected : '.mysql_affected_rows());
	}

	private function nextJob() {
		global $serverIP, $webCompanionPort;
		$soeg=new FX($serverIP,$webCompanionPort);  
		$soeg->SetDBData('Svampefund.fp7','wwwPics', 'all'); 
		$soeg->SetDBPassword('logpass','loguser');
		$soeg->AddDBParam('MycoTaxon current use link::DKIndex', $this->DKIndex);
		$test=$soeg->FMFind();
		foreach ($test['data'] as $arr) {
			$this->imageInfo[]=$arr;
		}
	}

	private function writeLog($text) {
		$file = "cron-update-log.txt";
		$fh = fopen($file, 'a') or die("can't open file");
		fwrite($fh, $text."\n");
		fclose($fh);
	}

}

$update = new UpdateDB();

?>
