<?php

$requestString = file_get_contents('php://input');
$requestObject = json_decode(json_encode($_POST));//json_decode($requestString); // same object as was sent in the ajax call



class Controller {

	private $link = null;
	private $resultJson = "";
	private $tabel = "tabel";

		function __construct() {
		//CONNECT TO DB
			// $this->link = new mysqli("localhost", "profimed_adi", "Start1312", "profimed_adi");
			$this->link = new mysqli("localhost", "root", "usbw", "test1");
			if ($this->link->connect_errno) {
			    echo "Failed to connect to MySQL: (" . $this->link->connect_errno . ") " . $this->link->connect_error;
			}
		}



	function mysql_tables($database='')
	{
	    $tables = array();
	    $sql = "SHOW TABLES FROM {$database};";
	    $result = mysqli_query($this->link, $sql);
	    if($result)
	    while($table = mysqli_fetch_row($result))
	    {
	        $tables[] = $table[0];
	    }
	    echo json_encode($tables);
	}

	function get_columns($tableName)
	{
		$columns = array();
		$sql = "DESCRIBE `" . $tableName;
		$result = mysqli_query($this->link, $sql);
		while($row = mysqli_fetch_array($result))
		{
			$columns[] = $row['Field'];
		}
		return $columns;
	}

	function copyFiles($tableName) {
		$RestBackendTemplate = file_get_contents('RestBackendTemplate.php');
		$RestFrontendTemplate = file_get_contents('RestFrontendTemplate.html');

		$arr = array("[[TableName]]" => $tableName, "\n\r" => "\n");

		$RestBackendTemplate = strtr($RestBackendTemplate , $arr);
		$RestFrontendTemplate = strtr($RestFrontendTemplate , $arr);

		$columnNames = $this->get_columns($tableName);

//FRONTEND
		$RestFrontendTemplateExploded = explode( "\n", $RestFrontendTemplate);

		for ($i=0 ; $i< count($RestFrontendTemplateExploded) ; $i++){
			if (strpos( $RestFrontendTemplateExploded[$i] , "[[ColumnName]]" ))
			{
				$columnLineTemplate = $RestFrontendTemplateExploded[$i];
				$RestFrontendTemplateExploded[$i]= "";
				foreach ($columnNames as $column) {
					$arrColumnsReplacer = array("[[ColumnName]]" => $column);
					$RestFrontendTemplateExploded[$i] .= strtr($columnLineTemplate , $arrColumnsReplacer);
				}
			}
		}

		$RestFrontendTemplate = implode( "\n", $RestFrontendTemplateExploded);

// BACKEND
		$RestBackendTemplateExploded = explode( "\n", $RestBackendTemplate);

		for ($i=0 ; $i< count($RestBackendTemplateExploded) ; $i++){
			if (strpos( $RestBackendTemplateExploded[$i] , "[[ColumnName]]" ))
			{
				$columnLineTemplate = $RestBackendTemplateExploded[$i];
				$RestBackendTemplateExploded[$i]= "";
				foreach ($columnNames as $column) {
					$arrColumnsReplacer = array("[[ColumnName]]" => $column);
					$RestBackendTemplateExploded[$i] .= strtr($columnLineTemplate , $arrColumnsReplacer);
				}
			}
		}

		$RestBackendTemplate = implode( "\n", $RestBackendTemplateExploded);



		$generatedBackend = file_put_contents("RestBackend".$tableName.".php", $RestBackendTemplate);
		$generatedFrontend = file_put_contents("RestFrontend".$tableName.".html", $RestFrontendTemplate);



		echo '{ "table":"'.$tableName.'"}';
	}

}




$controller = new Controller();


if ( isset($_GET['action']) ) {

	switch($_GET['action']){
		case "listtables":
			$controller->mysql_tables($requestObject->database);
			break;
		case "generate":
			$controller->copyFiles($requestObject->table);
			break;
		default:
			break;
	}
}
?>
