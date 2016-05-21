
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="shortcut icon" href="http://www.svampe.dk/atlas/favicon.ico"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Danmarks svampeatlas - indlæg og rediger fund</title>
<link href="styles/AtlasStyles.css" rel="stylesheet" type="text/css" />
<link href="styles/AtlasDivStyles.css" rel="stylesheet" type="text/css" />
<!-- key for 192.168.1.7 !-->
	<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAmvU52RJtOyI0zz81Y8BBoxTKSL5fUtutPPWazSqe5GsLCeoYoBTi9Ghh98xADiRWIM1h2ZY1Koe6Cg"
type="text/javascript"></script>
<!--<script src="dragzoom.js" type="text/javascript"></script>-->
 <script type="text/javascript">

</script>

<style type="text/css">
<!--
.style2 {font-size: 11px}

.button { display: block;
	width: 150px;
	border: 1px Solid #565;
	background-color:#F5F5F5; 
	padding: 2px;
	text-decoration: none;
	text-align:center;
	font-size:smaller;
}
.button:hover {
	background-color: white;
}
.coords{
display:none;
}
-->
      </style>
<input type="hidden" id="latstart" />
	 <input type="hidden" id="latend" />
	 <input type="hidden" id="longstart" />
	 <input type="hidden" id="longend" />

</head>

<body onload="loadMap()" onunload="GUnload()">

<!-- start #wholePageContainer -->
<div id="wholePageContainer">

<!-- start #topLinkBar -->
<div id="topLinkBar"><span class="smallTextGrey">|&nbsp;&nbsp;<a href="http://www.svampeatlas.dk/" class="grey">hjem</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.danske-svampe.dk/DKText/MycoKeyGroups.html" class="grey">start svampebestemmelse</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.mycokey.org/MycoKeySearchDK.shtml" target="_blank"  class="grey">find arter og nøgler</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.svampe.dk/svampeforum/viewforum.php?f=4" target="_blank" class="grey">til forum</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.svampe.dk/atlas/indexvalider.php" class="grey"> indlæg og rediger fund</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="soeg.php" class="grey">søg i atlas</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.svampeatlas.dk/links.html" class="grey">links</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="mailto:atlas@svampe.dk"  class="grey">kontakt os</a>&nbsp;&nbsp;|</span></div>
<!-- end #topLinkBar -->

<!-- start #headerContainer -->
<div id="headerContainer">
  <div id="overskrift"><span class="Header1">Danmarks svampeatlas</span></div>
  <div id="overskriftLogo"><img src="GeneralGraphics/LogoSmallest.png" width="57" height="57" alt="svampeatlas-logo" /></div>
</div>
<!-- end #headerContainer -->

<!-- start #bodyContainer -->
<div id="bodyContainer">
  <div class="HeaderGenus" id="bodyContainerHeader">
    <div class="smallTextWhite" id="bodyContainerBrugerinfo">
      <div align="right"><?php if(isset($_SESSION['Bruger'])){echo 'bruger: '.$_SESSION['Bruger'];}?>
	  <?php if (isset($_SESSION['Lokalitet'])){
	  echo ', tur: '.$_SESSION['Lokalitet'];}?>
	  <?php if (isset($_SESSION['Dato'])){
	 echo ', den '.$_SESSION['Dato'];}?></div>
    </div>
    <div align="left">&nbsp;Søg efte<span class="HeaderGenus">r fund i dat</span>abasen </div>
  </div>
  
  <!-- start #bodyTextArea -->
  <div id="bodyTextArea">
    <div class="mainTextDarkGrey" id="bodyTextHolder" div align="left">
     <table width="98%" border="0" align="center" cellpadding="3" cellspacing="2">
        <form action="visnyrecordliste.php" method="POST" name="recsoeg" id="recsoeg">
        <tr>
          <td colspan="2" align="center" valign="middle" class="descriptionsDullRedThinLarger"><p align="center">Polygon-søgning<br />
              <span class="mainTextDarkGrey">Tegn et polygon på kortet for at søge efter fund indenfor dette område<br />
              <table width="100%" border="0" cellpadding="0">
  			  <tr>
   			  <td><span class="mainTextDarkGrey"><center>Størrelse på polygon:</center>
             </span></td>
   			  <td id="status" class="mainTextDarkGrey">&nbsp;</td>
 			  </tr>
              <script type="text/javascript">
			  
//<![CDATA[

// Global variables
var map;
var polyShape;
var polyLineColor = "#ff3333";
var polyFillColor = "#ff9999";
var polyPoints = new Array();
var markers = new Array();
var report = document.getElementById("status");

function loadMap() {

 map = new GMap2(document.getElementById("map"), {draggableCursor:'auto', draggingCursor:'move'});
 map.setCenter(new GLatLng(56.07, 12.12), 7);
 map.addMapType(G_PHYSICAL_MAP);
 var hierarchy = new GHierarchicalMapTypeControl();
 hierarchy.addRelationship(G_SATELLITE_MAP, G_HYBRID_MAP, "Labels", true);
 map.addControl(hierarchy);
 map.addControl(new GSmallMapControl());
 map.disableDoubleClickZoom();
 GEvent.addListener(map, "click", leftClick);
}

function drawpoly() {
/*GEvent.addListener(map, "click", map.removeOverlay(polyShape));*/
 if(polyShape) map.removeOverlay(polyShape);
 
 polyPoints.length = 0;	
 document.getElementById("coords").innerHTML = "";
 for(i = 0; i < markers.length; i++) {
  polyPoints.push(markers[i].getLatLng());
  document.getElementById("coords").innerHTML += i+": "+markers[i].getLatLng().toUrlValue(6)+"<br>";
 }
 // Close the shape with the last line or not
 // polyPoints.push(markers[0].getLatLng());

 var polyShape = new GPolygon(polyPoints, polyLineColor, 1, 0.3, polyFillColor,.4);
 var unit = " km&sup2;";
 var area = polyShape.getArea()/(1000*1000);

 if(markers.length <= 2 ) {
  report.innerHTML = "&nbsp;";
 }
 else if(markers.length > 2 ) { 
  report.innerHTML = area.toFixed(3)+ unit;
 }
 map.addOverlay(polyShape);
 polyShape.length = 0;
}


function addIcon(icon) { // Add icon attributes

 icon.iconSize = new GSize(11, 11);
 icon.dragCrossSize = new GSize(0, 0);
 icon.shadowSize = new GSize(11, 11);
 icon.iconAnchor = new GPoint(5, 5);
// icon.infoWindowAnchor = new GPoint(5, 1);
}


function leftClick(overlay, point) {

 if(point) {

  // Square marker icons
  var square = new GIcon();
  square.image = "GeneralGraphics/csquare.png";
  addIcon(square);

  // Make markers draggable
  var marker =new GMarker(point, {icon:square, draggable:false, bouncy:false, dragCrossMove:false});
  markers.push(marker);
  map.addOverlay(marker);


  GEvent.addListener(marker, "drag", function() {
   drawpoly();
  });

  GEvent.addListener(marker, "mouseover", function() {
    marker.setImage("GeneralGraphics/cm-over-square.png");
  });

  GEvent.addListener(marker, "mouseout", function() {
   marker.setImage("GeneralGraphics/csquare.png");
  });

  // Second click listener to remove the square
  GEvent.addListener(marker, "click", function() {

  // Find out which square to remove
  for(var n = 0; n < markers.length; n++) {
   if(markers[n] == marker) {
    map.removeOverlay(markers[n]);
    break;
   }
  }
  markers.splice(n, 1);
  drawpoly();
  });

  drawpoly();
  
 }
}


function zoomToPoly() {

 if(polyShape && polyPoints.length > 0) {
  var bounds = polyShape.getBounds();
  map.setCenter(bounds.getCenter());
  map.setZoom(map.getBoundsZoomLevel(bounds));
 }
}

function clearPoly() {

 // Remove polygon and reset arrays
 map.clearOverlays();
 polyPoints.length = 0;
 markers.length = 0;
 report.innerHTML = "&nbsp;";
}

function setValue()
{
var geostring = polyPoints.toString();
document.test.geostringen.value=geostring;}
// This sets the string to the hidden form field. }
//]]>
</script>
           
 			  <tr>
   			  <td><div align="center"><a href="#" class="button" onclick="zoomToPoly();return false;">Zoom ind Til Polygon</a></div></td>
  			  <td><div align="center"><a href="#" class="button" onclick="clearPoly();return false;">Slet Polygon</a></div></td>
 			  </tr>
			  </table>
              
              <br />
              - eller søg i UTM-felt:
                  <input name="utm"  type="text" id="utm" size="5" maxlength="4"/>
              </span><br />
            </p>
            </td>
        </tr>
        <tr>
          <td colspan="2" align="center" valign="middle" class="descriptionsDullRedThinLarger">Tekstsøgning</td>
          </tr>
        <tr>
          <td width="29%" align="right" valign="middle" class="mainTextDarkGrey">Finder:</td>
		
	      
	          <input type="hidden" name="action" value="soegrec"/>
			  <input name="latsoeg"  type="hidden" size="15" id="latsoeg" />
	          <input name="longsoeg"  type="hidden" size="15" id="longsoeg" />
          <td width="71%" valign="middle" class="mainTextDarkGrey"><input name="finder"  type="text" size="45" id="finder"/></td>
		    <tr>
		      <td align="right" valign="middle" class="mainTextDarkGrey"> Initialer:</td>
		      <td valign="middle" class="mainTextDarkGrey"><input name="initialer"  type="text" size="10" id="initialer"/>
	            | med åben kommentar: 
		         <input name="Spm" id="Spm" type="checkbox" value="Spm" />
	          <img src="GeneralGraphics/roedbob.png" alt="icon" width="20" height="17" /></td>
	        <tr>
	          <td align="right" valign="middle" class="mainTextDarkGrey">Databasenummer:</td>
	          <td valign="middle" class="mainTextDarkGrey"><input name="DBnummer"  type="text" size="15" id="DBnummer"/>
	          Status:
	            <select name="valistatus" id="valistatus">
                  <option value="" selected="selected">V&aelig;lg kategori</option>
  <option value=">Godkendt">Godkendte</option>
  <option value="Valideres">Valideres</option>
  <option value="Afventer">Afventer</option>
  <option value="Afvist">Afviste</option>
  <option value="Gammelvali">Ældre fund afventer</option>
                </select>
              </td>
            <tr>
		        <td align="right" valign="middle" class="mainTextDarkGrey">(Dato) År:</td>
		        <td valign="middle" class="mainTextDarkGrey"><input name="year"  type="number" size="6" id="year"/>
Måned:
  <input name="month"  type="number" size="6" id="month"/>
Dag:
<input name="day"  type="number" size="6" id="day"/></td>
	        <tr>
		          <td align="right" valign="middle" class="mainTextDarkGrey">Latinsk
		            navn:</td>
		          <td valign="middle" class="mainTextDarkGrey"><input name="latinnavn"  type="text" size="45" id="latinnavn"/></td>
<tr>
	            <td align="right" valign="middle" class="mainTextDarkGrey">Dansk
	              navn:</td>
	            <td valign="middle" class="mainTextDarkGrey">
	            <input name="dknavn"  type="text" size="45" id="dknavn"/>	            </td>
            </tr>
             
		 		 
        <tr>
          <td align="right" valign="middle" class="mainTextDarkGrey style2">Lokalitet - Kommune:</td>
          <td valign="middle" class="mainTextDarkGrey"><input name="lokalitet"  type="text" size="22" id="lokalitet"/>
          -
            <!--<input name="kommune"  type="text" size="20" id="kommune"/> aktiveres når kommune er klar--></td>
        </tr>
        <tr>
          <td align="right" valign="middle" class="mainTextDarkGrey">Rødlistestatus:</td>
          <td valign="baseline" class="mainTextDarkGrey"><select name="redlist" id="redlist">
  <option value="" selected="selected">V&aelig;lg kategori</option>
  <option value=">10">Alle r&oslash;dlistede</option>
  <option value="20">RE - Forsvundet</option>
  <option value="18">CR - Kritisk truet</option>
  <option value="16">EN - Moderat truet</option>
  <option value="14">VU - S&aring;rbar</option>
  <option value="12">NT - N&aelig;sten truet</option>
  <option value="9">LC - Ikke truet</option>
  <option value="7">DD - Utilstr&aelig;kkelige data</option>
  <option value="5">NA - Ikke mulig</option>
  <option value="0">NE - Ikke bedømt</option>
          </select>          <br /></td>
        </tr>
        <tr>
          <td align="right" valign="middle" class="mainTextDarkGrey"></td>
          <td valign="middle" class="mainTextDarkGrey10">&nbsp;Der kan søges på<br />
            <span class="smallTextBlack">&nbsp;RE</span> (forsvundet)<br />
            <span class="smallTextRed">&nbsp;CR</span> (kritisk truet)<br />
            <span class="smallTextRed">&nbsp;EN</span> (moderat truet)<br />
            <span class="smallTextRed">&nbsp;VU</span> (sårbar)<br />
            <span class="smallTextRed">&nbsp;NT</span> (næsten truet)<br />
            <span class="taxonList">&nbsp;LC</span> (ikke truet)<br />
            <span class="taxonList">&nbsp;DD</span> (ikke vurderet p.g.af utilstrækkelige data)<br />
            <span class="taxonList">&nbsp;NA</span> Ikke mulig (at vurdere)<br />
            <span class="taxonList">&nbsp;NE</span> Ikke bedømt
            <br />
           &nbsp;Læs mere om rødlistekategorierne på <a href="http://www2.dmu.dk/1_Om_DMU/2_Tvaer-funk/3_fdc_bio/projekter/redlist/redliststructure.asp" target="_blank">DMUs
           hjemmeside</a></td>
        </tr>
        <tr>
          <td colspan="2" class="mainTextDarkGrey">
          <div align="center">
                            
               <input type="submit" name="Submit" value=" Søg "  />        
               <br />
               </div></td>
        </tr>
        </form>
      </table>
    
				  
	          <input name="latsoeg"  type="hidden" size="15" id="latsoeg" />
	          <input name="longsoeg"  type="hidden" size="15" id="longsoeg" />
	          <input name="centerlat"  type="hidden" size="15" id="centerlat" />
	          <input name="centerlong"  type="hidden" size="15" id="centerlong" />
        <br />

</div>
    </div>
    <!-- end #bodyTextArea -->
    
    <!-- start #bodyRightArea -->
  <div id="bodyRightArea">
   
    <div id="bodyRightAreaTop" style:top:15px">
       
	<div id="map" style="width: 412px; height: 613px"></div>
<div id="coords"></div>

    
	</div><!-- BUNDTOPLAG -->
  </div>
  <!-- end #bodyRightArea -->
 
   <!-- start #bodyBottumLinkbar -->
    <div class="smallText" id="bodyBottumLinkbar">
      <div id="bodyBottumDivider"></div>
      <span class="smallTextGrey"><a href="indexvalider.php" class="darkgrey">til start</a> | <a href="lokalitetfind.php">opret tur</a> | indlæg
      ny art | <a href="soegrecord.php">søg fund</a> | <a href="indexvalider.php" class="darkgrey">min side</a> | <a href="indexsessionslut.php">logout</a></span></div>
  <!-- end #bodyBottumLinkbar -->
  
</div>
  
<!-- end #bodyContainer -->

</div>
<!-- end #wholePageContainer -->

<div id="arrayviser" style="color: white;">
<script language="JavaScript1.1" type="text/javascript">
document.arrayviser.value=polyShape;

</script>

</div>
</body>
</html>
