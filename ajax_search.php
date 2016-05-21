<?
include('ajax_searchbase.php');

class AtlasSearch extends AtlasSearchBase {
	private $result;
	private $recordCount;
	private $DEBUG = false;

	// "antal arter" (til footer), kunne laves i SQL-kaldet 
	// men genereres udfra datasættet for at spare db ressourcer
	private $taxonUniqueCount;

	public function __construct() {
		parent::__construct();

		header('Content-Type: text/html; charset=ISO-8859-1');

		//stored search 
		if (isset($_GET['perma'])) {
			$this->vars=$this->getSearchRequest($_GET['perma']);
		}

		$this->drawScript();
		$this->evaluateParams();
		$this->performSearch();
		$this->drawTable();
		$this->processData();
	}

	private function drawScript() {
?>
<script type="text/javascript" src="js/ecology.js"></script>
<style type="text/css">
.DataTables_sort_wrapper {
	cursor: pointer;
	font-weight: bold;
	font-size: 13px;
	font-family : "arial, helvetica";
}
td.atlas-result-col {
	padding-right: 15px;
	vertical-align: top;
}
a.hover {
	text-decoration: none;
}
label {
	cursor: pointer;
}
.odd {
	background-color: #ebebeb;
}
#field-bar {
	font-size: 70%;
	font-weight: normal;
	width: 956px;
	padding-top: 5px;
	padding-bottom: 5px;
	float: left;
	clear: both;
	margin-top: 18px;
	height: 20px;
}
#field-bar label {
	vertical-align: middle;
	font-family: arial, helvetica;
	margin: 0px;
	padding: 0px;
	padding-right: 3px;
}
#field-bar input {
	vertical-align:middle;
}
img.result-table-img {
	height: 13px;
	border: 0;
}
#atlas-result-table {
	visibility: hidden;
}
#rendering {
	position: absolute;
	z-index: 100;
	left: 415px;
	top: 235px;
	text-align:center;
	width: auto:
	height: auto;
}
th {
	white-space: nowrap;
}
</style>
<script type="text/javascript">
var resultTable = null;
$(document).ready(function() {
	resultTable = $("#atlas-result-table").dataTable({
		sDom: 'RfrtiS',
		bJQueryUI: true,
		sScrollY: "540px",
		sWidth: "700px",
		bProcessing: false, //true,
        bDeferRender: false, //true,
		bSortClasses: false,
		aaSorting: [], //?
		oLanguage: { "sUrl": "DataTables-1.9.3/danish.txt" },
        bInfo: false,  
		bLengthChange: false,
        bFilter: false,
        bAutoWidth: false,
		asStripClasses:[],
		fnInitComplete: function(oSettings, json) {
			$("#atlas-result-table").css('visibility','visible');
			$("#rendering").hide();
			atlasSearch.auto();
		},
		/*
		fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
			$("#ajax-loader").attr('src',"glyph/ajax-loader.gif");
		},
		*/
		aoColumns: [ 
			//muligheder, links
			{"bSortable": false,
			 "sWidth" : "60px"
			},
			//latinsk navn		
			{"bVisible": true,
			 "sWidth": "200px"
			},
			//dansk navn
			{"bVisible": true,
			 "sWidth": "200px"
			},
			//lokalitet
			{"bVisible": true,
			 "sWidth": "200px"
			},
			//dato
			{"bVisible": true,
			 "sWidth": "70px",
			 "sType": "svampe-dato"
			},
			//finder / leg
			{"bVisible": true,
			 "sWidth": "50px"
			},
			//UTM
			{"bVisible": false,
			 "sWidth": "50px"
			},
			//status
			{"bVisible": false,
			 "sWidth": "50px"
			},
			//VegType
			{"bVisible": false,
			 "sWidth": "100px"
			},
			//associatedOrganism
			{"bVisible": false,
			 "sWidth": "100px"
			},
			//substrate
			{"bVisible": false,
			 "sWidth": "100px"
			},
			//AtlasIdNummer
			{"bVisible": false,
			 "sWidth": "100px"
			},
			//Det.
			{"bVisible": false,
			 "sWidth": "100px"
			},
			//CollNr
			{"bVisible": false,
			 "sWidth": "100px"
			},
			//lat
			{"bVisible": false,
			 "sWidth": "100px"
			},
			//long
			{"bVisible": false,
			 "sWidth": "100px"
			},
			//nøjagtighed
			{"bVisible": false,
			 "sWidth": "100px"
			}
		]
	});
	$("#result-count").html($("#count-message").val());
});
function showCol(col) {
	if (col==14) {
		resultTable.fnSetColumnVis(14, $("#col14").attr('checked')); 
		resultTable.fnSetColumnVis(15, $("#col14").attr('checked')); 
	} else {
		resultTable.fnSetColumnVis(col, $("#col"+col).attr('checked')); 
	}
	return false;
}
jQuery.extend( jQuery.fn.dataTableExt.oSort, {
"svampe-dato-pre": function (a) {
	var svampedato = a.split('/');
		if (svampedato[0].length==1) svampedato[0]='0'+svampedato[0];
		if (svampedato[1].length==1) svampedato[1]='0'+svampedato[1];
		return (svampedato[2] + svampedato[1] + svampedato[0]) * 1;
	},
	"svampe-dato-asc": function ( a, b ) {
		return ((a < b) ? -1 : ((a > b) ? 1 : 0));
	},
	"svampe-dato-desc": function ( a, b ) {
		return ((a < b) ? 1 : ((a > b) ? -1 : 0));
	}
});
if (typeof window.chrome === "object") {
	$("#perma-link").css('margin-top','4px');
}
function getPermaLink() {
	var id=$("#__SEARCH_ID").val();
	var link="http://svampe.dk/soeg/index.php?search_id="+id;
	var text="Nedenst&aring;ende link husker den aktuelle s&oslash;gning. <br>Kan videresendes i f.eks email<br><br>";
	var input='<input type="text" value="'+link+'" style="width:330px;font-size:14px;" onclick="this.focus();this.select()" readonly="readonly">';
	var html=text+input;
	return html;
}
$(document).ready(function() {
	var button='<button id="perma-link-button">Klik for at gemme s&oslash;gningen</button>';
	$("#perma-link").html(button);
	$("#perma-link-button").click(function() {
		$('<div/>').qtip({
			id: 'perma-box',
			content: {
				text: getPermaLink(),
				title: { text: 'Gem s&oslash;gning', button: false	}
			},
			position: { my: 'center', at: 'center', target: $("#perma-link-button") },
			show: {
				event: 'click', 
				ready: true, 
				modal: {
					on: true,
					blur: true, escape: true
				}
			},
			hide: true,
			style: { classes: 'ui-tooltip-light ui-tooltip-rounded', tip: false },
			events: { 
				render: function(event, api) { $('#cancel-download', api.elements.content).click(api.hide); }
			}
		});
	});
});
</script>
<?
	}

	private function footerButton($caption, $onclick) {
		$caption=$this->HTMLify($caption);
		echo '&nbsp;<input type="button" value="'.$caption.'&nbsp;&#9658;" onclick="'.$onclick.'">';
	}

	private function drawFooter() {
		echo '<div class="result-footer">';
		echo '<input type="button" value="&#9668; Ret s&oslash;gning" onclick="window.atlasSearch.back();" title="Tilbage til s&oslash;geformular">';
		echo '<span style="float:right;">';
		$this->footerButton('Fund kronologisk','atlasSearch.showChronoReport();');
		$this->footerButton('Økologi','Ecology.ecologyReport();');
		/*
		$this->footerButton('Vis søgeresultater som UTM-felter','window.atlasSearch.showUTMFindingsMap();');
		$this->footerButton('Vis søgeresultater på et Google-map','window.atlasSearch.showAllFindingsMap();');
		*/
		//$this->footerButton('Artsrigdom (beta)','window.atlasSearch.showDiversityMap();');
		/* */
		$this->footerButton('Tætheds-kort','window.atlasSearch.showHeatmap();');
		$this->footerButton('UTM-kort','window.atlasSearch.showUTMFindingsMap();');
		$this->footerButton('Prik-kort','window.atlasSearch.showAllFindingsMap();');

		echo '</span>';
		echo '</div>';
	}

	private function drawFieldBar() {
?>
<div id="field-bar" class="ui-state-default">
<input type="checkbox" id="col1" field="GenSpec" onclick="showCol(1);" checked="checked"><label for="col1" title="Latinsk navn">Latin</label>
<input type="checkbox" id="col2" field="DKName" onclick="showCol(2);" checked="checked"><label for="col2" title="Dansk navn">Dansk</label>
<input type="checkbox" id="col3" field="localitybasicString" onclick="showCol(3);" checked="checked"><label for="col3" title="Lokalitet">Lok.</label>
<input type="checkbox" id="col4" field="dato" onclick="showCol(4);" checked="checked"><label for="col4" title="Dato">Dato</label>
<input type="checkbox" id="col5" field="_AtlaslegInit" onclick="showCol(5);" checked="checked"><label for="col3" title="Finder initialer">Finder</label>
<input type="checkbox" id="col6" field="_UTM10" onclick="showCol(6);"><label for="col6" title="UTM10 koordinat">UTM</label>
<input type="checkbox" id="col7" field="Status" onclick="showCol(7);"><label for="col7" title="R&oslash;dliste status">Status</label>
<!-- // -->
<input type="checkbox" id="col8" field="VegType" onclick="showCol(8);"><label for="col8" title="Vegetationstype">Veg. type</label>
<input type="checkbox" id="col9" field="associatedOrganism" onclick="showCol(9);"><label for="col9" title="V&aelig;rt">V&aelig;rt</label>
<input type="checkbox" id="col10" field="Substrate" onclick="showCol(10);"><label for="col10" title="Substrat">Substrat</label>
<input type="checkbox" id="col11" field="AtlasIDnummer" onclick="showCol(11);"><label for="col11" title="Atlas ID-nummer">ID-nr</label>
<!-- Det, CollNr -->
<input type="checkbox" id="col12" field="Det" onclick="showCol(12);"><label for="col12" title="Bestemmer(e)">Det.</label>
<input type="checkbox" id="col13" field="CollNr" onclick="showCol(13);"><label for="col13" title="Internt samlingsnr.">CollNr.</label>
<!-- lat, long, nøjagtighed -->
<input type="checkbox" id="col14" field="AtlasUserLati" onclick="showCol(14);"><label for="col14" title="Lat/lng koordinater">Lat/Lng</label>
<input type="checkbox" id="col16" field="AtlasUserPrec" onclick="showCol(16);"><label for="col16" title="Stedbestemmelse N&oslash;jagtighed">N&oslash;j.</label>
<button id="save-as-csv" onclick="atlasSearch.saveAsCSV();" title="Gem s&oslash;geresultater som CSV">&#9660;CSV</button>
</div>
<?
	}
		
	private function drawTable() {
		echo '<div id="rendering"><img id="ajax-loader" src="glyph/ajax-loader.gif"></div>';

		if ($this->DEBUG) {
			echo '<small>'.$this->last_execution_time.' ..</small><br>';
			echo '<span style="font-family: arial, helvetica;font-size:12px;padding:5px;">'.$this->searchDesc.'</span><br>';
		}
		//set kolonner synlige / usynlige	
		$this->drawFieldBar();

		echo '<table id="atlas-result-table" style="">';
		echo '<thead><tr>';
		echo '<th style="width:45px;"></th>'; //handlinger, ikke sorterbar
		echo '<th style="width:190px;">Latinsk navn</th>';
		echo '<th style="width:190px;">Dansk navn</th>';
		echo '<th style="width:190px;">Lokalitet</th>';
		echo '<th style="width:90px;">Dato</th>';
		echo '<th style="width:60px;">Find.</th>';
		//usynlige kolonner
		echo '<th style="width:60px;">UTM</th>';
		echo '<th style="width:60px;">Status</th>';
		//nye øko-kolonner
		echo '<th style="width:100px;">Vegetationstype</th>';
		echo '<th style="width:100px;">V&aelig;rt</th>';
		echo '<th style="width:100px;">Substrat</th>';
		//
		echo '<th style="width:100px;">ID-nummer</th>';
		echo '<th style="width:100px;">Det.</th>';
		echo '<th style="width:100px;">CollNr.</th>';
		//latlong
		echo '<th style="width:100px;">Lat.</th>';
		echo '<th style="width:100px;">Long.</th>';
		echo '<th style="width:100px;">N&oslash;jagtighed</th>';
		//

		echo '</tr></thead>';
	}

	private function performSearch() {
		//echo '<br><br><br>'.$this->baseSQL;
		$this->fileDebug($this->baseSQL);
		$this->baseSQL.=' order by atlas.AtlasLNR desc';
		mysql_set_charset('Latin1');
		$this->result=$this->query($this->baseSQL);
	}

	private function getActionCol($row) {
		$html='<small style="white-space: nowrap;">'; 
		$html.='<a href="#" onclick="window.atlasSearch.showDetail(&quot;'.$row['AtlasIDnummer'].'&quot;);">Vis</a>';

		$html.='&nbsp;&nbsp;<a href="rapportside.php?DKIndex='.$row['DKIndex'].'" title="Vis artens rapportside" target=_blank>';
		$html.='<img src="glyph/rapportIkon.png" class="result-table-img"></a>';

		if ($row['AtlasPic']=='1') {
			$html.='&nbsp;<img src="glyph/camera.gif" title="Foto" alt="Foto" class="result-table-img"/>';
		}

		if ($row['MycokeyIDDKWebLink']!='') {
			$html.='&nbsp;<a href="'.$row['MycokeyIDDKWebLink'].'" title="Vis arten i MycoKey">';
			$html.='<img src="http://www.svampe.dk/atlas/GeneralGraphics/MycoKeyIkon.png" class="result-table-img"></a>';
		}

		$html.='</small>';
		return $html;
	}

	// generates rows for the result-table, count number of records
	// count numbers of unique species
	private function processData() {
		if ($this->center) {
			echo '<input type="hidden" id="center-lat" value="'.$this->center->x.'">';
			echo '<input type="hidden" id="center-long" value="'.$this->center->y.'">';
			echo '<input type="hidden" id="center-code" value="'.$this->center_code.'">';
		}

		$this->recordCount=0;
		$taxons = array();
		$ok=true;
		
		$count=0;
		$skipped=0;

		echo '<tbody>';
		while ($row=mysql_fetch_array($this->result)) {
			if ($this->isIncludable($row)) {
				$taxons[]=$row['genSpec'];
				$this->recordCount++;
				if ($count<4000) {
					$count=$count+1;
					//add latlong, id, year, utm, utmpoly to tr, so we can use them in the allFindingsMap
					echo '<tr ';
					echo 'lat="'.$row['AtlasUserLati'].'" ';
					echo 'lng="'.$row['AtlasUserLong'].'" ';
					echo 'id="'.$row['AtlasIDnummer'].'" ';
					echo 'year="'.$row['date_year'].'" ';
					echo 'utm="'.$row['UTM10'].'" ';
					echo 'leg="'.$row['AtlaslegInit'].'" ';
					$genspec= preg_replace('/[\x00-\x08\x0B\x0C\x0E]/u', '', $row['genSpec']);
					echo 'genspec="'.$genspec.'" ';
					echo '>';

					echo '<td>'.$this->getActionCol($row).'</td>';

					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$row['genSpec'].'</td>';
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$row['DKName'].'</td>';
					$loc=$row['localityBasicString'];
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$loc.'</td>';
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$row['date_day'].'/'.$row['date_month'].'/'.$row['date_year'].'</td>';
					echo '<td class="mainTextDarkGrey10 atlas-result-col" style="text-align:right;">'.$row['AtlaslegInit'].'</td>';
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$row['UTM10'].'</td>';
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$row['Status'].'</td>';
					//
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$row['VegType'].'</td>';
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$row['associatedOrganism'].'</td>';
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$row['Substrate'].'</td>';
					//
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$row['AtlasIDnummer'].'</td>';
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$row['Det'].'</td>';
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$row['CollNr'].'</td>';
					//
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$row['AtlasUserLati'].'</td>';
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$row['AtlasUserLong'].'</td>';

					//hvis 0 eller blank
					/*
					$accurancy = ($row['AtlasGeoRef']==0) ? 'Atlas' : 'Bruger';
					$accurancy.= ' ('.$row['AtlasUserPrec'].'m)';
					echo '<td class="mainTextDarkGrey10 atlas-result-col">'.$accurancy.'</td>';
					*/
					$accurancy='';
					if ($row['AtlasGeoRef']==0) {
						$accurancy=$row['AtlasUserPrec'].'m';
					}
					echo '<td class="mainTextDarkGrey10 atlas-result-col" style="text-align:right;">'.$accurancy.'</td>';

					echo '</tr>';
				}
			} else {
				$skipped=$skipped+1;
			}
		}
		echo '</tbody>';
		echo '</table>';

		$taxon=array_unique($taxons);;
		$this->taxonUniqueCount=count($taxon);

		$numrows=$count;
		$arter=($this->taxonUniqueCount==1) ? $this->taxonUniqueCount.' art' : $this->taxonUniqueCount.' arter';

		if ($numrows!=4000) {
			$msg=$numrows.' fund fordelt p&aring; '.$arter.' ('.$skipped.')';
		} else {
			//$msg=mysql_num_rows($this->result).' fund i alt, fordelt p&aring; '.$arter.' (4000 vises)';
			$msg=$this->recordCount.' fund i alt, fordelt p&aring; '.$arter.' (4000 vises)';
		}

		echo '<input type="hidden" id="count-message" value="'.$msg.'"/>';
		echo '<input type="hidden" id="num-rows" value="'.$numrows.'"/>';

		$this->drawFooter();
	}
}

$search = new AtlasSearch();

?>
