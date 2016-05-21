<? 
@session_start();
$perma=(isset($_GET['search_id'])) ? $_GET['search_id'] : 'false';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title>Danmarks svampeatlas - søgning i godkendte fund</title> 
<meta http-equiv="x-ua-compatible" content="IE=9"/>
<meta name="google-site-verification" content="6Oci4d1G8lUXGG2eJNWLIQSjjlc2td5CEQvIPTkNKNg" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/jquery-ui.min.js"></script>
<script type="text/javascript" language="javascript" src="DataTables-1.9.3/media/js/jquery.dataTables.js"></script> 
<link rel="stylesheet" href="DataTables-1.9.3/media/css/jquery.dataTables.css" type="text/css" media="screen" />
<link rel="stylesheet" href="DataTables-1.9.3/media/css/jquery.dataTables_themeroller.css" type="text/css" media="screen" />
<script type="text/javascript" src="DataTables-1.9.3/extras/ColReorder/media/js/ColReorder.js"></script>
<script type="text/javascript" src="DataTables-1.9.3/extras/ColVis/media/js/ColVis.js"></script>
<script type="text/javascript" src="DataTables-1.9.3/extras/Scroller/media/js/dataTables.scroller.js"></script>
<link rel="stylesheet" href="DataTables-1.9.3/extras/ColReorder/media/css/ColReorder.css" type="text/css" media="screen" />
<link rel="stylesheet" href="DataTables-1.9.3/extras/ColVis/media/css/ColVis.css" type="text/css" media="screen" />
<link rel="stylesheet" href="DataTables-1.9.3/extras/ColVis/media/css/ColVisAlt.css" type="text/css" media="screen" />
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAOj0_u0DRE2dK8X9YptdCXtxt89UCqfoo&amp;libraries=geometry,visualization&amp;sensor=true"></script>
<script type="text/javascript" src="js/search.js?ver=330003200"></script>
<script type="text/javascript" charset="UTF-8" src="js/gv3.js?ver=9234234234223"></script>
<script type="text/javascript" src="js/utm.js"></script>
<script type="text/javascript" src="js/jquery.qtip.js"></script>
<link rel="stylesheet" href="js/jquery.qtip.css" type="text/css" media="screen" />
<script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/keydragzoom/src/keydragzoom.js?"></script>
<script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/infobox/src/infobox.js"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">google.load('visualization', '1.0', {'packages':['corechart']});</script>
<link href="http://www.svampe.dk/atlas/styles/AtlasStyles.css" rel="stylesheet" type="text/css" /> 
<link href="http://www.svampe.dk/atlas/styles/AtlasDivStyles.css" rel="stylesheet" type="text/css" /> 
<link href="css/style.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="js/bootstrap.js"></script>
<script type="text/javascript">var perma="<? echo $perma;?>";</script>
<script type="text/javascript" src="js/form.js"></script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46015452-1', 'svampe.dk');
  ga('send', 'pageview');
</script>
</head> 
<body>
<input type="hidden" id="__SEARCH_ID" name="__SEARCH_ID"/>

<!-- start wholePageContainer -->
<div id="wholePageContainer">

<!-- start topLinkBar -->
<div id="topLinkBar"><span class="smallTextGrey">|&nbsp;&nbsp;<a href="http://www.svampeatlas.dk/" class="grey">hjem</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.danske-svampe.dk/DKText/MycoKeyGroups.html" class="grey">start svampebestemmelse</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.mycokey.org/MycoKeySearchDK.shtml" target="_blank"  class="grey">find arter og nøgler</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.svampe.dk/svampeforum/viewforum.php?f=4" target="_blank" class="grey">til forum</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.svampe.dk/atlas/indexvalider.php" class="grey"> indlæg og rediger fund</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="soeg.php" class="grey">søg i atlas</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.svampeatlas.dk/links.html" class="grey">links</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="mailto:atlas@svampe.dk"  class="grey">kontakt os</a>&nbsp;&nbsp;|</span></div>
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
    <div id="header-caption">Søg efter fund i databasen </div>
      <span id="perma-link"></span>
	  <span id="result-count"></span>
  </div>
<? 
if ((count($_GET)>0) && (!isset($_GET['search_id']))) {
	$_SESSION['details']='details';
	echo '<div id="detailCnt" style="text-align:left;">';
	include('ajax_detail.php');
	echo '</div>';
} else {
	unset($_SESSION['details']);
	include('search_form.php');
	echo '<div id="detailCnt" style="text-align:left;display:none;"></div>';
	echo '<div id="reportCnt" style="text-align:left;display:none;"></div>';
}

?>

<div id="resultCnt" style="text-align:left;"></div>

<div id="chronoCnt" style="text-align:left;display:none;">
  <div id="chronoGraph" style="height:605px;"></div>
  <div class="result-footer" style="margin-top:10px;">
     <input type="button" value="&#9668; Tilbage til s&oslash;geresultater" onclick="window.atlasSearch.resultBack('#chronoCnt');" title="Tilbage til s&oslash;geresultater"/>
  </div>
</div>

<div id="allFindingsMapCnt" style="text-align:left;display:none;">
  <div id="all-findings-map" style="float:left;clear:left;width:700px;height:580px;margin-left:10px;margin-top:24px;font-family:arial, helvetica;font-size:11px;"></div>
  <div id="map-legend" style="float:left;width:240px;height:580px;margin-left:10px;margin-top:24px;">
     <fieldset style="margin:6px;font-size:14px;">
     <legend>Farvekoder</legend>
        <table class="farvekoder">
	   <tr><td><img src="glyph/Circle_Blue.png" alt=""/></td><td>Fund : Atlasperioden (efter 2008)</td></tr>
	   <tr><td><img src="glyph/Circle_Orange.png" alt=""/></td><td>Fund : Periode 2000-2008</td></tr>
	   <tr><td><img src="glyph/Circle_Yellow.png" alt=""/></td><td>Fund : Periode 1975-1999</td></tr>
	   <tr><td><img src="glyph/Circle_Grey.png" alt=""/></td><td>Fund : Periode 1950-1974</td></tr>
	   <tr><td><img src="glyph/Circle_White.png" alt=""/></td><td>Fund : Periode 1925-1949</td></tr>
	   <tr><td><img src="glyph/Circle_Opaque.png" alt=""/></td><td>Fund : Periode 1900-1924</td></tr>
 	   <tr><td><img src="glyph/Circle_Red.png" alt=""/></td><td>Fund fra før år 1900</td></tr>
	</table>
     </fieldset>
     <fieldset style="margin:6px;font-size:14px;">
     <legend id="fund-caption">Fund</legend>
     <div id="all-findings-spot" style="width:204px;max-width:210px;font-size:11px;height:350px;max-height:350px;vertical-align:top;overflow-x:hidden;overflow-y:scroll;"></div>
     </fieldset>
  </div>
  <div class="result-footer" style="margin-top:10px;"> <!-- 26px; -->
     <input type="button" value="&#9668; Tilbage til s&oslash;geresultater" onclick="window.atlasSearch.resultBack('#allFindingsMapCnt');" title="Tilbage til s&oslash;geresultater"/>
  </div>
</div>

<div id="utmFindingsMapCnt" style="text-align:left;display:none;">
  <div id="utm-findings-map" style="float:left;clear:left;width:700px;height:580px;margin-left:10px;margin-top:24px;"></div>
  <div id="map-choice" style="float:left;width:240px;height:580px;margin-left:10px;margin-top:24px;">
     <fieldset style="margin:6px;font-size:14px;"><legend>Visning</legend>
        <input type="radio" id="utm-findings" name="utm-choice" checked="checked" onclick="initUTMMap_load();"/><label for="utm-findings">Udbredelse</label><br/><br/>
        <input type="radio" id="utm-density" name="utm-choice" onclick="initUTMDensityMap_load();"/><label for="utm-density">Densitet</label>
     </fieldset>
     <fieldset style="margin:6px;font-size:14px;">
     <legend>Fund</legend>
     <div id="utm-findings-spot" style="width:204px;max-width:210px;font-size:11px;height:445px;max-height:445px;vertical-align:top;overflow-x:hidden;overflow-y:scroll;"></div>
     </fieldset>
  </div>
  <div class="result-footer" style="margin-top:10px;">
     <input type="button" value="&#9668; Tilbage til s&oslash;geresultater" onclick="window.atlasSearch.resultBack('#utmFindingsMapCnt');" title="Tilbage til s&oslash;geresultater"/>
  </div>
</div>

<div id="heatmapCnt" style="text-align:left;display:none;">
  <div id="heatmapMap" style="float:left;clear:left;width:700px;height:580px;margin-left:10px;margin-top:24px;"></div>
  <div id="heatmapLegend" style="float:left;width:240px;height:580px;margin-left:10px;margin-top:24px;">
     <fieldset style="margin:6px;font-size:14px;"><legend>Visning af enkelte fund</legend>
		<span style="font-size:80%">
Ved klik på kortet tegnes en cirkel med radius på 2000 meter i forhold til centrum. 
Alle fund indenfor denne cirkel vises forneden i boksen "Fund". 
Klik på det enkelte fund for at se fundets detaljer.
		</span>
     </fieldset>
     <fieldset style="margin:6px;font-size:14px;">
     <legend>Fund</legend>
     <div id="heatmapDetails" style="width:204px;max-width:210px;font-size:11px;height:435px;max-height:445px;vertical-align:top;overflow-x:hidden;overflow-y:scroll;"></div>
     </fieldset>
  </div>
  <div class="result-footer" style="margin-top:10px;">
     <input type="button" value="&#9668; Tilbage til s&oslash;geresultater" onclick="window.atlasSearch.resultBack('#heatmapCnt');" title="Tilbage til s&oslash;geresultater"/>
  </div>
</div>

<div id="diversityCnt" style="text-align:left;display:none;">
  <div id="diversityMap" style="float:left;clear:left;width:700px;height:580px;margin-left:10px;margin-top:24px;"></div>
  <div id="diversityLegend" style="float:left;width:240px;height:580px;margin-left:10px;margin-top:24px;">
     <fieldset style="margin:6px;font-size:14px;"><legend>Artsrigdom</legend>
		<span style="font-size:80%">
Kortet udtrykker artsrigdom per lokalitet. Desto stærkere farve, fra lysegrøn til rød, desto flere forskellige arter
er der fundet i den omgivende lokalitet. Svampeatlas har pt. indkodet 28.820 forskellige lokaliteter for Danmark. 
		</span>
     </fieldset>
     <fieldset style="margin:6px;font-size:14px;"><legend>Visning af enkelte fund</legend>
		<span style="font-size:80%">
Ved klik på kortet tegnes en cirkel med radius på 2000 meter i forhold til centrum. 
Alle fund indenfor denne cirkel vises forneden i boksen "Fund". 
Klik på det enkelte fund for at se fundets detaljer.
		</span>
     </fieldset>
     <fieldset style="margin:6px;font-size:14px;">
     <legend>Fund</legend>
     <div id="diversityDetails" style="width:204px;max-width:210px;font-size:11px;height:305px;max-height:305px;vertical-align:top;overflow-x:hidden;overflow-y:scroll;"></div>
     </fieldset>
  </div>
  <div class="result-footer" style="margin-top:10px;">
     <input type="button" value="&#9668; Tilbage til s&oslash;geresultater" onclick="window.atlasSearch.resultBack('#diversityCnt');" title="Tilbage til s&oslash;geresultater"/>
  </div>
</div>

</div> <!-- bodyContainer -->
</div> <!-- wholePageContainer -->

<div id="download-csv-dlg-cnt" style="">
	<form action="" id="download-csv-form" style="display: none; padding:10px;font-family:verdana, helvetica;font-size:13px;">
		<label for="separator">Separator</label>
		<select id="separator" name="separator">
		<option value=";" selected="selected">; (semikolon)</option>
		<option value=",">, (komma)</option>
		</select>
		<br/><br/>
		<input type="radio" name="scope" id="scope-synlige" checked="checked"><label for="scope-synlige">Synlige kolonner</label>
		<input type="radio" name="scope" id="scope-all"><label for="scope-all">Samtlige kolonner</label>
		<br/><br/>
		<label for="column">Sorter efter</label>
		<select id="column" name="column">
			<option value="GenSpec" selected="selected">Latinsk navn</option>
			<option value="DKName">Dansk navn</option>
			<!--<option value="Dato">Dato</option>-->
			<option value="_UTM10">UTM</option>
			<option value="Status">Status</option>
		</select><br/><br/>
 		<label for="filename">Filnavn</label>
		<input id="filename" name="filename" value="svampeatlas.csv" type="text" style="font-size:14px;" /><br/><br/>
 		<span style="float:right;clear:both;border-top:1px solid #ebebeb;width:100%;margin-top:10px;margin-bottom:10px;text-align:right;padding-top:6px;">
		<input type="button" value="Download" id="begin-download"/>
		<input type="button" value="Fortryd" id="cancel-download"/>
		</span>
	</form>
</div>

</body> 
</html> 
