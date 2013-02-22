<?php

class JSON_API {

	protected $request;
	protected $response;

	protected static $parameters = array(
		'callback' => '^[a-zA-Z0-9_]+$'
	);

	private $status = 200;
	private $error_message;

	public function __construct( $args = NULL ) {
		if ( $args === NULL ) $args = $_GET;

		$this->request = new stdClass();

		foreach ( $this::$parameters as $name => $schema ) {
			if ( isset($args[$name]) ) {
				if (preg_match("/$schema/", $args[$name])) {
					$this->request->$name = $args[$name];
				} else {
					$this->error("invalid $name");
				}
				unset($args[$name]);
			}
		}

		$this->process_args();

		$this->response = new stdClass();
	}

	protected function process_args() { }

	protected function error( $message, $status = NULL ) {
		if (!$this->error_message) {
			$this->error_message = $message;
			$this->status = $status !== NULL ? $status : 400;
		}
	}

	public function respond() {
		$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? 
			        $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
	    header("$protocol ".$this->status);
		$this->response->status = $this->status;
		if ($this->error_message) {
			$this->response->error  = $this->error_message;
		}

		if (isset($this->request->callback)) {
			header('Content-Type: text/javascript; charset=utf-8');
			header('access-control-allow-origin: *');
			echo $this->request->callback . '(' . json_encode($this->response) . ')';
		} else {
			header('Content-type: application/json; charset=utf-8');
			header('access-control-allow-origin: *');
            if ( version_compare(PHP_VERSION, '5.4.0') >= 0) {
    			$json = json_encode($this->response, JSON_PRETTY_PRINT);
            } else {
    			$json = json_encode($this->response);
            }
            echo $json;
		}
	}
}

?>
