<?php
/*
Plugin Name: WC Product Meta Keywords from Google Suggestion
Plugin URI: http://faysal.me
Description: On Page SEO plugin for WooCommerce which will add meta keywords from google Suggestion
Author: Faysal Ahamed
Author URI: http://faysal.me
Version: 1.0.1
*/

//Adding a custom tab in WC Product
add_filter( 'woocommerce_product_data_tabs', 'wc_auto_seo_custom_tab' );

function wc_auto_seo_custom_tab( $tabs ) {
  
  $tabs['custom_tab'] = array(
    'label'  => __( 'Auto SEO Keyword', 'textdomain' ),
    'target' => 'wc_auto_seo_custom_panel',
    'class'  => array(),
  );
  
  return $tabs;
}

//Adding panel inside custom tab with a custom input field
add_action( 'woocommerce_product_data_panels', 'wc_auto_seo_custom_tab_panel' );
function wc_auto_seo_custom_tab_panel() {
  ?>
  <div id="wc_auto_seo_custom_panel" class="panel woocommerce_options_panel">
    <div class="options_group">
      <?php  
        $field = array(
          'id' => '_wc_auto_seo_keyword',
          'label' => __( 'Auto SEO Keyword', 'textdomain' ),
        );
        woocommerce_wp_text_input( $field );
      ?>
      <?php  
        $field = array(
            'id' => '_wc_auto_seo_google_suggestion_keyword_string',
            'label' => __( 'Suggestion by Google', 'textdomain' ),
            'custom_attributes' => array(
                'readonly' => 'readonly',
            ) 
        );
        woocommerce_wp_text_input( $field );
      ?>
    </div>
  </div>
<?php
}

//Save WooCommerce Product Custom Field
add_action( 'woocommerce_process_product_meta', 'wc_auto_seo_custom_fields_save' );

function wc_auto_seo_custom_fields_save($post_id)
{
    // Custom Product Text Field
    $wc_auto_seo_keyword = $_POST['_wc_auto_seo_keyword'];
    if (!empty($wc_auto_seo_keyword)){
        update_post_meta($post_id, '_wc_auto_seo_keyword', esc_attr($wc_auto_seo_keyword));
    }

    $string = getQueryString($_POST['_wc_auto_seo_keyword']);
    $meta_keyword_string = rtrim($string,", ");

    if (!empty($meta_keyword_string)){
        update_post_meta($post_id, '_wc_auto_seo_google_suggestion_keyword_string', esc_attr($meta_keyword_string));
    }
}

//Add google Suggestion as meta kewyord to head tag 
add_action('wp_head','wc_auto_seo_keywords_in_head');

function wc_auto_seo_keywords_in_head(){
    global $post;
    $wc_auto_seo_keyword = '';
    if(is_product())
    {
        $wc_auto_seo_keyword = get_post_meta($post->ID, '_wc_auto_seo_google_suggestion_keyword_string', true);

        echo '<meta name="keywords" content="'.html_entity_decode($wc_auto_seo_keyword).'"/>'."\r\n";
    }
}

//Get google suggestions
function getQueryString($keyword) 
{
    $keywords = array();
    $data = file_get_contents('http://suggestqueries.google.com/complete/search?output=firefox&client=firefox&hl=en-US&q='.urlencode($keyword));
    if (($data = json_decode($data, true)) !== null) {
        $keywords = $data[1];
    }
    
    $string = '';
    $i = 1;
    foreach ($keywords as $k) 
    {
        $string .= $k . ', ';
        if ($i++ == 5) break;
    }
    return $string;
}
