<?

include('ajax_detail.php');
include('common/Db.php');

//ini_set('display_errors', '1');

class taxonXML extends Db {
	public $exists = true;
	public $genus = '';
	public $species = '';
	public $author = '';

	public function __construct($AtlasRecID) {
		parent::__construct();
		if (!$this->checkExists($AtlasRecID)) return;
		$this->loadData($AtlasRecID);
	}

	private function checkExists($AtlasRecID) {
		$SQL = 'select count(*) as c from atlasFund where AtlasIDnummer = "'.$AtlasRecID.'"';
		$result = $this->getRow($SQL);
		$this->exists = $result['c']>0;
		return $this->exists;
	}

	private function loadData($AtlasRecID) {
		$SQL='select distinct _DKIndex from atlasFund where AtlasIDnummer="'.$AtlasRecID.'"';
		$record = $this->getRow($SQL);

		$SQL='select GenSpec, Author from atlasTaxon where DKIndex='.$record['_DKIndex'];
		$result = $this->getRow($SQL);

		$speciesName = explode(' ', $result['GenSpec']);
		$this->genus = $speciesName[0];
		$this->species = $speciesName[1];
		$this->author = $result['Author'];
	}
}
	
class fundXML extends DetailsBase {
	private $taxon;

	public function __construct($AtlasRecID) {
		$this->taxon = new taxonXML($AtlasRecID);
		if ($this->taxon->exists) {
			$this->loadData($AtlasRecID);
			$this->createXML();
		} else {
			$this->createError($AtlasRecID);
		}
	}

	private function clean($data) {
		$data = str_replace('&', '&amp;', $data);
		return $data;
	}

	private function createError($AtlasRecID) {
		$xml = new SimpleXMLElement('<xml/>');
		$xml->addChild('Error', $AtlasRecID.' eksisterer ikke i databasen');
		header('Content-Type: text/xml; charset=utf-8');
		echo $xml->asXML();
	}
		
	private function createXML() {
		$xml = new SimpleXMLElement('<xml/>');

		$xml->addChild('AtlasIDnummer', $this->AtlasRecID);
		$xml->addChild('GenSpec', $this->clean($this->enkeltrecord['MycoTaxon current use link::FullName'][0]));
		$xml->addChild('DKName', $this->clean($this->enkeltrecord['MycoTaxon current use link::DKName'][0]));

		//
		$xml->addChild('Genusname', $this->taxon->genus);
		$xml->addChild('Speciesname', $this->taxon->species);
		$xml->addChild('Authorname', $this->clean($this->taxon->author));
		//

		$lok = ($this->enkeltrecord['LocalityFriText'][0]!='') ? $this->enkeltrecord['localityBasicString'][0].', '.$this->enkeltrecord['LocalityFriText'][0] : $this->enkeltrecord['localityBasicString'][0]; 
		$xml->addChild('localitybasicString', $this->clean($lok));
		$xml->addChild('AtlasUserlati', $this->enkeltrecord['AtlasUserLatiNum'][0]);
		$xml->addChild('AtlasUserLong', $this->enkeltrecord['AtlasUserLongNum'][0]);
		$xml->addChild('AtlasUserPrec', $this->enkeltrecord['AtlasUserPrec'][0]);
		$xml->addChild('Vegetationstype',$this->clean($this->enkeltrecord['VegType'][0]));
		$xml->addChild('VegType',$this->clean($this->enkeltrecord['associatedOrganism'][0]));
		$xml->addChild('Substrate', $this->clean($this->enkeltrecord['Substrate'][0]));
		$xml->addChild('Jordbund', $this->clean($this->enkeltrecord['Jordbund'][0]));
		$xml->addChild('Klima', $this->clean($this->enkeltrecord['Klima'][0]));
		$xml->addChild('AtlasEcoNote', $this->clean($this->enkeltrecord['AtlasEcoNote'][0]));
		$xml->addChild('Dato', $this->createDate($this->enkeltrecord));
		$xml->addChild('date_day', $this->enkeltrecord['date_day'][0]);
		$xml->addChild('date_month', $this->enkeltrecord['date_month'][0]);
		$xml->addChild('date_year', $this->enkeltrecord['date_year'][0]);
		$xml->addChild('Leg', $this->clean($this->enkeltrecord['Leg'][0]));
		$xml->addChild('Det', $this->clean($this->enkeltrecord['Det'][0]));
		$xml->addChild('CollNr', $this->clean($this->enkeltrecord['CollNr'][0]));
		$xml->addChild('Herbarium', $this->clean($this->enkeltrecord['Herbarium'][0]));
		$xml->addChild('Bemaerkn', $this->clean($this->enkeltrecord['Bemaerkn'][0]));

		header('Content-Type: text/xml; charset=utf-8');
		echo $xml->asXML();
	}
}

if (isset($_GET['AtlasRecID'])) {
	$AtlasRecID = $_GET['AtlasRecID'];
	$_SESSION['AtlasRecID']=$AtlasRecID;
	$xml = new fundXML($AtlasRecID);
}

if (isset($_GET['AtlasIDnummer'])) {
	$AtlasRecID = $_GET['AtlasIDnummer'];
	$_SESSION['AtlasRecID']=$AtlasRecID;
	$xml = new fundXML($AtlasRecID);
}

?>
