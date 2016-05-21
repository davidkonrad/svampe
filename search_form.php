<? include('common/arrays.php');?>

<!-- mysql search begin -->
<div id="formCnt">
<div id="bodyTextArea">
<span class="poly-buttons">
	<input type="button" id="poly-zoom" value="Zoom ind til polygon" onclick="zoomToPolygon();"/>
	<input type="button" id="poly-reset" value="Nulstil polygon" onclick="resetPolygon();"/>&nbsp;&nbsp;<br/>
	<span id="poly-size" class=""></span>
</span>
<form action="" method="post" name="mysqlSearch" id="mysqlSearch"> 
<br/><br/><hr/>
<table width="98%" border="0" align="center" cellpadding="3" cellspacing="2"> 
<tr> 
	<td align="right" valign="middle" class="mainTextDarkGrey">Latinsk navn:</td> 
	<td valign="middle" class="input"><input name="GenSpec" type="text" size="25" id="GenSpec"/></td> 
</tr>
<tr> 
	<td align="right" valign="middle" class="mainTextDarkGrey">Dansk navn:</td> 
	<td valign="middle" class="input"> 
	<input type="text" size="25" id="DKName" name="DKName"/></td> 
</tr> 
<tr><td colspan="2"><hr/></td></tr>
<tr> 
	<td align="right" valign="middle" class="mainTextDarkGrey"> Initialer:</td> 
	<td valign="middle" class="input">
	<input name="initialer" type="text" size="10" id="initialer"/>
	</td> 
</tr>
<tr><td colspan="2"><hr/></td></tr>
<tr> 
	<td align="right" valign="middle" class="mainTextDarkGrey">Databasenummer:</td> 
	<td valign="middle" class="input"><input name="atlasID"  type="text" size="15" id="atlasID"/> 
	</td>
</tr>
<tr><td colspan="2"><hr></td></tr>
<tr>		
	<td align="right" class="mainTextDarkGrey style2">Dato (år)</td>
	<td class="input style2 mainTextDarkGrey">
	<input type="text" class="datoInterval" size="4" name="year" style="width:40px;" id="year"/>
	<small class="datoInterval">&nbsp;Måned&nbsp;</small>
	<input type="text" class="datoInterval" size="2" name="month" style="width:40px;" id="month"/>
	<small class="datoInterval">&nbsp;Dag&nbsp;</small>
	<input type="text" class="datoInterval" size="2" name="day" style="width:40px;" id="day"/>
	<span style="float:right;font-weight:normal;margin-right:20px;">
	<input type="checkbox" id="exact-date" tabindex="-1"/><label for="exact-date" title="Ved &quot;eksakt&quot; dato-søgning søges der på år, måned og dag som simple tal, uden interval. Standard er søgning på felterne sammensat som fortløbende kalenderdatoer. Eksakt søgning kan bruges til f.eks at søge på en bestemt måned uden angivelse af år, eller fremsøge fund med skæve datoer, som 0" style="position:relative;top:-1px;cursor:pointer;">Eksakt</label>
	</span>	
	</td>
</tr>
<tr>		
	<td align="right" class="mainTextDarkGrey style2">Til dato (år)</td>
	<td class="input style2 mainTextDarkGrey">
	<input type="text" class="datoInterval" size="4" name="to-year" style="width:40px;" id="to-year"/>
	<small class="datoInterval">&nbsp;Måned&nbsp;</small>
	<input type="text" class="datoInterval" size="2" name="to-month" style="width:40px;" id="to-month"/>
	<small class="datoInterval">&nbsp;Dag&nbsp;</small>
	<input type="text" class="datoInterval" size="2" name="to-day" style="width:40px;" id="to-day"/>
	</td>
</tr>
<tr><td colspan="2"><hr/></td></tr>
<tr>
	<td align="right" class="mainTextDarkGrey style2">UTM-felt:</td>
	<td class="input"><input name="utm" type="text" id="utm" size="5" maxlength="4"/> </td>
</tr>
<tr> 
	<td align="right" valign="middle" class="mainTextDarkGrey style2">Lokalitet:</td> 
	<td valign="middle" class="input"><input name="localitybasicString" type="text" size="22" id="localitybasicString" data-provide="typeahead" />
	</td> 
</tr> 
<tr> 
	<td align="right" valign="middle" class="mainTextDarkGrey style2">Kommune:</td> 
	<td valign="middle" class="input">
	<select id="kommune" name="kommune" style="height:24px;"><option value="--">Vælg kommune</option></select>
	</td> 
</tr> 
<tr><td colspan="2"><hr/></td></tr>

<tr> 
	<td align="right" valign="middle" class="mainTextDarkGrey"></td> 
	<td valign="top" class="input">
	<span style="float:left;width:150px;clear:left;">
		<small class="mainTextDarkGrey" style="font-size:10px;">Rødlistestatus</small><br>
		<input type="text" readonly="readonly" id="roedlist-input" value="Vælg kategori(er)" size="15" style="cursor:pointer;">
	</span>
	<span style="float:left;width:150px;margin-left:5px;">
		<small class="mainTextDarkGrey" style="font-size:10px;">Naturtype</small><br>
		<input class="input" type="text" readonly="readonly" id="naturtype-input" value="Vælg kategori(er)" size="15" style="cursor:pointer;">
	</span>
	</td> 
</tr> 

<tr><td colspan="2"><hr/></td></tr>
</table> 
</form> 
<input type="button" onclick="atlasSearch.submit();" id="searchBtn" value="Søg" style="width:200px;padding:5px;" />
<input type="button" onclick="atlasSearch.reset();" value="Nulstil" style="position:relative;top:5px;"/>

<!-- rødliste popup -->
<span id="roedliste-select" class="shadow" style="display:none;">
	<span style="float:right;">
	<button id="roedliste-close">OK</button>
	</span>
	<input type="checkbox" id="red-all" onchange="roedlisteCheck(this);"><label for="red-all"><span class="smallTextRed">&nbsp;(alle rødlistede)</span> Inkluderer NT,VU,EN,CR samt RE</label><br>
	<input type="checkbox" id="red-re" onchange="roedlisteCheck(this);"><label for="red-re"><span class="smallTextBlack">&nbsp;RE</span> (forsvundet)</label><br/>
	<input type="checkbox" id="red-cr" onchange="roedlisteCheck(this);"><label for="red-cr"><span class="smallTextRed">&nbsp;CR</span> (kritisk truet)</label><br /> 
	<input type="checkbox" id="red-en" onchange="roedlisteCheck(this);"><label for="red-en"><span class="smallTextRed">&nbsp;EN</span> (moderat truet)</label><br /> 
	<input type="checkbox" id="red-vu" onchange="roedlisteCheck(this);"><label for="red-vu"><span class="smallTextRed">&nbsp;VU</span> (sårbar)</label><br /> 
	<input type="checkbox" id="red-nt" onchange="roedlisteCheck(this);"><label for="red-nt"><span class="smallTextRed">&nbsp;NT</span> (næsten truet)</label><br /> 
	<input type="checkbox" id="red-lc" onchange="roedlisteCheck(this);"><label for="red-lc"><span class="taxonList">&nbsp;LC</span> (ikke truet)</label><br /> 
	<input type="checkbox" id="red-dd" onchange="roedlisteCheck(this);"><label for="red-dd"><span class="taxonList">&nbsp;DD</span> (ikke vurderet p.g.af utilstrækkelige data)</label><br /> 
	<input type="checkbox" id="red-na" onchange="roedlisteCheck(this);"><label for="red-na"><span class="taxonList">&nbsp;NA</span> Ikke mulig (at vurdere)</label><br /> 
	<input type="checkbox" id="red-ne" onchange="roedlisteCheck(this);"><label for="red-ne"><span class="taxonList">&nbsp;NE</span> Ikke bedømt</label><br/>
	<br /> 
	&nbsp;Læs mere om rødlistekategorierne på <a href="http://www2.dmu.dk/1_Om_DMU/2_Tvaer-funk/3_fdc_bio/projekter/redlist/redliststructure.asp" target="_blank">DMUs hjemmeside</a>
</span>

<!-- naturtype popup -->
<span id="naturtype-select" class="shadow" style="display:none;">
	<span style="float:right;">
	<button id="naturtype-close">OK</button>
	</span>
<?
global $naturtyper;
foreach ($naturtyper as $naturtype) {
	$code='nat-'.$naturtype['code'];
	echo '<input type="checkbox" id="'.$code.'" onchange="naturtypeCheck();">';
	echo '<label for="'.$code.'"><span class="smallTextMedGreen">&nbsp;'.$naturtype['code'].'</span>&nbsp;-&nbsp;'.$naturtype['desc'].'</label>';
	if ($naturtype['url']!='') {
		echo '&nbsp;[<a href="'.$naturtype['url'].'" title="Vis info, åbner i nyt vindue" target=_blank>link</a>]';
	}
	echo '<br/>';
	echo "\n";
}
?>
</span>

<div id="bodyRightArea"><div id="map"></div></div>

</div> <!-- bodyTextArea -->
<div id="frontpage-footer" class="mainTextDarkGrey" style="font-size:10px;padding-top:2px;">
   <a href="http://www.svampe.dk/" title="Svampe.dk forside">Svampe.dk forside</a>&nbsp;&nbsp;&#9679;&nbsp;&nbsp;
   <a href="http://www.svampeatlas.dk/" title="Svampeatlas">Svampeatlas</a>&nbsp;&nbsp;&#9679;&nbsp;&nbsp;
   <a href="http://www.svampe.dk/atlas/index.php" title="Log på svampeatlas">Login</a>
</div>

</div> <!-- formCnt -->
<!-- mysql search end -->
