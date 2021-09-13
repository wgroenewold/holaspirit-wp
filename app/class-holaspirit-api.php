<?php
/**
 * Holaspirit API
 *
 * @category PHP.
 * @package  Holaspirit.
 * @author   Wouter Groenewold <wgroenewold@gmail.com>.
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPLv3.
 * @link     https://github.com/wgroenewold.
 */

namespace holaspirit;

use GuzzleHttp\Client;

/**
 * Admin stuff for Holaspirit.
 */
class Holaspirit_API {

	public $client_id;
	public $username;
	public $password;
	public $base_url;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->client_id = get_field('hs_clientid', 'option');
		$this->username = get_field('hs_username', 'option');
		$this->password = get_field('hs_password', 'option');
		$this->base_url = 'https://app.holaspirit.com/';
	}

	public function obtain_access_token(){
		$result = $this->post('oauth/v2/token', array(
			'client_id' => $this->client_id,
			'grant_type' => 'password',
			'username' => $this->username,
			'password' => $this->password,
		));

		if(is_array($result) && array_key_exists('access_token', $result)){
		 	update_field('hs_token', $result['access_token'], 'option');
			return $result['access_token'];
		}

		return false;
	}


	public function get($path, $query){
		$token = get_field('hs_token', 'option');
		$client = new Client(
			array(
				'headers' => array(
					'Content-type' => 'application/json; charset=utf-8',
					'Authorization' => "Bearer $token",
				),
			)
		);

		if($query){
			$query = '?' . http_build_query($query);
		}

		$result = $client->get( "$this->base_url$path$query");
		$body = (string) $result->getBody();
		$body = json_decode($body, true);

		return $body;
	}

	public function post($path, $data){
		$token = get_field('hs_token', 'option');

		$client = new Client(
			array(
				'headers' => array(
					'Content-type' => 'application/json; charset=utf-8',
					'Authorization' => "Bearer $token",
				),
			)
		);

		$result = $client->post(
			"$this->base_url$path",
			array(
				'json' => $data,
			)
		);

		$body = (string) $result->getBody();
		$body = json_decode($body, true);

		return $body;
	}
}