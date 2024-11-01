<?php
/**
 *Plugin Name: Simple Testimonial Slider And Grid
 *Plugin URI: http://www.itobuz.com/
 *Description: This plugin is for testimonial slider.
 *Author: Navin Kumar
 *Version: 1.0.1
 */

//test
//test 2
// Our custom post type function
function ts_create_posttype()
{
    ts_register_post_type('testimonial',
        // CPT Options
        array(
            'labels' => array(
                'name' => __('Testimonial'),
                'singular_name' => __('Testimonial'),
                'add_new' => 'add new testimonial',
                'add_new_item' => __('Add New Book', 'your-plugin-textdomain'),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'testimonial'),
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
        )
    );
}
// Hooking up our function to theme setup
add_action('init', 'create_posttype');

add_action('admin_init', 'Navin_admin');

function ts_Navin_admin()
{
    add_meta_box('Testimonial_review_meta_box',
        'Testimonial Review Details',
        'display_Testimonial_review_meta_box',
        'Testimonial', 'normal', 'high'
    );
}

function ts_display_Testimonial_review_meta_box($testimonial_review)
{
    // Retrieve current name of the Director and Testimonial Rating based on review ID
    $testimonial_director = esc_html(get_post_meta($testimonial_review->ID, 'testimonial_director', true));
    // $Testimonial_rating = intval( get_post_meta( $Testimonial_review->ID, 'Testimonial_rating', true ) );
    ?>
    <table>
        <tr>
            <td style="width: 100%">Testimonail author</td>
            <td><input type="text" size="72" name="testimonial_review_author_name" value="<?php echo $testimonial_director; ?>" /></td>
        </tr>
    </table>
    <?php
}

add_action('save_post', 'add_testimonial_review_fields', 10, 2);

function ts_add_testimonial_review_fields($testimonial_review_id, $testimonial_review)
{
    // Check post type for testimonial reviews
    if ($testimonial_review->post_type == 'testimonial') {
        
        $testimonial_review_author_name = sanitize_text_field($_POST['testimonial_review_author_name']);

        if (isset($testimonial_review_author_name) && $testimonial_review_author_name != '') {
            update_post_meta($testimonial_review_id, 'testimonial_director', $testimonial_review_author_name);
        }
    }
}

;

//create shortcode to disply add_testimonial_review_fields
function ts_testimonial_list_function($atts)
{
    $atts = shortcode_atts(array(
        'no_of_post' => -1,
        'layout' => 'grid',
    ), $atts, 'testimonial_list');
    $output = '';

    $wpb_all_query = new WP_Query(array('post_type' => 'testimonial', 'post_status' => 'publish', 'posts_per_page' => $atts['no_of_post']));

    $class = '';
    $testimonial_slider = '';
    $option = '';
    $slider = true;
    if ($atts['layout'] == 'grid') {
        $class = 'col-md-4';
        $testimonial_slider = 'row';
        $option = 'testimonial-grid';
        $slider = false;
    }

    if (
        ($atts['layout'] == 'slider')
        ||
        ($atts['layout'] == 'SLIDER')
        ||
        ($atts['layout'] == 'Slider')

    ) {
        $testimonial_slider = 'testimonial-slider';
        $class = 'row';
        $option = 'slider-layout';
        $class = 'col-md-12';
    }

    $output .= '<div class="testimonial_list ' . $testimonial_slider . ' ' . $option . '">';

    while ($wpb_all_query->have_posts()): $wpb_all_query->the_post();

        $output .= '<div class="' . $class . '">
	    <div class="testimonial-content">';
        if($slider){$output .='<div class="col-md-2">';}
	       $output .= '<img src="' . get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') . '">';
        if($slider){$output .='</div>';}

        if($slider){$output .='<div class="col-md-10">';}
    	    $output .= '<div class="testimonial-description">
    	           <p>' . get_the_content('Read more') . '</p>
    	         <h2> <a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h2>
    	         <p>' . get_post_meta(get_the_ID(), "testimonial_director", true) . '</p>
    	        </div>';
        if($slider){$output .='</div>';}
	    $output .= '</div>
	</div>';
    endwhile;
    $output .= '</div>';

    wp_reset_postdata();
    return $output;
}
add_shortcode('testimonial_list', 'ts_testimonial_list_function');

//connecting with bootstrap
function ts_add_external_scripts_styles()
{
    wp_enqueue_style('bootstrap-min-css', plugin_dir_url(__FILE__) . 'bootstrap/css/bootstrap.css');

    wp_enqueue_script('bootstrap-min-js', plugin_dir_url(__FILE__) . 'bootstrap/js/bootstrap.min.js', array('jquery'));
}
add_action('wp_enqueue_scripts', 'ts_add_external_scripts_styles');

// connecting with slick slider
function ts_add_slick_scripts_styles()
{
    wp_register_style('slick-theme-css', plugin_dir_url(__FILE__) . 'js/slick/slick/slick-theme.css');
    wp_enqueue_style('slick-theme-css');

    wp_enqueue_script('slick-min-js', plugin_dir_url(__FILE__) . 'js/slick/slick/slick.js', array('jquery'), true, true);

    wp_enqueue_script('custom-js', plugin_dir_url(__FILE__) . 'css/custom.js', array('jquery', 'slick-min-js'), true, true);
}
add_action('wp_enqueue_scripts', 'ts_add_slick_scripts_styles');

//connecting with custom css file
function ts_add_custom_css_styles()
{
    wp_register_style('custom-theme-css', plugin_dir_url(__FILE__) . 'custom-css.css');
    wp_enqueue_style('custom-theme-css');
}
add_action('wp_enqueue_scripts', 'ts_add_custom_css_styles');

///////////////////////////////////////
/////////WIDGET START /////////////////
///////////////////////////////////////

class Navincustom_Widget extends WP_Widget
{

    public function __construct()
    {

        parent::__construct(
            'simple_testimonial_widget',
            __('Simple Testimonial Slider Widget', 'thisisanewblock'),
            array(
                'classname' => 'simple_testimonial_widget',
                'description' => __('Enter a custom description for your new widget', 'Navincustomdomain'),
            )
        );

        load_plugin_textdomain('thisisanewblock', false, basename(dirname(__FILE__)) . '/languages');

    }

    public function form($instance)
    {
        if (array_key_exists('title', $instance)) {
            $title = esc_attr($instance['title']);
        }

        if (array_key_exists('testimonial_layout', $instance)) {
            $testimonial_layout = esc_attr($instance['testimonial_layout']);
        }

        if (array_key_exists('no_of_testimonial', $instance)) {
            $no_of_testimonial = esc_attr($instance['no_of_testimonial']);
        }
        ?>

<p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:');?></label>
    <input class="widefat " type="text" value="<?php echo $title; ?>" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" />
</p>

<!-- <p>
    <label for="<?php //echo $this->get_field_id('testimonial_layout'); ?>"><?php //_e('Layout');?></label>
    <select name="<?php //echo $this->get_field_name('testimonial_layout'); ?>" id="<?php //echo $this->get_field_id('testimonial_layout'); ?>">
        <option value="slider" <?php //if($testimonial_layout == 'slider' ){echo 'selected';}?>>Slider</option>
        <option value="grid" <?php //if($testimonial_layout == 'grid' ){echo 'selected';}?>>Grid</option>
    </select>
</p> -->
<p>
    <label for="<?php echo $this->get_field_id('no_of_testimonial'); ?>"><?php _e('No Of Testimonial');?></label>
    <input type="number" value="<?php echo $no_of_testimonial; ?>" name="<?php echo $this->get_field_name('no_of_testimonial'); ?>" id="<?php echo $this->get_field_id('no_of_testimonial'); ?>" />
</p>
<?php
}

    public function widget($args, $instance)
    {

        extract($args);

        $title = apply_filters('widget_title', $instance['title']);
        $testimonial_layout = $instance['testimonial_layout'];
        $no_of_testimonial = $instance['no_of_testimonial'];


        $widget_arg = array(
            'post_type' => 'testimonial',
            'post_status' => 'publish',
            'posts_per_page' => $no_of_testimonial,
        );
            $output='<div class="testimonial-widget">';
    if($title){
        $output .= $before_title . $title . $after_title;
    }
    $output .= '<div class="testimonial-slider-widget">';
        $widget_query = new WP_Query($widget_arg);
        while ($widget_query->have_posts()): $widget_query->the_post();
        $output .= '<div class="slider-item col-md-12">
                        <img src="' . get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') . '">
	                      <p>' . get_the_content('Read more') . '</p>
	                           <h2> <a href="' .get_the_permalink() . '">' . get_the_title() . '</a></h2>
	                     <p>' . get_post_meta(get_the_ID(), "testimonial_director", true) . '</p>
                    </div>';
        endwhile;
    $output .= '</div>
                </div>';
    echo  $output;
    add_action('wp_enqueue_scripts', 'add_custom_css_styles');
    }

    public function update($new_instance, $old_instance)
    {

        $instance = $old_instance;

        $instance['title'] = strip_tags($new_instance['title']);
        $instance['testimonial_layout'] = strip_tags($new_instance['testimonial_layout']);
        $instance['no_of_testimonial'] = strip_tags($new_instance['no_of_testimonial']);

        return $instance;

    }

} //End of class "Navincustom_Widget"

add_action('widgets_init', function () {
    register_widget('Navincustom_Widget');
});
