/* SvampeAtlas mySQL search without login */

var atlasSearch = {
	detailOrigin : '', 
	lastParams: '',
	init : function() {
		$('#mysqlSearch input').bind('keypress', function(e) {
			var code = (e.keyCode ? e.keyCode : e.which);
			if (code == 13) {
				atlasSearch.submit();
			}
		});
		$('#mysqlSearch .datoInterval').bind('keydown', function(e) {
			atlasSearch.dateKeydown(e);
		});
		this.enableDateIntervals();

		//have we loaded the detailpage?
		if ($("#mysqlSearch").length<=0) return;

		/* set year
		$("#mysqlSearch")[0].reset();
		var d=new Date();
		$("#year").val(d.getFullYear());
		*/

		this.getKommuner();
	},

	reset : function() {
		//in this case, far the best approch
		var href=window.location.href.split('?');
		window.location.href=href[0];//reload();
		return false;
		//resetFields();
	},

	wait : function() {
		$('html, body').css("cursor", "wait");
	},

	auto : function() {
		$('html, body').css("cursor", "auto");
	},

	closePopups : function() {
		$("#naturtype-select").hide();
		$("#roedliste-select").hide();
	},

	searchLog : function(params) {
		var url='ajax_log.php';
		$.ajax({
			url : url,
			data: params,
			type: 'post',
			async: true,
			timeout: 15000,
			success: function(logid) {
				$('#__SEARCH_ID').val(logid);
			}
		});
	},	

	submit : function() {
		this.closePopups();
		this.wait();
		var params = '';
		params+='&DKName='+encodeURIComponent($("#DKName").val());
		params+='&GenSpec='+encodeURIComponent($("#GenSpec").val());
		params+='&localitybasicString='+encodeURIComponent($("#localitybasicString").val());
		params+='&kommune='+encodeURIComponent(getField('kommune'));
		params+='&atlasID='+encodeURIComponent($("#atlasID").val());
		params+='&initialer='+encodeURIComponent($("#initialer").val());

		if ($("#roedlist-input").val()!='Vælg kategori(er)') {
			if ($("#roedlist-input").val().indexOf('ALLE')>0) {
				params+='&redlist=ALL';
			} else {
				params+='&redlist='+encodeURIComponent($("#roedlist-input").val());
			}
		}
		if ($("#naturtype-input").val()!='Vælg kategori(er)') {
			params+='&naturtype='+encodeURIComponent($("#naturtype-input").val());
		}

		params+='&utm='+encodeURIComponent($("#utm").val());

		params+='&year='+$("#year").val();
		params+='&month='+$("#month").val();
		params+='&day='+$("#day").val();
		params+='&to-year='+$("#to-year").val();

		//autoset
		if ($("#to-year").val()!='') {
			params+='&to-month=';
			if ($("#to-month").val()!='') {
				params+=$("#to-month").val();
			} else {
				params+='12';
			}
		}
		if ($("#to-year").val()!='') {
			params+='&to-day=';
			if ($("#to-day").val()!='') {
				params+=$("#to-day").val();
			} else {
				params+='31';
			}
		}

		var latlong='';
		//only polygon, if kommune is not set
		if (getField('kommune')=='') {
			for (var i=0;i<markers.length;i++) {
				var test=markers[i].position;
				var LL=markers[i].getPosition().toUrlValue(4);
				latlong+='&LL'+i.toString()+'='+LL.toString();
			}
		}

		atlasSearch.searchLog(params+latlong);

		$("#formCnt").hide();
		$("#resultCnt").html('<br><h2 style="margin-left:20px;font-size:16px;font-family: courier, verdana, helvetica;color:#2B499A;">Søger ...</h2>');

		var url='ajax_search.php';

		atlasSearch.lastParams=params+latlong;
		$.ajax({
			url: url,
			data: params+latlong,
			cache: true,
			async: true,
			type: 'post',
			timeout: 60000,
			success: function(html) {
				atlasSearch.bodyContainerUp();
				atlasSearch.headerCaption(false);
				$("#resultCnt").html(html);
			}
		});
		$('html, body').animate({scrollTop:0}, 'slow');
	},

	parseLastParams : function() {
		var params = new Object();
		var last = atlasSearch.lastParams.split('&');
		for (i=0;i<last.length;i++) {
			var p=last[i].split('=');
			if (p[0]!='') params[p[0]]=p[1];
		}
		//console.log(params);
		for (var param in params) {
			if (param.indexOf('LL')==-1 && param!='kommune') {
				$("#"+param).val(params[param]);
			} else if (param.indexOf('LL')>-1) {
				//console.log(param);
				var latlng=params[param].split(',');
				var event=new Object();
				event.latLng=new google.maps.LatLng(latlng[0], latlng[1]);
				addPoint(event); //gv3.js
			} else if (param=='kommune') {
				var knr=params['kommune'];
				$("#kommune").val(knr);
				getKommuneGraense(knr); //form.js
			}
		}
	},
	
	permaSearch : function(id) {
		atlasSearch.wait();

		$("#formCnt").hide();
		$("#resultCnt").html('<br><h2 style="margin-left:20px;font-size:16px;font-family: courier, verdana, helvetica;color:#2B499A;">Søger ...</h2>');

		$('#__SEARCH_ID').val(id);

		var url='ajax_search.php';
		url+='?perma='+id;
		//var params='perma='+id;

		//atlasSearch.lastParams=params+latlong;
		$.ajax({
			url: url,
			//data: params,
			cache: true,
			async: true,
			type: 'post',
			timeout: 60000,
			success: function(html) {
				atlasSearch.bodyContainerUp();
				atlasSearch.headerCaption(false);
				$("#resultCnt").html(html);
				atlasSearch.auto();
				atlasSearch.parseLastParams();
			}
		});
		$('html, body').animate({scrollTop:0}, 'slow');
	},

	dateKeydown : function(e) {
		//allow backspace, delete, tab, escape, and enter
		if (e.keyCode == 46 || e.keyCode == 8 || e.keyCode == 9 || e.keyCode == 27 || e.keyCode == 13 || 
		//allow ctrl+a
		(e.keyCode == 65 && e.ctrlKey === true) || 
		//allow: home, end, left, right
		(e.keyCode >= 35 && e.keyCode <= 39)) {
			// let it happen, don't do anything
			atlasSearch.enableDateIntervals();
			return;
		} else {
			// ensure that it is a number and stop the keypress
			if (e.shiftKey || (e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105 )) {
		                e.preventDefault(); 
			}   
		}
	},

	enableDateIntervals : function() {
		atlasSearch.enableDateInterval("year");
		atlasSearch.enableDateInterval("month");
		atlasSearch.enableDateInterval("day");
	},

	enableDateInterval : function(from) {
		if ($("#"+from).attr('value')>0) {
			$("#to-"+from).removeAttr('disabled');
		} else {
			$("#to-"+from).attr('disabled','disabled');
		}
	},

	back : function() {
		$("#resultCnt").html('');
		$("#formCnt").show();
		this.bodyContainerDown();
		this.headerCaption(true);
		google.maps.event.trigger(map, 'resize');
		map.setCenter(new google.maps.LatLng(56.05, 10.4));
		map.setZoom(7);
		$("#perma-link").html('');
		$('html, body').animate({scrollTop:0}, 'slow');
	},

	showDetail : function(AtlasRecID) {
		if ($("#resultCnt").is(':visible')) atlasSearch.detailOrigin="#resultCnt";
		if ($("#allFindingsMapCnt").is(':visible')) atlasSearch.detailOrigin="#allFindingsMapCnt";
		if ($("#utmFindingsMapCnt").is(':visible')) atlasSearch.detailOrigin="#utmFindingsMapCnt";
		if ($("#heatmapCnt").is(':visible')) atlasSearch.detailOrigin="#heatmapCnt";
		var url='ajax_detail.php';
		url+='?AtlasRecID='+AtlasRecID;
		$.ajax({
			url: url,
			cache:true,
			async: true,
			timeout: 5000,
			success: function(html) {
				$(atlasSearch.detailOrigin).hide();
				$("#detailCnt").show();
				$("#detailCnt").html(html);
				load();
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert(jqXHR.responseText+' '+textStatus+' '+errorThrown);
			}
		});
	},

	detailBack : function() {
		$("#detailCnt").hide();
		$(atlasSearch.detailOrigin).show();
	},

	getKommuner : function() {
		var url = "proxy.php?url=http://geo.oiorest.dk/kommuner.json";
		var ka = [];
		$.ajax({
			url: url,
			dataType: 'json',
			success: function(data){
				$.each(data, function(index, kommune) {
					ka[ka.length+1]={navn:kommune.navn,nr:kommune.nr}
				});

				ka.sort(function(a, b) {
					var A=a.navn.toLowerCase();
					var B=b.navn.toLowerCase();
					if (A<B) return -1;
					if (A>B) return 1;
					return 0;
				})

				for (var i=0;i<=ka.length;i++) {
					if (ka[i]!==undefined) {
						$("#kommune").append('<option value="'+ka[i].nr+'">'+ka[i].navn+'</option>');
					}
				}
			}
		});
	},

	showAllFindingsMap : function() {
		$("#resultCnt").hide();
		$("#allFindingsMapCnt").show();
		this.headerCaption(false);
		this.bodyContainerUp();
		initAllFindingsMap();
	},

	showDiversityMap : function() {
		$("#resultCnt").hide();
		$("#diversityCnt").show();
		this.headerCaption(false);
		this.bodyContainerUp();
		atlasMaps.initDiversityMap();
	},

	showUTMFindingsMap : function() {
		$("#resultCnt").hide();
		$("#utmFindingsMapCnt").show();
		this.headerCaption(false);
		this.bodyContainerUp();
		initUTMMap_load();
	},

	resultBack : function(element) {
		$(element).hide();
		$("#resultCnt").show();
	},

	showChronoReport : function() {
		$("#resultCnt").hide();
		atlasSearch.wait();
		var url='ajax_tid_rapport.php?search_id='+$('#__SEARCH_ID').val();
		$.ajax({
			url: url,
			success : function(html) {
				$("#chronoCnt").show();
				$("#chronoGraph").html(html);
				atlasSearch.auto();
			}
		});
	},

	showHeatmap : function() {
		$("#resultCnt").hide();
		$("#heatmapCnt").show();
		this.headerCaption(false);
		this.bodyContainerUp();
		initHeatmap();
	},
	
	bodyContainerUp : function() {
		$("#bodyContainer").css('height','615');
	},

	bodyContainerDown : function() {
		$("#bodyContainer").css('height','624');
	},

	headerCaption : function(frontpage) {
		if (frontpage) {
			$("#header-caption").text('Søg efter fund i databasen');
		} else {
			$("#header-caption").text('Detaljerede oplysninger');
		}
	},

	getCSVCols : function(all) {
		var fields = $('#field-bar input');
		var cols = '';
		for (var i=0;i<fields.length;i++) {
			var field=$(fields[i]);
			if (all || field.is(':checked'))  {
				if (cols!='') cols+=',';
				if (field.attr('id')=='col14') {
					cols+='AtlasUserlati,AtlasUserLong';
				} else {
					cols+=field.attr('field');
				}
			}
		}
		return '&cols='+cols;
	},

	saveAsCSV : function() {
		$('<div/>').qtip({
			id: 'download',
			content: {
				text: $('#download-csv-form'),
				title: { text: 'Download søgeresultatet som CSV', button: false	}
			},
			position: { my: 'center', at: 'center', target: $(window) },
			show: {
				event: 'click', 
				ready: true, 
				modal: {
					on: true,
					blur: false, escape: false
				}
			},
			hide: false,
			style: { classes: 'ui-tooltip-light ui-tooltip-rounded', tip: false },
			events: { 
				render: function(event, api) { $('#cancel-download', api.elements.content).click(api.hide); }
			}
		});
		$('#begin-download').click(function() {
			//var params='?e=1'+atlasSearch.lastParams;
			var params='?search_id='+$('#__SEARCH_ID').val();
			params+='&separator='+encodeURIComponent($("#separator").val());
			params+='&column='+encodeURIComponent($("#column").val());
			params+='&filename='+encodeURIComponent($("#filename").val());
			params+=atlasSearch.getCSVCols($("#scope-all").is(":checked"));
			var url='ajax_csv.php'+params;
			window.location=url;
			$("#cancel-download").click();
		});
	}
};

//support IE7+8
if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function (searchElement, fromIndex) {
      if ( this === undefined || this === null ) {
        throw new TypeError( '"this" is null or not defined' );
      }

      var length = this.length >>> 0; // Hack to convert object.length to a UInt32

      fromIndex = +fromIndex || 0;

      if (Math.abs(fromIndex) === Infinity) {
        fromIndex = 0;
      }

      if (fromIndex < 0) {
        fromIndex += length;
        if (fromIndex < 0) {
          fromIndex = 0;
        }
      }

      for (;fromIndex < length; fromIndex++) {
        if (this[fromIndex] === searchElement) {
          return fromIndex;
        }
      }

      return -1;
    };
}
