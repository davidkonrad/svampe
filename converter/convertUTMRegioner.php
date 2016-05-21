<?
include('../common/Db.php');

class convertUTMRegioner extends Db {
	private $CSVFile = 'udtraek/UTMregioner.tab';
	private $delimiter = ';';

	public function __construct() {
		parent::__construct();
		$this->loadCSV();
	}

	protected function removeLastChar($s) {
		return substr_replace($s ,"", -1);
	}

	protected function loadCSV() {
		echo '$UTMRegioner = array();<br/>';
		if (($handle = fopen($this->CSVFile, "r")) !== false) {
			$this->fieldNames = fgetcsv($handle, 1000, $this->delimiter);

			while (($record = fgetcsv($handle, 1000, $this->delimiter)) !== false) {
				$index=0;
				foreach ($this->fieldNames as $fieldName) {
					$array[$fieldName] = $record[$index];
					$index++;
				}
				echo "&#36;UTMRegioner['".$array['Name']."']='".$array['Region']."';<br/>";
			}

		}
	}
}

$utm = new convertUTMRegioner();

?>
