<?php

//define('WP_DEBUG',true);
ob_start();

// Our custom post type function
function create_faqs() {

    register_post_type( 'faqs',
    // CPT Options
        array(
            'labels' => array(
                'name' => __( 'Frequently Asked Questions' ),
                'singular_name' => __( 'Frequently Asked Question' )
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'frequently-asked-questions'),
            'show_in_rest' => true,

        )
    );
}
// Hooking up our function to theme setup
add_action( 'init', 'create_faqs' );

function apctg_schema_faq() {

  $accordion_html = '<div class="accordion" id="accordionFaq">';

  $schema_code = '<script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [';

  // Loop through every post of 'faq' type and build the schema and html to return it all together;

  $args = array(
      'post_type' => 'faqs',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC',
  );

  $loop = new WP_Query( $args );

  $count = 0;
  while ( $loop->have_posts() ) : $loop->the_post();
    $accordion_html .= '<div class="accordion-item">
                        <h2 class="accordion-header" id="faq-heading-'.$count.'">
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-'.$count.'" aria-expanded="false" aria-controls="faq-'.$count.'">
                            '. T(get_the_title(),$language_code).'
                          </button>
                        </h2>
                        <div id="faq-'.$count.'" class="accordion-collapse collapse" aria-labelledby="faq-heading-'.$count.'" data-bs-parent="#accordionFaq">
                          <div class="accordion-body">
                            '.T(get_the_content(),$language_code).'
                          </div>
                        </div>
                      </div>';

    // If passed first question, add comma
    if($count > 0)
      $schema_code .= ',';

    $schema_code .= '{
                          "@type": "Question",
                          "name": "'.get_the_title().'",
                          "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "'.get_the_content().'"
                          }
                        }';
    $count++;
  endwhile;

  wp_reset_postdata();


  $accordion_html .= '</div>';

  // Close Schema code
  $schema_code .= ']
                  }
            </script>';
  // Close Bootstrap Accordion HTML


  return $schema_code.$accordion_html;
}

add_shortcode('schema_faq', 'apctg_schema_faq');

?>
