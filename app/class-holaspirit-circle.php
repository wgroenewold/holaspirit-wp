<?php
/**
 * Holaspirit Circle
 *
 * @category PHP.
 * @package  Holaspirit.
 * @author   Wouter Groenewold <wgroenewold@gmail.com>.
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPLv3.
 * @link     https://github.com/wgroenewold.
 */

namespace holaspirit;

/**
 * Circle stuff for Holaspirit.
 */
class Holaspirit_Circle {
	/**
	 * HS organisation ID.
	 *
	 * @var false|mixed|void HS organisation ID.
	 */
	private $organisation;
	/**
	 * HS path to circles.
	 *
	 * @var string HS path to circles.
	 */
	private $path;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->organisation = get_option( 'hs_organisation', '60b4a127e26ec6769111ec36' ); // @todo hier nog iets voor maken wat dat fetched
		$this->path         = "api/organizations/$this->organisation/circles";
		add_action( 'wp_ajax_fetch_circles', array( $this, 'fetch_circles' ), 10, 0 );
	}

	/**
	 * Fetch circles from HS.
	 */
	public function fetch_circles() {
		$circles = $this->get_circles();
		$current = array();

		foreach ( $circles as $item ) {
			$current[] = $this->check_circle( $item );
		}

		$wp_circles = get_terms(
			array(
				'taxonomy'   => 'holaspirit_tax',
				'meta_key'   => 'hs_version',
				'fields'     => 'ids',
				'hide_empty' => false,
			)
		);

		$diff = array_diff( $current, $wp_circles );

		foreach ( $diff as $value ) {
			wp_delete_term( $value, 'holaspirit_tax' );
		}
	}

	/**
	 * Get circles from HS.
	 *
	 * @return mixed Returns circles.
	 * @throws \GuzzleHttp\Exception\GuzzleException Throws error if broken.
	 */
	public function get_circles() {
		$instance = new Holaspirit_API();
		$query    = array( 'page' => 1 );

		$result = $instance->get( $this->path, $query );

		$data = $result['data'];

		while ( null !== $result['pagination']['nextPage'] ) {
			$query['page']++;
			$result = $instance->get( $this->path, $query );
			$data   = array_merge( $data, $result['data'] );
		}

		return $data;
	}

	/**
	 * Check if data is still the same between HS and WP.
	 *
	 * @param array $item Item to check.
	 *
	 * @return int|mixed Returns circle ID.
	 */
	public function check_circle( $item ) {
		$circle = get_terms(
			array(
				'meta_key'   => 'hs_id',
				'meta_value' => $item['id'],
				'taxonomy'   => 'holaspirit_tax',
				'hide_empty' => false,
			)
		);

		if ( is_array( $circle ) && ! empty( $circle ) ) {
			$circle_version = get_term_meta( $circle[0]->term_id, 'hs_version', true );
			if ( $circle_version !== $item['version'] ) {
				$this->update_circle( $item );
			}

			return $circle[0]->term_id;
		} else {
			return $this->create_circle( $item );
		}
	}

	/**
	 * Update the circle with new data from HS.
	 *
	 * @param array $item Item to update.
	 */
	public function update_circle( $item ) {
		$circle_id = get_terms(
			array(
				'meta_key'   => 'hs_id',
				'meta_value' => $item['id'],
				'taxonomy'   => 'holaspirit_tax',
				'hide_empty' => false,
			)
		);
		$circle_id = $circle_id[0]->term_id;

		wp_update_term( $circle_id, 'holaspirit_tax', array( 'name' => $item['name'] ) );

		update_term_meta( $circle_id, 'hs_version', $item['version'] );
	}

	/**
	 * Create the circle if missing in WP.
	 *
	 * @param array $item Circle to create.
	 *
	 * @return int|mixed Circle ID.
	 */
	public function create_circle( $item ) {
		$circle_id = wp_insert_term( $item['name'], 'holaspirit_tax' );
		$circle_id = $circle_id['term_id'];

		update_term_meta( $circle_id, 'hs_id', $item['id'] );
		update_term_meta( $circle_id, 'hs_version', $item['version'] );

		return $circle_id;
	}
}

// @todo meta velden aan circles
// @todo tree aan circles maken
// @todo multiselect binden aan circles voor users zonder rollen aan circles