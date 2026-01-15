<?php
/**
 * single-desk.php
 * Template for single posts of custom post type "desk"
 * Place in active theme folder (wp-content/themes/your-theme/)
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="site-content" role="main">
  <div class="content-inner" style="max-width:1100px;margin:0 auto;padding:2rem;">

  <?php
  if ( have_posts() ) :
    while ( have_posts() ) : the_post();
      $post_id = get_the_ID();
  ?>
      <article id="post-<?php echo esc_attr( $post_id ); ?>" <?php post_class(); ?>>
        <header class="entry-header" style="margin-bottom:1rem;">
          <h1 class="entry-title" style="margin:0 0 .5rem;"><?php the_title(); ?></h1>
        </header>

        <?php
        // Determine image URL (supports ACF 'desk_image' as ID/array/url, then featured image)
        $image_url = '';

        if ( function_exists( 'get_field' ) ) {
            $acf_img = get_field( 'desk_image', $post_id );
            if ( $acf_img ) {
                if ( is_numeric( $acf_img ) && intval( $acf_img ) > 0 ) {
                    $maybe = wp_get_attachment_image_url( intval( $acf_img ), 'large' );
                    if ( $maybe ) $image_url = $maybe;
                } elseif ( is_array( $acf_img ) && ! empty( $acf_img['url'] ) ) {
                    if ( ! empty( $acf_img['sizes']['large'] ) ) {
                        $image_url = $acf_img['sizes']['large'];
                    } elseif ( ! empty( $acf_img['sizes']['medium'] ) ) {
                        $image_url = $acf_img['sizes']['medium'];
                    } else {
                        $image_url = $acf_img['url'];
                    }
                } elseif ( is_string( $acf_img ) && filter_var( $acf_img, FILTER_VALIDATE_URL ) ) {
                    $image_url = $acf_img;
                }
            }
        }

        if ( empty( $image_url ) && has_post_thumbnail( $post_id ) ) {
            $maybe = get_the_post_thumbnail_url( $post_id, 'large' );
            if ( $maybe ) $image_url = $maybe;
        }

        if ( ! empty( $image_url ) ) {
            echo '<div class="entry-thumbnail" style="margin-bottom:1rem;">';
            echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( get_the_title( $post_id ) ) . '" style="width:100%;height:auto;border-radius:6px;display:block;" />';
            echo '</div>';
        } else {
            echo '<div class="entry-thumbnail" style="margin-bottom:1rem;">';
            echo '<div style="width:100%;height:220px;border-radius:6px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;color:#aaa">No image</div>';
            echo '</div>';
        }
        ?>

        <div class="entry-meta" style="margin-bottom:1rem;">
          <?php
          if ( function_exists( 'get_field' ) ) :
            $has_window = get_field( 'has_window', $post_id );
            $quiet      = get_field( 'quiet', $post_id );
            $power      = get_field( 'power', $post_id );
            $coffee     = get_field( 'coffee', $post_id );
            $food       = get_field( 'food', $post_id );
          ?>
            <ul class="desk-features" style="list-style:none;padding:0;margin:0 0 1rem 0;display:flex;flex-wrap:wrap;gap:.5rem 1rem;">
              <li><strong>Window:</strong> <?php echo $has_window ? 'Yes' : 'No'; ?></li>
              <li><strong>Quiet:</strong> <?php echo $quiet ? 'Yes' : 'No'; ?></li>
              <li><strong>Power:</strong> <?php echo $power ? 'Yes' : 'No'; ?></li>
              <li><strong>Coffee nearby:</strong> <?php echo $coffee ? 'Yes' : 'No'; ?></li>
              <li><strong>Food nearby:</strong> <?php echo $food ? 'Yes' : 'No'; ?></li>
            </ul>
          <?php endif; ?>
        </div>

        <div class="entry-content" style="margin-bottom:1.5rem;">
          <?php the_content(); ?>
        </div>

        <?php /*
         <section class="desk-booked-dates" style="margin-top:2rem;">
          <h2 style="margin-bottom:.5rem;">Booked dates</h2>
          <?php
          $desk_id = get_the_ID();

          $bookings_args = array(
              'post_type'      => 'booking',
              'posts_per_page' => -1,
              'meta_key'       => 'booking_date',
              'orderby'        => 'meta_value',
              'order'          => 'ASC',
              'meta_query'     => array(
                  array(
                      'key'   => 'desk_id',
                      'value' => $desk_id,
                  ),
              ),
          );

          $bookings_q = new WP_Query( $bookings_args );
          if ( $bookings_q->have_posts() ) {
              echo '<ul class="desk-bookings-list" style="padding-left:1rem;margin:0;">';
              while ( $bookings_q->have_posts() ) {
                  $bookings_q->the_post();

                  $bdate = function_exists( 'get_field' ) ? get_field( 'booking_date' ) : get_post_meta( get_the_ID(), 'booking_date', true );
                  $display_date = '';

                  if ( $bdate ) {
                      if ( preg_match( '/^\d{8}$/', $bdate ) ) {
                          $dt = DateTime::createFromFormat( 'Ymd', $bdate );
                          if ( $dt ) $display_date = $dt->format( 'd/m/Y' );
                      } elseif ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $bdate ) ) {
                          $dt = DateTime::createFromFormat( 'Y-m-d', $bdate );
                          if ( $dt ) $display_date = $dt->format( 'd/m/Y' );
                      } else {
                          $dt = DateTime::createFromFormat( 'd/m/Y', $bdate );
                          if ( $dt ) $display_date = $dt->format( 'd/m/Y' );
                      }
                  }

                  echo '<li style="margin:0 0 .25rem 0;">' . esc_html( $display_date ) . '</li>';
              }
              echo '</ul>';
              wp_reset_postdata();
          } else {
              echo '<p>No bookings yet</p>';
          }
          ?>
        </section> */

        <section class="desk-booking" style="margin-top:2rem;padding:1rem;border:1px solid #eee;border-radius:6px;">
          <h2 style="margin-top:0;">Book this desk</h2>

          <p style="margin-bottom:1rem;">When you submit the form, the email and Flamingo will receive this desk ID: <code><?php echo esc_html( $post_id ); ?></code></p>

          <?php
          echo do_shortcode( '[contact-form-7 id="221d388" title="Booking form"]' );
          ?>
        </section>

        <?php /*
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var postId = '<?php echo esc_js( $post_id ); ?>';
            var el = document.querySelector('input[name="desk-id"]');
            if ( el && (!el.value || el.value === '') ) el.value = postId;

            var bookedDates = <?php
                $bd = array();
                $query = new WP_Query( array(
                    'post_type'      => 'booking',
                    'posts_per_page' => -1,
                    'meta_query'     => array(
                        array( 'key' => 'desk_id', 'value' => $post_id ),
                    ),
                ) );
                if ( $query->have_posts() ) {
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        $raw = function_exists( 'get_field' ) ? get_field( 'booking_date' ) : get_post_meta( get_the_ID(), 'booking_date', true );
                        if ( ! $raw ) continue;
                        $iso = '';
                        if ( preg_match( '/^\d{8}$/', $raw ) ) {
                            $d = DateTime::createFromFormat( 'Ymd', $raw );
                            if ( $d ) $iso = $d->format( 'Y-m-d' );
                        } elseif ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ) {
                            $iso = $raw;
                        } else {
                            $d = DateTime::createFromFormat( 'd/m/Y', $raw );
                            if ( $d ) $iso = $d->format( 'Y-m-d' );
                        }
                        if ( $iso ) $bd[] = $iso;
                    }
                    wp_reset_postdata();
                }
                echo json_encode( array_values( array_unique( $bd ) ) );
            ?>;

            if ( ! bookedDates || bookedDates.length === 0 ) return;

            var dateInput = document.querySelector('input[name="booking-date"]');
            var formEl = dateInput ? dateInput.closest('form') : null;

            function isBooked(value) {
                return value && bookedDates.indexOf(value) !== -1;
            }

            if ( formEl && dateInput ) {
                formEl.addEventListener('submit', function (e) {
                    var val = dateInput.value;
                    if ( isBooked(val) ) {
                        e.preventDefault();
                        var resp = formEl.querySelector('.wpcf7-response-output');
                        if ( resp ) {
                            resp.classList.add('wpcf7-validation-errors');
                            resp.innerText = 'This date is already booked — please choose another.';
                        } else {
                            alert('This date is already booked — please choose another.');
                        }
                        return false;
                    }
                }, { capture: true });
            }
        });
        </script> */

      </article>

  <?php
    endwhile;
  else :
  ?>
    <p>Post not found.</p>
  <?php
  endif;
  ?>

  </div><!-- .content-inner -->
</main>

<?php
get_footer();
