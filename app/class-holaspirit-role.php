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
class Holaspirit_Role  {
	private $organisation;
	private $path;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->organisation = get_option('hs_organisation', '60b4a127e26ec6769111ec36'); //@todo hier nog iets voor maken wat dat fetched
		$this->path = "api/organizations/$this->organisation/roles";
		add_action( 'wp_ajax_fetch_roles', array( $this, 'fetch_roles' ), 10, 0 );
	}

	/*Helper to make accountabilities usable*/
	public function sort_accountabilities(){
		$accountabilities = $this->get_roles()['linked']['accountabilities'];

		$sorted = array();
		foreach($accountabilities as $value){
			$sorted[$value['id']] = array(
				'description' => strip_tags($value['description']),
				'version' => $value['version'],
			);
		}

		return $sorted;
	}

	public function fetch_roles(){
		$roles = $this->get_roles();
		$current = array();

		foreach($roles as $item){
			$current[] = $this->check_role($item);
		}

		$wp_roles = get_posts(array(
			'meta_key' => 'hs_version',
			'fields' => 'ids',
			'post_type' => 'holaspirit_cpt',
			'numberposts' => -1
		));

		$diff = array_diff($current, $wp_roles);

		foreach($diff as $value) {
			wp_delete_post( $value , true );
		}
	}

	public function get_roles(){
		$instance = new Holaspirit_API();
		$query = array("filter" => "role", "view" => "light", "page" => 1);
		$result = $instance->get($this->path, $query);

		$data = $result['data'];

		while($result['pagination']['nextPage'] !== null){
			$query['page']++;
			$result = $instance->get($this->path, $query);
			$data = array_merge($data, $result['data']);
		}

		return $data;
	}

	public function check_role($item){
		$role = get_posts(array(
			'meta_key' => 'hs_id',
			'meta_value' => $item['id'],
			'post_type' => 'holaspirit_cpt',
			'numberposts' => -1,
		));

		if(is_array($role) && !empty($role)){
			$role_version = get_post_meta($role[0]->ID, 'hs_version', true);
			if($role_version !== $item['version']){
				$this->update_role($item);
			}

			return $role[0]->ID;
		}else{
			return $this->create_role($item);
		}
	}

	public function update_role($item){
		$role_id = get_posts(array(
			'meta_key' => 'hs_id',
			'meta_value' => $item['id'],
			'post_type' => 'holaspirit_cpt',
			'numberposts' => -1,
		));
		$role_id = $role_id[0];

		wp_update_post(array(
			'ID' => $role_id,
			'post_title' => $item['name'],
			'post_type' => 'holaspirit_cpt',
			'post_status' => 'publish',
		));

		update_field('purpose', strip_tags($item['purpose']), $role_id);
		update_post_meta($role_id, 'hs_version', $item['version']);
	}

	public function create_role($item){
		$id = wp_insert_post(array(
			'post_title' => $item['name'],
			'post_type' => 'holaspirit_cpt',
			'post_status' => 'publish',
		));

		update_field('purpose', strip_tags($item['purpose']), $id);
		update_post_meta($id, 'hs_id', $item['id']);
		update_post_meta($id, 'hs_version', $item['version']);

		return $id;
	}
}




//		//Losse functie
//		$accountabilities = array();
//		$sorted = $this->sort_accountabilities();
//		foreach($item['accountabilities'] as $item){
//			$accountabilities[] = array(
//				'id' => $item,
//				'description' => $sorted['item']['description'],
//				'version' => $sorted['item']['version'],
//			);
//		}
//
//		update_field('accountabilities', $accountabilities, $id);
//		//Tot hier
//
//		//@todo ook nog aan de juiste category/circle toekennen.
//		//@todo $item['assignedMembers'] koppelen aan juiste users via een multiselect
