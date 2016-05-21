<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('atlaspage.php');
include('logged_search.php');

class Ecology extends AtlasPage {
	protected $search_id;
	protected $arter = array();
	protected $redlist = array();
	protected $ecology = array();
	protected $ecology_names = array();
	protected $count = 0;
	protected $months = array();
	protected $descs = array();
	protected $month_descs = array('Jan','Feb','Mar','Apr','Maj','Jun','Jul','Aug','Sep','Okt','Nov','Dec');
	protected $years = array();

	protected function extraHead() {
?>
<script type="text/javascript" src="js/ecology.js"></script>
<style type="text/css">
#details {
	position: absolute;
	z-index: 1;
	height:auto;
	min-height: 300px;
	overflow : visible;
	font-size: 13px;
	background-color:#EBE8DF;
	display: none;
	white-space: nowrap;
}
.ui-widget-header {
	background: #2B499A;
	color: white;
	height: 20px;
}
.ui-widget {
	font-size: 14px;
}
.ui-widget-header a {
	color: white;
}
#bodyContainer {
	height: 750px;
	background: none;
}
#ecograph {
	width: 850px;
	height: 500px;
	overflow: visible;
}
span.redlist {
	padding-left:6px;
	color:red;
}
div.content {
	height: 690px;
	float: left;
	overflow: hidden;
	clear: none;
	text-align: left;
}
#species {
	float: left;
	border: 1px inset silver;
	width: 95%;
	height: 465px;
	white-space:nowrap;
	background-color: #dadada;
	overflow-x: scroll;
}
div.bottom-panel {
	height: 34px;
	float: left;
	clear: both;
	width: 100%;
	background-image: url(http://www.svampe.dk/atlas/GeneralGraphics/GreyDarkStriae.gif);
	border-top: 1px solid black;
}
#link {
	clear: both;
	float: left;
	height: 20px;
	position: absolute;
	top: 470px;
	left: 320px;
	font-size: 12px;
	z-index: 1000;
}	
</style>
<?
	}	

	public function __construct() {
		parent::__construct();
		//
		$this->title="Typiske levesteder for rødlistede arter i søgning";
		$this->search_id=isset($_GET['search_id']) ? $_GET['search_id'] : 0;
		//$this->initDescs();
		$this->draw();

		$keys='';
		$values='';
		arsort($this->ecology);
		foreach ($this->ecology as $key=>$value) {
			//$keys.='"'.$this->descs[$key].'",';
			$keys.='"'.$this->naturtypeToDesc($key).'",';
			$values.=$value.',';
		}
		$keys='['.$this->removeLastChar($keys).']';
		$values='['.$this->removeLastChar($values).']';

		//month
		/*
		$month_keys='';
		$month_values='';
		foreach ($this->months as $key=>$value) {
			if ($key>0) {
				$month_keys.='"'.$this->month_descs[$key-1].'",';
				$month_values.=$value.',';
			}
		}
		$month_keys='['.$this->removeLastChar($month_keys).']';
		$month_values='['.$this->removeLastChar($month_values).']';
		*/

		//names
		$names='';
		$count=0;
		foreach ($this->ecology as $key=>$value) {
			$name=$this->ecology_names[$key];
			$names.='{ "row": '.$count.', "names" : "';
			foreach ($name as $n) {
				//$this->debug(utf8_decode($n['DKName']));
				//$dk=utf8_encode($n['DKName']);
				//$dk = mb_convert_encoding($n['DKName'], 'UTF-8', 'ISO-8859-1');
				$dk=$n['DKName'];
				$dk=($dk!='') ? '&nbsp;('.$dk.')' : '';
				$names.='<em><b>'.$n['GenSpec'].'</b></em>'.$dk;
				$names.="<span class='redlist'>".$n['Status'].'</span>';
				$names.='<br>';
			}
			$names.='"},';
			$count++;
		}
		$names='['.$this->removeLastChar($names).']';

		//year
		$year=date("Y");
		$low=$year;
		foreach ($this->years as $key=>$value) {
			if ($key>1800) {
				if ($key<$low) $low=$key;
			}
		}
		for ($i=$low;$i<=$year;$i++) {
			if (!isset($this->years[$i])) {
				$this->years[$i]=0;
			}
		}

		$year_keys='';
		$year_values='';
		ksort($this->years);
		foreach ($this->years as $key=>$value) {
			if ($key>0) {
				$year_keys.='"'.$key.'",';
				$year_values.=$value.',';
			}
		}
		$year_keys='['.$this->removeLastChar($year_keys).']';
		$year_values='['.$this->removeLastChar($year_values).']';
		
		$this->updateGraphs($keys, $values, $year_keys, $year_values, $names);

		//
		echo '<div id="species-window" style="text-align:left;font-size:13px;"></div>';
	}

	private function naturtypeToDesc($code) {
		global $naturtyper;
		foreach ($naturtyper as $naturtype) {
			if ($naturtype['code']==$code) {
				return $naturtype['desc'];
			}
		}
		return '';
	}

	private function HTMLify($s) {
		$s=str_replace('ø','&oslash;',$s);
		$s=str_replace('Ø','&Oslash;',$s);
		$s=str_replace('å','&aring;',$s);
		$s=str_replace('Å','&Aring;',$s);
		$s=str_replace('æ','&aelig;',$s);
		$s=str_replace('Æ','&AElig;',$s);
		return $s;
	}

	private function updateEcology($ecology, $dkname, $genspec, $status) {
		$split=explode(',',$ecology);
		foreach ($split as $s) {
			$s=trim($s);
			if (isset($this->ecology[$s])) {
				$this->ecology[$s]=$this->ecology[$s]+1;
			} else {
				$this->ecology[$s]=1;
			}
			
			if (!isset($this->ecology_names[$s])) {
				$this->ecology_names[$s]=array();
			}
			$this->ecology_names[$s][]=array('DKName'=>$dkname, 'GenSpec'=>$genspec, 'Status'=>$status);
		}
	}

	private function updateMonths($month) {
		if (!isset($this->months[$month])) {
			$this->months[]=$month;
			$this->months[$month]=0;
		}
		$this->months[$month]++;
	}

	private function updateYears($year) {
		if (!isset($this->years[$year])) {
			$this->years[$year]=0;
		}
		$this->years[$year]++;
	}

	public function drawText($caption, $text) {
		echo '<span style="font-size:14px;">';
		echo '<b>'.$caption.'</b>&nbsp;:&nbsp;'.$text.'<br>';
		echo '</span>';
	}

	public function draw() {
		parent::draw();

		$select = 'select atlas._DKIndex, taxon.StatusTal, taxon.Status, taxon.GenSpec, taxon.DKName, '.
				'atlas.AtlasUserLong, atlas.AtlasUserLati, atlas.date_month, atlas.date_year ';

		//mysql_set_charset('ISO-8859-1');
		//mysql_set_charset('Latin1');
		//mysql_set_charset('UTF8_danish_ci');
		mysql_set_charset('utf8');
		$search=new LoggedSearch($select);
		$result=$search->result;

		$kode=0;
		while ($row = mysql_fetch_array($result)) {
			if ($search->isIncludable($row)) {
				$this->count++;
				if (!in_array($row['_DKIndex'], $this->arter)) {
					$this->arter[]=$row['_DKIndex'];
					if ($row['StatusTal']>10) {
						if (!in_array($row['_DKIndex'], $this->redlist)) {
							$this->redlist[]=$row['_DKIndex'];
							$SQL='select Naturtyper from atlasEcology where DKIndexNumber="'.$row['_DKIndex'].'"';
							$type=$this->getRow($SQL);
							if ($type['Naturtyper']!='') {
								$kode++;

								//$dkname=utf8_decode($row['DKName']);
								$dkname=htmlentities(utf8_decode($row['DKName']));

								$this->updateEcology($type['Naturtyper'], $dkname, $row['GenSpec'], $row['Status']);
								$this->updateMonths($row['date_month']);
								$this->updateYears($row['date_year']);
							}
						}
					}
				}
			}
		}
		echo '<div class="content" style="font-size:12px;font-family:helvetica;padding-left:10px;padding-top:30px;width:310px;">';
		$this->drawText('Antal fund i alt', $this->count);
		$this->drawText('Antal arter total',count($this->arter));
		$this->drawText('Antal rødlistede arter',count($this->redlist));
		$this->drawText('Antal rødlistede med kode', $kode);

		echo '<br><b><em>Søgning</em></b>:<br><span style="margin-top:30px;">';

		if (count($search->polygon)>0) {
			echo '<div id="map" style="width:300px;height:200px;"></div>';
			echo '<input type="hidden" id="poly" name="poly" ';
			$count=1;
			foreach ($search->polygon as $poly) {
				echo 'p'.$count.'="'.$poly[0].','.$poly[1].'" ';
				$count++;
			}
			echo '>';
		}

		$local=$this->isLocalHost();
		foreach ($search->searchDescArray as $a) {
			if ($a['desc']!='Polygon') {
				echo '<b>'.$a['desc'].'</b>&nbsp;:&nbsp;';
				$value=($local) ? $a['value'] : utf8_encode($a['value']);
				echo $value.'<br>';
			}
		}
		//$s=str_replace(': ',':<br>',$search->searchDesc);
		//echo $s;
		echo '</span>';

		echo '<br><em><b>Arter:</b></em><br>';
		echo '<div id="species"></div>';

		echo '</div>';

		echo '<div class="content" style="width:640px;padding-top:10px;">';
		echo '<div id="ecograph"></div>';
		echo '<div id="link"></div>';
		echo '<div id="monthgraph"></div>';
		echo '</div>';

		echo '<div class="bottom-panel">&nbsp;</div>';
	}

	//arrays.php -> naturtyper to javascript links[]
	private function updateLinks() {
		global $naturtyper;
		foreach ($naturtyper as $naturtype) {
			echo 'links["'.$naturtype['desc'].'"]="'.$naturtype['url'].'";'."\n";
		}
	}

	private function updateGraphs($keys, $values, $month_keys, $month_values, $names) {
?>
<script type="text/javascript">
var links = [];
<? $this->updateLinks();?>

function keyToLink(key) {
	if (key in links) return links[key];
	return '';
}
function drawChart() {
	var names = <? echo $names;?>;

	var ecodata = new google.visualization.DataTable();
	ecodata.addColumn('string', 'Indikator');
	ecodata.addColumn('number', 'Andel');

	var keys=<? echo $keys;?>;
	var values=<? echo $values;?>;
	
	for (var i = 0; i < keys.length; i++) {
		ecodata.addRow([keys[i], values[i]]);
	}

	var options = {
		'width': 630,
		'height': 650,
		'is3D': 'true',
		'color' : 'maroon',
		'backgroundColor' : 'transparent',
		'legend' : { position: 'right', textStyle: {color: 'black', fontSize: 12}},
		'chartArea': {left:0,top:50,width:"110%"}
		//'chartArea': {left:0,top:50}
	};
	var chart = new google.visualization.PieChart(document.getElementById('ecograph'));
	chart.draw(ecodata, options);

	/* 26.12.2013
	google.visualization.events.addListener(chart, 'onmouseover',  function(row, col) {
		$('html, body').css("cursor", "pointer");
		//Ecology.speciesWindow(keys[row.row], names[row.row].names);
		$("#species").html(names[row.row].names);
		$("#link").html('');
		var link=keyToLink(keys[row.row]);
		if (link!='') {
			var html='Klik for at vise <a href="'+link+'" target=_blank>'+link+'</a> (åbner i ny side)';
			$("#link").html(html);
		}
	});
	*/

	google.visualization.events.addListener(chart, 'onmouseover',  function(row, col) {
		$('html, body').css("cursor", "pointer");
	});

	google.visualization.events.addListener(chart, 'onmouseout',  function(row, col) {
		$('html, body').css("cursor", "auto");
	});

	/* 26.12.2013
	google.visualization.events.addListener(chart, 'select',  function() {
		var selectedItem = chart.getSelection()[0];
		if (selectedItem) {
			var link=keyToLink(keys[selectedItem.row]);
			if (link!='') {
				window.open(link, '_blank');
			}
		}
	});
	*/

	google.visualization.events.addListener(chart, 'select',  function() {
		var selectedItem = chart.getSelection()[0];
		//console.log(selectedItem);
		if (selectedItem) {
			$("#species").html(names[selectedItem.row].names);
			var link=keyToLink(keys[selectedItem.row]);
			if (link!='') {
				var html='Klik for at vise <a href="'+link+'" target=_blank>'+link+'</a> (åbner i ny side)';
				$("#link").html(html);
				//window.open(link, '_blank');
			}
		}
		/*		
		$("#species").html(names[row.row].names);
		$("#link").html('');
		var link=keyToLink(keys[row.row]);
		if (link!='') {
			var html='Klik for at vise <a href="'+link+'" target=_blank>'+link+'</a> (åbner i ny side)';
			$("#link").html(html);
		}

		var selectedItem = chart.getSelection()[0];
		if (selectedItem) {
			var link=keyToLink(keys[selectedItem.row]);
			if (link!='') {
				window.open(link, '_blank');
			}
		}
		*/
	});

	//year
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Måned');
	data.addColumn('number', 'Fund');

	var year_keys=<? echo $month_keys;?>;
	var year_values=<? echo $month_values;?>;
	
	for (var i = 0; i < year_keys.length; i++) {
		data.addRow([year_keys[i], year_values[i]]);            
	}

	var options = {
		'title':'Fordeling over år',
		'titlePosition' : 'out',
		'colors' : ['#A52A2A', '#A52A2A'],
		'vAxis' : { 'textPosition' : 'out', 'gridlines' : { 'count' : 4 } },
		'hAxis' : { 'slantedTextAngle' : 0, 'maxAlternation' : 1 },
		'width': 600,
		'height': 170,
		'legend' : { 'position' : 'none'} ,
		'backgroundColor' : 'transparent',
		'chartArea' : { left: 30, top: 20, width: "100%", height: "70%"}
	};

	var yearchart = new google.visualization.ColumnChart(document.getElementById('monthgraph'));
	yearchart.draw(data, options);

}
$(document).ready(function() {
	drawChart();
	Ecology.initPolygonMap(34);
});
</script>
<?
	}
}

$ecology = new Ecology();

?>
