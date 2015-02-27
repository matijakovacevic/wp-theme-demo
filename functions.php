<?php
/**
 * demoTheme functions and definitions
 *
 * @package demoTheme
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 640; /* pixels */
}

if ( ! function_exists( 'demotheme_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function demotheme_setup() {

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on demoTheme, use a find and replace
	 * to change 'demotheme' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'demotheme', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

    add_theme_support('post-thumbnails');

    add_image_size('large', 700, '', true); // Large Thumbnail
    add_image_size('medium', 250, '', true); // Medium Thumbnail
    add_image_size('small', 120, '', true); // Small Thumbnail
    add_image_size('custom-size', 700, 200, true); // Custom Thumbnail Size call using the_post_thumbnail('custom-size');

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	//add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'demotheme' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 * See http://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside', 'image', 'video', 'quote', 'link',
	) );

	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'demotheme_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );
}
endif; // demotheme_setup
add_action( 'after_setup_theme', 'demotheme_setup' );

/**
 * Register widget area.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
function demotheme_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'demotheme' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}
add_action( 'widgets_init', 'demotheme_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function demotheme_scripts() {
	wp_enqueue_style( 'demotheme-style', get_stylesheet_uri() );

	wp_enqueue_script( 'demotheme-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20120206', true );

	wp_enqueue_script( 'demotheme-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20130115', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'demotheme_scripts' );



/**
 * Add custom meta field (rating) to posts
 */
function add_meta_boxes(){
	// Add this metabox to every selected post
    add_meta_box(
        'rating_section',
        'Rating for this post',
        'add_inner_meta_boxes',
        'post',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'add_meta_boxes');

function add_inner_meta_boxes(){
	global $post;

	$post_meta = get_post_meta($post->ID, 'post_rating', true);
	$rating = _e('Rating (1-5)', 'demoTheme');

	echo <<<HTML
<table>
    <tr>
        <th class="metabox_label_column">
            <label for="post_rating">$rating</label>
        </th>
        <td>
            <input type="text" id="post_rating" name="post_rating" value="$post_meta" />
        </td>
    </tr>
</table>
HTML;
}

function save_post($post_id){
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    {
        return;
    }

    if(isset($_POST['post_type']) && $_POST['post_type'] == 'post' && current_user_can('edit_post', $post_id))
    {
    	// validate and sanitize
    	$post_rating = (int) $_POST['post_rating'];
    	$post_rating = ($post_rating < 1 || $post_rating > 5) ? 0 : $post_rating;

        // Update the post's meta field
        update_post_meta($post_id, 'post_rating', $post_rating);
    }
    else
    {
        return;
    }
}
add_action('save_post', 'save_post');

// END: Add custom meta field (rating) to posts //

/**
 * Filter and order by custom field (post_rating)
 */
function filter_order_posts_by_rating( $query ) {
    if ( $query->is_home() && $query->is_main_query() ) {
        $query->set( 'posts_per_page', 5 );
        $query->set( 'meta_key', 'post_rating' );
        $query->set( 'orderby', 'post_rating' );
        $query->set( 'order', 'DESC' );
    }
}
add_action( 'pre_get_posts', 'filter_order_posts_by_rating' );


/**
 * Implement the Custom Header feature.
 */
//require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';
