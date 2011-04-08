<?php
class routeros_api {
	var $error_no;				// Variable for storing connection error number, if any
	var $error_str;				// Variable for storing connection error text, if any
	var $attempts = 3;			// Connection attempt count
	var $connected = false;		// Connection state
	var $port = 8728;			// Port to connect to
	var $socket;				// Variable for storing socket resource

	protected $params;

	public function setParams($params) {
    $this->params = $params;
		}

    $timeout = $this->params->getTimeout();
	$delay = $this->params->getDelay();



	function encode_length($length) {
		if ($length < 0x80) {
			$length = chr($length);
		}
		else
		if ($length < 0x4000) {
			$length |= 0x8000;
			$length = chr( ($length >> 8) & 0xFF) . chr($length & 0xFF);
		}
		else
		if ($length < 0x200000) {
			$length |= 0xC00000;
			$length = chr( ($length >> 8) & 0xFF) . chr( ($length >> 8) & 0xFF) . chr($length & 0xFF);
		}
		else
		if ($length < 0x10000000) {
			$length |= 0xE0000000;
			$length = chr( ($length >> 8) & 0xFF) . chr( ($length >> 8) & 0xFF) . chr( ($length >> 8) & 0xFF) . chr($length & 0xFF);
		}
		else
		if ($length >= 0x10000000)
			$length = chr(0xF0) . chr( ($length >> 8) & 0xFF) . chr( ($length >> 8) & 0xFF) . chr( ($length >> 8) & 0xFF) . chr($length & 0xFF);
		return $length;
	}
	/**************************************************
	 *
	 *************************************************/
	function connect($ip, $login, $password) {
		for ($ATTEMPT = 1; $ATTEMPT <= $this->attempts; $ATTEMPT++) {
			$this->connected = false;
			show_info('Connection attempt #' . $ATTEMPT . ' to ' . $ip . ':' . $this->port . '...');
			if ($this->socket = @fsockopen($ip, $this->port, $this->error_no, $this->error_str, $this->timeout) ) {
				socket_set_timeout($this->socket, $this->timeout);
				$this->write('/login');
				$RESPONSE = $this->read(false);
				if ($RESPONSE[0] == '!done') {
					if (preg_match_all('/[^=]+/i', $RESPONSE[1], $MATCHES) ) {
						if ($MATCHES[0][0] == 'ret' && strlen($MATCHES[0][1]) == 32) {
							$this->write('/login', false);
							$this->write('=name=' . $login, false);
							$this->write('=response=00' . md5(chr(0) . $password . pack('H*', $MATCHES[0][1]) ) );
							$RESPONSE = $this->read(false);
							if ($RESPONSE[0] == '!done') {
								$this->connected = true;
								break;
							}
						}
					}
				}
				fclose($this->socket);
			}
			sleep($this->delay);
		}
		if ($this->connected)
			show_info('Connected...');
		else
			show_error('Error...');
		return $this->connected;
	}
	/**************************************************
	 *
	 *************************************************/
	function disconnect() {
		fclose($this->socket);
		$this->connected = false;
		show_info('Disconnected...');
	}
	/**************************************************
	 *
	 *************************************************/
	function parse_response($response) {
		if (is_array($response) ) {
			$PARSED = array();
			$CURRENT = null;
			foreach ($response as $x) {
				if (in_array($x, array('!fatal', '!re', '!trap') ) ) {
					if ($x == '!re')
						$CURRENT = &$PARSED[];
					else
						$CURRENT = &$PARSED[$x][];
				}
				else
				if ($x != '!done') {
					if (preg_match_all('/[^=]+/i', $x, $MATCHES) )
						$CURRENT[$MATCHES[0][0]] = (isset($MATCHES[0][1]) ? $MATCHES[0][1] : '');
				}
			}
			return $PARSED;
		}
		else
			return array();
	}
	/**************************************************
	 *
	 *************************************************/
        function array_change_key_name(&$array) {
                if (is_array($array) ) {
                        foreach ($array as $k => $v) {
                                $tmp = str_replace("-","_",$k);
                                $tmp = str_replace("/","_",$tmp);
                                if ($tmp) {
                                        $array_new[$tmp] = $v;
                                } else {
                                        $array_new[$k] = $v;
                                }
                        }
                        return $array_new;
                } else {
                        return $array;
                }
        }
        /**************************************************
         *
         *************************************************/
        function parse_response4smarty($response) {
                if (is_array($response) ) {
                        $PARSED = array();
                        $CURRENT = null;
                        foreach ($response as $x) {
                                if (in_array($x, array('!fatal', '!re', '!trap') ) ) {
                                        if ($x == '!re')
                                                $CURRENT = &$PARSED[];
                                        else
                                                $CURRENT = &$PARSED[$x][];
                                }
                                else
                                if ($x != '!done') {
                                        if (preg_match_all('/[^=]+/i', $x, $MATCHES) )
                                                $CURRENT[$MATCHES[0][0]] = (isset($MATCHES[0][1]) ? $MATCHES[0][1] : '');
                                }
                        }
                        foreach ($PARSED as $key => $value) {
                                $PARSED[$key] = $this->array_change_key_name($value);
                        }
                        return $PARSED;
                }
                else {
                        return array();
                }
        }
	/**************************************************
	 *
	 *************************************************/
   function read($parse = true) {
         $RESPONSE = array();
	       while (true) {
			// Read the first byte of input which gives us some or all of the length
			// of the remaining reply.
			$BYTE = ord(fread($this->socket, 1) );
			$LENGTH = 0;
			//echo "$BYTE\n";
			// If the first bit is set then we need to remove the first four bits, shift left 8
			// and then read another byte in.
			// We repeat this for the second and third bits.
			// If the fourth bit is set, we need to remove anything left in the first byte
			// and then read in yet another byte.
			if ($BYTE & 128) {
				if (($BYTE & 192) == 128) {
					$LENGTH = (($BYTE & 63) << 8 ) + ord(fread($this->socket, 1)) ;
				} else {
				if (($BYTE & 224) == 192) {
					$LENGTH = (($BYTE & 31) << 8 ) + ord(fread($this->socket, 1)) ;
					$LENGTH = ($LENGTH << 8 ) + ord(fread($this->socket, 1)) ;
				} else {
					if (($BYTE & 240) == 224) {
					$LENGTH = (($BYTE & 15) << 8 ) + ord(fread($this->socket, 1)) ;
					$LENGTH = ($LENGTH << 8 ) + ord(fread($this->socket, 1)) ;
					$LENGTH = ($LENGTH << 8 ) + ord(fread($this->socket, 1)) ;
						} else {
				    $LENGTH = ord(fread($this->socket, 1)) ;
					$LENGTH = ($LENGTH << 8 ) + ord(fread($this->socket, 1)) ;
					$LENGTH = ($LENGTH << 8 ) + ord(fread($this->socket, 1)) ;
					$LENGTH = ($LENGTH << 8 ) + ord(fread($this->socket, 1)) ;
					}
				}
			}
			} else {
			$LENGTH = $BYTE;
		}
        // If we have got more characters to read, read them in.
        if ($LENGTH > 0) {
           $_ = "";
           $retlen=0;
           while ($retlen < $LENGTH) {
           $toread = $LENGTH - $retlen ;
           $_ .= fread($this->socket, $toread);
           $retlen = strlen($_);
         }
         $RESPONSE[] = $_ ;
         show_debug('>>> [' . $retlen . '/' . $LENGTH . ' bytes read.');
        }
        // If we get a !done, make a note of it.
         if ($_ == "!done")
         $receiveddone=true;
        $STATUS = socket_get_status($this->socket);
        if ($LENGTH > 0)
        show_debug('>>> [' . $LENGTH . ', ' . $STATUS['unread_bytes'] . '] ' . $_);
       if ( (!$this->connected && !$STATUS['unread_bytes']) ||
        ($this->connected && !$STATUS['unread_bytes'] && $receiveddone) )
         break;
     }
     if ($parse)
       $RESPONSE = $this->parse_response($RESPONSE);
      return $RESPONSE;
     }
	/**************************************************
	 *
	 *************************************************/
	function write($command, $param2 = true) {
		if ($command) {
			$data = explode("\n",$command);
			foreach ($data as $com) {
			$com = trim($com);
			        fwrite($this->socket, $this->encode_length(strlen($com) ) . $com);
			        show_debug('<<< [' . strlen($com) . '] ' . $com);
			}
			if (gettype($param2) == 'integer') {

				fwrite($this->socket, $this->encode_length(strlen('.tag=' . $param2) ) . '.tag=' . $param2 . chr(0) );

				show_debug('<<< [' . strlen('.tag=' . $param2) . '] .tag=' . $param2);
			}
			else
			if (gettype($param2) == 'boolean')
				fwrite($this->socket, ($param2 ? chr(0) : '') );
			return true;
		}
		else
			return false;
	}
	public function write_array(array $array) {
		$count = count ( $array );
		$is_last = 1 == $count;
		$i = 1;
		foreach ( $array as $name => $value ) {
			$this->write ( '=' . $name . '=' . $value, $is_last );
			$i ++;
			if ($i == $count) {
				$is_last = true;
			}
		}
	}
}

class Parser {

	public function __construct($file) {
		$this->_file	= $file;
	}

	public function parseFile() {
     	$data_from_file = file ( $this->_file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES );
		$data_from_file = (preg_grep ( '/^#.*/', $data_from_file, PREG_GREP_INVERT ));
		$tobeset = array();
		foreach ( $data_from_file as $line ) {
			$rows = preg_split ( "/[\s]+/", $line );
			$where = trim ( array_shift ( $rows ), "/" );
			foreach ( $rows as $to_build ) {
		  		list ( $key, $value ) = explode ( "=", $to_build );
					$tmp [$key] = $value;
				}
				$tobeset [$where] [] = $tmp;
				unset ( $tmp );
		}
		return $tobeset;
    }
}

class Mtcfengine {

    public function __construct($address, $user, $password) {
		$this->_address  = $address;
		$this->_user     = $user;
		$this->_password = $password;
		$this->api       = new routeros_api();
    }

	public function connect() {
	return $this->api->connect($this->_address, $this->_user, $this->_password);

	}

    public function configure(array $parsedoptions) {
		foreach ($parsedoptions as $tobesetid => $tobesetarray ) {
			$this->api->write ( '/' . $tobesetid . '/print' );
			$has = $this->api->read ( true );
			foreach ( $tobesetarray as $id => $data ) {
				$elem = isset ( $has [$id] ) ? $has [$id] : array ();
				$diff = array_diff ( $data, $elem );
				$this->save_diff($diff, $elem, $tobesetid, $data);
				if (array_key_exists ( '.id', $elem )) {
					$save [] = $elem ['.id'];
				}
			}
			foreach ( $has as $id => $value ) {
				if (isset ( $value ['.id'] )) {
					$collect_ids [] = $value ['.id'];
				}
			}
			if (! empty ( $collect_ids ) && ! empty ( $save )) {
				$this->remove(array_diff ( $collect_ids, $save ), $tobesetid);
			}
		}
    }

    protected function save_diff($diff, $elem, $tobesetid, array $data) {
    	if (!$diff) {
    		return;
    	}
		if (empty ( $elem )) {
			$this->api->write ( '/' . $tobesetid . '/add', false );
			$this->api->write_array ( $data );
			$this->api->read ();
		} else {
			$this->api->write ( '/' . $tobesetid . '/set', false );
			if (array_key_exists ( '.id', $elem )) {
				$this->api->write ( '=.id=' . $elem ['.id'], false );
			}
			$this->api->write_array ( $diff );
			$this->api->read ();
		}
    }

    protected function remove($remove, $tobesetid) {
    	if (empty($remove)) {
    		return;
    	}
		$this->api->write ( '/' . $tobesetid . '/remove', false );
		$tmp = array_shift ( $remove );
		$command = '=.id=' . $tmp;
		foreach ( $remove as $id => $value ) {
			$command .= ',' . $value;
		}
		$this->api->write ( $command );
		$this->api->read ();
    }

    public function disconnect() {
  		$this->api->disconnect();
    }
}


interface MtcfengineParams {
	public function isValid();
	public function isHelp();
	public function isDebug();
	public function isError();
	public function isInfo();
	public function emptyParams();
	public function getUser();
	public function getPassword();
	public function getAddress();
	public function getTimeout();
	public function getDelay();
	public function getConfigFile();
}

class MtcfengineCLIParams implements MtcfengineParams {
	private $required = array('u' => '0', 'p' => '0', 'a' => '0');

	public function __construct() {
		$this->params = getopt("p:u:a:vhl:t:c:");
	}

	public function getUser() {
		return $this->getOption('u');
	}

	public function getPassword() {
		return $this->getOption('p');
	}

	public function getAddress() {
		return $this->getOption('a');
	}

	public function getConfigFile() {
    	return $this->getOption('c', $this->getAddress() . '.conf');
    }

    public function isDebug() {
    	return $this->getOption('v');
	}

    public function isHelp() {
		return $this->getOption('h');
	}

	public function isQuiet() {
		return $this->getOption('q');
	}

	public function isInfo() {
		return $this->isQuiet();
	}

	public function isError() {
		return true;
	}

    public function getDelay() {
    	return $this->getIntOption('l', $default = 2);
    }
    public function getTimeout() {
    	return $this->getIntOption('t', $default = 2);;
    }

	public function emptyParams() {
		return empty($this->params);
	}

	public function isValid() {
		return count(array_values(array_intersect_key($this->params, $this->required))) == count($this->required);
	}

	private function getIntOption($key, $default = 0) {
		return is_int($key, $this->params) ? $this->params[$key] : $default;
	}

	private function getOption($key, $default = false) {
		return array_key_exists($key, $this->params) ? $this->params[$key] : $default;
	}
}

function show_help() {
?>
You must specify -u -p -a
	-a address. can be fqdn
	-c configuration file for address. defaults to specified address.conf
	-d delay between connection attempts in seconds (defaults to 2)
	-h this help
	-p password
	-t connection attempt timeout and data read timeout (defaults to 2)
	-u username
	-v show (api debug) information. be verbose what is going on
	-q be quiet. this supresses info and errors
<?php
}

function show_debug($text) {
	if ($options->isDebug)
		echo $text . "\n";
}

function show_info($text) {
	if ($options->isInfo)
		echo $text . "\n";
}

function show_error($text) {
	if ($options->isError)
		echo $text . "\n";
}

$options = new MtcfengineCLIParams();

if (!$options->isValid() || $options->isHelp() || $options->emptyParams()) {
	show_help();
	exit;
}

if (!file_exists($options->getConfigFile())) {
	show_error("Specified file \"$options->getConfigFile()\" does not exist.\n");
	exit;
}

$parser = new Parser($options->getConfigFile());


$api = new routeros_api();
$api->setParams($options);


$configurator = new Mtcfengine($options->getAddress(), $options->getUser(), $options->getPassword());

if (!$configurator->connect()) {
	show_error("Connection to \"$options->getAddress()\" failed");
	exit();
}

$configurator->configure($parser->parseFile());
$configurator->disconnect();

?>