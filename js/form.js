$(document).ready(function() {
	atlasSearch.init();
	styleInput();
	initMap();
	$("#kommune").change(function() {
		var knr=$("#kommune option:selected").val()
		if (knr!='') {
			//disable the search button, reenabled getKommuneGraense->success
			selectCallback('kommune', knr);
			disableBtn('#searchBtn', false);
			getKommuneGraense(knr);
		}
	});
	$("#GenSpec").focus();
	$("#roedlist-input").click(function() {
		$("#roedliste-select").show();
	});
	$("#roedliste-close").click(function(e) {
		$("#roedliste-select").hide();
	});
	$("#naturtype-input").click(function() {
		$("#naturtype-select").show();
	});
	$("#naturtype-close").click(function(e) {
		$("#naturtype-select").hide();
	});
	$.getJSON("json/taxon.json", function(json) {
		$("#GenSpec").typeahead({
			source : json.lookup,
			items : 12
		});
	});
	$.getJSON("json/dkname.json", function(json) {
		$("#DKName").typeahead({
			source : json.lookup,
			items : 12
		});
	});
	$.getJSON("json/localitybasicString.json", function(json) {
		$("#localitybasicString").typeahead({
			source : json.lookup,
			items : 12
		});
	});
	if (perma!='false') {
		atlasSearch.permaSearch(perma);
	}
});
var fields = [];
function selectCallback(field, value) {
	fields[field]=value;
}
function getField(field) {
	value=fields[field];
	if (value===undefined) value='';
	return value;
}
function resetFields() {
	fields=[];
}
function disableBtn(btn, mode) {
	if (mode) {
		$(btn).removeAttr('disabled','disabled');
	} else {
		$(btn).attr('disabled','disabled');
	}
}
function styleInput() {
	$('body input[type=text]').addClass('ui-widget-content ui-corner-all ui-corner-top ui-corner-left ui-corner-tl ui-corner-all ui-corner-bottom ui-corner-left ui-corner-bl');
} 
function roedlisteCheck(item) {
	var list='';
	$("#roedliste-select input[type=checkbox]").each(function(index) {
		if ($(this).is(':checked')) {
			var id=$(this).attr('id');
			id=id.substring(id.indexOf('-')+1);
			if (list.length>0) list+='+';
			if (id=='all') id="(alle rødlistede)";
			list+=id.toUpperCase();
		}
	});
	if (list!='') {
		$("#roedlist-input").val(list);
	} else {
		$("#roedlist-input").val("Vælg kategori(er)");
	}
}
function naturtypeCheck() {
	var list='';
	$("#naturtype-select input[type=checkbox]").each(function(index) {
		if ($(this).is(':checked')) {
			var id=$(this).attr('id');
			id=id.substring(id.indexOf('-')+1);
			if (list.length>0) list+='+';
			list+=id.toUpperCase();
		}
	});
	if (list!='') {
		$("#naturtype-input").val(list);
	} else {
		$("#naturtype-input").val("Vælg kategori(er)");
	}
}


