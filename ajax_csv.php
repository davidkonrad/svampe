<?
include('ajax_searchbase.php');

class DownloadCSV extends AtlasSearchBase {

	public function __construct() {
		parent::__construct();

		$filename=$_GET['filename'];
		$separator=$_GET['separator'];

		header('Content-type: application/text');
		header('Content-Disposition: attachment; filename="'.$filename.'"');

		$this->baseSQL='select atlas.date_year, atlas.date_month, atlas.date_day, atlas.AtlasUserLati, atlas.AtlasUserLong, '.
			'atlas.AtlasIDnummer, atlas._AtlaslegInit, taxon.Status, taxon.DKName, taxon.GenSpec, '.
			'atlas._UTM10, gaz.localitybasicString, '.
			'atlas.VegType, atlas.associatedOrganism, atlas.Substrate, '.
			'atlas.AtlasGeoRef, atlas.AtlasUserPrec, '.
			'atlas.Det, atlas.CollNr '.

			'from atlasFund atlas, atlasTaxon taxon, atlasPerson person, atlasForum forum, atlasGaz gaz '.
			'where '.
			'(atlas._DKIndex=taxon.DKIndex '.
			'and atlas._AtlaslegInit=person.AtlaslegInit '.
			'and atlas.AtlasIDnummer=forum.AtlasIDnummer '.
			'and atlas._AtlasLocID=gaz.AtlasLocID) ';

		$test=implode(',',$_GET);
		$this->fileDebug($test);

		$this->vars=$this->getSearchRequest($_GET['search_id'], false); //false->do not update lastParams
		$this->evaluateParams();

		switch($_GET['column']) {
			case 'GenSpec' : $this->baseSQL.=' order by taxon.GenSpec'; break;
			case 'DKName' : $this->baseSQL.=' order by taxon.DKName'; break;
			case '_UTM10' : $this->baseSQL.=' order by atlas._UTM10'; break;
			case 'Status' : $this->baseSQL.=' order by taxon.StatusTal'; break; //ids instead of code
			//case 'dato' : $this->baseSQL.=' order by str_to_date(atlas.date_month+"/"+atlas.date_day+"/"+atlas.date_year, "%m/%d/%Y")'; break;
			default : break;
		}

		$this->fileDebug('CSV: '.$this->baseSQL);

		mysql_set_charset('Latin1'); //Latin1 on prod server
		//mysql_set_charset('utf8');

		$result=$this->query($this->baseSQL);

		$cols=explode(',',$_GET['cols']);

		$line='';
		foreach($cols as $col) {
			$line.='"'.$this->fieldToName($col).'"'.$separator;
		}
		$line=$this->removeLastChar($line);
		echo $line."\n";

		while ($row = mysql_fetch_array($result)) {
			$line='';
			if ($this->isIncludable($row)) {
				foreach($cols as $col) {
					if ($col=='AtlasUserlati') $col='AtlasUserLati';
					if ($col=='dato') {
						$line.='"'.$row['date_day'].'/'.$row['date_month'].'/'.$row['date_year'].'"'.$separator;
					} elseif (($col=='AtlasUserLati') || ($col=='AtlasUserLong')) {
						$comma=str_replace('.',',',$row[$col]);
						$line.='"'.$comma.'"'.$separator;
					} elseif ($col=='AtlasUserPrec') {
						$accurancy='';
						if ($row['AtlasGeoRef']==0) {
							$accurancy=$row['AtlasUserPrec'].'m';
						}
						$line.='"'.$accurancy.'"'.$separator;
					} else {
						$line.='"'.$row[$col].'"'.$separator;
					}
				}
				$line=$this->removeLastChar($line);
				echo $line."\n";
			}
		}
	}

	private function fieldToName($field) {
		if ($field=='GenSpec') return 'Latinsk navn';
		if ($field=='DKName') return 'Dansk navn';
		if ($field=='localitybasicString') return 'Lokalitet';
		if ($field=='dato') return 'Dato';
		if ($field=='_AtlaslegInit') return 'Finder';
		if ($field=='_UTM10') return 'UTM10';
		if ($field=='Status') return 'Status';
		if ($field=='AtlasUserLati') return 'Latitude';
		if ($field=='AtlasUserlati') return 'Latitude';
		if ($field=='AtlasUserLong') return 'Longitude';
		if ($field=='VegType') return 'Vegetationstype';
		if ($field=='associatedOrganism') return 'V&aelig;rt';
		if ($field=='Substrate') return 'Substrat';
		if ($field=='CollNo') return 'CollNo';
		if ($field=='AtlasUserPrec') return 'N&oslash;jagtighed';
		if ($field=='Det.') return 'Bestemmer';
		if ($field=='Det') return 'Bestemmer';
		return $field;
	}
}

$CSV = new DownloadCSV();

?>
