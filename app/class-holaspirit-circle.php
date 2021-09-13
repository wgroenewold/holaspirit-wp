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
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_fetch_circles', array( $this, 'fetch_circles' ), 10, 0 );
	}

	public function fetch_circles(){

	}
}