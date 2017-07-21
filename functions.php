<?php

function cloudy_after_setup_theme() {

    global $content_width;

    if ( ! isset( $content_width ) ) {
        $content_width = 650;
    }

    add_editor_style('editor-style.css');

    register_nav_menu('primary', 'Header Menu');

    add_theme_support('automatic-feed-links');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-header', array(
        'default-image' => get_template_directory_uri() . '/img/heading.jpg',
        'width'  => 707,
        'height' => 200,
        'default-text-color' => 'ffffff',
        'wp-head-callback' => 'cloudy_header_style',
    ));
    add_theme_support('custom-background');
    add_theme_support('html5');
}
add_action('after_setup_theme', 'cloudy_after_setup_theme');

function cloudy_header_style() {
    ?><style type="text/css">
        #header {
            background: url(<?php header_image() ?>) 0 52px no-repeat;
        }
        #heading a,
        #heading .description {
            color: #<?php header_textcolor();?>;
        }
    </style><?php
}

$cloudy_themename = "Cloudy";
$cloudy_shortname = "cld";
$cloudy_options = array(
    array(
        "name" => "Message",
        "desc" => "Text to display as welcome message.",
        "id" => $cloudy_shortname."_welcome_message",
        "type" => "textarea"
    ),
);

add_action( 'widgets_init', 'cloudy_widgets_init' );
function cloudy_widgets_init() {
        register_sidebar( array(
        'name'  => 'Sidebar',
        'id'    => 'sidebar',
        'description'   => 'Left Sidebar',
        'before_title'=>'<h3>',
        'after_title'=>'</h3>',
        'before_widget'=>'<div class="box">',
        'after_widget'=>'</div>'
    ));
}

# get recent comments
function cloudy_recent_comments($src_count = 7, $src_length = 60, $pre_HTML = '<li><h2>Recent Comments</h2>', $post_HTML = '</li>') {
    $comments = get_comments(array(
        'orderby'   => 'comment_date_gmt',
        'order'     => 'DESC',
        'number'    => $src_count,
        'status'    => 'approve',
    ));

    $output = $pre_HTML;
    $output .= "\n<ul>";
    foreach ($comments as $comment) {
        $content = substr(strip_tags($comment->comment_content), 0, $src_length);
        if (strlen($content) == $src_length) {
            $content .= '...';
        }
        $output .= "\n\t<li><div class=\"author\"><a href=\"" . get_permalink($comment->comment_post_ID) . "#comment-" . $comment->comment_ID  . "\">" . $comment->comment_author . "</a></div><div class=\"comment\">" . $content . "</div></li>";
    }
    $output .= "\n</ul>";
    $output .= $post_HTML;

    echo $output;
}

function cloudy_wp_enqueue_scripts() {

    wp_enqueue_style( 'cloudy', get_stylesheet_uri() );

    wp_register_style('cloudy-print', get_template_directory_uri().'/print.css', '', 'false', 'print');
    wp_enqueue_style( 'cloudy-print');

    if ( is_singular() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action('wp_enqueue_scripts', 'cloudy_wp_enqueue_scripts');


add_action( 'admin_init', 'cloudy_options_init' );
add_action( 'admin_menu', 'cloudy_options_add_page' );

function cloudy_options_init(){
    register_setting( 'cloudy_theme_options', 'cloudy_theme_options', 'cloudy_options_validate' );
}

function cloudy_options_add_page() {
    add_theme_page('Cloudy Options', 'Cloudy Options', 'edit_theme_options', 'theme_options', 'cloudy_theme_options_do_page' );
}

function cloudy_theme_options_do_page() {
	if ( ! isset( $_REQUEST['updated'] ) )
		$_REQUEST['updated'] = false;
	?>
	<div class="wrap">
		<?php screen_icon(); echo "<h2>" . wp_get_theme() . " Options</h2>"; ?>
		<?php if ( false !== $_REQUEST['updated'] ) : ?>
            <div class="updated fade"><p><strong>Options saved</strong></p></div>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'cloudy_theme_options' ); ?>
			<?php $options = get_option( 'cloudy_theme_options' ); ?>
			<table class="form-table">
                <tr valign="top"><th scope="row">Welcome title</th>
                    <td>
                        <input id="cloudy_theme_options[welcome_title]" class="regular-text" type="text" name="cloudy_theme_options[welcome_title]" value="<?php echo esc_attr( $options['welcome_title']); ?>" />
                    </td>
                </tr>
                <tr valign="top"><th scope="row">Welcome Message</th>
                    <td>
                        <textarea id="cloudy_theme_options[welcome_message]" name="cloudy_theme_options[welcome_message]" rows="5" cols="50"><?php echo esc_attr( $options['welcome_message']); ?></textarea>
                    </td>
                </tr>
                <tr valign="top"><th scope="row">Welcome Message Author</th>
                    <td>
                        <input id="cloudy_theme_options[welcome_author]" class="regular-text" type="text" name="cloudy_theme_options[welcome_author]" value="<?php echo esc_attr( $options['welcome_author']); ?>" />
                    </td>
                </tr>
			</table>
			<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Options', 'cloudy'); ?>" /></p>
		</form>
	</div>
	<?php
}
function cloudy_options_validate( $input ) {
	$input['welcome_title']   = wp_filter_nohtml_kses( $input['welcome_title'] );
    $input['welcome_message'] = wp_filter_nohtml_kses( $input['welcome_message'] );
	$input['welcome_author']  = wp_filter_nohtml_kses( $input['welcome_author'] );

	return $input;
}

$cloudyThemeOptions = get_option('cloudy_theme_options');
function cloudy_theme_option($option) {
	global $cloudyThemeOptions;
	return $cloudyThemeOptions[$option];
}


function cloudy_remove_width_attribute( $html ) {
   $html = preg_replace( '/(width|height)="\d*"\s/', "", $html );
   return $html;
}
add_filter( 'post_thumbnail_html', 'cloudy_remove_width_attribute', 10 );
add_filter( 'image_send_to_editor', 'cloudy_remove_width_attribute', 10 );


function cloudy_wp_title( $title, $sep ) {
    global $paged, $page;

    if ( is_feed() ) {
        return $title;
    }

    // Add the site name.
    $title .= get_bloginfo( 'name' );

    // Add the site description for the home/front page.
    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description && ( is_home() || is_front_page() ) ) {
        $title = "$title $sep $site_description";
    }

    // Add a page number if necessary.
    if ( $paged >= 2 || $page >= 2 ) {
        $title = "$title $sep " . sprintf( __( 'Page %s', 'twentyfourteen' ), max( $paged, $page ) );
    }

    return $title;
}
add_filter( 'wp_title', 'cloudy_wp_title', 10, 2 );
