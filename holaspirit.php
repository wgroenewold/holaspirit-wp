<?php
/**
 * Holaspirit.
 *
 * PHP version 7
 *
 * @category  PHP
 * @package   Holaspirit
 * @author    Wouter Groenewold <wgroenewold@gmail.com>
 * @copyright 2019-2021 G00gle
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link      https://ghocommunicatie.nl
 *
 * @wordpress-plugin
 * Plugin Name: Holaspirit
 * Version:     1.0.0
 * Plugin URI:  https://ghocommunicatie.nl
 * Description: Use Holaspirit-data in WordPress
 * Author:      Wouter Groenewold
 * Author URI:  https://github.com/wgroenewold
 * Text Domain: holaspirit
 * Domain Path: /languages/
 * License:     GPL v3
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace holaspirit;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

define( 'HOLASPIRIT_ROOT', dirname( __FILE__ ) );

setlocale( LC_ALL, 'nl_NL' );

require_once ABSPATH . 'wp-admin/includes/plugin.php';
require HOLASPIRIT_ROOT . '/vendor/autoload.php';

register_deactivation_hook( __FILE__, 'token_deactivate' );

/**
 * Deactivate the cron hook on plugin deactivation.
 */
function token_deactivate() {
	$timestamp = wp_next_scheduled( 'token_cron_hook' );
	wp_unschedule_event( $timestamp, 'token_cron_hook' );
}

require_once 'app/class-holaspirit-admin.php';
require_once 'app/class-holaspirit-api.php';
require_once 'app/class-holaspirit-user.php';
require_once 'app/class-holaspirit-role.php';
require_once 'app/class-holaspirit-circle.php';

( new Holaspirit_Admin() );
( new Holaspirit_API() );
( new Holaspirit_User() );
( new Holaspirit_Role() );
( new Holaspirit_Circle() );
