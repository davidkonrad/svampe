<? 
//error_reporting(E_ALL);
error_reporting(E_ALL ^ E_DEPRECATED);
//ini_set('display_errors', '1');

//log
include('common/log.php');
Log::accessLog();
//
include_once('FX/FX.php'); 
include_once('FX/server_data.php');

class DetailsBase {
	public $enkeltsoeg;
	public $kommentarsoeg;
	public $billedsoeg;
	public $enkeltrecord;
	public $AtlasRecID;

	public function loadData($AtlasRecID) {
		global $serverIP, $webCompanionPort;

		$this->AtlasRecID = $AtlasRecID;

		$soeg=new FX($serverIP,$webCompanionPort);  
		$soeg->SetDBData('Svampefund.fmp12','wwwFungi', '1');  //fp7
		$soeg->SetDBPassword('logpass','loguser');
		$soeg->AddDBParam('AtlasIDnummer', $AtlasRecID);
		$this->enkeltsoeg = $soeg->FMFind();

		foreach ($this->enkeltsoeg['data'] as $key=>$enkeltrecord) {
			$recordPointers =explode('.',$key);
			$RecID=$recordPointers[0];
			//
			$this->enkeltrecord = $enkeltrecord;
		}

		$soegkomm=new FX($serverIP,$webCompanionPort);  
		$soegkomm->SetDBData('Svampefund.fmp12','Mikroforum', 'all'); 
		$soegkomm->SetDBPassword('logpass','loguser');
		$soegkomm->AddDBParam('IDFelt', $enkeltrecord['AtlasIDnummer'][0], 'eq');
		$this->kommentarsoeg = $soegkomm->FMFind();

		$soegpic=new FX($serverIP,$webCompanionPort);  
		$soegpic->SetDBData('Svampefund.fmp12','wwwPics', 'all'); 
		$soegpic->SetDBPassword('logpass','loguser');
		//$soegpic->AddDBParam('AtlasRecID', $enkeltrecord['AtlasIDnummer'][0], 'eq');
		$soegpic->AddDBParam('AtlasRecID', $AtlasRecID, 'eq');
		$this->billedsoeg = $soegpic->FMFind();
	}

	public function createDate($enkeltrecord) {
		$day = ($enkeltrecord['date_day'][0]!='') ? $enkeltrecord['date_day'][0] : '??';
		$month = ($enkeltrecord['date_month'][0]!='') ? $enkeltrecord['date_month'][0] : '??';
		$year = ($enkeltrecord['date_year'][0]!='') ? $enkeltrecord['date_year'][0] : '????';
		return $day.'/'.$month.'/'.$year;
	}

}

class fundPage extends DetailsBase {

	public function __construct($AtlasRecID) {
		$this->loadData($AtlasRecID);
		$this->drawCSS();
		$this->draw();
		$this->drawScript();
	}

	private function drawCSS() {
?>
<style type="text/css">
#fund {
	width:505px;
	background-color:white;
	font-size:13px;
	font-family: arial, helvetica;
	overflow:auto;
	padding-left: 3px;
	padding-right:5px;
	padding-top: 5px;
	margin-top: 24px;
	float: left;
	border-right:1px solid black;
	border-left:1px solid black;
}
#kommentarer {
	width:505px;
	height:166px;
	border: 1px solid #666666;
	overflow: auto;
	padding-right:5px;
	padding-left: 3px;
	background-color: #ebebeb;
}
#mapdiv {
/*
	width:425px;
	margin-top: 24px;
	height:180px;
	border: 1px solid #000;
*/
}
#left {
	float: left;
	clear: left;
	width: 525px;
	margin-left: 8px;
	height:580px;
}
#right {
	width: 430px;
	float: left;
	clear: right;
	height:580px;
}
#picdiv {
	margin-top: 10px;
	width:425px;
	overflow: auto;
}
.img-cnt {
	clear: none;
	float: left;
	margin-right: 20px;
	margin-bottom: 10px;
}
img.img-thumb {
	height: 110px;
	border:1px solid gray;
}
.img-cnt h4 {
	color: gray;
	font-size: 12px;
	font-family: arial, helvetica;
	margin: 0px;
	padding: 0px;
}
</style>
<?php
	}

	function drawRow($caption, $data) {
		if (is_array($data)) $data=$data[0]; //latlong can be array (??)
		echo '<div style="float:left;clear:left;margin-left:6px;">'.$caption;
		echo '&nbsp;:</div>';
		echo '<div style="float:left;font-weight:bold;margin-left:4px;">'.$data.'</div>';
		echo '<br/>';
	}

	function caption($caption, $data, $direction) {
		echo '<div style="float:'.$direction.';clear:'.$direction.';">'.$caption;
		if ($caption!='') echo ':';
		echo '<span style="font-weight:bold;">'.$data.'</span></div>';
	}

	function hr() {
		echo '<div style="float:left;width:100%;"><hr/></div>';
	}

	function drawFooter() {
		echo '<div class="result-footer" style="margin-top:34px;">'; //other pages have margin-top 24px, footer-margin+margin-top
		echo '<input id="detail-footer-back-btn" type="button" value="&#9668; Tilbage" onclick="window.atlasSearch.detailBack();" title="Tilbage til oversigt"/>';
		echo '</div>';
	}

	function drawFund() {
		//$base = 'http://localhost/svampeatlas/search/index.php?';
		$base = 'http://svampe.dk/soeg/?';

		echo '<div id="fund">';
		echo '<center>';
		$this->caption('','<i>'.$this->enkeltrecord['MycoTaxon current use link::FullName'][0].'</i>','');
		echo '';
		$this->caption('',$this->enkeltrecord['MycoTaxon current use link::DKName'][0],'');
		echo '</center>';
		echo '<br/>';
		$this->caption('ID nummer',' <a href="'.$base.'AtlasRecID='.$this->enkeltrecord['AtlasIDnummer'][0].'" title="Statisk link til denne detalje-side">'.$this->enkeltrecord['AtlasIDnummer'][0].'</a>', 'right');
		$this->hr();
		$lok = ($this->enkeltrecord['LocalityFriText'][0]!='') ? $this->enkeltrecord['localityBasicString'][0].', '.$this->enkeltrecord['LocalityFriText'][0] : $this->enkeltrecord['localityBasicString'][0]; 
		$this->drawRow('Findested', $lok);
		$this->drawRow('Breddegrad',$this->enkeltrecord['AtlasUserLatiNum'][0], true);
		$this->drawRow('Længdegrad',$this->enkeltrecord['AtlasUserLongNum'], true);
		$this->drawRow('Præcision (m)',$this->enkeltrecord['AtlasUserPrec'][0]);
		$this->hr();
		$this->drawRow('Vegetationstype',$this->enkeltrecord['VegType'][0], true);
		$this->drawRow('vært/mykorrhizapartner',$this->enkeltrecord['associatedOrganism'][0]);
		$this->drawRow('Substrat',$this->enkeltrecord['Substrate'][0], true);
		$this->drawRow('Jordbund',$this->enkeltrecord['Jordbund'][0]);
		$this->drawRow('Klima',$this->enkeltrecord['Klima'][0]);
		$this->drawRow('Økologi-kommentarer',$this->enkeltrecord['AtlasEcoNote'][0]);
		$this->hr();
		//drawRow('Dato',$enkeltrecord['date_day'][0].'/'.$enkeltrecord['date_month'][0].'/'.$enkeltrecord['date_year'][0]);
		$this->drawRow('Dato', $this->createDate($this->enkeltrecord));
		$this->hr();
		$this->drawRow('Finder',$this->enkeltrecord['Leg'][0]);
		$this->drawRow('Bestemmer',$this->enkeltrecord['Det'][0]);
		$this->drawRow('Kollektionsnummer',$this->enkeltrecord['CollNr'][0]);
		$this->drawRow('Herbarium',$this->enkeltrecord['Herbarium'][0]);
		$this->drawRow('Bemærkninger',$this->enkeltrecord['Bemaerkn'][0]);
		echo '</div>';
	}

	function drawMap() {
		//echo '<div id="mapdiv"></div>';
		echo '<div id="mapdiv" style="width:425px;margin-top: 24px;height:180px;border: 1px solid #000;"></div>';
	}

	function drawKommentarer() {
		echo '<div id="kommentarer" class="mainTextDarkGrey10" align="left">';
		echo '<span class="smallTextBlack">Kommentarer:</span><br/>';
		foreach ($this->kommentarsoeg['data'] as $key=>$mikroforum) {
			echo $mikroforum['Textfelt'][0];
			echo '<br/>';
			echo '<div style="font-size:100%" align="center"><font color="#666699">';
			echo '<i>.................... Skrevet den ';
			echo $mikroforum['Dato'][0];
			echo ' af ';
			echo $mikroforum['Bruger'][0];
			echo '</i>    ....................</font></div>';
		}
		echo '</div>';
	}

	function showPic($src, $num, $dato) {
		echo '<div class="img-cnt">';
		echo '<h4>Billede nr. '.$num.'</h4>';
		echo '<a href="'.$src.'" title="Klik for at se stor version" target=_blank>';
		echo '<img class="img-thumb" src="'.$src.'">';
		echo '</a>';
		echo '</div>';
	}

	function drawPic() {
		//global $billedsoeg;
	
		if (count($this->billedsoeg['data'])>0) {
			echo '<div id="picdiv">';
			$nummer = 0;
			foreach ($this->billedsoeg['data'] as $key=>$billedliste){
				$nummer ++;
				if($billedliste['Y'][0]<2012) {	//her skal manuelt stilles hvor billedet skal kaldes fra, lager eller uploadfolder
					//$href='http://130.225.211.119/svampeatlas/uploads/'.$billedliste['PicID'][0];
					//$src='http://130.225.211.119/svampeatlas/uploads/'.$billedliste['PicID'][0];
					$href='http://192.38.112.99/svampeatlas/uploads/'.$billedliste['PicID'][0];
					$src='http://192.38.112.99/svampeatlas/uploads/'.$billedliste['PicID'][0];
				} else {
					$href='http://svampe.dk/atlas/uploads/'.$billedliste['PicID'][0].'.JPG';
					$src='http://svampe.dk/atlas/uploads/'.$billedliste['PicID'][0].'.JPG';
				}
				$dato=$billedliste['D'][0].'/'.$billedliste['M'][0].'/'.$billedliste['Y'][0];
				$this->showPic($src, $nummer, $dato);
			}
		}
		echo '</div>';
	}

	function draw() {
		echo '<div id="left">';
		$this->drawFund();
		$this->drawKommentarer();
		echo '</div><div id="right">';
		$this->drawMap();
		$this->drawPic();
		echo '</div>';
		$this->drawFooter();
	}

	public function drawScript() {
?>
<script type="text/javascript">
$(window).ready(function() {
	var offset=$("#kommentarer").offset();
	var top = offset.top;
	var diff=Math.abs(parseInt(463)-parseInt(top));
	var ch=$("#kommentarer").height();
	var h=parseInt(ch)-parseInt(diff);
	$("#kommentarer").height(h+'px');	
});
var map = null;
function load() {
	map = new google.maps.Map(document.getElementById("mapdiv"), {
		center: new google.maps.LatLng(<?php echo $this->enkeltrecord['AtlasUserLati'][0]; ?>, <?php echo $this->enkeltrecord['AtlasUserLong'][0]; ?>),
		zoom: 14,
		streetViewControl: false,
		mapTypeId: google.maps.MapTypeId.TERRAIN
	});
	var Marker = new google.maps.Marker({
		position: new google.maps.LatLng(<?php echo $this->enkeltrecord['AtlasUserLati'][0]; ?>, <?php echo $this->enkeltrecord['AtlasUserLong'][0]; ?>),
		map: map
	});
}
atlasSearch.bodyContainerUp();
</script>
	<? if (isset($_SESSION['details'])) {
	//this is a standalone detail page
	?>
<script type="text/javascript">
var atlasid="<? echo $this->enkeltrecord['AtlasIDnummer'][0];?>";
load();
$("#detail-footer-back-btn").attr('onclick','window.location.href="http://svampe.dk/soeg/"');
$("#detail-footer-back-btn").attr('value','◄ Gå til søgefunktionens forside');
$("#detail-footer-back-btn").attr('title','Gå til søgningens forside');
$("#header-caption").text('Svampefund '+atlasid);
</script>
<?
		}
	}
}

//classes also used by standalone xmldata.php
//echo basename($_SERVER['PHP_SELF']);

if (basename($_SERVER['PHP_SELF'])=='xmldata.php') return;

if (isset($_GET['AtlasRecID'])) {
	$ask=$_GET['AtlasRecID'];
	$_SESSION['AtlasRecID']=$_GET['AtlasRecID'];
	$fund = new fundPage($ask);
} else {
	$ask='';
}

?>
