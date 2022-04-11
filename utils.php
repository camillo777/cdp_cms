<?php

class Result {

	public $status;
	public $msg;
	public $errno;
  
	// array to return custom data
	public $data = [];

	function __construct($status = null, $msg = null, $errno = null, $data = null) {
		//print "In BaseClass constructor\n";
		$this->status = $status;
		$this->msg = $msg;
		$this->errno = $errno;
		$this->data = $data;
    }
	
	// to return custom data
	public function addData($key, $value) {
		//array_push($this->$data, $k => $v);
		$this->data[$key] = $value;
	}

	public function toArray(){
		return [
			"status" => $this->status,
			"msg" => $this->msg,
			"errno" => $this->errno,
		];
	}
  }
  
  class SimpleHtml {
  
	public $ok = false;
	private $messages = [];
	
	public function add($m) {
		array_push($this->messages, $m);
	}
	
	public function render(){
	  $s = "";
	  $s .= "<div>";
	  $s .= "Status: ".$this->ok?"OK":"FAILED";
	  $s .= "</div>";
	  
	  foreach($this->messages as $m){
		$s .= "<div>".$m."</div>";
	  }	
	  return $s;
	}  
  }

function output_json($result){

    header('Content-Type: application/json');
    echo json_encode($result);

}

function connect() {
    $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $conn;
}

function dbConnect($dbconfig){

	$sqli = new mysqli(
		$dbconfig["host"],
		$dbconfig["user"],
		$dbconfig["password"],
		$dbconfig["db"]
	);
	
	/* check connection */
	if (mysqli_connect_errno()) {

        $result = new Result();
        $result->status = ST_ERR;
        $result->msg = "Connect failed: ". mysqli_connect_error();
        return $result;

		//$result = 
		//return [
		//		"status" => "ERR",
		//		"msg" => "Connect failed: %s\n", mysqli_connect_error()
		//	];
		//output_json($result);
		//exit(0);
	}
    
    $result = new Result();
    $result->status = ST_OK;
    $result->msg = "Connected to DB";
    $result->addData("sqli", $sqli);
		//"sqli" => $sqli
		//"msg" => "Connect failed: %s\n", mysqli_connect_error()
    //];
    
    return $result;
}

// call to function
//my_log("this is my log message");
function my_log($log_msg)
{
    $log_filename = "log";
    if (!file_exists($log_filename)) 
    {
        // create directory/folder uploads.
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename.'/log_' . date('d-M-Y') . '.log';
    // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
    file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
} 

?>