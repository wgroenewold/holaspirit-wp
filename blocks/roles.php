<?php
/**
 * Block Name: Roles
 *
 * This is the template that displays the roles block.
 */

// create id attribute for specific styling
if ( ! empty( $block ) ) {
	$id = 'roles-' . $block['id'];
}

// create align class ("alignwide") from block setting ("wide")
$align_class = $block['align'] ? 'align' . $block['align'] : '';
?>

<div id="<?php echo $id; ?>" class="roles <?php echo $align_class; ?>">
    <?php
    $roles = get_posts(
	    array(
		    'post_type'   => 'holaspirit_cpt',
		    'numberposts' => -1, //@todo paginate
            'order' => 'ASC',
            'orderby' => 'post_title',
	    )
    );

    if($roles){
        echo '<ul class="roles_list">';
	    foreach($roles as $item){
	        $permalink = get_permalink($item->ID);
	        echo "<li><a href=\"$permalink\">$item->post_title</a></li>";
        }
	    echo '</ul>';
    }

    ?>
</div>
