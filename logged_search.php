<?
include('ajax_searchbase.php');

class LoggedSearch extends AtlasSearchBase {
	public $result;
	public $where;

	public $from ='from atlasFund atlas, atlasTaxon taxon, atlasPerson person, 
				atlasForum forum, atlasGaz gaz ';

	public $select = 'select atlas.date_year, atlas.date_month, atlas.date_day, 
				atlas.AtlasUserLati, atlas.AtlasUserLong, atlas.AtlasIDnummer, 
				atlas._AtlaslegInit, taxon.Status, taxon.DKName, taxon.GenSpec, 
				atlas._UTM10, gaz.localitybasicString, 
				atlas.VegType, atlas.associatedOrganism, atlas.Substrate ';

	public function initVars() {
		$this->where='where '.
			'(atlas._DKIndex=taxon.DKIndex '.
			'and atlas._AtlaslegInit=person.AtlaslegInit '.
			'and atlas.AtlasIDnummer=forum.AtlasIDnummer '.
			'and atlas._AtlasLocID=gaz.AtlasLocID) ';
	}

	public function __construct($select=null) {
		$this->initVars();
		parent::__construct();

		if ($select!=null) $this->select=$select;
	
		$this->vars=$this->getSearchRequest($_GET['search_id']);

		foreach($this->vars as $key=>$value) {
			if ($key=='naturtype') {
				$this->from.=', atlasEcology ecol ';
			}
		}
		$this->baseSQL=$this->select.$this->from.$this->where;
		$this->evaluateParams();
		//mysql_set_charset('Latin1');//utf8_danish_ci');
		$this->fileDebug($this->baseSQL);
		$this->result=$this->query($this->baseSQL);
	}

}

?>
