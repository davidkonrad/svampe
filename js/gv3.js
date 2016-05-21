var atlasMaps = {};

//expected param google.maps.latLng
atlasMaps.testLatLng = function(latLng) {
	if (isNaN(latLng.lat())) return false;
	if (isNaN(latLng.lng())) return false;
	return true;
}

//returns clickable details HTML
atlasMaps.getDetailsHTML = function(id, genspec, dkname, leg, date) {
	var hint=genspec;
	if (dkname!='') hint+=' ('+dkname+')';
	hint='Se detaljer for fundet - '+hint;

	date=date.replace(' ','');
	var desc='<br>';
	if (date!='') desc+='<span style="color:gray;">'+date+'</span> - ';
	desc+=leg;
	desc+=' - <a href="#" title="'+hint+'" onclick="window.atlasSearch.showDetail(&quot;'+id+'&quot;);" class="fund">'+id+'</a><br>';
	desc+='<em>'+genspec+'</em>';
	if (dkname!='') desc+=' <span style="color:darkslategray;">('+dkname+')</span>';
	desc += '<br>';
	
	return desc;
}

//returns the google map used for details (prikkort etc)
atlasMaps.getDetailsMap = function(mapid) {
	var center, zoom, theMap;
	if ($("#center-lat").val()!=undefined) {
		center = new google.maps.LatLng($("#center-lat").val(), $("#center-long").val());
		switch ($('#center-code').val()) {
			case 'K' : zoom=10; break;
			case 'P' : zoom=11; break;
			case 'L' : zoom=12; break;
			default : zoom=7; break;
		}
	} else {
		center = new google.maps.LatLng(56.2, 11.44);
		zoom=7;	
	}
	
	var stylers = [
			{
			//remove "Danmark / Denmark"
			featureType: "administrative.country",
			elementType: 'labels',
			stylers: [ { visibility: 'off' } ]
			},
			{
			//remove all transit lines, bustoppes and so on
			featureType: "transit",
			elementType: 'all',
			stylers: [{ visibility: 'off' } ]
			},
			{
			//make all type of roads more discrete
			featureType: "road",
			elementType: 'all',
			stylers: [ { saturation: -100 } ]
			},
			{
			//hide all highways
			featureType: "road.highway",
			elementType: 'all',
			stylers: [ { visibility: 'off' } ]
			}
	];

	var options = {
		center: center,
		zoom: zoom,
		zoomControl: true,
		streetViewControl: false,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		},
		styles : stylers,
		mapTypeId: google.maps.MapTypeId.TERRAIN
    };

    theMap = new google.maps.Map(document.getElementById(mapid), options);
	theMap.enableKeyDragZoom({
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

	return theMap;
}


/***************/
var poly, map;
var markers = [];
var path = new google.maps.MVCArray;

function initMap() {
	//if we are a detail standalone page
	if ($("#map").length<=0) return false;

	var stylers = [	{
			//remove "Danmark / Denmark"
			featureType: "administrative.country",
			elementType: 'labels',
			stylers: [ { visibility: 'off' } ]
	}];

	map = new google.maps.Map(document.getElementById("map"), {
		center: new google.maps.LatLng(56.05, 10.4),
		zoom: 7,
		mapTypeId: google.maps.MapTypeId.TERRAIN,
		zoomControl: true,
		streetViewControl: false,
		styles: stylers,		
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

	poly = new google.maps.Polygon({
		strokeWeight: 1,
		strokeColor: 'transparent',
		fillColor: 'transparent'
	});

	poly.setMap(map);
	poly.setPaths(new google.maps.MVCArray([path]));

	google.maps.event.addListener(map, 'click', addPoint);
}

function enableSearchBtn() {
	setTimeout(function() {
		disableBtn('#searchBtn', true);
	}, 1000);
}

function roundTo(num, dec) {
	var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
	return result;
}

function calcArea() {
	var area = google.maps.geometry.spherical.computeArea(poly.getPath())/parseInt(1000000);
	var len = google.maps.geometry.spherical.computeLength(poly.getPath())/parseInt(1000);
	area = roundTo(area, 2);
	len = roundTo(len, 2);
	$("#poly-size").html('Samlet areal : <b>'+area+'</b> km&sup2;. Samlet længde : <b>'+len+'</b> km&nbsp;&nbsp;');
}

function resetPolygon() {
	for (var i = 0; i < markers.length; i++ ) {
		markers[i].setMap(null);
	}
	for (i=0;i<=path.getLength();i++){
		path.removeAt(i);
		path.clear();
		path.pop();
	}
	markers=[];
	calcArea();
}

function resetTest() {
	var test=markers;
	for (var i = 0; i < markers.length; i++ ) {
		markers[i].setMap(null);
	}
	for (i=0;i<=path.getLength();i++){
		path.removeAt(i);
		path.clear();
		path.pop();
	}
	markers=[];
	calcArea();

	for (i=0;i<test.length;i++) {
		var marker = new google.maps.Marker({
			icon : 'none',
			position: test[i].getPosition(),
			map: map,
			draggable: true,
        		strokeColor: "transparent",
		        fillColor: "transparent"
		});
	 }
}

function zoomToPolygon() {
	var latlngbounds = new google.maps.LatLngBounds( );
	for (var i = 0; i < markers.length; i++ ) {
		latlngbounds.extend(markers[i].position);
	}
	map.fitBounds(latlngbounds);
}

function addPoint(event) {
	//change poly colors "back" to yellow, preset as transparent
	poly.setOptions({ strokeColor: '#FFF380', fillColor: '#FFF380'});

	path.insertAt(path.length, event.latLng);

	var marker = new google.maps.Marker({
		icon : 'glyph/csquare.png',
		position: event.latLng,
		map: map,
		draggable: true
	});
	markers.push(marker);
	marker.setTitle("#" + path.length);

	google.maps.event.addListener(marker, 'click', function() {
		marker.setMap(null);
		for (var i = 0, I = markers.length; i < I && markers[i] != marker; ++i);
		markers.splice(i, 1);
		path.removeAt(i);
	});
	
	google.maps.event.addListener(marker, 'dragend', function() {
		for (var i = 0, I = markers.length; i < I && markers[i] != marker; ++i);
		path.setAt(i, marker.getPosition());
	});

	calcArea();
}

//x2
var bounds = new google.maps.LatLngBounds(); 
var graense = false;

function getKommuneGraense(nr) {
	//reset
	bounds = new google.maps.LatLngBounds(); 
	if (graense) graense.setMap(null);
	for (var i = 0; i < markers.length; i++ ) {
		markers[i].setMap(null);
  	}

	var type="kommuner";
	var url= "proxy.php?url=http://geo.oiorest.dk/"+type+"/"+nr+"/graense.json"; 			
	$.ajax({
		url: url,
		dataType: 'json',
		success:  function (data) {
			for (var index = 0; index<data.coordinates.length; index++) {
				showKommuneGraense(data.coordinates[index], type);
			}
			//enable the search button
			enableSearchBtn();
		},
		error: function (xhr, ajaxOptions, thrownError){
			enableSearchBtn();
		}   
	});
}

function showKommuneGraense(polygon) {
	var lat, lng;
	var paths;
	var coordinates= polygon[0];
	var latlng;
	var vertices = new Array(coordinates.length);
	for (var index = 0; index < coordinates.length; index++) {
		lat = parseFloat(coordinates[index][1]);
		lng = parseFloat(coordinates[index][0]);
		latlng = new google.maps.LatLng(lat, lng);
		vertices[index] = latlng;
		bounds.extend(latlng); 

		//add each 20 latlong as invisible marker 
		if (index % 20 == 0)  {
			var marker = new google.maps.Marker({
				icon : 'none',
				position: latlng,
				map: map,
				draggable: true,
	        		strokeColor: "transparent",
			        fillColor: "transparent"
			});
			markers.push(marker);
			path.insertAt(path.length, latlng);
		}
	}
	var hole;
	if (polygon.length > 1) {
		coordinates= polygon[1];
		hole= new Array(coordinates.length);
		for (var index = 0; index < coordinates.length; index++) {
			lat = parseFloat(coordinates[index][1]);
			lng = parseFloat(coordinates[index][0]);
			latlng = new google.maps.LatLng(lat, lng);
			hole[index] = latlng;
			bounds.extend(latlng); 
		}
	}

	if (polygon.length > 1) {
		paths= new Array(2);
		paths[0]= vertices;
		paths[1]= hole;
	} else paths= vertices;

	//var graense = new google.maps.Polygon({
	graense = new google.maps.Polygon({
		paths: paths,
		strokeOpacity: 0.8,
		strokeWeight: 2,
		strokeColor: '#FFF380',
		fillColor: 'blue' 
	});

	map.fitBounds(bounds); 
	graense.setMap(map);
	calcArea();
}

function getRandomIcon() {
	var r=Math.floor(Math.random()*8)+1;
	switch (r) {
		case 1 : return "glyph/Circle_Blue.png"; break;
		case 2 : return "glyph/Circle_Yellow.png"; break;
		case 3 : return "glyph/Circle_Red.png"; break;
		case 4 : return "glyph/Circle_Green.png"; break;
		case 5 : return "glyph/Circle_Grey.png"; break;
		case 6 : return "glyph/Circle_Opaque.png"; break;
		case 7 : return "glyph/Circle_Orange.png"; break;
		case 8 : return "glyph/Circle_White.png"; break;
		default : return "glyph/Circle_White.png"; break;
	}
}

function yearToIcon(year) {
	if (parseInt(year)>2008) {
		return 'glyph/Circle_Blue.png';
	}
	if (parseInt(year)>1999) {
		return 'glyph/Circle_Orange.png';
	}
	if (parseInt(year)>1974) {
		return 'glyph/Circle_Yellow.png';
	}
	if (parseInt(year)>1949) {
		return 'glyph/Circle_Grey.png';
	}
	if (parseInt(year)>1924) {
		return 'glyph/Circle_White.png';
	}
	if (parseInt(year)>1899) {
		return 'glyph/Circle_Opaque.png';
	}
	return 'glyph/Circle_Red.png';
}

function yearToZ(year) {
	if (parseInt(year)>2008) {
		return 10;
	}
	if (parseInt(year)>1999) {
		return 9;
	}
	if (parseInt(year)>1974) {
		return 8;
	}
	if (parseInt(year)>1949) {
		return 7;
	}
	if (parseInt(year)>1924) {
		return 5;
	}
	if (parseInt(year)>1899) {
		return 3;
	}
	return 1;
}

//initAllFindingsMap with data reload
function initAllFindingsMap() {
	$("#all-findings-spot").html('');
	$('html, body, #all-findings-map').css("cursor", "wait");

	/*
	var center, zoom;
	if ($("#center-lat").val()!=undefined) {
		center = new google.maps.LatLng($("#center-lat").val(), $("#center-long").val());
		switch ($('#center-code').val()) {
			case 'K' : zoom=10; break;
			case 'P' : zoom=11; break;
			case 'L' : zoom=12; break;
			default : zoom=7; break;
		}
	} else {
		center = new google.maps.LatLng(56.2, 11.44);
		zoom=7;	
	}
	*/
	/*
	var afmap = null;
	var options = {
		center: center, //new google.maps.LatLng(56.2, 11.44),
		zoom: zoom, //7,
		zoomControl: true,
		streetViewControl: false,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		},
		mapTypeId: google.maps.MapTypeId.TERRAIN
        };
        afmap = new google.maps.Map(document.getElementById("all-findings-map"), options);
	*/
	var afmap = atlasMaps.getDetailsMap('all-findings-map');

	/*
	afmap.enableKeyDragZoom({
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
	*/

	var stack = []; 
	var idStack = [];
	var markers = new Array();
	var url='ajax_fund.php';
	var fundCount = 0;
	var icon, zIndex;

	$.ajax({
		url: url,
		data: atlasSearch.lastParams,
		cache: true,
		async: true,
		type: 'post',
		dataType: 'json',
		timeout: 60000,
		success: function(html) {
			$.each(html, function(id, fund) {
				fundCount++;
				$('html, body, #all-findings-map').css("cursor", "wait");
				var details=atlasMaps.getDetailsHTML(fund.id, fund.genspec, fund.dkname, fund.leg, fund.date);
				var slatlng=fund.lat+fund.lng;
				if (stack[slatlng]===undefined) {
					stack[slatlng]=details;
				} else {
					stack[slatlng]=stack[slatlng]+details;
				}
				idStack[fund.id]=slatlng;
				
				icon = yearToIcon(fund.year);
				zIndex = yearToZ(fund.year);
				
				var Marker = new google.maps.Marker({
					optimized: true, //false
					icon: icon,
					zIndex: zIndex,
					position: new google.maps.LatLng(fund.lat, fund.lng),
					map: afmap
				});

				google.maps.event.addListener(Marker, 'click', function() {
					var html = stack[idStack[fund.id]];
					var br=Math.ceil($(html+' br').length/9);
					$("#all-findings-spot").html('<b>'+fund.locality+'</b>, '+br+' fund<br>'+html);
				});

				$('html, body, #all-findings-map').css("cursor", "auto");

			});
			$("#fund-caption").text(fundCount+' fund i alt');
		},
		complete: function(jqXHR, textStatus) {
			$('html, body, #all-findings-map').css("cursor", "auto");
		},
		error: function(jqXHR, textStatus, errorThrown) {
			$('html, body, #all-findings-map').css("cursor", "auto");
			alert('error :'+textStatus+' '+errorThrown+' '+jqXHR.responseText);
		}

	});

	/* 05012014
	alert('ok');
	map = afmap;
	getKommuneGraense(155);
	*/
}

//utm with data reload
var ejUTM = ['','Fund','VA89','V68','WB30','N36'];
function initUTMMap_load() {
	$("#utm-findings-spot").html('');
	$('html, body').css("cursor", "wait");
	/*
	var utmmap = null;
	var options = {
		center: new google.maps.LatLng(56.2, 11.44),
		zoom: 7,
		zoomControl: true,
		streetViewControl: false,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		},
		mapTypeId: google.maps.MapTypeId.TERRAIN
	};
	utmmap = new google.maps.Map(document.getElementById("utm-findings-map"), options);
	*/
	var utmmap = atlasMaps.getDetailsMap('utm-findings-map');
	var stack=[];
	var url='ajax_utm.php';
	var processed = new Array();
	$.ajax({
		url: url,
		data: atlasSearch.lastParams,
		cache: true,
		async: true,
		type: 'post',
		dataType: 'json',
		timeout: 60000,
		success: function(html) {
			$.each(html, function(id, utm) {
				if ($.inArray(utm.utm, processed)<0) {
					if ($.inArray(utm.utm, ejUTM)<0) {
						var utmcoords = eval(UTM_LatLng[utm.utm]);
						var poly = new google.maps.Polygon({
				    		paths: utmcoords,
							strokeColor: "blue",
							strokeOpacity: 0.50,
							strokeWeight: 0,
							fillColor: "blue",
							fillOpacity: 0.50
						});
						poly.setMap(utmmap);
						processed.push(utm.utm);
						google.maps.event.addListener(poly, 'click', function() {
							var html=stack[utm.utm];
							var br=Math.ceil($(html+' br').length/9);
							$("#utm-findings-spot").html('<b>'+utm.utm+'</b>, '+br+' fund<br>'+html);
						});

						var detail=atlasMaps.getDetailsHTML(utm.atlasid, utm.genspec, utm.dkname, utm.leg, utm.date);
						if (stack[utm.utm]===undefined) {
							stack[utm.utm]=detail;
						} else {
							stack[utm.utm]=stack[utm.utm]+detail;
						}
					}
				}
			});
			$('html, body').css("cursor", "auto");
		},
		error: function(jqXHR, textStatus, errorThrown) {
			$('html, body').css("cursor", "auto");
			alert('error :'+jqXHR.responseText+' '+textStatus+' '+errorThrown);
		}
	});
}

function initUTMDensityMap_load() {
	$("#utm-findings-spot").html('');
	$('html, body').css("cursor", "wait");
	/*
	var utmmap = null;
	var options = {
		center: new google.maps.LatLng(56.2, 11.44),
		zoom: 7,
		zoomControl: true,
		streetViewControl: false,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		},
		mapTypeId: google.maps.MapTypeId.TERRAIN
	};
	utmmap = new google.maps.Map(document.getElementById("utm-findings-map"), options);
	*/
	var utmmap = atlasMaps.getDetailsMap('utm-findings-map');
	var stack=[];
	var url='ajax_utm.php';
	$.ajax({
		url: url,
		data: atlasSearch.lastParams,
		cache: true,
		async: true,
		type: 'post',
		dataType: 'json',
		timeout: 60000,
		success: function(html) {
			$.each(html, function(id, utm) {
				if ($.inArray(utm.utm, ejUTM)<0) {
					var utmcoords = eval(UTM_LatLng[utm.utm]);
					var poly = new google.maps.Polygon({
			    		paths: utmcoords,
						strokeColor: "blue",
						strokeOpacity: 0.25,
						strokeWeight: 0,
						fillColor: "blue",
						fillOpacity: 0.25
					});

					var detail=atlasMaps.getDetailsHTML(utm.atlasid, utm.genspec, utm.dkname, utm.leg, utm.date);
					if (stack[utm.utm]===undefined) {
						stack[utm.utm]=detail;
					} else {
						stack[utm.utm]=stack[utm.utm]+detail;
					}

					poly.setMap(utmmap);

					google.maps.event.addListener(poly, 'click', function() {
						var html=stack[utm.utm];
						var br=Math.ceil($(html+' br').length/9);
						$("#utm-findings-spot").html('<b>'+utm.utm+'</b>, '+br+' fund<br>'+html);
					});
				}
			});
			$('html, body').css("cursor", "auto");
		},
		error: function(jqXHR, textStatus, errorThrown) {
			$('html, body').css("cursor", "auto");
			alert('error :'+jqXHR+' '+textStatus+' '+errorThrown);
		}
	});
}

function reportMapContent(fund) {
	var href="http://svampe.dk/soeg/index.php?AtlasRecID="+fund.atlasidnummer;
	var html='<div style="font-size:12px;font-family: arial;text-align:left;height:60px;margin:0px;padding:0px;">';
	html+='Dato: '+fund.day+'/'+fund.month+'/'+fund.year+'<br>';
	html+='Finder: '+fund.leg+'<br>';
	html+='Lokalitet: '+fund.locality+'<br>';
	html+='ID-nummer: <a href="'+href+'" title="Klik for at se fundet (åbner i nyt vindue)" target=_blank>'+fund.atlasidnummer+'</a>';
	html+='</div>';
	return html;
}

function reportMap(DKIndex) {
	map = new google.maps.Map(document.getElementById("report-map"), {
		center: new google.maps.LatLng(56.05, 11.4),
		zoom: 6,
		mapTypeId: google.maps.MapTypeId.TERRAIN,
		zoomControl: true,
		streetViewControl: false,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		}
	});

	var contents=[];
	var infowindow = new google.maps.InfoWindow({
		pixelOffset: new google.maps.Size(0, 0)
	});
	var url='ajax_fund.php';
	var icon, zIndex;
	$.ajax({
		url: url,
		data: '&DKIndex='+DKIndex,
		cache: true,
		async: true,
		type: 'post',
		dataType: 'json',
		timeout: 60000,
		success: function(html) {
			$.each(html, function(id, fund) {
				contents[fund.atlasidnummer]=reportMapContent(fund);

				if (fund.year!='') {
					if (fund.year>=2009) {
						icon='glyph/blue12.png';
					} else if (fund.year>=1991) {
						icon='glyph/orange12.png'
					} else {
						icon='glyph/yellow12.png';
					}
					zIndex=yearToZ(fund.year);

					var Marker = new google.maps.Marker({
						optimized: true, 
						zIndex: zIndex,
						icon: icon,
						position: new google.maps.LatLng(fund.lat, fund.lng),
						map: map
					});

					google.maps.event.addListener(Marker, 'click', function() {
						//void(open('index.php?AtlasRecID='+fund.atlasidnummer));
						infowindow.setContent(contents[fund.atlasidnummer]);
						infowindow.setPosition(Marker.position);
						infowindow.open(map);
					});
				}
			});
		},
		error: function(jqXHR, textStatus, errorThrown) {
			$('html, body').css("cursor", "auto");
			alert('error :'+jqXHR+' '+textStatus+' '+errorThrown);
		}
	});
 }

/********** heatmap 08.10.2013 ***************/
function initHeatmap() {
	$("#heatmapDetails").html('');
	$('html, body, #heatmapMap').css("cursor", "wait");

	var theMap = atlasMaps.getDetailsMap('heatmapMap');
	var heatmapData = [];
	var stack = [];
	var url='ajax_fund.php';
	var fundCount = 0;
	var circle = null;

	$.ajax({
		url: url,
		data: atlasSearch.lastParams,
		cache: false,
		async: true,
		type: 'post',
		dataType: 'json',
		timeout: 60000,
		success: function(html) {
			$.each(html, function(id, fund) {
				var latLng = new google.maps.LatLng(fund.lat, fund.lng);
				if (atlasMaps.testLatLng(latLng)) {
					var enkeltFund = new Object();
					enkeltFund.latLng=latLng;
					enkeltFund.html=atlasMaps.getDetailsHTML(fund.id, fund.genspec, fund.dkname, fund.leg, fund.date);
					stack.push(enkeltFund);
					heatmapData.push(latLng);
				}
			});
			$("#fund-caption").text(fundCount+' fund i alt');

			var heatmap = new google.maps.visualization.HeatmapLayer({
			    data: heatmapData,
			    dissipating: true,
				radius : 15,
				opacity : 0.8,
			    map: theMap
			});

			google.maps.Circle.prototype.contains = function(latLng) {
  				return this.getBounds().contains(latLng) && google.maps.geometry.spherical.computeDistanceBetween(this.getCenter(), latLng) <= this.getRadius();
			}
			google.maps.event.addListener(theMap, 'click', function(args) {
				if (circle!=null) {
					circle.setMap(null);
				}
				circle = new google.maps.Circle({
		           center: args.latLng,
		           fillColor:"navy",
		           fillOpacity: 1,
		           strokeColor:"fff",
		           strokeOpacity: 1,
		           strokeWeight: 0,
		           zIndex: 5,
		           radius: 2000
		        });
		        circle.setMap(theMap);
				var html='';
				for (var i=0;i<stack.length;i++) {
					if (circle.contains(stack[i].latLng)) {
						html=html+stack[i].html;
					}
				}
				$("#heatmapDetails").html(html);
			});
		},
		complete: function(jqXHR, textStatus) {
			$('html, body, #heatmapMap').css("cursor", "auto");
		},
		error: function(jqXHR, textStatus, errorThrown) {
			$('html, body, #heatmapMap').css("cursor", "auto");
			alert('error :'+textStatus+' '+errorThrown+' '+jqXHR.responseText);
			//alert(id+' '+fund+' error :'+textStatus+' '+errorThrown+' ');//+jqXHR.responseText);
		}

	});
}

/********** diversity 03.02.2014 ***************/
atlasMaps.initDiversityMap = function() {
	$("#diversityDetails").html('');
	$('html, body, #diversityMap').css("cursor", "wait");

	var theMap = atlasMaps.getDetailsMap('diversityMap');
	var diversityData = [];
	var lokalitet = [];
	var stack = [];
	var url='ajax_fund.php';
	var fundCount = 0;
	var circle = null;

	$.ajax({
		url: url,
		data: atlasSearch.lastParams,
		cache: true,
		async: true,
		type: 'post',
		dataType: 'json',
		timeout: 60000,
		success: function(html) {
			$.each(html, function(id, fund) {
				console.log(fund);
				if (lokalitet.indexOf(fund.locality)==-1) {
					lokalitet[fund.locality]=[];
				} 
				if (lokalitet[fund.locality].indexOf(fund.genspec)==-1) {
					//console.log(fund.locality,fund.genspec);
					var latLng = new google.maps.LatLng(fund.lat, fund.lng);
					if (atlasMaps.testLatLng(latLng)) {
						var enkeltFund = new Object();
						enkeltFund.latLng=latLng;
						enkeltFund.html=atlasMaps.getDetailsHTML(fund.id, fund.genspec, fund.dkname, fund.leg, fund.date);
						stack.push(enkeltFund);
						diversityData.push(latLng);
					}
				}
			});
			var total = 0;
			for (var i=0;i<lokalitet.length;i++) {
				total=total+lokalitet[i].length;
			}
			$("#fund-caption").text(total+' unikke arter fordelt på '+lokalitet.length+' lokaliteter');

			var heatmap = new google.maps.visualization.HeatmapLayer({
			    data: diversityData,
			    dissipating: true,
				radius : 15,
				opacity : 0.8,
			    map: theMap
			});

			google.maps.Circle.prototype.contains = function(latLng) {
  				return this.getBounds().contains(latLng) && google.maps.geometry.spherical.computeDistanceBetween(this.getCenter(), latLng) <= this.getRadius();
			}
			google.maps.event.addListener(theMap, 'click', function(args) {
				if (circle!=null) {
					circle.setMap(null);
				}
				circle = new google.maps.Circle({
		           center: args.latLng,
		           fillColor:"navy",
		           fillOpacity: 1,
		           strokeColor:"fff",
		           strokeOpacity: 1,
		           strokeWeight: 0,
		           zIndex: 5,
		           radius: 2000
		        });
		        circle.setMap(theMap);
				var html='';
				for (var i=0;i<stack.length;i++) {
					if (circle.contains(stack[i].latLng)) {
						html=html+stack[i].html;
					}
				}
				$("#diversityDetails").html(html);
			});
		},
		complete: function(jqXHR, textStatus) {
			$('html, body, #diversityMap').css("cursor", "auto");
		},
		error: function(jqXHR, textStatus, errorThrown) {
			$('html, body, #diversityMap').css("cursor", "auto");
			alert('error :'+textStatus+' '+errorThrown+' '+jqXHR.responseText);
		}
	});
}


