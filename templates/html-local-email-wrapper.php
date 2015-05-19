<?php
/**
 * HTML Email template, called with variables in scope:
 * @var string $subject
 * @var string $message
 * @var string $brand_type text or html
 * @var string $brand_text
 * @var string $brand_image_url
 * @var int $brand_image_height
 * @var int $brand_image_width
 * @var string $footer_widgets
 * @var string $footer_type
 * @var string $footer_text
 * @var string $site_icon_url
 * @var string $unsubscribe_url
 * @var bool $will_strip_content
 */
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="viewport" content="width=device-width"/>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title><?php bloginfo( 'name' ); ?> | <?php echo esc_html( $subject ); ?></title>
		<style>
		body {line-height: 150%; font-family: sans-serif;}
		a {color: #2980b9;}
		h1 {font-weight: normal; line-height: normal;}
		h1 a { text-decoration: none;}
		p.padded {font-size: 85%;}
    .btn-secondary {
      text-decoration: none;
      color: #FFF !important;
      background-color: #aaa;
      border: solid #aaa;
      border-width: 5px 10px;
      line-height: 2;
      font-weight: normal;
      margin-right: 10px;
      text-align: center;
      cursor: pointer;
      display: inline-block;
      border-radius: 15px;
      margin-top: 10px;
    }

    .comment-body {font-size: 110%; padding-bottom: 10px; border-bottom: 1px solid #ddd;}
		</style>
</head>
<body bgcolor="#fff">
<?php if ( !empty( $will_strip_content ) ) : ?>
	<p style="background:#F6F6F6; padding: 5px; border: 1px dotted #ddd; margin-bottom: 15px; font-size: 90%;">This post
		contains images and other content which are not available in the email version. <br/>
		<a href="<?php the_permalink(); ?>">
			<?php _e( 'Click here to view the full post in your browser', 'Postmatic' ); ?>
		</a>
	</p>
<?php endif; ?>
<h2><?php echo $brand_text; ?></h2>

<div class="content" style="margin-top: 25px;"><?php echo $message; ?></div>
<?php echo $footer_text; ?>
<?php if ( empty( $suppress_delivery ) ) : ?>

	<?php
	printf(
		__( 'Sent from %s.', 'Postmatic' ),
		'<a href="' . get_bloginfo( 'url' ) . '">' . get_bloginfo( 'name' ) . '</a>'
	);
	?> 
	<?php
	printf(
		__( 'Delivered by <a href="%s">Postmatic</a>. ', 'Postmatic' ),
		path_join( Prompt_Enum_Urls::HOME, '?utm_source=footer&utm_medium=email&utm_campaign=pluginfooter' )
	);
	?>

	<?php if ( !empty( $unsubscribe_url ) ) : ?>
		<p>
			<unsubscribe>
				<?php
				printf(
					__(
						'To immediately stop receiving all posts and comments from %s you can <a href="%s">unsubscribe with a single click</a>.',
						'Postmatic'
					),
					get_bloginfo( 'name' ),
					$unsubscribe_url
				);
				?>
			</unsubscribe>
		</p>
	<?php endif; ?>

<?php endif; ?>

</body>
</html>