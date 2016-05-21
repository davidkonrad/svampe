<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />

<title>Resizable Polygons</title>

<style type="text/css">

body { font-family: Verdana; }

h3 { margin-left: 8px; }

#map { height: 400px;
	width: 550px;
	border: 1px solid gray;
	margin-top: 8px;
	margin-left: 8px;
}
.button { display: block;
	width: 180px;
	border: 1px Solid #565;
	background-color:#F5F5F5; 
	padding: 3px;
	text-decoration: none;
	text-align:center;
	font-size:smaller;
}
.button:hover {
	background-color: white;
}


#descr { position:absolute;
	top:44px;
	left: 580px;
	width: 250px;
}
#testdiv{
overflow:hidden;
}

</style>

<!-- key for 192.168.1.7 !-->
	<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAmvU52RJtOyI0zz81Y8BBoxTKSL5fUtutPPWazSqe5GsLCeoYoBTi9Ghh98xADiRWIM1h2ZY1Koe6Cg"
type="text/javascript"></script>


</head>

<body onload="loadMap()" onunload="GUnload()">

<h3>Tegn et polygon</h3>
<div id="testdiv">
<div id="map"> </div>		
</div>
<table id="descr" border="0" cellspacing="10" cellpadding="1">
<tr><td>
Klik for sat tegne et polygon og klik herefter søg for at søge efter fund indenfor polygonet.
Pas på med for store polygoner og derved for mange hits. Prøv at holde søgningerne under 500 km2.
</td></tr>
<tr><td>
Area of polygon:
</td></tr>
<tr><td id="status">&nbsp; </td></tr>

<tr><td height="20">&nbsp;


</td></tr><tr><td>
<a href="#" class="button" onclick="zoomToPoly();return false;">Zoom ind Til Polygon</a>
</td></tr><tr><td>
<a href="#" class="button" onclick="clearPoly();return false;">Slet Polygon</a>
</td></tr><tr><td style="padding-right:19px; height:30px;">

</td></tr>
</table>

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

function drawPoly() {
/*GEvent.addListener(map, "click", map.removeOverlay(polyShape));*/
 if(polyShape) {
	map.removeOverlay(polyShape);
 }
 
 polyPoints.length = 0;	
 document.getElementById("coords").innerHTML = "";
 for(i = 0; i < markers.length; i++) {
  polyPoints.push(markers[i].getLatLng());
  document.getElementById("coords").innerHTML += i+": "+markers[i].getLatLng().toUrlValue(6)+"<br>";
 }
 // Close the shape with the last line or not
 // polyPoints.push(markers[0].getLatLng());

 //var polyShape = new GPolygon(polyPoints, polyLineColor, 1, 0.3, polyFillColor,.4);
 //var polyShape = new GPolygon(polyPoints, polyLineColor, 1, .8, polyFillColor, .3);
 polyShape = new GPolygon(polyPoints, polyLineColor, 1, .8, polyFillColor, .3);
 var unit = " km&sup2;";
 var area = polyShape.getArea()/(1000*1000);

 if(markers.length <= 2 ) {
  report.innerHTML = "&nbsp;";
 }
 else if(markers.length > 2 ) { 
  report.innerHTML = area.toFixed(3)+ unit;
 }
 map.addOverlay(polyShape);
 //polyShape.length = 0;
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
  var marker =new GMarker(point, {icon:square, draggable:true, bouncy:false, dragCrossMove:true});
  markers.push(marker);
  map.addOverlay(marker);


  GEvent.addListener(marker, "drag", function() {
   drawPoly();
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
  drawPoly();
  });

  drawPoly();
  
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
 
<div id="coords"></div>
<form action="phpArrayTest.php" method="post" name="test" onSubmit="setValue()">
<input name="geostringen" type="hidden" value="">
<input type="submit">
</form>
</body>
</html>

