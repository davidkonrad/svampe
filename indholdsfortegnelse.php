<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('atlaspage.php');
include('logged_search.php');

class Index extends AtlasPage {
	private $domain = 'http://svampe.dk/soeg/';
	private $char;
	private $ca;
	private $arter;
	private $result;

	public function __construct($char) {
		parent::__construct();

		$this->title="Svampe Atlas - alle svampearter i Danmark";

		if (!isset($_GET['arter'])) {
			$this->arter='da';
			$this->ca=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','x','y','z','Æ','Ø','Å');
		} else {
			switch($_GET['arter']) {
				case 'int' : 
					$this->arter='int';
					$this->ca=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','x','y','z');
					break;
				default :
					$this->arter='da';
					$this->ca=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','x','y','z','Æ','Ø','Å');
					break;
			}
		}
		if (strlen($char)<=1) {
			$this->char= (in_array($char, $this->ca)) ? $char : 'a'; 
		} else {
			$this->char=$char;
		}

		if ($this->arter=='da') {
			//$SQL='select DKIndex, GenSpec, DKName from atlasTaxon where DKName like "'.$this->char.'%" order by DKName asc';
			$SQL='select distinct taxon.DKIndex, taxon.GenSpec, taxon.DKName '.
				'from atlasTaxon taxon, atlasFund fund '.
				'where '.
				'taxon.DKName like "'.$this->char.'%" '.
				//'left(taxon.DKName,1)="'.$this->char.'" '.
				//'convert(taxon.DKName USING utf8) like "'.$this->char.'%" '.

				'and fund._DKIndex=taxon.DKIndex '.
				'and fund.AtlasForumVali="Godkendt" '.
				'and trim(taxon.GenSpec) like "% %" '.
				'order by taxon.DKName asc';
		} else {
			//$SQL='select DKIndex, GenSpec, DKName from atlasTaxon where Genspec like "'.$this->char.'%" order by GenSpec';
			$SQL='select distinct taxon.DKIndex, taxon.GenSpec, taxon.DKName '.
				'from atlasTaxon taxon, atlasFund fund '.
				'where '.
				'taxon.Genspec like "'.$this->char.'%" '.
				'and fund._DKIndex=taxon.DKIndex '.
				'and fund.AtlasForumVali="Godkendt" '.
				'and trim(taxon.GenSpec) like "% %" '.
				'order by taxon.GenSpec asc';
		}
		//mysql_set_charset('utf8_danish_ci');
		mysql_set_charset('utf8');
		//mysql_set_charset('latin1');
		//echo $SQL;
		$this->result=$this->query($SQL);

		$this->draw();
	}

	private function drawCount($DKIndex) {
		$SQL='select count(*) as c from atlasFund '.
			'where '.
			'_DKIndex='.$DKIndex.' and '.
			'AtlasForumVali="Godkendt"';
		$row=$this->getRow($SQL);
		echo '&nbsp;&nbsp;';
		echo '<span style="font-size:70%;vertical-align:top;" title="Antal godkendte fund i svampeatlas">';
		echo '<span style="color:gray;vertical-align:top;">(</span>';
		echo $row['c'];
		echo '<span style="color:gray;vertical-align:top;">)</span>';
		echo '</span>';
	}

	public function draw() {
		parent::draw();

		$this->arterMenu();
		if (strlen($this->char)==1) {
			$header='<h1 class="taksonomi">'.ucfirst($this->char).'</h1>';
		} else {
			$header='<h1 class="taksonomi">A</h1>';
		}

		echo '<div class="header-cnt">';
		echo $header;
		$this->leftMenu();
		echo '</div>';
		echo '<div class="sitemap-cnt"><br/>';

		if (!mysql_num_rows($this->result)) {
			echo '<span style="margin:10px;color: gray;font-family:helvetica;"">';
			echo (strlen($this->char)==1) 
				? 'Desværre. Ingen svampearter med forbogstavet <b style="font-soze:150%;">'.$this->char.'</b>'
				: 'Desværre. Ingen godkendte svampefund med navnet <b style="font-soze:150%;">'.$this->char.'</b>';
			echo '<br></span>';
		}

		while ($row = mysql_fetch_array($this->result)) {
			$url=$this->domain.'rapportside.php?DKIndex='.$row['DKIndex'];
			if ($this->arter=='da') {
					echo '<a class="taksonomi" href="'.$url.'"><strong>'.$row['DKName'].'</strong>&nbsp;&nbsp;<em style="font-family:times,serif;color:black;">'.$row['GenSpec'].'</em></a>';
					$this->drawCount($row['DKIndex']);
					echo '<br>';
			} else {
				echo '<a class="taksonomi" href="'.$url.'"><strong><em>'.$row['GenSpec'].'</em></strong>';
				if ($row['DKName']!='') {
					echo '&nbsp;&nbsp;<span style="font-family:times,serif;color:black;">'.$row['DKName'].'</span>';
				} 
				echo '</a>';
				$this->drawCount($row['DKIndex']);
				echo '<br>';
			}
		}
		echo '<br></div></div></div>';
		echo '</body></html>';
	}

	private function leftMenu() {
		$href='indholdsfortegnelse.php?arter='.$this->arter;
		foreach ($this->ca as $a) {
			if ($a==$this->char) {
				echo '<span class="index" style="color:silver;">'.ucfirst($a).'</span>';
			} else {
				echo '<a class="index" href="'.$href.'&index='.$a.'"><span class="index">'.ucfirst($a).'</a></span>';
			}
		}
	}

	private function arterMenu() {
		$index='&index='.$this->char;
		echo '<div class="arter-menu"><br/>';

		echo 'Sortering : ';
		if ($this->arter=='da') {
			echo '<span class="arter-overview">Danske navne</span>';
			echo '<span class="arter-overview" style="border:0;"><a href="indholdsfortegnelse.php?arter=int'.$index.'">Videnskabeligt navn</a></span>';
		} else {
			echo '<span class="arter-overview" style="border:0;"><a href="indholdsfortegnelse.php?arter=da'.$index.'">Danske navne</a></span>';
			echo '<span class="arter-overview">Videnskabeligt navn</span>';
		}

		$value=(strlen($this->char)>1) ? $this->char : '';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<input type="text" name="search" id="search" value="'.$value.'">';
		echo '<input type="button" id="search-btn" value="Søg">';
		echo '&nbsp;&nbsp;';

		echo '</div>';
	}

	protected function extraHead() {
?>
<script type="text/javascript" src="js/bootstrap.js"></script>
<link href="css/style.css" rel="stylesheet" type="text/css"/>
<style type="text/css">
a:hover {
	text-decoration: none;
	text-transform: none;
	font-weight: normal;
}
a.taksonomi {
	color: #2B499A;
	text-decoration: none;
	margin-left: 25px;
	font-size: 1.3em;
	white-space: nowrap;
}
a.taksonomi:hover {
	color: red;
}
h1.taksonomi {
	color: #2B499A;
	font-size: 7.5em;
	margin: 0px;
	padding: 0px;
	padding-left: 20px;
	font-family : 'times','times new roman','serif';
}
span.arter-overview {
	font-size: 1.2em;
	color: #2B499A; /*silver;*/
	border: 1px dotted #2B499A;
	padding: 2px;
}
span.arter-overview a {
	text-decoration: none;
	color: #2B499A;
	border: 0;
}
.arter-menu {
	margin-top: 5px;
	font-family: arial, verdana, helvetica;
	width: 831px;
	border-bottom: 1px solid silver;
	float: left;
	padding-left: 137px;
	text-align: left;
}
a.index {
	text-decoration: none;
}
span.index {
	color: #2B499A;
	text-decoration: none;
	font-size: 2.5em;
	margin-right: 45px;	
	float: right;
	clear: right;
	font-family : 'verdana','courier new','times','times new roman','serif';
}
.header-cnt {
	float: left;
	width: 140px;
	height: 100%;
	clear : left;
}
.sitemap-cnt {
	float: left;
	width: 500px;
	clear: none;
	text-align: left;
	border-left: 1px solid silver;
	border-bottom: 1px solid silver;		
}
#bodyContainer {
	height: auto;
	overflow: auto;
	padding-bottom: 50px;
}
#header-caption {
	float: none;
}
</style>
<script type="text/javascript">
var target="<? echo (isset($_GET['arter'])) ? $_GET['arter'] : 'da';?>";
$(document).ready(function() {
if (target=='da') {
	$.getJSON("json/dkname.json", function(json) {
		$("#search").typeahead({
			source : json.lookup,
			items : 12
		});
	});
} else {
	$.getJSON("json/taxon.json", function(json) {
		$("#search").typeahead({
			source : json.lookup,
			items : 12
		});
	});
}
$("#search-btn").click(function(){
	//var url='http://localhost/svampeatlas/search/indholdsfortegnelse.php';
	var url='http://svampe.dk/soeg/indholdsfortegnelse.php';
	url+='?arter='+target;
	url+='&index='+$("#search").val();
	window.location=url;
});
});
</script>
<?
	}

}

if (isset($_GET['index'])) {
	$char=$_GET['index'];
} else {
	$char='a';
}
$index = new Index($char);

?>
