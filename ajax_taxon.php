<?
header('Content-type: application/json');

@include_once('../common/Db.php');

class load extends Db {
	private $master;
	
	public function __construct() {
		parent::__construct();

		$input=$_GET['userinput'];
		$target=$_GET['target'];

		$this->fileDebug(implode(',',$_GET));

		if (($input!=' ') && ($input!='')) {
			$SQL='select distinct '.$target.' from atlasTaxon where '.$target.' like "'.$input.'%" order by '.$target.' asc';
		} else {
			$SQL='select distinct '.$target.' from atlasTaxon where '.$target.'<>"" order by '.$target.' asc';
		}

		$this->fileDebug($SQL);

		mysql_set_charset('utf8');
		$result=$this->query($SQL);
		$html='';
		while($row = mysql_fetch_array($result)) {
			if ($row[$target]!='') {
				if ($html!='') $html.=',';
				$taxon=str_replace('"', '&quot;', $row[$target]);
				$html.='{"id" : "'.$taxon.'", "taxon": "'.$taxon.'"}';
			}
		}
		$html='['.$html.']';
		echo $html;
		//$this->fileDebug($html);
	}

	private function fileDebug($text) {
		$file = "debug.txt";
		$fh = fopen($file, 'a') or die("can't open file");
		fwrite($fh, $text."\n");
		fclose($fh);
	}


}

$load = new load();

?>
