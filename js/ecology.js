
var Ecology = {
	
	ecologyReport : function() {
		var params='?search_id='+$('#__SEARCH_ID').val();
		var url='oekologi_rapport.php'+params;
		//window.location=url;
		window.open(url);
	},

	initPolygonMap : function(polygon) {
		var map = new google.maps.Map(document.getElementById("map"), {
			center: new google.maps.LatLng(56.05, 10.4),
			zoom: 7,
			mapTypeId: google.maps.MapTypeId.TERRAIN,
			zoomControl: true,
			streetViewControl: false,
			zoomControlOptions: {
				style: google.maps.ZoomControlStyle.SMALL
			}
		});

		var paths = [];
		var count=1;
		var ok=true;
		var ll;
		console.log($("#poly"));
		while (ok) {
			ok=$("#poly").attr('p'+count)!==undefined;
			if (ok) {
				var a=$("#poly").attr('p'+count).split(',');
				ll=new google.maps.LatLng(a[0], a[1]);
				paths.push(ll);
				count++;
			}
		}

		var poly = new google.maps.Polygon({
			paths: paths,
			strokeColor: "blue",
			strokeOpacity: 0.50,
			strokeWeight: 0.50,
			fillColor: "blue",
		});

		poly.setMap(map);

		var latlngbounds = new google.maps.LatLngBounds( );
		for (var i = 0; i < paths.length; i++ ) {
			latlngbounds.extend(paths[i]);
		}
		map.fitBounds(latlngbounds);

	},

	speciesWindow : function(caption, html) {
		$('#species-window').dialog({
			title: caption,
			height: 300,
			width: 400,
			position: { my: "left top", at: "left top" }
		});
		$('#species-window').html(html);
	}

};
