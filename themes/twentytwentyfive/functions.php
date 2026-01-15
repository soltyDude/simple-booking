<?php
/**
 * Twenty Twenty-Five functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Twenty Twenty-Five 1.0
 */

/**
 * Force English locale (en_US) for whole site — add near top of functions.php
 * This will force WordPress to use English translations.
 */
add_filter( 'locale', function( $locale ) {
    return 'en_US';
}, 999 );

// Adds theme support for post formats.
if ( ! function_exists( 'twentytwentyfive_post_format_setup' ) ) :
	function twentytwentyfive_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_post_format_setup' );

// Enqueues editor-style.css in the editors.
if ( ! function_exists( 'twentytwentyfive_editor_style' ) ) :
	function twentytwentyfive_editor_style() {
		add_editor_style( 'assets/css/editor-style.css' );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_editor_style' );

// Enqueues the theme stylesheet on the front.
if ( ! function_exists( 'twentytwentyfive_enqueue_styles' ) ) :
	function twentytwentyfive_enqueue_styles() {
		$suffix = SCRIPT_DEBUG ? '' : '.min';
		$src    = 'style' . $suffix . '.css';

		wp_enqueue_style(
			'twentytwentyfive-style',
			get_parent_theme_file_uri( $src ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
		wp_style_add_data(
			'twentytwentyfive-style',
			'path',
			get_parent_theme_file_path( $src )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles' );

// Registers custom block styles.
if ( ! function_exists( 'twentytwentyfive_block_styles' ) ) :
	function twentytwentyfive_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'twentytwentyfive' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_block_styles' );

// Registers pattern categories.
if ( ! function_exists( 'twentytwentyfive_pattern_categories' ) ) :
	function twentytwentyfive_pattern_categories() {
		register_block_pattern_category(
			'twentytwentyfive_page',
			array(
				'label'       => __( 'Pages', 'twentytwentyfive' ),
				'description' => __( 'A collection of full page layouts.', 'twentytwentyfive' ),
			)
		);
		register_block_pattern_category(
			'twentytwentyfive_post-format',
			array(
				'label'       => __( 'Post formats', 'twentytwentyfive' ),
				'description' => __( 'A collection of post format patterns.', 'twentytwentyfive' ),
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_pattern_categories' );

// Registers block binding sources.
if ( ! function_exists( 'twentytwentyfive_register_block_bindings' ) ) :
	function twentytwentyfive_register_block_bindings() {
		register_block_bindings_source(
			'twentytwentyfive/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'twentytwentyfive' ),
				'get_value_callback' => 'twentytwentyfive_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_register_block_bindings' );

// Registers block binding callback function for the post format name.
if ( ! function_exists( 'twentytwentyfive_format_binding' ) ) :
	function twentytwentyfive_format_binding() {
		$post_format_slug = get_post_format();
		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;

/* ----------------------------
   Shortcode: [desks_list] — English labels and ACF image support
   ---------------------------- */
function wpb_desks_list_shortcode( $atts ) {
    $args = array(
        'post_type' => 'desk',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    );

    $q = new WP_Query( $args );
    if ( ! $q->have_posts() ) {
        return '<p>No desks found.</p>';
    }

    ob_start();
    echo '<div class="desks-list" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;margin:0;">';
    while ( $q->have_posts() ) {
        $q->the_post();
        $id = get_the_ID();
        $title = get_the_title();
        $permalink = esc_url( get_permalink() ) . '?desk_id=' . esc_attr( $id );

        echo '<div class="desk-card" style="border:1px solid #eee;padding:12px;border-radius:6px;background:#fff;display:flex;flex-direction:column;gap:10px;">';

        // Try ACF image (ID / array / URL)
        $img_html = '';
        if ( function_exists('get_field') ) {
            $img = get_field( 'desk_image', $id );
            if ( is_numeric( $img ) && intval( $img ) > 0 ) {
                $img_src = wp_get_attachment_image_url( intval( $img ), 'medium' );
                if ( $img_src ) {
                    $img_html = '<img src="' . esc_url( $img_src ) . '" alt="' . esc_attr( $title ) . '" style="width:100%;height:150px;object-fit:cover;display:block;border-radius:6px 6px 0 0;" />';
                }
            } elseif ( is_array( $img ) && ! empty( $img['sizes']['medium'] ) ) {
                $img_html = '<img src="' . esc_url( $img['sizes']['medium'] ) . '" alt="' . esc_attr( $title ) . '" style="width:100%;height:150px;object-fit:cover;display:block;border-radius:6px 6px 0 0;" />';
            } elseif ( is_string( $img ) && filter_var( $img, FILTER_VALIDATE_URL ) ) {
                $img_html = '<img src="' . esc_url( $img ) . '" alt="' . esc_attr( $title ) . '" style="width:100%;height:150px;object-fit:cover;display:block;border-radius:6px 6px 0 0;" />';
            }
        }

        // Fallback to featured image
        if ( ! $img_html && has_post_thumbnail( $id ) ) {
            $thumb_src = get_the_post_thumbnail_url( $id, 'medium' );
            if ( $thumb_src ) {
                $img_html = '<img src="' . esc_url( $thumb_src ) . '" alt="' . esc_attr( $title ) . '" style="width:100%;height:150px;object-fit:cover;display:block;border-radius:6px 6px 0 0;" />';
            }
        }

        if ( $img_html ) {
            echo '<div class="desk-thumb" style="overflow:hidden;">' . $img_html . '</div>';
        }

        echo '<h3 style="margin:0;font-weight:400;"><a href="' . $permalink . '" style="text-decoration:underline;color:inherit;">' . esc_html( $title ) . '</a></h3>';

        if ( function_exists( 'get_field' ) ) {
            $labels = array();
            if ( get_field( 'has_window', $id ) ) $labels[] = 'Window';
            if ( get_field( 'quiet', $id ) ) $labels[] = 'Quiet';
            if ( get_field( 'power', $id ) ) $labels[] = 'Power';
            if ( get_field( 'coffee', $id ) ) $labels[] = 'Coffee nearby';
            if ( get_field( 'food', $id ) ) $labels[] = 'Food nearby';
            if ( $labels ) {
                echo '<p style="margin:8px 0;color:#666;">' . esc_html( implode( ', ', $labels ) ) . '</p>';
            }
        }

        echo '<a class="desk-book-btn" href="' . $permalink . '" style="margin-top:auto;color:inherit;text-decoration:underline;">Book</a>';
        echo '</div>';
    }
    echo '</div>';

    wp_reset_postdata();
    return ob_get_clean();
}
remove_shortcode('desks_list');
add_shortcode( 'desks_list', 'wpb_desks_list_shortcode' );

/* ========== Create booking after Contact Form 7 submit (no changes) ========== */
add_action( 'wpcf7_mail_sent', function ( $contact_form ) {
    if ( ! class_exists( 'WPCF7_Submission' ) ) {
        return;
    }
    $submission = WPCF7_Submission::get_instance();
    if ( ! $submission ) {
        return;
    }
    $data = $submission->get_posted_data();

    $desk_id      = isset( $data['desk-id'] ) ? intval( $data['desk-id'] ) : 0;
    $booking_date = isset( $data['booking-date'] ) ? sanitize_text_field( $data['booking-date'] ) : '';
    $name         = isset( $data['your-name'] ) ? sanitize_text_field( $data['your-name'] ) : '';
    $email        = isset( $data['your-email'] ) ? sanitize_email( $data['your-email'] ) : '';
    $phone        = isset( $data['your-phone'] ) ? sanitize_text_field( $data['your-phone'] ) : '';

    if ( $desk_id <= 0 || empty( $booking_date ) ) {
        return;
    }

    $existing = get_posts( array(
        'post_type'   => 'booking',
        'meta_query'  => array(
            array( 'key' => 'desk_id', 'value' => $desk_id ),
            array( 'key' => 'booking_date', 'value' => $booking_date ),
        ),
        'posts_per_page' => 1,
        'fields' => 'ids',
    ) );

    if ( ! empty( $existing ) ) {
        return;
    }

    $booking_post_id = wp_insert_post( array(
        'post_type'   => 'booking',
        'post_title'  => sprintf( 'Booking: desk %d — %s', $desk_id, $booking_date ),
        'post_status' => 'publish',
    ) );

    if ( is_wp_error( $booking_post_id ) || ! $booking_post_id ) {
        return;
    }

    if ( function_exists( 'update_field' ) ) {
        update_field( 'desk_id', $desk_id, $booking_post_id );
        update_field( 'booking_date', $booking_date, $booking_post_id );
        update_field( 'name', $name, $booking_post_id );
        update_field( 'email', $email, $booking_post_id );
        update_field( 'phone', $phone, $booking_post_id );
    } else {
        update_post_meta( $booking_post_id, 'desk_id', $desk_id );
        update_post_meta( $booking_post_id, 'booking_date', $booking_date );
        update_post_meta( $booking_post_id, 'name', $name );
        update_post_meta( $booking_post_id, 'email', $email );
        update_post_meta( $booking_post_id, 'phone', $phone );
    }
} );

/* ========== CF7: booking-date validation (prevent duplicates) ========== */
add_filter( 'wpcf7_validate_date*', 'wpse_booking_date_validate', 20, 2 );
add_filter( 'wpcf7_validate_date',  'wpse_booking_date_validate', 20, 2 );

function wpse_booking_date_validate( $result, $tag ) {
    $tag_name = isset( $tag->name ) ? $tag->name : '';
    if ( 'booking-date' !== $tag_name ) {
        return $result;
    }

    $booking_date = isset( $_POST['booking-date'] ) ? sanitize_text_field( wp_unslash( $_POST['booking-date'] ) ) : '';
    $desk_id      = isset( $_POST['desk-id'] ) ? intval( $_POST['desk-id'] ) : 0;

    if ( empty( $booking_date ) || $desk_id <= 0 ) {
        return $result;
    }

    $candidates = array( $booking_date );
    $dt = DateTime::createFromFormat( 'Y-m-d', $booking_date );
    if ( $dt ) {
        $candidates[] = $dt->format( 'Ymd' );
    }

    $meta_queries = array( 'relation' => 'AND',
        array( 'key' => 'desk_id', 'value' => $desk_id ),
        array( 'relation' => 'OR' ),
    );

    foreach ( $candidates as $c ) {
        $meta_queries[2][] = array( 'key' => 'booking_date', 'value' => $c );
    }

    $existing = get_posts( array(
        'post_type'      => 'booking',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => $meta_queries,
    ) );

    if ( ! empty( $existing ) ) {
        $result->invalidate( $tag, 'This date is already booked — please choose another.' );
    }

    return $result;
}

/* ----------------------------
   Force login (updated logic) — unchanged other than comments
   ---------------------------- */
// Force login — обновлённая версия: разрешаем доступ к страницам регистрации/восстановления и стандартным auth-эндпоинтам.
/* Robust force-login with exceptions for specific pages and endpoints */
add_action( 'template_redirect', function() {

    // Если уже залогинен — ничего не делаем
    if ( is_user_logged_in() ) {
        return;
    }

    // Не редиректим AJAX, Cron или REST запросы
    if ( wp_doing_ajax() || wp_doing_cron() ) {
        return;
    }
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        return;
    }

    // Разрешаем admin-ajax напрямую (нужно для фронтенд AJAX)
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? strtolower( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
    if ( strpos( $request_uri, '/admin-ajax.php' ) !== false ) {
        return;
    }

    // Технические файлы, которые должны быть доступны
    $script = isset( $_SERVER['PHP_SELF'] ) ? basename( $_SERVER['PHP_SELF'] ) : '';
    $allowed_files = array( 'wp-login.php', 'wp-register.php', 'wp-cron.php' );
    if ( in_array( $script, $allowed_files, true ) ) {
        return;
    }

    // Явные allowed substrings (чтобы работать на любых путях)
    $allowed_substrings = array(
        '/login',
        '/register',
        '/signup',
        '/password',
        '/lost',
        '/reset',
        '/wp-login.php?action=register',
        '/wp-login.php?action=lostpassword',
        '/wp-login.php',
        '/um-register',
        '/account',
        '/profile',
        '/wp-json/',           // REST
    );
    foreach ( $allowed_substrings as $sub ) {
        if ( strpos( $request_uri, $sub ) !== false ) {
            return;
        }
    }

    // ПОЛЕЗНО: точные проверки через WP conditional tags.
    // Эти проверки работают в template_redirect (WP запрос уже установлен).
    // Разрешаем доступ к странице со списком десков (slug 'desks')
    if ( function_exists( 'is_page' ) && is_page( 'desks' ) ) {
        return;
    }

    // Разрешаем все single для CPT 'desk'
    if ( function_exists( 'is_singular' ) && is_singular( 'desk' ) ) {
        return;
    }

    // Также разрешаем фронт-энд ajax/noscript handlers (если у тебя другие эндпоинты — добавь их)
    // Пример: если путь содержит /desk/ (защитная мера)
    if ( strpos( $request_uri, '/desk/' ) !== false ) {
        return;
    }

    // DEBUG: записать причину (временно) — убери/закомментируй в проде
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[force-login] redirecting visitor. request_uri=' . $request_uri . ' php_self=' . $script . ' is_page_desks=' . ( function_exists( 'is_page' ) ? (int) is_page( 'desks' ) : -1 ) . ' is_singular_desk=' . ( function_exists( 'is_singular' ) ? (int) is_singular( 'desk' ) : -1 ) );
    }

    // Всё остальное — редирект на страницу логина
    wp_redirect( site_url( '/login/' ) );
    exit;
}, 10 );
