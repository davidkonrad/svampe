<!doctype html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">google.load('visualization', '1.0', {'packages':['corechart']});</script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<style>
.left {
	float: left;
	clear: none;
	height: 300px;
}
.right {
	float: left;
	clear: right;
	display: block;
}
td {
	font-size: 16px;
}
td.desc {
	font-weight: bold;
	padding-right: 20px;
}
</style>
</head>
<body>
<script>
/*
$.ajax({
	url : 'json/kommuner.json',
	success : function(json) {
		var kommuner = json.kommuner;
	}
});
function idToKommune(id) {
	for (var i=0;i<kommuner.length;i++) {
		if (kommuner[i].id==id) {
			return kommuner[i].navn;
		}
	}
}
*/
</script>

<?

include('common/Db.php');

class Statistik extends Db {
	private $max = 25;
	private $kommuner;

	public function __construct() {
		parent::__construct();
		$this->getKommuner();
		$this->searchOverAll();
		$this->DKName();
		$this->GenSpec();
		$this->locality();
		$this->kommuneGraph();
		$this->redlist();
		$this->naturtype();
		$this->leg();
	}

	private function getKommuner() {
		$k = file_get_contents('http://geo.oiorest.dk/kommuner.json');
		$this->kommuner = json_decode($k);
	}

	private function idToKommune($id) {
		foreach ($this->kommuner as $kommune) {
			if ($kommune->nr==$id) {
				return $kommune->navn;
			}
		}
		//return 'fejl '.$id;
 	}

	private function drawGraph($array, $div, $title, $captionDesc, $valueDesc, $large=false) {
		$captions='';
		$values='';
		echo '<h1>'.$title.'</h1>';
		echo '<div class="left">';
		echo '<table>';
		foreach($array as $key=>$value) {
			if ($captions!='') $captions.=',';
			if ($values!='') $values.=',';
			$captions.='"'.$key.'"';
			$values.=$value;
			echo '<tr><td class="desc">'.$key.'</td><td>'.$value.'</td></tr>';
		}
		echo '</table>';
		echo '</div>';
		echo '<div class="right">';
		echo '<div id="'.$div.'" class="graph"></div>';
		echo '</div>';
		echo '<hr style="clear:both;width:100%;">';
		$captions='['.$captions.']';
		$values='['.$values.']';

?>
<script>
$(document).ready(function() {
var captions=<? echo $captions;?>;
var values=<? echo $values;?>;

var data = new google.visualization.DataTable();
data.addColumn('string', '<? echo $captionDesc;?>');
data.addColumn('number', '<? echo $valueDesc;?>');

for (var i=0;i<captions.length; i++) {
	data.addRow([captions[i], values[i]]);            
}

var options = {
	'titlePosition' : 'out',
	'colors' : ['#A52A2A', '#A52A2A'],
	'vAxis' : { 'textPosition' : 'out', 'gridlines' : { 'count' : 3 }, "viewWindow" : { "min": 0 } },
	'hAxis' : { 'slantedTextAngle' : 145, 'maxAlternation' : 1, "viewWindow" : { "min": 0 } },
	'width': 1000,
<?
if ($large) {
	echo 'height: 700,';
} else {
	echo 'height: 300,';
}
?>
	'legend' : { 'position' : 'none'} ,
	'backgroundColor' : 'transparent' ,
	'chartArea' : { left: 50, top: 10, width: "90%", height: "65%"}
};

var chart = new google.visualization.ColumnChart(document.getElementById('<? echo $div;?>'));
chart.draw(data, options);
});
</script>
<?
	}

	private function getOverAllParam($param) {
		$SQL='select count(distinct log_id) as c from userLogRequest where param="'.$param.'"';
		$result=$this->getRow($SQL);
		return $result['c'];
	}

	private function getOverAllDate() {
		$SQL='select count(distinct log_id) as c from userLogRequest where '.
			'param="day" or param="month" or param="year" or '.
			'param="to-day" or param="to-month" or param="to-year"';

		$result=$this->getRow($SQL);
		return $result['c'];
	}

	private function getOverAllPolygon() {
		$SQL='select count(distinct log_id) as c from userLogRequest where param like "LL%"';
		$result=$this->getRow($SQL);
		return $result['c'];
	}
		
	private function searchOverAll() {
		$overall = array();
		$overall['Dansk navn']=$this->getOverAllParam('DKName');
		$overall['Latinsk navn']=$this->getOverAllParam('GenSpec');
		$overall['Kommune']=$this->getOverAllParam('kommune');
		$overall['Lokalitet']=$this->getOverAllParam('localitybasicString');
		$overall['AtlasID nr.']=$this->getOverAllParam('atlasID');
		$overall['UTM10']=$this->getOverAllParam('utm');
		$overall['Naturtype']=$this->getOverAllParam('naturtype');
		$overall['Rødliste']=$this->getOverAllParam('redlist');
		$overall['Finder']=$this->getOverAllParam('initialer');
		$overall['Dato interval']=$this->getOverAllDate();
		$overall['Polygon']=$this->getOverAllPolygon();

		arsort($overall);
		$this->drawGraph($overall, 'overall', 'Alle søgninger - fordeling på søgeparametre', 'Parameter', 'Antal');
	}

	private function DKName() {
		mysql_set_charset('utf8');
		$dkname = array();
		$SQL='select distinct value from userLogRequest where param="DKName"';
		$result=$this->query($SQL);
		while ($row = mysql_fetch_assoc($result)) {
			$SQL='select count(*) as c from userLogRequest where param="DKName" and value="'.$row['value'].'"';
			$count=$this->getRow($SQL);
			$dkname[$row['value']]=$count['c'];
		}
		arsort($dkname);
		$dkname = array_slice($dkname, 0, $this->max);		
		$this->drawGraph($dkname, 'dkname', 'De '.$this->max.' mest søgte danske artsnavne', 'Dansk navn', 'Antal', true);
	}

	private function GenSpec() {
		mysql_set_charset('utf8');
		$genspec = array();
		$SQL='select distinct value from userLogRequest where param="GenSpec"';
		$result=$this->query($SQL);
		while ($row = mysql_fetch_assoc($result)) {
			mysql_set_charset('utf8');
			$SQL='select count(*) as c from userLogRequest where param="GenSpec" and value="'.$row['value'].'"';
			$count=$this->getRow($SQL);
			$genspec[$row['value']]=$count['c'];
		}
		arsort($genspec);
		$genspec = array_slice($genspec, 0, $this->max);		
		$this->drawGraph($genspec, 'genspec', 'De '.$this->max.' mest søgte arter baseret på latinsk navn', 'Latinsk navn', 'Antal', true);
	}

	private function locality() {
		mysql_set_charset('utf8');
		$loc = array();
		$SQL='select distinct value from userLogRequest where param="localitybasicString"';
		$result=$this->query($SQL);
		while ($row = mysql_fetch_assoc($result)) {
			mysql_set_charset('utf8');
			$SQL='select count(*) as c from userLogRequest where param="localitybasicString" and value="'.$row['value'].'"';
			$count=$this->getRow($SQL);
			$loc[$row['value']]=$count['c'];
		}
		arsort($loc);
		$loc = array_slice($loc, 0, $this->max);
		$this->drawGraph($loc, 'loc', 'De '.$this->max.' mest søgte lokaliteter', 'Lokalitet', 'Antal', true);
	}

	private function leg() {
		mysql_set_charset('utf8');
		$leg = array();
		$SQL='select distinct value from userLogRequest where param="initialer"';
		$result=$this->query($SQL);
		while ($row = mysql_fetch_assoc($result)) {
			mysql_set_charset('utf8');
			$SQL='select count(*) as c from userLogRequest where param="initialer" and value="'.$row['value'].'"';
			$count=$this->getRow($SQL);
			$leg[$row['value']]=$count['c'];
		}
		arsort($leg);
		$leg = array_slice($leg, 0, $this->max);
		$this->drawGraph($leg, 'leg', 'De '.$this->max.' mest "populære" findere', 'Finder', 'Antal', true);
	}

	private function kommuneGraph() {
		mysql_set_charset('utf8');
		$kommuner = array();
		$SQL='select distinct value from userLogRequest where param="kommune"';
		$result=$this->query($SQL);
		while ($row = mysql_fetch_assoc($result)) {
			mysql_set_charset('utf8');
			if ($row['value']!='--') {
				$SQL='select count(*) as c from userLogRequest where param="kommune" and value="'.$row['value'].'"';
				$count=$this->getRow($SQL);
				$kommune=$this->idToKommune($row['value']);
				$kommuner[$kommune]=$count['c'];
			}
		}
		arsort($kommuner);
		$kommuner = array_slice($kommuner, 0, $this->max);
		$this->drawGraph($kommuner, 'kommuner', 'De '.$this->max.' mest søgte kommuner', 'Kommuner', 'Antal', true);
	}

	private function redlist() {
		mysql_set_charset('utf8');
		$red = array();
		$SQL='select distinct value from userLogRequest where param="redlist"';
		$result=$this->query($SQL);
		while ($row = mysql_fetch_assoc($result)) {
			mysql_set_charset('utf8');
			if ($row['value']!='undefined') {
				$SQL='select count(*) as c from userLogRequest where param="redlist" and value="'.$row['value'].'"';
				$count=$this->getRow($SQL);
				
				$categories=explode('+', $row['value']);
				foreach ($categories as $cat) {
					if (isset($red[$cat])) {
						$red[$cat]=$red[$cat]+$count['c'];
					} else {
						$red[$cat]=$count['c'];
					}
				}
			}
		}
		arsort($red);
		$this->drawGraph($red, 'red', 'Søgning på rødlistekategorier', 'Kategori', 'Antal', true);
	}

	private function naturtype() {
		mysql_set_charset('utf8');
		$type = array();
		$SQL='select distinct value from userLogRequest where param="naturtype"';
		$result=$this->query($SQL);
		while ($row = mysql_fetch_assoc($result)) {
			mysql_set_charset('utf8');
			$SQL='select count(*) as c from userLogRequest where param="naturtype" and value="'.$row['value'].'"';
			$count=$this->getRow($SQL);

			$naturtyper=explode('+', $row['value']);
			foreach ($naturtyper as $naturtype) {
				if (isset($type[$naturtype])) {
					$type[$naturtype]=$type[$naturtype]+$count['c'];
				} else {
					$type[$naturtype]=$count['c'];
				}
			}
		}
		arsort($type);
		$type = array_slice($type, 0, $this->max);
		$this->drawGraph($type, 'type', 'De '.$this->max.' mest søgte naturtyper', 'Naturtype', 'Antal', true);
	}

}

$statistik= new Statistik();

?>


