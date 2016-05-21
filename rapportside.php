<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('common/Db.php');
include_once('FX/FX.php'); 
include_once('FX/server_data.php');

include('atlaspage.php');

class Report extends AtlasPage {
	private $DKIndex = '';
	private $mycoImage = array();
	private $taxonInfo = array();
	private $hostInfo = array();
	private $imageInfo = array();
	//
	private $json_months = ''; //json fund over måneder [{ "jan" : "45"}, {"feb" : "67" osv
	private $json_decade = '';
	private $json_hosts = '';
	private $host_names = array();
	public $title;
	public $udbredelseText = '';
	
	public function __construct() {
		parent::__construct();
		if (!isset($_GET['DKIndex'])) return false;

		$this->DKIndex=$_GET['DKIndex'];

		$this->getTitle();
		$this->getMycoImage();
		$this->getTaxonInfo();
		$this->getHostInfo();
		$this->getImagesInfo();
		$this->udbredelseText=$this->getRegionerUdbredelseText();

		$this->meta=ucfirst($this->title).' '.lcfirst(str_replace('I ','i ',str_replace(' :','',strip_tags($this->udbredelseText))));

		$this->draw();
	}

	private function cleanStr($s) {
		$s=str_replace('&#248;','ø',$s);
		$s=str_replace('&#230;','æ',$s);
		$s=str_replace('&#229;','å',$s);
		return $s;
	}

	private function script() {
		$sh='';
		$lo='';
		foreach ($this->host_names as $n) {
			$n=$this->cleanStr($n);
			if ($sh!='') $sh.=',';
			$sh.='"'.substr($n,0,4).'"';
			if ($lo!='') $lo.=',';
			$lo.='"'.$n.'"';
		}
		$sh='['.$sh.']';
		$lo='['.$lo.']';
?>
<script type="text/javascript">
function adjust() {
	var ih=$("#images").height();
	if (ih>0) {
		var h=$("#bodyContainer").innerHeight();
		//console.log(parseInt(ih),parseInt(h));
		$("#bodyContainer").height(parseInt(ih)+parseInt(h)+'px');
	}
	var h=$("#left").height();
	if (h>800) $("#leftright").height(h+'px');
}
$(document).ready(function() {
	reportMap(<? echo $this->DKIndex;?>);

	var months = [<? echo $this->json_months;?>];
	var monthtext = ['Jan','Feb',' Mar','Apr','Maj','Jun','Jul','Aug','Sep','Okt','Nov','Dec'];

	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Måned');
	data.addColumn('number', 'Fund');

	for (var i = 0; i < months.length; i++) {
		data.addRow([monthtext[i], months[i]]);            
	}

	var options = {
		'title':'Fordeling af fund over årets måneder',
		'titlePosition' : 'out',
		'colors' : ['#A52A2A', '#A52A2A'],
		'vAxis' : { 'textPosition' : 'out', 'gridlines' : { 'count' : 4 }, "viewWindow" : { "min": 0 } },
		'hAxis' : { 'slantedTextAngle' : 90, 'maxAlternation' : 1, "viewWindow" : { "min": 0 } },
		'width': 400,
		'height': 180,
		'legend' : { 'position' : 'none'} ,
		'backgroundColor' : 'transparent' ,
		'chartArea' : { left: 30, top: 20, width: "100%", height: "65%"}
		//'chartArea': {left:0,top:0,width:"100%"}
	};

	var chart = new google.visualization.ColumnChart(document.getElementById('fund_per_month'));
	chart.draw(data, options);

	var decades = [<? echo $this->json_decade;?>];
	var dectext = ['1920','1930','1940','1950','1960','1970','1980','1990','< 05','< 09','> 09'];
	var dectextlong = ['1920-1929','1930-1939','1940-1949','1950-1959','1960-1969','1970-1979','1980-1989','1990-1999','2000-2005','2000-2008','2009 og frem'];

	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Periode');
	data.addColumn('number', 'Fund');

	for (var i = 0; i < decades.length; i++) {
		data.addRow([dectextlong[i], decades[i]]);            
	}

	var options = {
		'title':'Fordeling af fund over 10-års perioder',
		'titlePosition' : 'out',
		'colors' : ['#A52A2A', '#A52A2A'],
		'vAxis' : { 'textPosition' : 'out', 'gridlines' : { 'count' : 6 } },
		'hAxis' : { 'slantedTextAngle' : 45, 'maxAlternation' : 1 },
		'width': 400,
		'height': 170,
		'legend' : { 'position' : 'none'} ,
		'backgroundColor' : 'transparent',
		'chartArea' : { left: 40, top: 20, width: "100%", height: "115px"}
		//'chartArea': {left:0,top:0,width:"100%"}
	};

	var chart = new google.visualization.ColumnChart(document.getElementById('fund_per_decade'));
	chart.draw(data, options);

	var hostnames_full = <? echo $lo;?>;
	var hostnames_short = <? echo $sh;?>;
	var hosts = [<? echo $this->json_hosts;?>];

	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Vært');
	data.addColumn('number', 'Fund');

	for (var i = 0; i < hosts.length; i++) {
		data.addRow([hostnames_full[i], hosts[i]]);            
	}

	var options = {
		'title':'Fordeling på de 10 hyppigste værter',
		'titlePosition' : 'out',
		'colors' : ['#A52A2A', '#A52A2A'],
		'vAxis' : { 'textPosition' : 'out', 'gridlines' : { 'count' : 4 } },
		'hAxis' : { 'slantedTextAngle' : 45, 'maxAlternation' : 1 },
		'width': 400,
		'height': 170,
		'legend' : { 'position' : 'none'} ,
		'backgroundColor' : 'transparent',
		'chartArea' : { left: 30, top: 20, width: "100%", height: "70%"}
		//'chartArea': {left:0,top:0,width:"100%"}
	};

	var chart = new google.visualization.ColumnChart(document.getElementById('top_hosts'));
	chart.draw(data, options);

	//setTimeout('adjust()',1000);
});
</script>
<?	
	}

	private function getTitle() {
		$SQL='select DKName, GenSpec, Author from atlasTaxon where DKIndex='.$this->DKIndex;
		mysql_set_charset('utf8');
		$row=$this->getRow($SQL);
		$name=$row['GenSpec'];
		if ($row['Author']!='') $name.=' '.$row['Author'];

		if ($row['DKName']!='') {
			$this->title=$row['DKName'].' ('.$name.')';
		} else {
			$this->title=$name;
		}
	}

	private function getMycoImage() {
		global $serverIP, $webCompanionPort;
		$soeg=new FX($serverIP,$webCompanionPort);  
		$soeg->SetDBData('Svampefund.fmp12','MycoKeyImageTable', '1'); 
		$soeg->SetDBPassword('logpass','loguser');
		$soeg->AddDBParam('MycoTaxon::DKIndex', $this->DKIndex);
		$test = $soeg->FMFind();
		foreach ($test['data'] as $key=>$this->mycoImage);
	}

	private function getTaxonInfo() {
		global $serverIP, $webCompanionPort;
		$soeg=new FX($serverIP,$webCompanionPort);  
		$soeg->SetDBData('Svampefund.fmp12','MycoTaxon', '1'); 
		$soeg->SetDBPassword('logpass','loguser');
		$soeg->AddDBParam('MycoTaxon::DKIndex', $this->DKIndex);
		$test=$soeg->FMFind();
		foreach ($test['data'] as $key=>$this->taxonInfo);
		//$this->debug($this->taxonInfo);
	}

	private function getHostInfo() {
		global $serverIP, $webCompanionPort;
		$soeg=new FX($serverIP,$webCompanionPort);  
		$soeg->SetDBData('Svampefund.fmp12','Host', 'all'); 
		$soeg->SetDBPassword('logpass','loguser');
		$soeg->AddDBParam('DKSpeciesIndex', $this->DKIndex);
		$soeg->AddDBParam('UnikHost', 1);
		$soeg->AddSortParam('CountHost', 'descend');
		$test=$soeg->FMFind();
		//$this->debug($test);
		foreach ($test['data'] as $arr) {
			//$this->debug($arr);
			$this->hostInfo[]=$arr;
		}
	}

	private function isGodkendt($AtlasRecID) {
		$SQL='select AtlasForumVali from atlasFund where AtlasIDnummer="'.$AtlasRecID.'"';
		$row=$this->getRow($SQL);
		return ($row['AtlasForumVali']=='Godkendt');
	}

	private function getImagesInfo() {
		global $serverIP, $webCompanionPort;
		$soeg=new FX($serverIP,$webCompanionPort);  
		$soeg->SetDBData('Svampefund.fmp12','wwwPics', 'all'); 
		$soeg->SetDBPassword('logpass','loguser');
		$soeg->AddDBParam('MycoTaxon current use link::DKIndex', $this->DKIndex);
		$test=$soeg->FMFind();
		foreach ($test['data'] as $arr) {
			if ($this->isGodkendt($arr['AtlasRecID'][0])) {
				$this->imageInfo[]=$arr;
			}
		}
	}

	private function getFundPerDecade() {
		$SQL='select '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_year>=1920 and date_year<1930 and AtlasForumVali="Godkendt") as d1920, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_year>=1930 and date_year<1940 and AtlasForumVali="Godkendt") as d1930, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_year>=1940 and date_year<1950 and AtlasForumVali="Godkendt") as d1940, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_year>=1950 and date_year<1960 and AtlasForumVali="Godkendt") as d1950, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_year>=1960 and date_year<1970 and AtlasForumVali="Godkendt") as d1960, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_year>=1970 and date_year<1980 and AtlasForumVali="Godkendt") as d1970, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_year>=1980 and date_year<1990 and AtlasForumVali="Godkendt") as d1980, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_year>=1990 and date_year<2000 and AtlasForumVali="Godkendt") as d1990, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_year>=2000 and date_year<2005 and AtlasForumVali="Godkendt") as d2000, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_year>=2005 and date_year<2009 and AtlasForumVali="Godkendt") as d2005, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_year>=2009 and AtlasForumVali="Godkendt") as d2009 ';
		//echo $SQL;
		$row=$this->getRow($SQL);

		$this->json_decade.=$row['d1920'].', ';	
		$this->json_decade.=$row['d1930'].', ';	
		$this->json_decade.=$row['d1940'].', ';	
		$this->json_decade.=$row['d1950'].', ';	
		$this->json_decade.=$row['d1960'].', ';	
		$this->json_decade.=$row['d1970'].', ';	
		$this->json_decade.=$row['d1980'].', ';
		$this->json_decade.=$row['d1990'].', ';	
		$this->json_decade.=$row['d2000'].', ';	
		$this->json_decade.=$row['d2005'].', ';		
		$this->json_decade.=$row['d2009'];	

		echo '<div id="fund_per_decade" class="diagram" style="height:170px;"></div>';
	}
	
	private function getFundPerMonth() {
		$SQL='select '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_month=1 and AtlasForumVali="Godkendt") as jan, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_month=2 and AtlasForumVali="Godkendt") as feb, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_month=3 and AtlasForumVali="Godkendt") as mar, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_month=4 and AtlasForumVali="Godkendt") as apr, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_month=5 and AtlasForumVali="Godkendt") as maj, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_month=6 and AtlasForumVali="Godkendt") as jun, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_month=7 and AtlasForumVali="Godkendt") as jul, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_month=8 and AtlasForumVali="Godkendt") as aug, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_month=9 and AtlasForumVali="Godkendt") as sep, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_month=10 and AtlasForumVali="Godkendt") as okt, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_month=11 and AtlasForumVali="Godkendt") as nov, '.
		'(select count(*) from atlasFund where _DKIndex='.$this->DKIndex.' and date_month=12 and AtlasForumVali="Godkendt") as de ';

		$row=$this->getRow($SQL);
	
		$this->json_months='';
		$this->json_months.=$row['jan'].', ';
		$this->json_months.=$row['feb'].', ';
		$this->json_months.=$row['mar'].', ';
		$this->json_months.=$row['apr'].', ';
		$this->json_months.=$row['maj'].', ';
		$this->json_months.=$row['jun'].', ';
		$this->json_months.=$row['jul'].', ';
		$this->json_months.=$row['aug'].', ';
		$this->json_months.=$row['sep'].', ';
		$this->json_months.=$row['okt'].', ';
		$this->json_months.=$row['nov'].', ';
		$this->json_months.=$row['de'];

		echo '<div id="fund_per_month" class="diagram"></div>';
	}
		
	private function getFundCount() {
		$SQL='select count(*) as c from atlasFund where _DKIndex='.$this->DKIndex.' and AtlasForumVali="Godkendt"';;
		$row=$this->getRow($SQL);
		return $row['c'];
	}

	private function drawInfoText($desc, $text, $isRL=false) {  //$br=true) {
		/*
		echo '<span class="desc">'.$desc.'</span><span class="desc-text"> : '.$text.'</span><br/>';
		if ($br) echo '<br/>';
		*/
		echo '<p class="mainTextDarkGrey10">';
		if ($isRL) {
			echo '<span class="mainTextDarkGrey10Caps">'.$desc.'</span>';
		} else {
			echo $desc;
		}
		echo ': ';
		echo '<span class="mainTextDarkGrey10Thin">'.$text.'</span>';
		echo '</p>';
	}

	private function getUdbredelseText($caption, $revcaption, $array) {
		if (count($array)==0) return '';
		$text=($caption!='') ? '<b>'.$caption.'</b> : ' : '';
		for ($i=0;$i<count($array);$i++) {
			if (($i==count($array)-1) && (count($array)>1)) {
				$text.=' samt ';
				if ($array[$i]['region']!='øer i Kattegat') {
					$ord=explode(' ',$array[$i]['region']);
					for ($o=1;$o<count($ord);$o++) {
						$text.=$ord[$o];
						if ($o<count($ord)-1) $text.=' ';
					}
				} else {
					$text.=$array[$i]['region'];
				}
			} else {
				if ($i!=0) $text.=', ';
				$text.=($i==0) ? ucfirst($array[$i]['region']) : $array[$i]['region'];
			}
			if ($array[$i]['lokation']<4) {
				if ($revcaption!='') {
					$text.=($array[$i]['lokation']==1) ? ' (enkelt lokalitet)' : ' ('.$revcaption.')';
				}
			}
		}
		$text.='. ';
		return $text;
	}

	private function getRegionerUdbredelseText() {
		include('common/UTMRegioner.php');
		$r=array(); //array of array[lokation]+[count]
		$SQL='select _UTM10, _AtlasLocID from atlasFund where _DKIndex='.$this->DKIndex.' and AtlasForumVali="Godkendt"';
		$result=$this->query($SQL);
		$fund=mysql_numrows($result);
		while ($row = mysql_fetch_array($result)) {
			if (($row['_UTM10']!='') && ($row['_UTM10']!='null')) {
				//be aware of incorrect (or missing) UTM definitions
				if (isset($UTMRegioner[$row['_UTM10']])) {
					$region=$UTMRegioner[$row['_UTM10']];
					$regionarray = explode(', ',$region);
					foreach($regionarray as $region) {
						if (isset($r[$region])) {
							$r[$region]['count']++;
							$r[$region]['lokation'][]=$row['_AtlasLocID'];
						} else {
							$r[$region]['count']=1;
							$r[$region]['lokation']=array();
							$r[$region]['lokation'][]=$row['_AtlasLocID'];
						}
					}
					$r[$region]['lokation']=array_unique($r[$region]['lokation']);
				}
			}
		}
		arsort($r);
		$max=0;
		foreach ($r as $rk=>$rv) {
			if ($r[$rk]['count']>$max) $max=$r[$rk]['count'];
		}

		$pct=array();
		foreach ($r as $rk=>$rv) {
			$pct[$rk]=($r[$rk]['count']/$max)*100;
		}
		
		$r90=array();
		$r10=array();
		$r1=array();
		foreach ($pct as $rk=>$rv) {
			if ($rv>=10) {
				$r90[]=array('region'=>$regionNavne[$rk], 'lokation'=>count($r[$rk]['lokation']));
			} elseif ($rv>1) {
				$r10[]=array('region'=>$regionNavne[$rk], 'lokation'=>count($r[$rk]['lokation']));
			} else {
				$r1[]=array('region'=>$regionNavne[$rk], 'lokation'=>count($r[$rk]['lokation']));
			}
		}

		//$text=$this->getUdbredelseText('udbredt', 'spredte fund', $r90 );
		$text=$this->getUdbredelseText('Forekommer', 'spredte fund', $r90 );
		$text.=$this->getUdbredelseText('Spredte forekomster', 'enkelte fund', $r10);
		$text.=$this->getUdbredelseText('Enkelte forekomster', 'sjælden', $r1);

		$findesej=array();
		foreach ($regionNavne as $rk=>$rv) {
			if (!array_key_exists($rk, $pct)) $findesej[]=array('region'=>$regionNavne[$rk], 'lokation'=>0);
		}
		if (count($findesej)>0) $text.=$this->getUdbredelseText('Ikke fundet', '', $findesej);

		return $text;
	}				

	private function drawMap() {
		echo '<div id="report-map"></div>';
		echo '<div id="legend">';
		echo '<b>Signaturforklaring</b><br/>';
		echo '<img src="glyph/blue12.png" alt=""/>&nbsp;2009- (atlasperioden)';
		echo '&nbsp;&nbsp;<img src="glyph/orange12.png" alt=""/>&nbsp;1991-2008';
		echo '&nbsp;&nbsp;<img src="glyph/yellow12.png" alt=""/>&nbsp;-1991';
		echo '</div>';
	}

	private function getFundDate($AtlasRecID) {
		$SQL='select date_day, date_month, date_year from atlasFund where AtlasIDnummer="'.$AtlasRecID.'"';
		$row=$this->getRow($SQL);
		return $row['date_day'].'/'.$row['date_month'].'/'.$row['date_year'];
	}

	private function drawImageThumbs() {
		$dates = array();
		foreach($this->imageInfo as $image) {
			$AtlasRecID=$image['AtlasRecID'][0];

			if (!isset($dates[$AtlasRecID])) {
				$dates[$AtlasRecID]=$this->getFundDate($AtlasRecID);
			}
			
			if ($image['Y'][0]<2012) {
				//$src='http://130.225.211.119/svampeatlas/uploads/'.$image['PicID'][0];
				$src='http://192.38.112.99/svampeatlas/uploads/'.$image['PicID'][0];
			} else {
				$src='http://svampe.dk/atlas/uploads/'.$image['PicID'][0].'.JPG';
			}

			//$dato=$image['D'][0].'/'.$image['M'][0].'/'.$image['Y'][0];
			echo '<div class="img-cnt">';
			echo '<a href="'.$src.'" title="Se stor version (åbner i nyt vindue / tab)" target=_blank>';
			echo '<img class="thumb" src="'.$src.'" alt=""/>';
			echo '</a>';
			echo '<br/>&nbsp;<b>'.$image['MatchRecIDPicBase::Leg'][0].'</b>';
			echo '<br/>&nbsp;'.$dates[$AtlasRecID];//dato;
			echo '<br/>&nbsp;<a href="index.php?AtlasRecID='.$image['AtlasRecID'][0].'" target=_blank title="Gå til fundets detaljeside">'.$image['AtlasRecID'][0].'</a>';
			echo '</div>';
		}
	}
	
	private function drawLeft() {
		//$this->debug($this->taxonInfo);

		echo '<div class="left" id="left">';
		echo '<div class="top-img"><br/>';
		if (count($this->mycoImage)>0) {
			echo '<img src="'.$this->mycoImage['urlstringFULD'][0].'" style="margin-bottom:3px;"/><br/>';
			echo $this->mycoImage['copyrightstatement'][0];
			echo '&nbsp;&nbsp;<a href="'.$this->taxonInfo['MycoKeyIDDKWebLink'][0].'" title="Arten på Mycokey" target=_blank>Se billede(r) og mere i MycoKey</a>';
		}
		echo '</div>';
		if (isset($this->taxonInfo['diagnose'][0]) && ($this->taxonInfo['diagnose'][0]!='')) {
			$this->drawInfoText('Diagnose',$this->taxonInfo['diagnose'][0]);
		}
		$this->drawInfoText('Forvekslingsmuligheder',$this->taxonInfo['RappForveksling'][0]);
		$this->drawInfoText('Økologisk strategi',$this->taxonInfo['RappErnæringsrapport'][0]);		
		$this->drawInfoText('Rødlistedata (ukritisk gengivet fra Den Danske Rødliste 2009)', '', false);
		/*
		$this->drawInfoText('Kategori',$this->taxonInfo['RappRødlistekategori'][0]);
		$this->drawInfoText('National status',$this->taxonInfo['RappRødlisteStatus'][0]);
		$this->drawInfoText('Bestandsudvikling',$this->taxonInfo['RappRødlisteBestandsudvikling'][0]);
		$this->drawInfoText('Udbredelse',$this->taxonInfo['RappRødlisteUdbredelse'][0]);
		*/
		$this->drawInfoText('Kategori', $this->taxonInfo['RappRødlistekategori'][0], true);
		$this->drawInfoText('National status', $this->taxonInfo['RappRødlisteStatus'][0], true);
		$this->drawInfoText('Bestandsudvikling', $this->taxonInfo['RappRødlisteBestandsudvikling'][0], true);
		$this->drawInfoText('Udbredelse', $this->taxonInfo['RappRødlisteUdbredelse'][0], true);

		$fundCount=$this->getFundCount();
		$this->drawInfoText('Antal fund i svampeatlas', $fundCount);

		$ah=array();
		if ($fundCount>0) {
			$host='Fundet ved '.count($this->hostInfo).' værter : ';
			for ($i=0;$i<count($this->hostInfo);$i++) {
				$host.='<b>'.$this->hostInfo[$i]['HostDK'][0].'</b> ('.
				$this->hostInfo[$i]['HostLat'][0].', '.
				$this->hostInfo[$i]['CountHost'][0].' fund) ';
				$ah[$this->hostInfo[$i]['HostDK'][0]]=$this->hostInfo[$i]['CountHost'][0];
			}
		} else {
			$host='Fundet ved 0 værter.';
		}
		$this->drawInfoText('Værter', $host);

		arsort($ah);
		$count=1;
		foreach($ah as $key=>$value) {
			if ($count>=10) break;
			if ($this->json_hosts!='') $this->json_hosts.=', ';
			$this->json_hosts.=$value;
			$this->host_names[]=$key;
			$count++;
		}
		//ensure there is 10 hosts
		for ($i=$count;$i<=10;$i++) {
			if ($this->json_hosts!='') $this->json_hosts.=', ';
			$this->json_hosts.='0';
			$this->host_names[]='';
		}
		
		$this->drawInfoText('Udbredelse', $this->udbredelseText);
		echo '</div>';
	}

	private function drawRight() {
		echo '<div class="right">';
		$this->drawMap();
		$this->getFundPerMonth();
		$this->getFundPerDecade();
		echo '<div id="top_hosts" class="diagram"></div>';
		echo '</div>';
	}

	public function draw() {
		parent::draw();

		echo '<div id="leftright">';
		$this->drawLeft();
		$this->drawRight();
		echo '</div>';
		echo '<div id="images">';
		if (count($this->imageInfo)>0) {
			echo '<b style="text-transform:uppercase;margin-left:5px;">Billeder tilføjet af atlasbrugerne</b><br/>';
			$this->drawImageThumbs();
		}
		echo '<span style="float:right;clear:both;margin-right:16px;">[&nbsp;<a href="#" onclick="javascript:window.close();">Luk vindue / tab</a>&nbsp;]</span>';
		echo '</div>';

		$this->script();

		//echo '</div>';
		//parent::stdFooter();
	}

	protected function extraHead() {
?>
<style type="text/css">
#bodyContainer {
	position: relative;
	height: auto !important; 
	overflow: hidden;
	font-size: 1.0em;
}
.left {
	width: 540px;
	float: left;
	padding-left: 8px;
	overflow-y: auto;
	overflow-x: hidden;
	font-family: helvetica, arial;
	text-align: left;
	height: auto; /*800px;*/
}
.right {
	width: 410px;
	height: 900px;
	float: left;
	padding-left: 8px;
	overflow-y: hidden;
	overflow-x: hidden;
	font-family: helvetica, arial;
}
#leftright {
	height: 805px;
}
span.desc  {
	text-transform : uppercase;
	font-family : verdana, arial, helvetica;
	font-weight: bold;
	font-size: 80%;
}
span.desc-text  {
	font-family : verdana, arial, helvetica;
	font-size: 80%;
}
span.desc:first-letter {
	font-size: 150%;
}
.diagram {
	margin-top: 10px;
	width:400px;
	background-color: white; 
	border:1px solid gray;
}
#report-map {
	width: 400px;
	height: 300px;
}
#legend {
	background-color: #fff;
	width:400px;
	height: 40px;
	font-size: 12px;
	border-left: 1px solid gray;
	border-right: 1px solid gray;
	border-bottom: 1px solid gray;
}
img.thumb {
	height: 90px;
}
a img.thumb {
	border: none;
}
.img-cnt {
	margin-right: 6px;
	margin-left: 6px;
	margin-bottom: 6px;
	border: 1px solid gray;
	font-size: 11px;
	float: left;
	text-align: left;
}
.top-img {
	float: left;
	font-size: 12px;
	width:100%;
	clear: both;
	margin-bottom: 12px;
}
#images {
	clear: both;
	float: left;
	width: 100%;
	font-family : verdana, helvetica;
	text-align: left;
}
#bodyContainerHeader {
	position: relative;
	left: 0px;
	top: 0px;
	width: 968px;
	height: 18px;
}
#report-map {
	border-left: 1px solid gray;
	border-right: 1px solid gray;
}
.mainTextDarkGrey10 {
	font-size: 12px;
	line-height: 16px;
}
.mainTextDarkGrey10Thin {
	font-size: 12px;
	line-height: 16px;
}
.mainTextDarkGrey10Caps {
	font-size: 12px;
	line-height: 16px;
}
</style>
<?
		}
}

$report = new Report();

?>
