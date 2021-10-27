<?php
/**
 * Holaspirit Role
 *
 * @category PHP.
 * @package  Holaspirit.
 * @author   Wouter Groenewold <wgroenewold@gmail.com>.
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPLv3.
 * @link     https://github.com/wgroenewold.
 */

namespace holaspirit;

/**
 * Role stuff for Holaspirit.
 */
class Holaspirit_Role {
	/**
	 * Organisation ID.
	 *
	 * @var false|mixed|void Organisation ID.
	 */
	private $organisation;
	/**
	 * Path for roles.
	 *
	 * @var string Path for roles.
	 */
	private $path;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->organisation = get_option( 'hs_organisation', '60b4a127e26ec6769111ec36' ); // @todo hier nog iets voor maken wat dat fetched
		$this->path         = "api/organizations/$this->organisation/roles";
		add_action( 'wp_ajax_fetch_roles', array( $this, 'fetch_roles' ), 10, 0 );
	}


	/**
	 * Helper to make accountabilities usable.
	 *
	 * @return array with accountabilities sorted.
	 * @throws \GuzzleHttp\Exception\GuzzleException Error if something is wrong.
	 */
	public function sort_accountabilities() {
		$accountabilities = $this->get_roles()['linked']['accountabilities'];

		$sorted = array();
		foreach ( $accountabilities as $value ) {
			$sorted[ $value['id'] ] = array(
				'description' => wp_strip_all_tags( $value['description'] ),
				'version'     => $value['version'],
			);
		}

		return $sorted;
	}

	/**
	 * Fetch roles from HS and sync to WP.
	 */
	public function fetch_roles() {
		$roles   = $this->get_roles();
		$current = array();

		foreach ( $roles as $item ) {
			$current[] = $this->check_role( $item );
		}

		$wp_roles = get_posts(
			array(
				'meta_key'    => 'hs_version',
				'fields'      => 'ids',
				'post_type'   => 'holaspirit_cpt',
				'numberposts' => -1,
			)
		);

		$diff = array_diff( $current, $wp_roles );

		foreach ( $diff as $value ) {
			wp_delete_post( $value, true );
		}
	}

	/**
	 * Get all roles from HS.
	 *
	 * @return mixed Return all roles.
	 * @throws \GuzzleHttp\Exception\GuzzleException Throw error if needed.
	 */
	public function get_roles() {
		$instance = new Holaspirit_API();
		$query    = array(
			'filter' => 'role',
			'view'   => 'light',
			'page'   => 1,
		);
		$result   = $instance->get( $this->path, $query );

		$data = $result['data'];

		while ( null !== $result['pagination']['nextPage'] ) {
			$query['page']++;
			$result = $instance->get( $this->path, $query );
			$data   = array_merge( $data, $result['data'] );
		}

		return $data;
	}

	/**
	 * Check if all data corresponds between HS and WP.
	 *
	 * @param array $item Role item.
	 *
	 * @return int|\WP_Error Role ID.
	 */
	public function check_role( $item ) {
		$role = get_posts(
			array(
				'meta_key'    => 'hs_id',
				'meta_value'  => $item['id'],
				'post_type'   => 'holaspirit_cpt',
				'numberposts' => -1,
			)
		);

		if ( is_array( $role ) && ! empty( $role ) ) {
			$role_version = get_post_meta( $role[0]->ID, 'hs_version', true );
			if ( $role_version !== $item['version'] ) {
				$this->update_role( $item );
			}

			return $role[0]->ID;
		} else {
			return $this->create_role( $item );
		}
	}

	/**
	 * Update the role with new data from HS.
	 *
	 * @param array $item Role in WP.
	 */
	public function update_role( $item ) {
		$role_id = get_posts(
			array(
				'meta_key'    => 'hs_id',
				'meta_value'  => $item['id'],
				'post_type'   => 'holaspirit_cpt',
				'numberposts' => -1,
			)
		);
		$role_id = $role_id[0];

		wp_update_post(
			array(
				'ID'          => $role_id,
				'post_title'  => $item['name'],
				'post_type'   => 'holaspirit_cpt',
				'post_status' => 'publish',
			)
		);

		update_field( 'purpose', wp_strip_all_tags( $item['purpose'] ), $role_id );
		update_post_meta( $role_id, 'hs_version', $item['version'] );
	}

	/**
	 * Create role from HS data.
	 *
	 * @param array $item HS role item.
	 *
	 * @return int|\WP_Error Created role ID.
	 */
	public function create_role( $item ) {
		$id = wp_insert_post(
			array(
				'post_title'  => $item['name'],
				'post_type'   => 'holaspirit_cpt',
				'post_status' => 'publish',
			)
		);

		update_field( 'purpose', wp_strip_all_tags( $item['purpose'] ), $id );
		update_post_meta( $id, 'hs_id', $item['id'] );
		update_post_meta( $id, 'hs_version', $item['version'] );

		return $id;
	}
}

// @todo ook nog aan de juiste category/circle toekennen.
// @todo $item['assignedMembers'] koppelen aan juiste users via een multiselect
