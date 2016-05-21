<?
header('Content-type: application/json');

@include_once('common/Db.php');

class Load extends Db {
	private $master;
	
	public function __construct() {
		parent::__construct();

		$input=$_GET['userinput'];
		$target=$_GET['target'];

		$this->fileDebug(implode(',',$_GET));

		if (($input!=' ') && ($input!='')) {
			$SQL='select distinct '.$target.' from atlasGaz where '.$target.' like "'.$input.'%" order by '.$target.' asc';
		} else {
			$SQL='select distinct '.$target.' from atlasGaz where '.$target.'<>"" order by '.$target.' asc';
		}

		$this->fileDebug($SQL);

		mysql_set_charset('utf8');
		$result=$this->query($SQL);
		$html='';
		while($row = mysql_fetch_array($result)) {
			if (($row[$target]!='') && ($row[$target]!=' ')) {
				if ($html!='') $html.=',';
				//$taxon=$this->crlf($row[$target]);
				//remove invalid/non ASCII
				//$taxon = preg_replace('/[^(\x20-\x7F)]*/','', $row[$target]);
				//$taxon = preg_replace('/[^(\x00-\x20)]/','', $row[$target]);
				//$taxon = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $tow[$target]);
				//$taxon = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\xFF]/u', '', $row[$target]);
				//HURRA, tager alle ulovlige under #32, men inkluderer æøå
				$taxon = preg_replace('/[\x00-\x08\x0B\x0C\x0E]/u', '', $row[$target]);
				$taxon=str_replace('"', '&quot;', $taxon);
				$taxon=str_replace("'", '&#39;', $taxon);
				//$taxon=str_replace(')', '&#41;', $taxon);
				$html.='{"id" : "'.$taxon.'", "taxon": "'.$taxon.'"}';
			}
		}
		$html='['.$html.']';
		$this->fileDebug($html);
		echo $html;
	}

	private function crlf($string) {
		return preg_replace('#[\r\n]#', '', $string);
	}

	private function fileDebug($text) {
		return false; //no more need for debugging
		$file = "debug.txt";
		$fh = fopen($file, 'a') or die("can't open file");
		fwrite($fh, $text."\n");
		fclose($fh);
	}


}

$load = new Load();

?>
