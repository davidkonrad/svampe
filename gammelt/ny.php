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
