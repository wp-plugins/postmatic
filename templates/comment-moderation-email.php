<?php
/**
 * comment moderation template
 *
 * @var object $comment
 * @var string $commenter_name
 * @var string $type
 * @var string $comment_author_domain
 * @var WP_Post $post
 */

?>

<h3 class="padded">
	<?php printf( __( 'There is a new comment to moderate from <span class="capitalize">%s</span>.', 'Postmatic' ), $commenter_name ); ?>
</h3>

<h4 class="inreply padded">
	<?php
	printf(
		__( 'In reply to: %s.', 'Postmatic' ),
		'<a href="' . get_permalink( $comment->comment_post_ID ) . '">' .  get_the_title( $comment->comment_post_ID ) . '</a>'
	);
	?>
</h4>

<div class="primary-comment comment padded">
	<div class="comment-header">
		<?php echo get_avatar( $comment ); ?>
		<div class="author-name"><p>
			<a href="http://<?php echo $comment_author_domain; ?>"><?php echo $comment->comment_author; ?></a>
		
		(<?php echo $comment->comment_author_email; ?><br /><a href="http://whois.arin.net/rest/ip/<?php echo $comment->comment_author_IP; ?>">
			<?php echo $comment->comment_author_IP; ?>
		</a> | 
		<a href="http://<?php echo $comment_author_domain; ?>"><?php echo $comment_author_domain; ?></a>
		)
	</p></div>

		<div class="comment-body">
			<em><?php echo wpautop( $comment->comment_content ); ?></em>
		</div>
	</div>
</div>



<h3 class="padded"><?php _e( 'Reply, Approve, Trash, or Spam?', 'Postmatic' ); ?></h3>

<ul> 
	<li>
		<?php _e( '<strong>Approve and Reply</strong>: Reply to this email with your response to both approve this comment and reply to it.', 'Postmatic' ); ?>
	</li>
	<li>
		<a href="<?php echo admin_url( 'comment.php?action=approve&c=' . $comment->comment_ID ); ?>"><?php
			_e( 'Approve', 'Postmatic' );
		?></a>:
		<?php _e( 'Reply to this email with a blank message or the word <em>approve</em>.', 'Postmatic' ); ?>
	</li>
	<li>
		<a href="<?php echo admin_url( 'comment.php?action=trash&c=' . $comment->comment_ID ); ?>"><?php
			_e( 'Trash', 'Postmatic' );
		?></a>:
		<?php _e( 'Reply to this email with the word <em>trash</em>.', 'Postmatic' ); ?>
	</li>
	<li>
		<a href="<?php echo admin_url( 'comment.php?action=spam&c=' . $comment->comment_ID ); ?>"><?php
			_e( 'Spam', 'Postmatic' );
		?></a>:
		<?php _e( 'Reply to this email with the word <em>spam</em>.', 'Postmatic' ); ?>
	</li>
</ul>