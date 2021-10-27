<?php
/**
 * Holaspirit User
 *
 * @category PHP.
 * @package  Holaspirit.
 * @author   Wouter Groenewold <wgroenewold@gmail.com>.
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPLv3.
 * @link     https://github.com/wgroenewold.
 */

namespace holaspirit;

/**
 * User stuff for Holaspirit.
 */
class Holaspirit_User {
	/**
	 * HS organisation ID.
	 *
	 * @var false|mixed|void HS organisation ID.
	 */
	private $organisation;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->organisation = get_option( 'hs_organisation', '60b4a127e26ec6769111ec36' );
		add_action( 'wp_ajax_fetch_users', array( $this, 'fetch_users' ), 10, 0 );
		add_filter( 'get_avatar_data', array( $this, 'change_avatar' ), 10, 2 );
	}

	/**
	 * Fetch all users from HS to WP.
	 */
	public function fetch_users() {
		$users   = $this->get_users();
		$current = array();

		foreach ( $users as $item ) {
			$current[] = $this->check_user( $item );
		}

		$wp_users = get_users(
			array(
				'meta_key' => 'hs_version',
				'fields'   => 'ID',
			)
		);

		$diff = array_diff( $current, $wp_users );

		foreach ( $diff as $value ) {
			wp_delete_user( $value );
		}
	}

	/**
	 * Get all users from HS.
	 *
	 * @return mixed All members.
	 * @throws \GuzzleHttp\Exception\GuzzleException Error if needed.
	 */
	public function get_users() {
		$instance = new Holaspirit_API();
		$query    = array( 'page' => 1 );

		$result = $instance->get( "api/organizations/$this->organisation/members", $query );
		$data   = $result['data'];

		while ( $result['pagination']['nextPage'] !== null ) {
			$query['page']++;
			$rec  = $instance->get( "api/organizations/$this->organisation/members", $query );
			$data = array_merge( $data, $rec['data'] );
		}

		return $data;
	}

	/**
	 * @param array $item Check if userdata still corresponds with HS.
	 *
	 * @return int|\WP_Error User ID.
	 */
	public function check_user( $item ) {
		$user = get_user_by( 'email', $item['email'] );
		if ( $user ) {
			$user_version = get_user_meta( $user->ID, 'hs_version', true );
			if ( $user_version !== $item['version'] ) {
				$this->update_user( $item );
			}

			return $user->ID;
		} else {
			return $this->create_user( $item );
		}
	}

	/**
	 * Create WP user if needed
	 * @param array $item HS user data
	 *
	 * @return int|\WP_Error WP user ID.
	 */
	public function create_user( $item ) {
		$user_id = wp_insert_user(
			array(
				'user_login' => $item['displayName'],
				'user_pass'  => null,
				'first_name' => $item['firstName'],
				'last_name'  => $item['lastName'],
				'user_email' => $item['email'],
			)
		);

		update_user_meta( $user_id, 'hs_id', $item['id'] );
		update_user_meta( $user_id, 'hs_version', $item['version'] );
		update_user_meta( $user_id, 'hs_avatar', $item['avatarUrl'] );

		return $user_id;
	}

	/**
	 * Update WP userdata.
	 *
	 * @param array $item HS user item.
	 */
	public function update_user( $item ) {
		$user_id = get_user_by( 'email', $item['email'] );

		wp_update_user(
			array(
				'ID'         => $user_id,
				'first_name' => $item['firstName'],
				'last_name'  => $item['lastName'],
			)
		);

		update_user_meta( $user_id, 'hs_id', $item['id'] );
		update_user_meta( $user_id, 'hs_version', $item['version'] );
		update_user_meta( $user_id, 'hs_avatar', $item['avatarUrl'] );
	}

	/**
	 * Filter HS avatar to WP avatar.
	 *
	 * @param array $args Arguments
	 * @param mixed $id_or_email ID or email from user.
	 *
	 * @return mixed HS avatar.
	 */
	public function change_avatar( $args, $id_or_email ) {
		$instance = new Holaspirit_API();
		$base_url = $instance->base_url;

		$avatar_url = get_user_meta( $id_or_email, 'hs_avatar', true );

		if ( $avatar_url ) {
			$args['url'] = "$base_url$avatar_url";
		}

		return $args;
	}
}
