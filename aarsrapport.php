<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('atlaspage.php');
include('logged_search.php');

class Report extends AtlasPage {
	private $year;
	private $mode;

	public function __construct() {
		parent::__construct();
		$this->year=(isset($_GET['aar'])) ? $_GET['aar'] : 2009;
		//prik, utm, densitet
		$this->mode=(isset($_GET['vis'])) ? $_GET['vis'] : 'utm';

		if ($this->mode=='2009-2010-prik') $this->year='2009 og 2010, prikkort';
		if ($this->mode=='2009-2010-2011-prik') $this->year='2009, 2010 og 2011, prikkort';
		if ($this->mode=='2009-2010-2011-2012-prik') $this->year='2009, 2010, 2011 og 2012, prikkort';

		if ($this->mode=='2009-2010-densitet') $this->year='2009 og 2010, densitet';
		if ($this->mode=='2009-2010-2011-densitet') $this->year='2009, 2010 og 2011, densitet';
		if ($this->mode=='2009-2010-2011-2012-densitet') $this->year='2009, 2010, 2011 og 2012, densitet';

		$this->title='Atlassæson '.$this->year;
		$this->draw();
	}

	public function draw() {
		parent::draw();
		echo '<div id="map"></div>';
		//echo '<div style="clear:both;float;left;height: 25px;"></div>';
?>
<div class="links">
<a href="aarsrapport.php?aar=2009&vis=prik">2009, prikkort</a><br>
<a href="aarsrapport.php?aar=2009&vis=utm">2009, UTM</a><br>
<a href="aarsrapport.php?aar=2009&vis=densitet">2009, densitet</a><br>
</div>
<div class="links">
<a href="aarsrapport.php?aar=2010&vis=prik">2010, prikkort</a><br>
<a href="aarsrapport.php?aar=2010&vis=utm">2010, UTM</a><br>
<a href="aarsrapport.php?aar=2010&vis=densitet">2010, densitet</a><br>
</div>
<div class="links">
<a href="aarsrapport.php?aar=2011&vis=prik">2011, prikkort</a><br>
<a href="aarsrapport.php?aar=2011&vis=utm">2011, UTM</a><br>
<a href="aarsrapport.php?aar=2011&vis=densitet">2011, densitet</a><br>
</div>
<div class="links">
<a href="aarsrapport.php?aar=2012&vis=prik">2012, prikkort</a><br>
<a href="aarsrapport.php?aar=2012&vis=utm">2012, UTM</a><br>
<a href="aarsrapport.php?aar=2012&vis=densitet">2012, densitet</a><br>
</div>
<div class="links">
<a href="aarsrapport.php?vis=2009-2010-prik">2009-2010 prikkort</a><br>
<a href="aarsrapport.php?vis=2009-2010-2011-prik">2009-2011 prikkort</a><br>
<a href="aarsrapport.php?vis=2009-2010-2011-2012-prik">2009-2012 prikkort</a><br>
</div>
<div class="links">
<a href="aarsrapport.php?vis=2009-densitet">2009 densitet</a><br>
<a href="aarsrapport.php?vis=2009-2010-densitet">2009-2010 densitet</a><br>
<a href="aarsrapport.php?vis=2009-2010-2011-densitet">2009-2011 densitet</a><br>
<a href="aarsrapport.php?vis=2009-2010-2011-2012-densitet">2009-2012 densitet</a><br>
</div>
<div id="legend">
<img src="glyph/alt/bullet_yellow_alt_x.png">2009<br>
<img src="glyph/alt/bullet_red_alt_x.png">2010<br>
<img src="glyph/alt/bullet_green_alt_x.png">2011<br>
<img src="glyph/alt/bullet_orange_alt_x.png">2012<br>
</div>
<div id="kumuleret">
<div class="kumu-cnt">2009<br>
<img src="glyph/kumu/2009-cut.png">
</div>
<div class="kumu-cnt">2009-2010<br>
<img src="glyph/kumu/2009-2010-cut.png">
</div>
<div class="kumu-cnt">2009-2011<br>
<img src="glyph/kumu/2009-2010-2011-cut.png">
</div>
<div class="kumu-cnt">2009-2012<br>
<img src="glyph/kumu/2009-2010-2011-2012-cut.png">
</div>
</div>
<?
	}

	protected function extraHead() {
?>
<script type="text/javascript" src="js/utm.js"></script>
<style type="text/css">
#bodyContainer {
	height: 1520px;
	overflow: auto;
}
#map {
	top: 30px;
	left: 10px;
	width:945px;
	height:600px;
	clear: both;
}
.links {
	float: left;
	clear: none;
	width: 130px;
	margin-top: 38px;
	margin-left: 25px;
	font-size: 13px;
	text-align: left;
}
#legend {
	position: absolute;
	display: none;
	z-index: 1000;
	left: 700px;
	top: 100px;
	height: 100px;
	width: 100px;
	border: 1px solid #000;
	background-color: white;
	padding: 10px;
	text-align: left;
	font-size: 12px;
}
.kumu-cnt {
	float: left;
	font-size: 16px;
	font-weight: bold;
	text-align: left;
	clear: none;
	margin-left: 15px;
	margin-bottom: 15px;
}
.kumu-cnt img {
	width: 450px;
	border:1px solid #000;
}
</style>
<script type="text/javascript">
var mode = "<? echo $this->mode;?>";
var year = "<? echo $this->year;?>";
var map;
var opacity = (mode=='utm') ? 0.45 : 0.07;
var cache = [];
var color = 'blue';
//var icon = 'glyph/alt/bullet_green_alt_x.png';
function initMap() {
	map = new google.maps.Map(document.getElementById("map"), {
		center: new google.maps.LatLng(56.05, 11.4),
		zoom: 7,
		mapTypeId: google.maps.MapTypeId.TERRAIN,
		zoomControl: true,
		streetViewControl: false,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		}
	});
	map.enableKeyDragZoom({
		visualEnabled: true,
		visualPosition: google.maps.ControlPosition.LEFT,
		visualPositionMargin: new google.maps.Size(35, 0),
		visualImages: {
		},
		visualTips: {
			off: "Zoom til",
			on: "Zoom fra"
		}
	});
}
function showPrik(lat, long, icon) {
	var Marker = new google.maps.Marker({
		optimized: true,
		icon: icon,
		position: new google.maps.LatLng(lat, long),
		map: map
	});
}
function showUTM(utm) {
	if (utm=='') return;
	if (utm=='Fund') return;
	if (utm=='VA89') return;
	if (utm=='V68') return;
	if (utm=='WB30') return;
	if (utm=='N36') return;
	var utmcoords = eval(UTM_LatLng[utm.toUpperCase()]);
	var poly = new google.maps.Polygon({
   		paths: utmcoords,
		strokeColor: color,
		strokeOpacity: opacity,
		strokeWeight: 0,
		fillColor: color,
		fillOpacity: opacity
	});
	poly.setMap(map);
}
function showUTMex(utm, color, opacity) {
	if (utm=='') return;
	if (utm=='Fund') return;
	if (utm=='VA89') return;
	if (utm=='V68') return;
	if (utm=='WB30') return;
	if (utm=='N36') return;
	var utmcoords = eval(UTM_LatLng[utm.toUpperCase()]);
	var poly = new google.maps.Polygon({
   		paths: utmcoords,
		strokeColor: color,
		strokeOpacity: opacity,
		strokeWeight: 0,
		fillColor: color,
		fillOpacity: opacity
	});
	poly.setMap(map);
}
function loadData() {
	opacity = (mode=='utm') ? 0.45 : 0.07;
	color = 'blue';
	var icon = 'glyph/bullet-blue2.png';
	$('html, body').css("cursor", "wait");
	$.getJSON('json/'+year+'.json', function(data) {
 		$.each(data, function(index, fund) {
			switch (mode) {
				case 'prik' : showPrik(fund.lat, fund.long, icon); break;
				case 'densitet' : showUTM(fund.utm); break;
				case 'utm' : if ($.inArray(fund.utm, cache)==-1) {
								showUTM(fund.utm);
								cache.push(fund.utm);
							}; break;
				default : break;
			}
		});
		$('html, body').css("cursor", "auto");
	});
}
function loadYearIcon(year, icon) {
	$('html, body').css("cursor", "wait");
	$.getJSON('json/'+year+'.json', function(data) {
		$.each(data, function(index, fund) {
			showPrik(fund.lat, fund.long, icon);
		});
		$('html, body').css("cursor", "auto");
	});
}
function loadYearDensity(year) {
	$('html, body').css("cursor", "wait");
	opacity = '0.01';
	color = 'red';
	$.getJSON('json/'+year+'.json', function(data) {
		$.each(data, function(index, fund) {
			showUTM(fund.utm);
		});
		$('html, body').css("cursor", "auto");
	});
}
function loadYearDensityCache(year) {
	var local=[];
	$('html, body').css("cursor", "wait");
	$.getJSON('json/'+year+'.json', function(data) {
		$.each(data, function(index, fund) {
			if ($.inArray(fund.utm, local)==-1) {
				showUTMex(fund.utm, 'red', 0.25);
				local.push(fund.utm);
			}
		});
		$('html, body').css("cursor", "auto");
	});
}	

$(document).ready(function() {
	initMap();

	switch (mode) {
		case '2009-2010-prik' : 
			loadYearIcon(2009, 'glyph/alt/bullet_yellow_alt_x.png');
			loadYearIcon(2010, 'glyph/alt/bullet_red_alt_x.png');
			$("#legend").show();
			break;
		case '2009-2010-2011-prik' : 
			loadYearIcon(2009, 'glyph/alt/bullet_yellow_alt_x.png');
			loadYearIcon(2010, 'glyph/alt/bullet_red_alt_x.png');
			loadYearIcon(2011, 'glyph/alt/bullet_green_alt_x.png');
			$("#legend").show();
			break;
		case '2009-2010-2011-2012-prik' : 
			loadYearIcon(2009, 'glyph/alt/bullet_yellow_alt_x.png');
			loadYearIcon(2010, 'glyph/alt/bullet_red_alt_x.png');
			loadYearIcon(2011, 'glyph/alt/bullet_green_alt_x.png');
			loadYearIcon(2011, 'glyph/alt/bullet_orange_alt_x.png');
			$("#legend").show();
			break;
		case '2009-densitet' : 
			loadYearDensityCache(2009);
			break;
		case '2009-2010-densitet' : 
			loadYearDensityCache(2009);
			loadYearDensityCache(2010);
			break;
		case '2009-2010-2011-densitet' : 
			loadYearDensityCache(2009);
			loadYearDensityCache(2010);
			loadYearDensityCache(2011);
			break;
		case '2009-2010-2011-2012-densitet' : 
			loadYearDensityCache(2009);
			loadYearDensityCache(2010);
			loadYearDensityCache(2011);
			loadYearDensityCache(2012);
			break;
		default : 
			loadData();
			break;
	}
});
</script>	
<?
	}

}

$report = new Report();

?>

