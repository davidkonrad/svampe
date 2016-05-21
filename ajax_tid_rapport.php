<?

include('logged_search.php');

class TidRapport extends LoggedSearch {
	private $weeks = array();
	private $weeks_json = '';

	private $months = array();
	private $months_json = '';

	private $years = array();
	private $years_fund_json = '';
	private $years_year_json = '';

	public function __construct() {
		parent::__construct();

		$this->calculateWeeks();
		$this->calculateMonths();
		$this->calculateYears();
		$this->drawScript();
	}

	private function calculateWeeks() {
		while ($row = mysql_fetch_array($this->result)) {
			if ($row['date_month']!=0 && $row['date_day']!=0 && $row['date_year']!=0) {
				$w=date('W', mktime(0, 0, 0, $row['date_month'], $row['date_day'], $row['date_year']));
				if (isset($this->weeks[$w])) {
					$this->weeks[$w]++;
				} else {
					$this->weeks[$w]=1;
				}
			}
		}
		//fill blanks
		for ($i=1;$i<=52;$i++) {
			if (strlen($i)==1) $i='0'.$i;
			if (!isset($this->weeks[$i])) {
				$this->weeks[$i]=0;
			}
		}
		ksort($this->weeks);

		$this->weeks_json='[';
		foreach ($this->weeks as $key=>$value) {
			$this->weeks_json.=$value.',';
		}
		$this->weeks_json=$this->removeLastChar($this->weeks_json);
		$this->weeks_json.=']';

		//$this->debug($this->weeks);
	}

	private function calculateMonths() {
		mysql_data_seek($this->result, 0);
		
		while ($row = mysql_fetch_array($this->result)) {
			$month=$row['date_month'];
			if (($month>0) && ($month<=12)) {
				if (isset($this->months[$month])) {
					$this->months[$month]++;
				} else {
					$this->months[$month]=1;
				}
			}
		}
		//fill blanks
		for ($i=1;$i<=12;$i++) {
			if (!isset($this->months[$i])) {
				$this->months[$i]=0;
			}
		}
		ksort($this->months);
		//$this->debug($this->months);

		$this->months_json='[';
		foreach ($this->months as $key=>$value) {
			$this->months_json.=$value.',';
		}
		$this->months_json=$this->removeLastChar($this->months_json);
		$this->months_json.=']';
	}

	private function calculateYears() {
		mysql_data_seek($this->result, 0);
		$current=date("Y");
		while ($row = mysql_fetch_array($this->result)) {
			$year=$row['date_year'];
			if (($year>1800) && ($year<=$current)) {
				if (isset($this->years[$year])) {
					$this->years[$year]++;
				} else {
					$this->years[$year]=1;
				}
			}
		}

		$year=date("Y");
		$low=$year;
		$max=0;
		foreach ($this->years as $key=>$value) {
			if ($key>1800) {
				if ($key<$low) $low=$key;
			}
			if ($max<$key) $max=$key;
		}
		for ($i=$low;$i<=$max;$i++) {
			if (!isset($this->years[$i])) {
				$this->years[$i]=0;
			}
		}

		ksort($this->years);
		//$this->debug($this->years);

		$this->years_fund_json='[';
		$this->years_year_json='[';
		foreach ($this->years as $key=>$value) {
			$this->years_fund_json.=$value.',';
			$this->years_year_json.=$key.',';
		}
		$this->years_fund_json=$this->removeLastChar($this->years_fund_json);
		$this->years_fund_json.=']';
		$this->years_year_json=$this->removeLastChar($this->years_year_json);
		$this->years_year_json.=']';
	}

	private function drawScript() {
?>
<br>
<div id="fund-fordelt-over-aar"></div>
<div id="fund-fordelt-over-maaneder"></div>
<div id="fund-fordelt-over-uger"></div>
<script type="text/javascript">
var weeks = <? echo $this->weeks_json;?>;
var months = <? echo $this->months_json;?>;
var years = <? echo $this->years_fund_json;?>;
var years_year = <? echo $this->years_year_json;?>;
var monthdesc = ['Jan','Feb',' Mar','Apr','Maj','Jun','Jul','Aug','Sep','Okt','Nov','Dec'];
function drawChart(element, title, data) {
	var options = {
		'title': title,
		'titlePosition' : 'out',
		'colors' : ['#A52A2A', '#A52A2A'],
		'vAxis' : { 'textPosition' : 'out', 'gridlines' : { 'count' : 6 } },
		'hAxis' : { 'slantedTextAngle' : 0, 'maxAlternation' : 1 },
		'width': 700,
		'height': 200,
		'legend' : { 'position' : 'none'} ,
		'backgroundColor' : 'transparent',
		'chartArea' : { left: 40, top: 20, width: "100%", height: "120px"}
	};

	var chart = new google.visualization.ColumnChart(document.getElementById(element));
	chart.draw(data, options);
}
</script>
<script type="text/javascript">
$(document).ready(function() {
	var wdata = new google.visualization.DataTable();
	var uge;
	wdata.addColumn('string', 'Uge');
	wdata.addColumn('number', 'Antal Fund');
	for (var i = 0; i < weeks.length; i++) {
		uge=parseInt(i)+1;
		wdata.addRow(['Uge '+uge.toString(), weeks[i]]);            
	}
	drawChart("fund-fordelt-over-uger", "Fordeling af fund over uger", wdata);

	var mdata = new google.visualization.DataTable();
	mdata.addColumn('string', 'Måned');
	mdata.addColumn('number', 'Antal Fund');
	for (var i = 0; i < months.length; i++) {
		mdata.addRow([monthdesc[i], months[i]]);            
	}
	drawChart("fund-fordelt-over-maaneder", "Fordeling af fund over måneder", mdata);

	var ydata = new google.visualization.DataTable();
	ydata.addColumn('string', 'År');
	ydata.addColumn('number', 'Antal Fund');
	for (var i = 0; i < years.length; i++) {
		ydata.addRow([years_year[i].toString(), years[i]]);            
	}
	drawChart("fund-fordelt-over-aar", "Fordeling af fund over år", ydata);

});
</script>
<?
	}
}

$tidrapport = new TidRapport();

?>
