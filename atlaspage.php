<?

if (!class_exists('Db')) {
	include('common/Db.php');
}
include('common/log.php');

class AtlasPage extends Db {
	public $title = 'SvampeAtlas';
	public $meta = 'Danmarks Svampatlas';

	public function __construct() {
		parent::__construct();
		Log::accessLog();
	}

	//overwrite to include
	protected function extraHead() {
	}

	//overwrite to include
	public function draw() {
		$this->drawHeader();
		$this->drawbody();		
	}

	public function stdFooter() {
?>
<div id="frontpage-footer" class="mainTextDarkGrey" style="font-size:10px;padding-top:2px;">
   <a href="http://www.svampe.dk/" title="Svampe.dk forside">Svampe.dk forside</a>&nbsp;&nbsp;&#9679;&nbsp;&nbsp;
   <a href="http://www.svampeatlas.dk/" title="Svampeatlas">Svampeatlas</a>&nbsp;&nbsp;&#9679;&nbsp;&nbsp;
   <a href="http://www.svampe.dk/atlas/index.php" title="Log på svampeatlas">Login</a>
</div>
<?
	}

	public function drawHeader() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title><? echo $this->title;?></title> 
<meta name="description" content="<? echo $this->meta;?>"/>
<meta name="google-site-verification" content="6Oci4d1G8lUXGG2eJNWLIQSjjlc2td5CEQvIPTkNKNg" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.6/jquery-ui.min.js"></script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAOj0_u0DRE2dK8X9YptdCXtxt89UCqfoo&amp;sensor=true"></script>
<script type="text/javascript" src="js/gv3.js?id=123"></script>
<script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/keydragzoom/src/keydragzoom.js?"></script>
<script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/infobox/src/infobox.js"></script>
<link href="http://www.svampe.dk/atlas/styles/AtlasStyles.css" rel="stylesheet" type="text/css" /> 
<link href="http://www.svampe.dk/atlas/styles/AtlasDivStyles.css" rel="stylesheet" type="text/css" /> 
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">google.load('visualization', '1.0', {'packages':['corechart']});</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46015452-1', 'svampe.dk');
  ga('send', 'pageview');

</script>
<?
		$this->extraHead();
?>
</head> 
<?
	}

	public function drawBody() {
?>
<body>

<!-- start wholePageContainer -->
<div id="wholePageContainer">

<!-- start topLinkBar -->
<div id="topLinkBar">
<span class="smallTextGrey">|&nbsp;&nbsp;<a href="http://www.svampeatlas.dk/" class="grey">hjem</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.danske-svampe.dk/DKText/MycoKeyGroups.html" class="grey">start svampebestemmelse</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.mycokey.org/MycoKeySearchDK.shtml" target="_blank"  class="grey">find arter og nøgler</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.svampe.dk/svampeforum/viewforum.php?f=4" target="_blank" class="grey">til forum</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.svampe.dk/atlas/indexvalider.php" class="grey"> indlæg og rediger fund</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="soeg.php" class="grey">søg i atlas</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.svampeatlas.dk/links.html" class="grey">links</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="mailto:atlas@svampe.dk"  class="grey">kontakt os</a>&nbsp;&nbsp;|
</span>
</div>
<!-- end topLinkBar -->

<!-- start headerContainer -->
<div id="headerContainer">
  <div id="overskrift"><span class="Header1">Danmarks svampeatlas</span></div>
  <div id="overskriftLogo"><img src="http://www.svampe.dk/atlas/GeneralGraphics/LogoSmallest.png" width="57" height="57" alt="svampeatlas-logo" /></div>
</div>
<!-- end headerContainer -->

<!-- start bodyContainer -->
<div id="bodyContainer" style="background-color:#EBE8DF;">
  <div class="HeaderGenus" id="bodyContainerHeader">
    <div align="left" id="header-caption" style="padding-left:7px;padding-top:1px;"><? echo $this->title;?>
      <span style="float:right;clear:none;height:20px;margin-top:2px;">
        <a href="indholdsfortegnelse.php" title="Samlet oversigt over Svampeatlassets arter" class="smallTextWhite" style="font-size:12px;padding-right:5px;">Svampeatlas A - Å</a>
      </span>
    </div>
  </div>
<?
	}

}

?>
