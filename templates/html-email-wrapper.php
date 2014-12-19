<!-- Inliner Build Version 4380b7741bb759d6cb997545f3add21ad48f010b -->
<!DOCTYPE html>
<?php /**
* HTML Email template, called with variables in scope:
*  @var string  $subject
*  @var string  $message
* @var string  $brand_type text or html
* @var string  $brand_text
* @var string  $brand_image_url
* @var int     $brand_image_height
* @var int     $brand_image_width
* @var string  $footer_widgets
* @var string  $footer_type
* @var string  $footer_text
*/
?>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php bloginfo( 'name' ); ?> | <?php echo esc_html( $subject ); ?></title>
  </head>
  <body bgcolor="#f6f6f6"><style type="text/css">
  /* -------------------------------------
        GLOBAL
    ------------------------------------- */
    * {
      margin: 0;
      padding: 0;
      font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
      font-size: 100%;
      line-height: 1.6;
    }
    a img {
    border: none;
    }
    img {
    outline:none;
    text-decoration:none;
    -ms-interpolation-mode: bicubic;
    width: auto;
    max-width: 100%;
    clear: both;
    height: auto !important;
    display: block;
    }
    
    body {
      -webkit-font-smoothing: antialiased;
      -webkit-text-size-adjust: none;
      width: 100%!important;
      height: 100%;
    }
    /* -------------------------------------
        ELEMENTS
    ------------------------------------- */
    a {
      color: #348eda;
    }
    #button {
      clear: both;
      margin-top: 25px;
    }
    .btn-primary {
      text-decoration: none;
      color: #FFF;
      background-color: #348eda;
      border: solid #348eda;
      border-width: 10px 20px;
      line-height: 2;
      font-weight: bold;
      margin-right: 10px;
      margin-bottom:10px;
      text-align: center;
      cursor: pointer;
      display: inline-block;
      border-radius: 25px;
    }
    .btn-secondary {
      text-decoration: none;
      color: #FFF;
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
    .capitalize {text-transform: capitalize;}

    .last {
      margin-bottom: 0;
    }

    .logo {
      display: block;
      margin: 15px auto !important;
      text-align: center;
      float: none !important;
      height: auto;
    }

    .first {
      margin-top: 0;
    }
    .padding {
      padding: 10px 0;
    }

    .alignright {float: right !important; margin: 0 0 20px 20px !important;}
    .alignleft {float: left; margin: 0 20px 20px 0;}
    .aligncenter {margin: 20px auto; display: block !important; float: none;}

 
    .gallery-item, .ngg-gallery-thumbnail-box {float: left !important; margin: 5px;}
    .gallery-caption {margin 0;}
    .wp-caption-text.gallery-caption {width: 110px; font-size: 10px;}
    .wp-caption {max-width: 100% !important; height: auto !important;}
    .alignright .wp-caption-text {text-align: right;width: 100% !important;clear:right !important;}
    .alignright img {float:right;}

    /* -------------------------------------
        BODY
    ------------------------------------- */
    table.body-wrap {
      width: 100%;
      padding: 2%;
    }
    table.body-wrap .container {
      border: 1px solid #f0f0f0;
      margin-bottom: 20px;

    }
    #web {clear: both; padding-top: 20px;}
    /* -------------------------------------
        FOOTER
    ------------------------------------- */
    table.footer-wrap {
      width: 100%;
      clear: both!important;
      margin-top: 35px !important;
    }

    .footer-wrap .container * {
      font-size: 12px;
      
    }
    .credit a {
      color: #666;
    }
    .midwidget {
      padding: 0 2%;
      width: 37%;
    }
    .widgets td {
      width: 33%;
    }
    .widgets a {
      color: #666;
    }
    .credit {
      color:#666;
      padding-top: 35px;
    }
    .footer-wrap h4 {
      color:#348eda;
      margin-bottom: 10px;
    }
    .footnote {
      font-size: 85% !important;
      clear: both;
      margin-top: 20px;
    }
    /* -------------------------------------
        TYPOGRAPHY
    ------------------------------------- */
    h1, h2, h3 , h4 {
      font-family: "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
      line-height: 1.1;
      margin-bottom: 15px;
      color: #000;
      margin: 40px 0 10px;
      line-height: 1.2;
      font-weight: 200;
    }
    h1 {
      font-size: 36px;
    }
    h2 {
      font-size: 28px;
    }
    h3 {
      font-size: 22px;
    }
    h4 {
      font-weight: bold;
    }
    p, ul, ol {
      margin-bottom: 10px;
      font-weight: normal;
      font-size: 14px;
    }

    pre {display: block;font-family: courier;}

    ul li, ol li {
      margin-left: 5px;
      list-style-position: inside;
    }

    ol li {list-style-type: decimal;}

    blockquote { background:#f9f9f9;border:1px solid #eee;padding:5%;font-style: italic;}

    .alert {background: #FFFEBA; padding: 2px; font-weight: normal;}
   .slideshowlink {margin: 15px 0; text-align: center;} 
   .addtoany_list a {float: left;}
    /* ---------------------------------------------------
        RESPONSIVENESS
        Nuke it from orbit. It's the only way to be sure.
    ------------------------------------------------------ */
    /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
    .container {
      display: block!important;
      max-width: 720px!important;
      margin: 0 auto!important; /* makes it centered */
      clear: both!important;
    }
    /* Set the padding on the td rather than the div for Outlook compatibility */
    .body-wrap .container {
      padding: 2%;
    }
    .footer-wrap .container {padding: 0 !important;max-width: 720px !important;}
    /* This should also be a block element, so that it will fill 100% of the .container */
    .content {
      max-width: 720px;
      margin: 0 auto;
      display: block;
    }
    .footer-wrap {padding: 0 2%;}
    .footer-wrap .content {
      max-width: 720px!important;
    }
    /* Let's make sure tables in the content area are 100% wide */
    .content table {
      width: 100%;
    }
    .widgets li, .widgets ul {list-style: none !important; margin-left: 0 !important; padding-left: 0 !important;}
    .alignright {float: right !important; margin: 0 0 10px 10px !important;}

/*Sharedaddy Support*/
.sd-content ul li {
  list-style: none;
  display: inline;
}

.content .sd-content ul li a {
  color: #555;
  font-size: 12px;
  padding: 5px 8px;
  -webkit-border-radius: 4px;
  -moz-border-radius: 4px;
  border-radius: 4px;
  border: 1px solid #bbb;
  background: #F8F8F8;
  text-decoration: none;

}

/*Beta styles*/

.inverse td {padding: 4%;}
.inverse h3, .inverse h4 {margin: 0 0 5px 0; }
.brand h1 {font-size:36px; color:#348eda;padding:0;margin:0 0 5px 0;}
.left {float: left;margin:0; width: 45%}
.right {margin-left:50%; width:45%;}

/*Plugin and shortcode specific*/
   .gallery, .ngg-galleryoverview {
      margin: 10px 0; padding: 15px 5px 0 5px; border: 1px solid #ddd; background: #eee; width: 100%; float: left;
    }

.incompatible {
    background: #eee; 
    border: 1px solid #ddd; 
    padding: 15px;
    margin: 20px 0;
  }

.www-youtube-com,.animoto-com,.blip-tv,.www-collegehumor-com,.www-dailymotion-com,.flickr-com,.www-flickr-com,.www-funnyordie-com,.www-hulu-com,.embed-revision3-com,.www-ted-com,.vimeo-com,.vine-co,.wordpress-tv{
  background-image: url(<?php echo Prompt_Core::$url_path . '/media/video.jpg' ;?>);
}

.www-mixcloud-com,.www-rdio-com,.www-soundcloud-com,.soundcloud-com,.w-soundcloud-com,.www-spotify-com {
  background-image: url(<?php echo Prompt_Core::$url_path . '/media/audio.jpg' ;?>);
}

.issueembed,.embedarticles-com,.www-scribd-com,.www-slideshare-net {
  background-image: url(<?php echo Prompt_Core::$url_path . '/media/article.jpg' ;?>);
}

.embed {
  width: 95% !important;
  overflow: hidden;  
}

.incompatible.embed {
  background-color: #333 !important;
  background-size: 100% !important;
  background-repeat: no-repeat;
  background-position: bottom center;
  width: 95%;
  overflow: hidden;
  height: 180px;
  padding-top:150px;
  color: #fff;
  text-align: center; 
}

.incompatible.embed a {
  display: block;
  width: 100%;
  height: 90%;
}

/*Mobile syles*/
    @media only screen and (max-width: 480px) {
     table.body-wrap {
      width: 100% !important;
      padding: 0% !important;
    }
    .container {padding: 0 !important; border: 0 !important;}
    .credit {
    text-align: left !important;
    }
    .left,.right {width: 100% !important;float: none !important;}
    .padding img {
    width: auto !important; height: auto !important;
    }
    .widgets {
    padding: 0 !important;
    }
    .widgets td {
    width: 100% !important; display: block !important; margin-bottom: 15px !important;
    }
    .midwidget {
    padding: 0 !important;
    }
    .body-wrap { padding: 10px !imporant;}

    #content img {float: none !important; margin: 10px auto !important;}
    #content img.avatar, #content img.reply-icon {float: left !important; clear: left !important; margin: 0 10px 0 0 !important;}
    .gallery br {display:none !important; clear: none !important;}
    .gallery-item, .ngg-gallery-thumbnail-box {margin: 5px auto !important; float: none !important; display: block !important; width: 100% !important; text-align: center !important;}
    .gallery-item img, .ngg-gallery-thumbnail-box img {margin: 0 auto !important; display: block !important;}
    .gallery-caption {width: auto !important; text-align: center;}
    #demo { float: none; width: auto; padding: 20px; margin: 20px 0;}
    #demo p, #demo h3, #demo h4 { margin: 5px 0;}
    }


/*Demo Styles*/
  #demo {
  float: right;
  width: 31%;
  padding: 0;
  font-style: normal;
  font-size: 85%;
  background: #f4feff;
  margin-left: 20px;
  border: 1px dashed #698688;
  line-height: normal !important;
  }

  #demo h2 {
    padding: 2px;
    text-align: center;
    color:#fff;
    font-weight: bold;
    background: #21bfc7;
    margin-top: 0 !important;
    font-size: 145%;
  }

  #demo p, #demo h3, #demo h4 {
    margin: 2px 10px;
}

/*Comments Template*/
img.avatar {width: 48px !important; height: 48px !important; max-height: 48px !important;float: left; margin-right: 10px; padding-bottom: 15px; border-radius: 5px;}

.inreply {font-weight: normal; font-size: 120%; color: #737373; margin-bottom: 15px;}
.inreply a {color: black; text-decoration: none !important; font-style: italic;}

.author-name {color: #DF623B; font-style: italic; font-family: serif; line-height: normal;}
.comment {margin-bottom: 55px; font-size: 110%;}
.comment-date {color: gray; font-size: 90%;}
.comment-header {padding-bottom: 15px; font-size: 110%;}
.comment-body {clear: left; color: #000;}
.reply {padding-bottom: 35px; border-bottom: 1px solid #ddd; margin-left: 40px;}
.newpost {border-bottom: none;  margin-top: 25px; padding-bottom: 15px;}
.reply-prompt {clear: both; margin-top: 20px;}
.reply-prompt img {float: left !important; margin-right: 10px; width: 30px; height: 30px;}
.previous-comment-3 {opacity: .4;}
.previous-comment-2 {opacity: .6;}
.previous-comment-1 {opacity: .8;}
.new-reply {margin-left: 55px; margin-bottom: 55px; font-size: 115%;}
.the-reply, {margin-bottom: 25px;}
.reply-content {margin-left: 60px;}
.comment blockquote, .previous-comments blockquote, .reply-content blockquote {
  background: #fff;
  border: none;
  border-left: 3px solid #ddd;
  padding: 0;
  padding-left: 10px;
  font-weight: normal;
}

    </style>
    <!-- body -->
    <table class="body-wrap"><tr>
    <td class="container" bgcolor="#FFFFFF">
      <!-- content -->
      <div class="content">
        <table>
          <tr>
            <td class="brand">
              <?php if ( Prompt_Enum_Email_Header_Types::IMAGE === $brand_type ) : ?>
              <img width="<?php echo intval( $brand_image_width ); ?>" src="<?php echo esc_attr( $brand_image_url ); ?>" align="middle" class="logo" />
              <?php else : ?>
              <h1><?php echo $brand_text; ?></h1>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td id="content">
              
              <?php echo $message; ?>
            </td>
          </tr>
        </table>
      </div>
      <!-- /content -->
      
    </td>
    <td></td>
    </tr></table><!-- /body --><!-- footer --><table class="footer-wrap"><tr><td></td>
    <td class="container">
      
      <!-- content -->
      <div class="content widgets">
        <table><tr>
          <?php if ( Prompt_Enum_Email_Footer_Types::WIDGETS === $footer_type ) : ?>
              <?php Prompt_Email_Footer_Sidebar::render(); ?>
          <?php else : ?>
              <?php echo $footer_text; ?>
          <?php endif; ?>
        </tr></table>
        <table>
          <tr>
            <td class="credit">
              <p>
                  <?php
                  printf(
                      __( 'Sent from %s.', 'Postmatic' ),
                      '<unsubscribe><a href="' . get_bloginfo( 'url' ) . '">' . get_bloginfo( 'name' ) . '</a></unsubscribe>'
                  );
                  ?>
              <?php if ( '' == Prompt_Core::$options->get( 'plan' ) ) : ?>
                  <?php
                  printf(
                      __( 'Delivered by %s.', 'Postmatic' ),
                      '<a href="http://gopostmatic.com/?utm_source=footer&utm_medium=email&utm_campaign=pluginfooter">Postmatic</a>'
                  );
                  ?>
              <?php endif; ?>
              </p>
              <h3><?php _e( 'About Postmatic (Beta)', 'Postmatic' ); ?></h3>
              <p style="margin-bottom: 15px;">
                  <?php
                  printf(
                      __(
                          'Postmatic sends your posts to your readers where they’re comfortable: their inbox. ' .
                              'They can send comments back by hitting the reply button. Just like you can with this ' .
                              'very email. We’re currently in public beta. <a href="%s">Sign Up</a> Thanks for being a ' .
                              'willing test pilot! <a href="%s">Share your thoughts in this short survey.</a>',
                          'Postmatic'
                      ),
                      'http://gopostmatic.com/?utm_source=footer&utm_medium=email&utm_campaign=pluginfooter',
                      'https://vernal.typeform.com/to/lehwGc'
                  );
                  ?>
              </p>
            </td>
          </tr>
        </table>
      </div>
      <!-- /content -->
      
    </td>
    <td></td>
    </tr></table><!-- /footer --></body>
  </html>