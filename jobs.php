<?php
//define('WP_DEBUG',true);

$schema_employment_types =
  array('FULL_TIME',
        'PART_TIME',
        'CONTRACTOR',
        'TEMPORARY',
        'INTERN',
        'VOLUNTEER',
        'PER_DIEM',
        'OTHER');

// Our custom post type function
function create_jobs() {

    register_post_type( 'job_opening',
    // CPT Options
        array(
            'labels' => array(
                'name' => __( 'Careers' ),
                'singular_name' => __( 'Career' )
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'careers'),
            'show_in_rest' => true,
            'supports' => array('title','editor','custom-fields')
        )
    );

    register_post_type( 'job_application',
    // CPT Options
        array(
            'labels' => array(
                'name' => __( 'Job Applications' ),
                'singular_name' => __( 'Job Application' )
            ),
            'public' => false,
            'has_archive' => false,
            'rewrite' => array('slug' => 'job-applications'),
            'show_in_rest' => false,
            'supports' => array('title','editor','custom-fields')
        )
    );
}
// Hooking up our function to theme setup
add_action( 'init', 'create_jobs' );

function apctg_schema_jobs() {
  global $post;
  $accordion_html = '<div class="accordion" id="accordionJobs">';

  // Loop through every post of 'job_opening' type and build the schema and html to return it all together;

  $args = array(
      'post_type' => 'job_opening',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC',
  );

  $loop = new WP_Query( $args );

  $count = 0;
  while ( $loop->have_posts() ) : $loop->the_post();
    #based on the location ID, get the post_type (location) data.

    // Get the store information for the given job post using the store ID
    $store = get_post($post->store_id);

    $accordion_html .= '<div class="accordion-item">
                        <h2 class="accordion-header" id="job-heading-'.$count.'">
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#job-'.$count.'" aria-expanded="false" aria-controls="job-'.$count.'">
                            <div class="row w-100">
                              <div class="col-md-6">
                                '. T(get_the_title(),$language_code).'
                              </div>
                              <div class="col-md-6">
                                <span class="ms-auto text-secondary small fst-italic"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-fill" viewBox="0 0 16 16">
                                  <path fill-rule="evenodd" d="M4 4a4 4 0 1 1 4.5 3.969V13.5a.5.5 0 0 1-1 0V7.97A4 4 0 0 1 4 3.999zm2.493 8.574a.5.5 0 0 1-.411.575c-.712.118-1.28.295-1.655.493a1.319 1.319 0 0 0-.37.265.301.301 0 0 0-.057.09V14l.002.008a.147.147 0 0 0 .016.033.617.617 0 0 0 .145.15c.165.13.435.27.813.395.751.25 1.82.414 3.024.414s2.273-.163 3.024-.414c.378-.126.648-.265.813-.395a.619.619 0 0 0 .146-.15.148.148 0 0 0 .015-.033L12 14v-.004a.301.301 0 0 0-.057-.09 1.318 1.318 0 0 0-.37-.264c-.376-.198-.943-.375-1.655-.493a.5.5 0 1 1 .164-.986c.77.127 1.452.328 1.957.594C12.5 13 13 13.4 13 14c0 .426-.26.752-.544.977-.29.228-.68.413-1.116.558-.878.293-2.059.465-3.34.465-1.281 0-2.462-.172-3.34-.465-.436-.145-.826-.33-1.116-.558C3.26 14.752 3 14.426 3 14c0-.599.5-1 .961-1.243.505-.266 1.187-.467 1.957-.594a.5.5 0 0 1 .575.411z"/>
                                </svg>'.
                                $store->city.', '.$store->state.'</span>
                              </div>
                            </div>
                          </button>
                        </h2>
                        <div id="job-'.$count.'" class="accordion-collapse collapse" aria-labelledby="job-heading-'.$count.'" data-bs-parent="#accordionJobs">
                          <div class="accordion-body">
                            '.T(get_the_content(),$language_code).'
                            <a href="'.get_the_permalink().'" type="button" class="btn btn-primary">View Opportunity</a>
                          </div>
                        </div>
                      </div>';
    $count++;
  endwhile; wp_reset_postdata();
  $accordion_html .= '</div>';

  return $accordion_html;
}

add_shortcode('schema_jobs', 'apctg_schema_jobs');

function apctg_schema_jobs_single() {
  global $post;

  $store = get_post($post->store_id);

  $schema_code = '<script type="application/ld+json">
  {
    "@context" : "https://schema.org/",
    "@type" : "JobPosting",
    "title" : "'.get_the_title().'",
    "description" : "'.get_the_content().'",
    "identifier": {
      "@type": "PropertyValue",
      "name": "LavanderiaPR",
      "value": "'.$post->ID.'"
    },
    "datePosted" : "'.get_the_date().'",
    "validThrough" : "2017-03-18T00:00",
    "employmentType" : "'.$post->employment_type.'",
    "hiringOrganization" : {
      "@type" : "Organization",
      "name" : "LavanderiaPR.com",
      "sameAs" : "https://lavanderiapr.com",
      "logo" : "https://lh6.googleusercontent.com/-i24IJMTNxPU/AAAAAAAAAAI/AAAAAAAAAAA/VOea443Kmi8/s88-p-k-no-ns-nd/photo.jpg"
    },
    "jobLocation": {
      "@type": "Place",
      "sameAs": "'.get_the_permalink($store->ID).'",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "'.$store->street_address.'",
        "addressLocality": "'.$store->city.'",
        "addressRegion": "'.$store->state.'",
        "postalCode": "'.$store->zip_code.'",
        "addressCountry": "US"
      }
    },
    "baseSalary": {
      "@type": "MonetaryAmount",
      "currency": "USD",
      "value": {
        "@type": "QuantitativeValue",
        "value": '.$post->employment_hourly.',
        "unitText": "HOUR"
      }
    }
  }
  </script>';

  $single_job_html = '<div class="col-md-8 mx-auto">
    <div class="mb-5">
      <a class="link" href="">
        <i class="bi-chevron-left small ms-1"></i> Back to careers
      </a>
    </div>
    <div class="card card-lg">
      <div class="card-body">
        <div class="row justify-content-sm-between align-items-sm-center mb-5">
          <div class="col-sm mb-3 mb-sm-0">
            <h1 class="card-title h2">'.get_the_title().'</h1>
            <p class="card-text">'.$store->city.', '.$store->state.' - '.$post->employment_type.'</p>
          </div>
          <div class="col-sm-auto">
            <a class="js-go-to position-static btn btn-primary btn-transition">
              Apply Now
            </a>
          </div>
        </div>
        <div class="mb-5">
          '.get_the_content().'
        </div>
        <div class="mb-5">
          <h2 class="h4">Benefits:</h2>
          <ul>
            <li>Employer-paid health care</li>
            <li>Casual and diverse workplace</li>
            <li>Free snacks</li>
            <li>Unlimited Vacation policy</li>
            <li>Stock options</li>
          </ul>
        </div>
        <div class="mb-5">
          <h3 class="h4">Requirements:</h3>
          <ul>
            <li>Energized to join a startup</li>
            <li>Excited to mentor more junior developers</li>
            <li>Good at problem selection, problem solving, and course correcting</li>
            <li>Focused on best practices</li>
            <li>Highly pragmatic and collaborative</li>
          </ul>
        </div>
        <div class="border-top text-center pt-5 pt-md-9 mb-5 mb-md-9">
          <h2>Apply for this Job</h2>
        </div>
      </div>
    </div>
  </div>';

  return $schema_code.$single_job_html;
}
add_shortcode('schema_jobs_single', 'apctg_schema_jobs_single');
?>
