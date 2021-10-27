<?php
/**
 * Block Name: Circles
 *
 * This is the template that displays the circles block.
 */

$circles_img  = get_field( 'block_circles_img' );
$circles_link = get_field( 'block_circles_link' );

// create id attribute for specific styling
$id = 'circles-' . $block['id'];

// create align class ("alignwide") from block setting ("wide")
$align_class = $block['align'] ? 'align' . $block['align'] : '';

?>

<div id="<?php echo $id; ?>" class="testimonial <?php echo $align_class; ?>">
	<a href="<?php echo $circles_link; ?>">
		<img src="<?php echo $circles_img['url']; ?>" />
	</a>
</div>
