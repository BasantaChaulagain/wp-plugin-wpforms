<?php
//missing constant
define("WP_DEBUG",false);
define("WP_DEBUG_DISPLAY",false);
define("WP_USE_EXT_MYSQL",false);
define("OBJECT","OBJECT");
define("ABSPATH",__DIR__.'/');

//includes
include ABSPATH. "wp-admin/includes/class-wp-hook.php";
include ABSPATH. "wp-admin/includes/class-wp-wpdb.php";

//missing global
//string array: store func name for each hook
$wp_filter = array();
$wp_current_filter = array();
$blog_id = "test.default.com";
$wpdb = new fake_wpdb('root','123','wptest','127.0.0.1');

function _wp_call_all_hook( $args ) {
    global $wp_filter;
 
    $wp_filter['all']->do_all_hook( $args );
}

function apply_filters( $hook_name, $value ) {
    global $wp_filter, $wp_current_filter;
 
    $args = func_get_args();
 
    // Do 'all' actions first.
    if ( isset( $wp_filter['all'] ) ) {
        $wp_current_filter[] = $hook_name;
        _wp_call_all_hook( $args );
    }
 
    if ( ! isset( $wp_filter[ $hook_name ] ) ) {
        if ( isset( $wp_filter['all'] ) ) {
            array_pop( $wp_current_filter );
        }
 
        return $value;
    }
 
    if ( ! isset( $wp_filter['all'] ) ) {
        $wp_current_filter[] = $hook_name;
    }
 
    // Don't pass the tag name to WP_Hook.
    array_shift( $args );
 
    $filtered = $wp_filter[ $hook_name ]->apply_filters( $value, $args );
 
    array_pop( $wp_current_filter );
 
    return $filtered;
}

function do_action( $hook_name, $value ) {
    return apply_filters($hook_name, $value);
}

function add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
    global $wp_filter;
 
    if ( ! isset( $wp_filter[ $hook_name ] ) ) {
        $wp_filter[ $hook_name ] = new WP_Hook();
    }
 
    $wp_filter[ $hook_name ]->add_filter( $hook_name, $callback, $priority, $accepted_args );
 
    return true;
}


function absint( $maybeint ) {
    return abs( (int) $maybeint );
}

function get_current_blog_id() {
    global $blog_id;
    return absint( $blog_id );
}

function wp_die( $message = '', $title = '', $args = array() ) {
    die($message);
}

function get_the_title( $post = 0 ) {
    $title = $_POST['post_title'];
    return $title;
}

function wp_check_invalid_utf8( $string, $strip = false ) {
    $string = (string) $string;
 
    if ( 0 === strlen( $string ) ) {
        return '';
    }
 
    // Store the site charset as a static to avoid multiple calls to get_option().
    static $is_utf8 = null;
    if ( ! isset( $is_utf8 ) ) {
        // $is_utf8 = in_array( get_option( 'blog_charset' ), array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ), true );
        //ca_mark: hardcode blog_charset to utf8
        $is_utf8 = in_array( 'utf8', array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ), true );
    }
    if ( ! $is_utf8 ) {
        return $string;
    }
 
    // Check for support for utf8 in the installed PCRE library once and store the result in a static.
    static $utf8_pcre = null;
    if ( ! isset( $utf8_pcre ) ) {
        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
        $utf8_pcre = @preg_match( '/^./u', 'a' );
    }
    // We can't demand utf8 in the PCRE installation, so just return the string in those cases.
    if ( ! $utf8_pcre ) {
        return $string;
    }
 
    // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- preg_match fails when it encounters invalid UTF8 in $string.
    if ( 1 === @preg_match( '/^./us', $string ) ) {
        return $string;
    }
 
    // Attempt to strip the bad chars if requested (not recommended).
    if ( $strip && function_exists( 'iconv' ) ) {
        return iconv( 'utf-8', 'utf-8', $string );
    }
 
    return '';
}

function wp_kses_normalize_entities( $string, $context = 'html' ) {
    // Disarm all entities by converting & to &amp;
    $string = str_replace( '&', '&amp;', $string );
 
    // Change back the allowed entities in our list of allowed entities.
    if ( 'xml' === $context ) {
        $string = preg_replace_callback( '/&amp;([A-Za-z]{2,8}[0-9]{0,2});/', 'wp_kses_xml_named_entities', $string );
    } else {
        $string = preg_replace_callback( '/&amp;([A-Za-z]{2,8}[0-9]{0,2});/', 'wp_kses_named_entities', $string );
    }
    $string = preg_replace_callback( '/&amp;#(0*[0-9]{1,7});/', 'wp_kses_normalize_entities2', $string );
    $string = preg_replace_callback( '/&amp;#[Xx](0*[0-9A-Fa-f]{1,6});/', 'wp_kses_normalize_entities3', $string );
 
    return $string;
}

function _wp_specialchars( $string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false ) {
    $string = (string) $string;
 
    if ( 0 === strlen( $string ) ) {
        return '';
    }
 
    // Don't bother if there are no specialchars - saves some processing.
    if ( ! preg_match( '/[&<>"\']/', $string ) ) {
        return $string;
    }
 
    // Account for the previous behaviour of the function when the $quote_style is not an accepted value.
    if ( empty( $quote_style ) ) {
        $quote_style = ENT_NOQUOTES;
    } elseif ( ENT_XML1 === $quote_style ) {
        $quote_style = ENT_QUOTES | ENT_XML1;
    } elseif ( ! in_array( $quote_style, array( ENT_NOQUOTES, ENT_COMPAT, ENT_QUOTES, 'single', 'double' ), true ) ) {
        $quote_style = ENT_QUOTES;
    }
 
    // Store the site charset as a static to avoid multiple calls to wp_load_alloptions().
    if ( ! $charset ) {
        static $_charset = null;
        if ( ! isset( $_charset ) ) {
            $_charset   = 'utf8';
        }
        $charset = $_charset;
    }
 
    if ( in_array( $charset, array( 'utf8', 'utf-8', 'UTF8' ), true ) ) {
        $charset = 'UTF-8';
    }
 
    $_quote_style = $quote_style;
 
    if ( 'double' === $quote_style ) {
        $quote_style  = ENT_COMPAT;
        $_quote_style = ENT_COMPAT;
    } elseif ( 'single' === $quote_style ) {
        $quote_style = ENT_NOQUOTES;
    }
 
    if ( ! $double_encode ) {
        // Guarantee every &entity; is valid, convert &garbage; into &amp;garbage;
        // This is required for PHP < 5.4.0 because ENT_HTML401 flag is unavailable.
        $string = wp_kses_normalize_entities( $string, ( $quote_style & ENT_XML1 ) ? 'xml' : 'html' );
    }
 
    $string = htmlspecialchars( $string, $quote_style, $charset, $double_encode );
 
    // Back-compat.
    if ( 'single' === $_quote_style ) {
        $string = str_replace( "'", '&#039;', $string );
    }
 
    return $string;
}

function esc_html( $text ) {
    $safe_text = wp_check_invalid_utf8( $text );
    $safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
    /**
     * Filters a string cleaned and escaped for output in HTML.
     *
     * Text passed to esc_html() is stripped of invalid or special characters
     * before output.
     *
     * @since 2.8.0
     *
     * @param string $safe_text The text after it has been escaped.
     * @param string $text      The text prior to being escaped.
     */
    return apply_filters( 'esc_html', $safe_text, $text );
}

function wp_upload_dir( $time = null, $create_dir = true, $refresh_cache = false ){
    $res = array();
    $res['baseurl'] = "/home/ca224/";
    return $res;
}

function wp_count_posts( $type = 'post', $perm = '' ) {
        return new stdClass;
}

function get_the_ID() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
    $post = get_post();
    return ! empty( $post ) ? $post->ID : false;
}

function get_post( $post = null, $output = OBJECT, $filter = 'raw' ) {
    if ( empty( $post ) && isset( $GLOBALS['post'] ) ) {
        $post = $GLOBALS['post'];
    }
 
    if ( $post instanceof WP_Post ) {
        $_post = $post;
    } elseif ( is_object( $post ) ) {
        if ( empty( $post->filter ) ) {
            // $_post = sanitize_post( $post, 'raw' );
            $_post = new WP_Post( $post );
        } elseif ( 'raw' === $post->filter ) {
            $_post = new WP_Post( $post );
        } else {
            $_post = WP_Post::get_instance( $post->ID );
        }
    } else {
        $_post = WP_Post::get_instance( $post );
    }
 
    if ( ! $_post ) {
        return null;
    }
 
    $_post = $_post->filter( $filter );
 
    if ( ARRAY_A === $output ) {
        return $_post->to_array();
    } elseif ( ARRAY_N === $output ) {
        return array_values( $_post->to_array() );
    }
 
    return $_post;
}