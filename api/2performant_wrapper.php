<?php

class TPerformant_Wrapper {

	public $api;

	// 2perfomant wrapper
	public function wrapper() {
		if ( !isset( $this->api ) ) {
			$err = $this->connection();
			if( !empty($err) )
				return false;
		}
	}
	
	
	// create a connection with 2Performant API
	public function connection() {
		$connected = false;
		$errors = array();
		$connection = get_option( 'tpag_options_connection' );
		if ( ! ( $connection['network'] && $connection['username'] && $connection['password'] ) ) {
			$errors[] = 'Please define connection parameters';
		} else {
			try {
				$api_options = array_merge( array( 'connection_timeout' => 10, 'timeout' => 0, 'adapter' => 'curl' ), $connection );
				$config = array( 'HTTP_Request2_config' => array(
					'connect_timeout' => $api_options['connection_timeout'],
					'timeout' => $api_options['timeout'],
					'adapter' => 'HTTP_Request2_Adapter_' . ucfirst($api_options['adapter'])
				) );
				$this->api = new TPerformant( 'simple', array( "user" => trim( $connection['username'] ), "pass" => trim( $connection['password'] ) ), trim( $connection['network'] ), $config );
				
				try {
					$response = $this->api->user_loggedin();
				} catch( Exception $e ) {
					$error = $e->getMessage();
					function starts_with( $haystack, $needle ) { return substr( $haystack, 0, strlen( $needle ) ) == $needle; }
					if ( starts_with( $error, 'Unavailable server. Response code: 401' ) ) {
						$errors[] = 'Invalid username and password';
					} elseif ( starts_with( $error, 'Unavailable server. Response code: ' ) ) {
						$errors[] = 'Invalid API URL';
					}
				}
				if( isset( $response ) && $response ) {
					if ( is_object( $response ) ) {
						$connected = true;
						
						if ( ! ( isset( $response->role ) && $response->role == 'affiliate' ) )
							$errors[] = 'Logged in user is not an affiliate';
					} else {
						$errors[] = 'Invalid API response. Please verify settings and contact affiliate network administrator.';
						ob_start();
						//print_r( $response );
						trigger_error( 'API response: ' . ob_get_contents(), E_USER_WARNING );
						ob_end_clean();
					}
				}
			} catch( HTTP_Request2_Exception $e ) {
				$errors[] = 'API connection error: ' . $e->getMessage();
			} catch( Exception $e ) {
				$errors[] = get_class($e) . ': ' . $e->getMessage();
			}
		}
		return $errors;
	}

}