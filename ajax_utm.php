<?
include('ajax_searchbase.php');

class AtlasSearchUTM extends AtlasSearchBase {

	function cleanDKName($DKName) {	
		$res=str_replace('"', '', $DKName); //&quot;
		$res=str_replace("'", '', $res); //&#39;
		$res=str_replace(array("\r", "\n", "\t", "\f", "\b"), "", $res);
		$res=str_replace("\x0B", "", $res);

		return $res;
	}

	public function __construct() {
		parent::__construct();

		$this->baseSQL='select atlas._UTM10 as UTM10, atlas.AtlasUserLati, AtlasUserLong, '.
			'taxon.GenSpec, taxon.DKName, atlas._AtlaslegInit, atlas.AtlasIDnummer, '.
			'atlas.date_year, atlas.date_month, atlas.date_day '.

			'from atlasFund atlas, atlasTaxon taxon, atlasPerson person, atlasForum forum, '.
			'atlasGaz gaz, atlasEcology ecol '.

			'where '.
			'(atlas._DKIndex=taxon.DKIndex '.
			'and atlas._AtlaslegInit=person.AtlaslegInit '.
			'and atlas.AtlasIDnummer=forum.AtlasIDnummer '.
			'and atlas._AtlasLocID=gaz.AtlasLocID) ';
			//'and atlas._UTM10=utm.UTM10) ';

		$this->evaluateParams();

		//$this->fileDebug($this->baseSQL);

		header('Content-type: application/json');

		mysql_set_charset('Latin1');
		$result=$this->query($this->baseSQL);
		$html='';
		$ok=true;
		while ($row = mysql_fetch_array($result)) {
			if (count($this->polygon)>0) {
				$lat=$row['AtlasUserLati'];
				$long=$row['AtlasUserLong'];
				$ok=($this->inPolygon($long, $lat));
			}
			if ($ok) {
				$date=($row['date_day']>0 && $row['date_month']>0 && $row['date_year']>0) ? $row['date_day'].'/'.$row['date_month'].'/'.$row['date_year'] : '';
				if ($html!='') $html.=',';
				$html.='{ "utm" : "'.$row['UTM10'].'" ,'.
					 '"atlasid" : "'.$row['AtlasIDnummer'].'" ,'.
					 '"leg" : "'.$row['_AtlaslegInit'].'" ,'.
					 '"dkname" : "'.$this->cleanDKName($row['DKName']).'",'.
					 '"date" : "'.$date.'" ,'.
					 '"genspec" : "'.$row['GenSpec'].'" }';
			}
		}
		//$this->fileDebug($html);
		$html='['.$html.']';
		echo $html;
	}

}

$utm = new AtlasSearchUTM();

?>
