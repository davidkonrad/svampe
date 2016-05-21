<?
include('ajax_searchbase.php');

//services both support a single DKIndex or repeats an actual search
class AtlasFund extends AtlasSearchBase {

	public function __construct() {
		if (empty($_POST)) return false;
		parent::__construct();
				
		if (count($_POST)==1) {
			if (!isset($_POST['DKIndex'])) {
				$this->repeatSearch('dkindex');
			} else {
				$this->DKIndex();
			}
		} else {
			$this->repeatSearch('ddd');
		}
	}

	private function cleanStr($s) {
		$s=str_replace('ø','&oslash;',$s);
		$s=str_replace('å','&aring;',$s);
		$s=str_replace('&#248;','ø',$s);
		$s=str_replace('&#230;','æ',$s);
		$s=str_replace('&#229;','å',$s);
		return $s;
	}

	function cleanDKName($DKName) {	
		$res=str_replace('"', '', $DKName); //&quot;
		$res=str_replace("'", '', $res); //&#39;
		$res=str_replace(array("\r", "\n", "\t", "\f", "\b"), "", $res);
		$res=str_replace("\x0B", "", $res);

		return $res;
	}

	private function getDate($row) {
		$day=($row['date_day']>0) ? $row['date_day'] : '??';
		$month=($row['date_month']>0) ? $row['date_month'] : '??';
		$year=($row['date_year']>0) ? $row['date_year'] : '????';
		return $day.'/'.$month.'/'.$year;
	}

	private function repeatSearch($x) {
		$this->baseSQL='select atlas.date_year, atlas.date_month, atlas.date_day, atlas.AtlasUserLati, '.
			'atlas.AtlasUserLong, atlas.AtlasIDnummer, atlas._AtlaslegInit, taxon.GenSpec, '.
			'taxon.DKName, gaz.localitybasicString '.

			'from atlasFund atlas, atlasTaxon taxon, atlasPerson person, atlasForum forum, '.
			'atlasGaz gaz, atlasEcology ecol '.

			'where '.
			'(atlas._DKIndex=taxon.DKIndex '.
			'and atlas._AtlaslegInit=person.AtlaslegInit '.
			'and atlas.AtlasIDnummer=forum.AtlasIDnummer '.
			'and atlas._AtlasLocID=gaz.AtlasLocID) ';
	
		$this->evaluateParams();
		$this->baseSQL.=' order by atlas.date_year asc';

		//$this->fileDebug($this->baseSQL);

		header('Content-type: application/json; charset=latin1'); //utf-8');

		//skal være Latin1 på meebox-server!! (???)
		mysql_set_charset('Latin1');
		//mysql_set_charset('utf8');

		$result=$this->query($this->baseSQL);

		$html='';
		$ok=true;
		while ($row = mysql_fetch_array($result)) {
			if ($this->isIncludable($row)) {
				$date=$this->getDate($row);
				if ($html!='') $html.=',';
				$html.='{ "year" : "'.$row['date_year'].'" ,'.
					 '"lat" : "'.$row['AtlasUserLati'].'" ,'.
					 '"lng" : "'.$row['AtlasUserLong'].'",'.
					 '"leg" : "'.$this->cleanDKName($row['_AtlaslegInit']).'",'.
					 '"genspec" : "'.$this->cleanDKName($row['GenSpec']).'",'.
					 '"dkname" : "'.$this->cleanDKName($row['DKName']).'",'.
					'"locality" : "'.$this->cleanDKName($row['localitybasicString']).'",'.
					 '"date" : "'.$date.'",'.
					 '"id" : "'.$row['AtlasIDnummer'].'" }';
			}
		}
		$html='['.$html.']';
		//$this->fileDebug($html);
		echo $html;
	}

	private function DKIndex() {
		$this->baseSQL='select atlas.date_year, atlas.date_month, atlas.date_day, atlas.AtlasIDnummer, '.
			'atlas.AtlasUserLati, atlas.AtlasUserLong, atlas._DKIndex, '.
			'atlas._AtlaslegInit, gaz.localitybasicString '.
			'from atlasFund atlas, atlasTaxon taxon, atlasPerson person, atlasForum forum, atlasGaz gaz '.
			'where '.
			'(atlas._DKIndex=taxon.DKIndex '.
			'and atlas._AtlaslegInit=person.AtlaslegInit '.
			'and atlas.AtlasIDnummer=forum.AtlasIDnummer '.
			'and atlas._AtlasLocID=gaz.AtlasLocID) '.
			'and atlas._DKIndex='.$_POST['DKIndex'];
		
		$this->evaluateParams();
		//$this->fileDebug($this->baseSQL);

		header('Content-type: application/json');

		mysql_set_charset('Latin1');
		$result=$this->query($this->baseSQL);
		$html='';
		while ($row = mysql_fetch_array($result)) {
			if ($html!='') $html.=',';
			$html.='{ "year" : "'.$row['date_year'].'" ,'.
				'"month" : "'.$row['date_month'].'" ,'.
				'"day" : "'.$row['date_day'].'" ,'.
				'"locality" : "'.utf8_encode($row['localitybasicString']).'" ,'.
				'"atlasidnummer" : "'.$row['AtlasIDnummer'].'" ,'.
				'"leg" : "'.$row['_AtlaslegInit'].'" ,'.
				'"lat" : "'.$row['AtlasUserLati'].'" ,'.
				'"lng" : "'.$row['AtlasUserLong'].'" }';
		}
		//$this->fileDebug($html);
		$html='['.$html.']';
		echo $html;
	}


}

$fund = new AtlasFund();

?>
