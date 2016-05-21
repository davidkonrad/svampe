<?
if (!class_exists('Db')) {
	include('common/Db.php');
}
include('common/arrays.php');

class Point {
    var $x;
    var $y;

    function Point($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }
}

class AtlasSearchBase extends Db {
	protected $baseSQL; 
	public $polygon = array();
	protected $polygon_points;

	protected $kommune_polygons = null;
	protected $kommune_polygons_points = null;
	protected $kommune_lookup = array();

	public $center = null; //should be type of Point
	public $center_code = null; //should be K, P or L for kommune / polygon / lokalitet

	public $searchDesc ='';
	public $searchDescArray = array();

	protected $vars; // either $_POST or $_GET (for CSV)

	public function __construct() {
		parent::__construct();

		$this->vars=(!empty($_POST)) ? $_POST : $_GET;

		$this->baseSQL='select atlas.AtlasIDnummer, atlas.AtlasPic, '.
			'taxon.genSpec, taxon.DKName, taxon.DKIndex, taxon.MycokeyIDDKWebLink, '.
			'gaz.localityBasicString, atlas.date_day, atlas.date_month, '.
			'atlas.date_year, person.AtlaslegInit, '.
			'atlas._UTM10 as UTM10, person.Leg, taxon.Status, '.
			'atlas.AtlasUserLati, atlas.AtlasUserLong, '.
			'atlas.VegType, atlas.associatedOrganism, atlas.Substrate, '.
			'atlas.AtlasGeoRef, atlas.AtlasUserPrec, '.
			'atlas.Det, atlas.CollNr '.

			'from atlasFund atlas, atlasTaxon taxon, atlasPerson person, '.
			/*'atlasForum forum, */'atlasGaz gaz, atlasEcology ecol '.

			'where '.
			'(atlas._DKIndex=taxon.DKIndex '.
			'and atlas._AtlaslegInit=person.AtlaslegInit '.
			/*'and atlas.AtlasIDnummer=forum.AtlasIDnummer '.*/
			'and atlas._AtlasLocID=gaz.AtlasLocID) ';
	}

	protected function HTMLify($s) {
		//$this->fileDebug($s);
		$s=str_replace('ø','&oslash;',$s);
		$s=str_replace('Ø','&Oslash;',$s);
		$s=str_replace('å','&aring;',$s);
		$s=str_replace('Å','&Aring;',$s);
		$s=str_replace('æ','&aelig;',$s);
		$s=str_replace('Æ','&AElig;',$s);
		return $s;
	}

	protected function calcCenter($lat1, $lat2, $long1, $long2, $code) {
		$lat=($lat1+$lat2)/2;
		$long=($long1+$long2)/2;
		$this->center=new Point($lat, $long);
		$this->center_code=$code;
	}

	protected function getLocalityCenter($localityBasicString) {
		$SQL='select Latitude, Longitude from atlasGaz where localitybasicString="'.$localityBasicString.'"';
		mysql_set_charset('Latin1');
		$result=$this->query($SQL);
		if (mysql_num_rows($result)>0) {
			$row=mysql_fetch_assoc($result);
			$lat=str_replace(',','.',$row['Latitude']);
			$long=str_replace(',','.',$row['Longitude']);
			$this->center=new Point($lat, $long);
			$this->center_code='L';
		}
	}

	protected function addSearchDesc($desc, $value) {
		if ($this->searchDesc!='') $this->searchDesc.='. ';
		$this->searchDesc.=$desc.': <b>'.$value.'</b>';
		$this->searchDescArray[]=array('desc'=>$desc, 'value'=>$value);
	}
		
	private function testParam($param) {
		return ((isset($this->vars[$param])) && ($this->vars[$param]!=''));
	}

	protected function evaluateParams() {
		global $kommuner; //kommuner.php
		//altid kun godkendte fund!!
		$this->baseSQL.=' and atlas.AtlasForumVali="Godkendt"';

		$searchDesc='';		
		if (isset($this->vars['LL0'])) {
			$this->polygon=array();
			$val=0;
			while (isset($this->vars['LL'.$val])) {
				//is in form &LL0=(56.12573618568972, 9.751806640624977)
				$raw=$this->vars['LL'.$val];
				$raw=str_replace(array('(',')',' '),'', $raw);
				$poly=explode(',',$raw);
				$this->polygon[]=$poly;
				$val++;
				//
				if ($searchDesc!='') $searchDesc.='->';
				$searchDesc.=$raw;
			}
			//min max
			$latmin=90;
			$latmax=-90;
			$longmin=90;
			$longmax=-90;
			foreach ($this->polygon as $poly) {
				if ($latmin>$poly[0]) $latmin=$poly[0];
				if ($latmax<$poly[0]) $latmax=$poly[0];
				if ($longmin>$poly[1]) $longmin=$poly[1];
				if ($longmax<$poly[1]) $longmax=$poly[1];
			}
			$this->baseSQL.=' and ('.
				'(CAST(AtlasUserlati as DECIMAL(6,4))>'.$latmin.') and '.
				'(CAST(AtlasUserlati as DECIMAL(6,4))<'.$latmax.') and '.
				'(CAST(AtlasUserLong as DECIMAL(6,4))>'.$longmin.') and '.
				'(CAST(AtlasUserLong as DECIMAL(6,4))<'.$longmax.') '.
				')';

			$this->calcCenter($latmin, $latmax, $longmin, $longmax, 'P');
			$this->addSearchDesc('Polygon', $searchDesc);
		}

		if ($this->testParam('initialer')) {
			$this->baseSQL.=' and person.AtlaslegInit="'.$this->vars['initialer'].'"';
			$this->addSearchDesc('Initialer', $this->vars['initialer']);
		}
		if ($this->testParam('GenSpec')) {
			$this->baseSQL.=' and taxon.GenSpec like "%'.utf8_decode($this->vars['GenSpec']).'%"';
			//$this->baseSQL.=' and taxon.FullName like "%'.utf8_decode($this->vars['GenSpec']).'%"';
			$this->addSearchDesc('Latinsk navn', $this->vars['GenSpec']);
		}
		if ($this->testParam('DKName')) {
			$this->baseSQL.=' and taxon.DKName like "%'.utf8_decode(trim($this->vars['DKName'])).'%"';
			$this->addSearchDesc('Dansk navn', utf8_decode($this->vars['DKName']));
		}
		/*
		if ($this->testParam('atlasID')) {
			$this->baseSQL.=' and atlas.AtlasIDNummer="'.utf8_decode($this->vars['atlasID']).'"';
			$this->addSearchDesc('Database Nr.', utf8_decode($this->vars['atlasID']));
		}
		*/
		if ($this->testParam('atlasID')) {
			$ids = explode(',', $this->vars['atlasID']);
			if (count($ids)==1) {
				$this->baseSQL.=' and atlas.AtlasIDNummer="'.utf8_decode($this->vars['atlasID']).'"';
				$this->addSearchDesc('Database Nr.', utf8_decode($this->vars['DKName']));
			} else {
				$idSQL='';
				foreach ($ids as $id) {
					if ($idSQL!='') $idSQL.=' or ';
					$idSQL.='atlas.AtlasIDNummer="'.utf8_decode($id).'"';
					$this->addSearchDesc('Database Nr.', utf8_decode($id));
				}
				$this->baseSQL.=' and ('.$idSQL.') ';
			}
		}
		if ($this->testParam('kommune')) {
			//$this->addSearchDesc('Kommune', utf8_decode($this->vars['kommune']));
			$this->addSearchDesc('Kommune', $kommuner[$this->vars['kommune']]);
			$this->baseSQL.=$this->createKommunePolygons($this->vars['kommune']);
		}

		if ($this->testParam('localitybasicString')) {
			$this->baseSQL.=' and gaz.localitybasicString like "%'.utf8_decode($this->vars['localitybasicString']).'%"';
			//$this->addSearchDesc('Lokalitet', utf8_decode($this->vars['localitybasicString']));
			$this->addSearchDesc('Lokalitet', utf8_decode($this->vars['localitybasicString']));
			$this->getLocalityCenter(utf8_decode($this->vars['localitybasicString']));
		}
		if ($this->testParam('valistatus')) {
			$v=$this->vars['valistatus']; 
			$v=str_replace('>','',$v); //godkendt er ">godkendt"
			$this->baseSQL.=' and atlas.AtlasForumVali="'.$v.'"';
			$this->addSearchDesc('Status', $v);
		}
		if ($this->testParam('utm')) {
			$this->baseSQL.=' and atlas._UTM10="'.$this->vars['utm'].'"';
			$this->addSearchDesc('UTM10', $this->vars['utm']);
		}

		if ($this->testParam('naturtype')) {
			$list=explode('+', $this->vars['naturtype']);
			$param='';
			foreach($list as $l) {
				if ($param!='') $param.=' or ';
				$param.=' (ecol.Naturtyper like "%'.$l.'%" and ecol.DKIndexNumber=atlas._DKIndex) ';
				$this->addSearchDesc('Naturtype', $l);
			}
			$this->baseSQL.=' and ('.$param.')';			
		} else {
			//HACK! since mySQL apparently hangs when including atlasEcology without using it
			if (strpos($this->baseSQL, 'Naturtyper')<=0) {
				$this->baseSQL=str_replace(', atlasEcology ecol', '', $this->baseSQL);
			}
		}

		if ($this->testParam('redlist')) {
			$list=explode('+', $this->vars['redlist']);
			$param='';
			foreach($list as $l) {
				//ALL is only criteria
				if ($l=='ALL') {
					$param='taxon.StatusTal>10';
					$this->addSearchDesc('Rødlistede','alle');
				} else {
					$eval = ($param!='') ? ' or ' : '';
					switch($l) {
						case 'RE' : $param.=$eval.'taxon.StatusTal=20'; break;
						case 'CR' : $param.=$eval.'taxon.StatusTal=18'; break;
						case 'EN' : $param.=$eval.'taxon.StatusTal=16'; break;
						case 'VU' : $param.=$eval.'taxon.StatusTal=14'; break;
						case 'NT' : $param.=$eval.'taxon.StatusTal=12'; break;
						case 'LC' : $param.=$eval.'taxon.StatusTal=9'; break;
						case 'DD' : $param.=$eval.'taxon.StatusTal=7'; break;
						case 'NA' : $param.=$eval.'taxon.StatusTal=5'; break;
						case 'NE' : $param.=$eval.'taxon.StatusTal=0'; break;
						default : break;
					}
					$this->addSearchDesc('Rødlistede', $l);
				}
			}
			$this->baseSQL.=' and ('.$param.')';
		}
		if (!$this->testParam('exact-date')) {
			if ($this->testParam('year')) {
				//$this->addSearchDesc('&Aring;r', $this->vars['year']);
				if ($this->testParam('to-year')) {
					//$this->addSearchDesc('Til &aring;r', $this->vars['to-year']);
					$from='';
					if ($this->testParam('day')) {
						$from.=$this->vars['day'];
					} else {
						$from.='0';
					}
					if ($this->testParam('month')) {
						$from.='-'.$this->vars['month'];
					} else {
						$from.='-0';
					}
					$from.='-'.$this->vars['year'];

					$to='';
					if ($this->testParam('to-day')) {
						$to.=$this->vars['to-day'];
					} else {
						$to.='0';
					}
					if ($this->testParam('to-month')) {
						$to.='-'.$this->vars['to-month'];
					} else {
						$to.='-0';
					}
					$to.='-'.$this->vars['to-year'];

					$this->baseSQL.=" and (STR_TO_DATE(concat(date_day,'-',date_month,'-',date_year), '%d-%m-%Y') ".
						"between STR_TO_DATE('".$from."', '%d-%m-%Y') AND STR_TO_DATE('".$to."', '%d-%m-%Y')) ";

					$this->addSearchDesc('Fra dato', $from);
					$this->addSearchDesc('Til dato', $to);

				 } else {
					$this->baseSQL.=' and (date_year='.$this->vars['year'];
					$this->addSearchDesc('&Aring;r', $this->vars['year']);

					if ($this->testParam('month')) {
						$this->baseSQL.=' and date_month='.$this->vars['month'];
						$this->addSearchDesc('M&aring;ned', $this->vars['month']);
					}

					if ($this->testParam('day')) {
						$this->baseSQL.=' and date_day='.$this->vars['day'];
						$this->addSearchDesc('Dag', $this->vars['day']);
					}

					$this->baseSQL.=') ';
				}
			} else {
				if ($this->testParam('year')) $this->baseSQL.=' and date_year='.$this->vars['year'];
				if ($this->testParam('month')) $this->baseSQL.=' and date_month='.$this->vars['month'];
				if ($this->testParam('day')) $this->baseSQL.=' and date_day='.$this->vars['day'];
			}
		
		}
	}

	//check for polygon / kommune, check for inside polygons, if set
	public function isIncludable($row) {
		if ((count($this->polygon)>0) || (is_array($this->kommune_polygons_points))) {
			$lat=$row['AtlasUserLati'];
			$long=$row['AtlasUserLong'];
			return $this->inPolygon($long, $lat);
		}
		return true;
	}

	private function pointInside($p,&$points) {
		set_time_limit(60);
		$c = 0;
		$p1 = $points[0];
		$n = count($points);

		for ($i=1; $i<=$n; $i++) {
			$p2 = $points[$i % $n];
			if ($p->y > min($p1->y, $p2->y)
				&& $p->y <= max($p1->y, $p2->y)
				&& $p->x <= max($p1->x, $p2->x)
				&& $p1->y != $p2->y) {
					$xinters = ($p->y - $p1->y) * ($p2->x - $p1->x) / ($p2->y - $p1->y) + $p1->x;
					if ($p1->x == $p2->x || $p->x <= $xinters) {
						$c++;
					}
			}
			$p1 = $p2;
		}
		// if the number of edges we passed through is even, then it's not in the poly.
		return $c%2!=0;
	}

	protected function inPolygon($lat, $long) {
		if (is_array($this->kommune_polygons_points)) {
			if (isset($this->kommune_lookup[$lat.$long])) {
				return $this->kommune_lookup[$lat.$long];
			}
			$this->kommune_lookup[]=$lat.$long;
			foreach($this->kommune_polygons_points as $points) {
				if ($this->pointInside(new Point($long, $lat), $points)) {
					$this->kommune_lookup[$lat.$long]=true;
					return true;
				}
			}
			$this->kommune_lookup[$lat.$long]=false;
			return false;
				
		//normal polygon search
		} else {
			if (!is_array($this->polygon_points)) { 
				$this->polygon_points=array();
				foreach ($this->polygon as $p) {
					$this->polygon_points[]=new Point((float)$p[0], (float)$p[1]);
				}
				if (count($this->polygon_points)%2!==0) {
					$this->polygon_point[]=$this->polygon_points[0];
				}
			}
			return $this->pointInside(new Point($long, $lat), $this->polygon_points);
		}
		/*
		$vertices_x=array();
		$vertices_y=array();
		$points_polygon=count($this->polygon)-1;
		for ($i=0;$i<=$points_polygon;$i++) {
			$point=$this->polygon[$i];
			$vertices_x[]=$point[0];
			$vertices_y[]=$point[1];
		}
		$i = $j = $c = 0;
		for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
			if ( (($vertices_y[$i]  >  $lat != ($vertices_y[$j] > $lat)) &&
			($long < ($vertices_x[$j] - $vertices_x[$i]) * ($lat - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) )
				$c = !$c;
		}
		return $c;
		*/
	}	

	protected function createKommunePolygons($knr) {
		$json = file_get_contents('http://geo.oiorest.dk/kommuner/'.$knr.'/graense.json');
		$data=json_decode($json);

		$this->kommune_polygons = array();
		$this->kommune_polygons_points = array();

		//min max
		$latmin=90;
		$latmax=-90;
		$longmin=90;
		$longmax=-90;

		foreach($data->coordinates as $poly) {
			$kommune_points=array();
			$count=0;
			foreach($poly[0] as $point) {
				if (($count % 10)==0) {
					$kommune_points[]=new Point($point[1], $point[0]);
				}
				//check min max
				if ($latmin>$point[1]) $latmin=$point[1];
				if ($latmax<$point[1]) $latmax=$point[1];
				if ($longmin>$point[0]) $longmin=$point[0];
				if ($longmax<$point[0]) $longmax=$point[0];

				$count++;
			}
			$this->kommune_polygons_points[]=$kommune_points;
		}
	
		$this->calcCenter($latmin, $latmax, $longmin, $longmax, 'K');
	
		//return min max SQL
		return ' and ('.
			'(CAST(AtlasUserlati as DECIMAL(6,4))>'.$latmin.') and '.
			'(CAST(AtlasUserlati as DECIMAL(6,4))<'.$latmax.') and '.
			'(CAST(AtlasUserLong as DECIMAL(6,4))>'.$longmin.') and '.
			'(CAST(AtlasUserLong as DECIMAL(6,4))<'.$longmax.') '.
		')';
	}		

	protected function getSearchRequest($id, $updateLastParams=true) {
		$SQL='select r.param, r.value from userLogRequest r, userLog l '.
			'where l.log_id='.$id.' and l.log_id=r.log_id '.
			'order by request_order';
	
		$LAST_PARAMS = '';

		$GET=array();
		//mysql_set_charset('utf8');
		//$this->setCharset();

		$result=$this->query($SQL);
		while ($row = mysql_fetch_array($result)) {
			$GET[$row['param']]=$row['value']; //simulate a browser request

			$LAST_PARAMS.='&'.$row['param'].'='.utf8_decode($row['value']);
		}

		if ($updateLastParams) {
			echo '<script type="text/javascript">';
			echo 'if (typeof atlasSearch != "undefined") { atlasSearch.lastParams="'.$LAST_PARAMS.'"; }';
			echo '</script>';
		}

		return $GET;
	}

}

?>
