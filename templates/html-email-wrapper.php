<!-- Inliner Build Version 4380b7741bb759d6cb997545f3add21ad48f010b -->
<!DOCTYPE html>
<?php /**
* HTML Email template, called with variables in scope:
* @var string           $subject
* @var string           $message
* @var string           $brand_type text or html
* @var string           $brand_text
* @var string           $brand_image_url
* @var int              $brand_image_height
* @var int              $brand_image_width
* @var string           $footer_widgets
* @var string           $footer_type
* @var string           $footer_text
* @var string           $site_icon_url
* @var string           $unsubscribe_url
 */
$is_comment = isset( $comment_header ) ? $comment_header : false;
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

img.retina {
      width: 100% !important;
      max-width: 675px !important;
}

img.featured {
      width: 100% !important;
      max-width: 720px !important;
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
      color: black;
    }
    .padded {padding: 0 20px 20px 20px;}
    .padded a {color:black;}
    .gray {background: #fbfbfb; padding: 25px; border-top: 1px solid #ddd;}
    .padded h3 {clear: both;}
    .padded h3.reply {clear: none;}
    #the_title {padding-bottom: 0;}
    #the_content a {
          color: #348eda;
    }
    #button {
      clear: both !important;
      margin-top: 25px;
    }
    .btn-primary {
      text-decoration: none;
      color: #FFF !important;
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
    .capitalize {text-transform: capitalize;}

    .last {
      margin-bottom: 0;
    }

    .logo {
      display: block;
      text-align: center;
      float: none !important;
      height: auto;
      <?php if ( $brand_image_width < 720 ) : ?>
        width: <?php echo intval( $brand_image_width ); ?>px !important;
        margin: 20px auto 15px auto !important;
      <?php else: ?>
        width: 100% !important;
        margin: 0 auto 15px auto !important;
      <?php endif; ?>
    } 

    .brand {background: #fff;max-width: 760px!important;border-bottom: 0 !important;}
    .brand img.favicon {width: 32px !important; float: left; margin-right: 10px;}
    .brand h2.sitename {padding: 0 !important;}
    .commentheader td, .noheader {padding: 20px 20px 0 20px;}
    .post {border-top: 0 !important;}
    .first {
      margin-top: 0;
    }
    .padding {
      padding: 10px 0;
    }

    .alignright {float: right !important; margin: 0 0 20px 20px !important;}
    .alignleft {float: left; margin: 0 20px 20px 0;}
    .aligncenter, .alignnone {margin: 20px auto; display: block !important; float: none; width: auto !important;}

 
    .gallery-item, .ngg-gallery-thumbnail-box {float: left !important; margin: 5px;}
    .gallery-caption {margin 0;}
    .wp-caption-text.gallery-caption {width: 110px; font-size: 10px;}
    .wp-caption {max-width: 100% !important; height: auto !important;}
    .alignright .wp-caption-text {text-align: right;width: 100% !important;clear:right !important;}
    .alignright img {float:right;}

    /* -------------------------------------
        BODY
    ------------------------------------- */
    table.wrap {
      width: 100%;
      border-collapse: collapse;
    }
    table.wrap .container {
      padding: 0 !important;
      margin-bottom: 20px;
      border: 1px solid #f0f0f0;
    }
    table.header {
      padding: 0 !important;
      border: 1px solid #f0f0f0;
      border-bottom: none;
      max-width: 722px;
      width: 100%;
      margin: 0 auto;
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
      clear: both;
    }
    .footnote h3, .footnote h4 {font-size: 12px; margin-bottom: 5px;}
    .footnote p  {font-size: 10px; margin-bottom: 15px;}

    /* -------------------------------------
        TYPOGRAPHY
    ------------------------------------- */
    h1, h2, h3 , h4 {
      font-family: "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
      line-height: 1.1;
      margin-bottom: 15px;
      color: #000;
      line-height: 1.2;
      font-weight: 200;
    }

    h1 a {
      color: #000;
      text-decoration: none;
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
      font-weight: normal;
      font-size: 16px;
      margin-bottom: 5px;
    }
    p, ul, ol {
      margin-bottom: 10px;
      font-weight: normal;
      font-size: 14px;
    }

    pre {display: block;font-family: courier;}

    ul li, ol li {
      margin-left: 25px !important;
      list-style-position: inside;
    }

    ol li {list-style-type: decimal;}

    blockquote { background:#f9f9f9;border:1px solid #eee;padding:15px;font-style: italic;margin: 15px 0 !important; clear: both !important;}

    .alert {background: #FFFEBA; padding: 2px; font-weight: normal;}
    .noforward { background: #fffeee; padding: 2px;}
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
    .wrap .container {
      padding: 20px;
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

/*Sharedaddy by Jetpack and Juiz Social Share*/
.sd-content ul li, ul.juiz_sps_links_list li {
  list-style: none;
  display: inline;
}

.sd-title { clear: both !important;}

.content .sd-content ul li, ul.juiz_sps_links_list li {
  margin: 0 5px 10px 0 !important;
  display: block;
  float: left;
}
.content .sd-content ul li a, ul.juiz_sps_links_list li a {
  color: #555 !important;
  font-size: 12px;
  padding: 5px 8px;
  -webkit-border-radius: 4px;
  -moz-border-radius: 4px;
  border-radius: 4px;
  border: 1px solid #bbb;
  background: #F8F8F8;
  text-decoration: none;

}
#socialmedia-container div {
  float: left;
  margin-right: 10px;
}
.sd-like {display: none !important;}

/*Jetpack tiled gallery*/
.gallery-row {width: 675px !important; float: none !important;}
.gallery-group {float: left; margin-bottom: 20px !important;}
.tiled-gallery-caption {font-size: 70% !important; padding-left: 5px !important;}
.tiled-gallery img {margin: 1px !important;}


/*Beta styles*/

.inverse td {padding: 4%;}
.inverse h3, .inverse h4 {margin: 0 0 5px 0; }
.brand h1 {font-size:42px; color:#348eda;padding:0;margin:3% 2%;}
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

/*oembed placeholders*/

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

.et_social_inline {display: none !important;}

/*zemanta related posts*/
div.zem_rp_wrap {
  background: #eee;
  border: 1px solid #ddd;
  padding: 5px;
}

ul.related_post {
  margin: 0;
  padding: 0;
}

ul.related_post li {
  list-style: none;
  float: left;
  text-align: center;
  font-size: 85%;
}

.socialmedia-buttons a {
  float: left;
  display: block;
  margin-right: 5px;
}

.ssba a {
  float: left;
  margin-right: 5px;
}

/*flexible posts widget*/
ul.dpe-flexible-posts li {
  clear: left;
  margin-bottom: 10px;
}

ul.dpe-flexible-posts li a img {
  width: 50px;
  height: auto;
  float: left;
  margin-right: 10px;
  margin-bottom: 10px;
}

.shopthepost-widget {
  display: none !important;
}

/*yumrecipes*/
.blog-yumprint-recipe-published,.blog-yumprint-header,.blog-yumprint-adapted-print,.blog-yumprint-recipe-source {display: none;}
.blog-yumprint-recipe {border: 1px dashed #ddd; border-radius: 5px; padding: 25px; margin: 10px;}
.blog-yumprint-subheader {font-size: 150%;border-bottom: 1px solid #eee;}

/*wp ultimate recipes*/
.wpurp-responsive-desktop,.wpurp-recipe-image,.wpurp-recipe-servings-changer {display: none;}

/*Hupso social*/
.hupso_toolbar {display: none;}

/*Official twitter plugin*/
.twitter-share .twitter-share-button {
  color: #555 !important;
  font-weight: bold;
  font-size: 12px;
  padding: 5px 8px;
  -webkit-border-radius: 4px;
  -moz-border-radius: 4px;
  border-radius: 4px;
  border: 1px solid #bbb;
  background: #F8F8F8;
  text-decoration: none; 
}

/*Social warfare*/
.nc_socialPanel {
  margin: 25px 0;
  height: 30px;
  clear: both;
}

.nc_socialPanel .count {
  display: none !important;
}

.nc_socialPanel .nc_tweetContainer {
  float: left;
  width: auto;
  margin-right: 8px;
}

.nc_socialPanel a {
  display: block;
  -webkit-border-radius: 20px;
  -moz-border-radius: 20px;
  border-radius: 20px;
  color: #ffffff !important;
  background: #AAAAAA;
    padding: 5px 15px !important;
  text-decoration: none;
  font-weight: bold;
  font-size: 12px;

}

.nc_socialPanel a:hover {
  background-color: #343434 !important;
}

.nc_tweetContainer.googlePlus a {
  background: #DF4B37;
}
.nc_tweetContainer.twitter a {
  background: #5FA8DC;
}
.nc_tweetContainer.fb a {
  background: #3A589E;
}
.nc_pinterest {
  display: none !important;
}
.nc_tweetContainer.linkedIn a {
  background: #0D77B7;
}

a.sw_CTT {
  display: block;
  margin: 25px 0;
  border: 1px solid #ddd;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
  padding: 15px;
  font-size: 115%;
  letter-spacing: 1px;
  line-height: 100%;
  color: gray;
  text-decoration: none !important;
}

a.sw_CTT span.sw-ctt-text {
  color: gray;
  font-style: italic;
}

a.sw_CTT span.sw-ctt-btn {
  display: block;
  text-align: right;
  text-transform: uppercase;
  font-size: 60%;
  font-weight: bold;
  letter-spacing: normal;
}

.widgets-list-layout li {
    clear: left !important;
  }

.widgets-list-layout li img {
  width: 35px !important;
  height: 35px !important;
  float: left !important;
  margin: 0 10px 10px 0 !important;
}

/*Juiz social share*/
.juiz_sps_maybe_hidden_text {display: none;}


/*Mobile syles*/
    @media only screen and (max-width: 480px) {
     table.wrap {
      width: 100% !important;
      padding: 0% !important;
    }

    table.wrap .container {padding: 0 !important; border: 0 !important;}
    .header {border: 0 !important;}
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
    .wrap {padding: 0 !important;}
      
      .logo {
      <?php if ( $brand_image_width < 720 ) : ?>
        margin: 20px 20px 15px 20px !important;
        width: 80% !important;
        max-width: 90% !important;
      <?php else: ?>
        width: 100% !important;
        margin: 0 auto 15px auto !important;
      <?php endif; ?>
    }
    #content img {float: none !important; margin: 10px auto !important;}
    img.avatar {float: left !important;}
    #content img.avatar, #content img.reply-icon {float: left !important; clear: left !important; margin: 0 10px 0 0 !important;}
    .gallery br {display:none !important; clear: none !important;}
    .gallery-item, .ngg-gallery-thumbnail-box {margin: 5px auto !important; float: none !important; display: block !important; width: 100% !important; text-align: center !important;}
    .gallery-item img, .ngg-gallery-thumbnail-box img {margin: 0 auto !important; display: block !important;}
    .gallery-caption {width: auto !important; text-align: center;}
    #demo { float: none; width: auto; padding: 20px; margin: 20px 0;}
    #demo p, #demo h3, #demo h4 { margin: 5px 0;}
    }

/*Comments Template*/
img.avatar {width: 48px !important; height: 48px !important; max-height: 48px !important;float: left !important;  padding-bottom: 15px; border-radius: 5px;}

.inreply {font-weight: normal; font-size: 120%; color: #737373; margin-bottom: 15px;}
.inreply a {color: black; text-decoration: none !important; font-style: italic;}
.author-name,.comment-date,.comment-body { margin-left: 60px;}
.author-name, .author-name a {color: #DF623B; font-style: italic; font-family: serif; line-height: normal;}
.comment {font-size: 100%; line-height:normal; min-height: 55px; clear: left !important;}
.comment-date {color: gray; font-size: 90%;}
.comment-header {padding-bottom: 0; font-size: 100%;margin-bottom: 15px !important; }
.comment-body {color: #000;}
.rejoin .comment-header {opacity: 0.5;}
.rejoin .comment.post-flood .comment-header {opacity: 1.0;}
.reply {padding-bottom: 15px; margin-left: 40px; clear: none;}
.newpost {border-bottom: none;  margin-top: 25px; padding-bottom: 15px;}
.reply-prompt {clear: both; margin-top: 20px; margin-bottom: 20px;}
.reply-prompt img {float: left !important; margin-right: 10px; width: 30px; height: 30px;}
.reply-prompt h3 {font-size: 125% !important; padding-top: 5px !important; clear: none;}
.reply-prompt h3 small {display: block; font-size: 70% !important;}
.reply-prompt p {margin-bottom: 0 !important;}
.reply-promtp a {color: black;}
.previous-comments {margin-bottom: 30px;}
.previous-comment-3 {opacity: .4;}
.previous-comment-2 {opacity: .6;}
.previous-comment-1 {opacity: .8;}
/*.new-reply {margin-left: 55px; margin-bottom: 25px; font-size: 115%;}*/
.depth-2 {margin-left: 25px; margin-bottom: 15px;}
.depth-3 {margin-left: 25px; margin-bottom: 15px;}
.depth-4 {margin-left: 25px; margin-bottom: 15px;}
.depth-5 {margin-left: 25px; margin-bottom: 15px;}
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
.context {font-size: 90%; line-height: normal; margin-bottom: 45px;}
.context .excerpt {font-style: italic;}
.context h4 {margin-bottom: 10px;}
.context img {float: left; margin-right: 15px; padding: 0px; border: 1px solid #ddd; margin-bottom: 25px;}

    </style>
    <!-- body -->

   <?php if ( $is_comment ) : ?>
       <table class="header wrap commentheader">
          <tr>
              <td class="brand" bgcolor="#FFFFFF">
                <img width="32" height="32" src="<?php echo $site_icon_url; ?>" class="favicon" />
                <h2 class="sitename"><?php echo $brand_text; ?></h2>
              </td>
          </tr>
        </table>

   <?php elseif ( !$is_comment ) : ?>

       <table class="header wrap">
          <tr>
              <td class="brand" bgcolor="#FFFFFF">
                <?php if ( Prompt_Enum_Email_Header_Types::IMAGE === $brand_type ) : ?>
                <img width="<?php echo intval( $brand_image_width ); ?>" src="<?php echo esc_attr( $brand_image_url ); ?>" align="middle" class="logo" />
                <?php else : ?>
                <div class="noheader">
                  <img width="32" height="32" src="<?php echo $site_icon_url; ?>" class="favicon" />
                <h2 class="sitename"><?php echo $brand_text; ?></h2>
                </div>

                <?php endif; ?>
              </td>
          </tr>
        </table>

   <?php endif; ?>

    <table class="body wrap">
      <tr>
    <td class="post container" bgcolor="#FFFFFF">
      <!-- content -->
      <div class="content">
        <table>
         
          <tr>
            <td>
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
              <?php if ( $is_comment ) : ?>
                  <?php Prompt_Comment_Email_Footer_Sidebar::render(); ?>
              <?php elseif ( !$is_comment ) : ?>
                  <?php Prompt_Email_Footer_Sidebar::render(); ?>
              <?php endif; ?>
          <?php else : ?>
              <?php echo $footer_text; ?>
          <?php endif; ?>
        </tr></table>
        <?php if ( empty( $suppress_delivery ) ) : ?>
        <table>
          <tr>
            <td class="credit">
              <p>
                  <?php
                  printf(
                      __( 'Sent from %s.', 'Postmatic' ),
                      '<a href="' . get_bloginfo( 'url' ) . '">' . get_bloginfo( 'name' ) . '</a>'
                  );
                  ?>
                  <?php
                  printf(
                      __( 'Delivered by <a href="%s">Postmatic</a>.', 'Postmatic' ),
                      path_join( Prompt_Enum_Urls::HOME, '?utm_source=footer&utm_medium=email&utm_campaign=pluginfooter' )
                  );
                  ?>
              </p>
              <?php if ( !empty( $unsubscribe_url ) ) : ?>
                  <p><unsubscribe>
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
                  </unsubscribe></p>
              <?php endif; ?>
            </td>
          </tr>
        </table>
        <?php endif; ?>
      </div>
      <!-- /content -->
      
    </td>
    <td></td>
    </tr></table><!-- /footer --></body>
  </html>