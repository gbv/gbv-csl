<?php

class CSLAPI {

	// query
	private $callback;
	private $list = array();
	private $style;
	private $locale;
	private $dbkey;
	private $query;

	// response (public)
	public $stylenames;
	public $styles;
	public $locales;
	public $items;

	// response (private)
	private $response = array();
	private $errormessage;
	private $status = 200;


	public function __construct( $args = NULL ) {
		$this->process_args( $args !== NULL ? $args : $_GET );
	}

	private function process_args( $args ) {

		if ( isset($args['callback']) ) {
			if (preg_match('/^[a-zA-Z0-9_]+$/', $args['callback'])) {
				$this->callback = $args['callback'];
			} else {
				$this->error('invalid callback');
			}
		}
		
		if ( isset($args['style']) ) {
		    if (preg_match('/^[a-zA-Z0-9-]+$/', $args['style'])) {
				$this->style = $args['style'];
			} else {
		        $this->error("invalid style name");
			}
		}

		if ( isset($args['locale']) ) {
    		if (preg_match('/^[a-z][a-z](-[A-Z][A-Z])?$/', $args['locale'])) {
				$this->locale = $args['locale'];
			} else {
		        $this->error("invalid locale name");
			}
		}

		if ( isset($args['list']) ) {
			$this->list = explode(',',$args['list']);
		}

		if ( isset($args['query']) or isset($args['dbkey']) ) {
			if (!isset($args['query'])) {
				$this->error('missing query parameter');
			} else if (!isset($args['dbkey'])) {
				$this->error('missing dbkey parameter');
			} else {
				$this->query = $args['query'];
				$this->dbkey = $args['dbkey'];
			}
		}
	}

	public function __get($name) {
		switch($name) {
			case 'list_styles':
				return in_array('styles',$this->list);
			case 'get_style':
				return $this->style;
			case 'get_locale':
				return $this->locale;
			case 'get_dbkey':
				return $this->dbkey;
			case 'get_query':
				return $this->query;
			default:
				return NULL;
		}
	}

	public function error( $message, $status = NULL ) {
		if (!$this->errormessage) {
			$this->errormessage = $message;
			$this->status = $status !== NULL ? $status : 400;
		}
	}

	public function process() {
		if ($this->styles) {
			$this->response["styles"] = $this->styles;
		}
		if ($this->stylenames) {
			$this->response["stylenames"] = $this->stylenames;
		}
		if ($this->locales) {
			$this->response["locales"] = $this->locales;
		}
		if ($this->items) {
			$this->response["items"] = $this->items;
		}
	}

	/**
	 * Send API response.
	 */
	public function respond() {
		$this->send_json($this->response, $this->status);
	}

	/**
	 * Serialize and send JSON or JSONP with given HTTP status.
	 */
	public function send_json( $response, $status ) {
		if (!$response) $response = array();

		$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? 
			        $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
	    header("$protocol $status");
		$response['status'] = $status;

		if ($this->callback) {
			header('Content-Type: text/javascript; charset=utf-8');
			header('access-control-allow-origin: *');
			echo $this->callback . '(' . json_encode($response) . ')';
		} else {
			header('Content-type: application/json; charset=utf-8');
			header('access-control-allow-origin: *');
			echo json_encode($response);
		}
	}
}

?>
