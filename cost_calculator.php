<?php
/*

    Plugin Name: Cost Calculator

    Plugin URI: https://www.costcalulator.com

    Description: Calculate cost based on user's budget.

    Author: John Doe

    Version: 1.0

    Author URI: https://www.costcalulator.com

*/

if (!defined('ABSPATH')):
    die("You are not allowed to access protected files directly");
endif;

define( 'COST_CALCULATOR_PATH', plugin_dir_path( __FILE__ ) );	// define the absolute plugin path for includes

// create required table(s) on plugin activate
function cost_calculator_plugin_sql_tables()
{      
  	global $wpdb; 
  	$charset_collate = $wpdb->get_charset_collate();
	
	$sql1 = "CREATE TABLE IF NOT EXISTS `custom_cost_calculator`(
			`id` int(11) NOT NULL AUTO_INCREMENT,
            `cost_from` int(11) NOT NULL,
            `cost_to` int(11) NOT NULL,
            `expert_fees` VARCHAR(999),  
            `crossed_amount` VARCHAR(999), 
            `cost_label` VARCHAR(999),    
			PRIMARY KEY  (id) )$charset_collate;";
	
	$queries = array();
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	array_push($queries,$sql1);
	foreach ($queries as $key => $sql) {
		dbDelta( $sql );
	}
}
register_activation_hook( __FILE__, 'cost_calculator_plugin_sql_tables' );


// enqueue styles and scripts
function cost_enqueue_scripts() {

    global $wpdb;
    $cost_range = array();
    $regular_price = array();
    $sale_price = array();
    $get_cost_ranges = "SELECT * FROM `custom_cost_calculator`";
    $cost_results = $wpdb->get_results($get_cost_ranges);
    if (!empty($cost_results)) {
        foreach ($cost_results as $cc_result){
            array_push($cost_range, $cc_result->cost_label);
            array_push($regular_price, $cc_result->crossed_amount);
            array_push($sale_price, $cc_result->expert_fees);
        }
    }

    $theme_info = wp_get_theme();
    wp_enqueue_style( 'cost_custom_styles', plugins_url('/assets/css/cost_custom_styles.css', __FILE__), array(), $theme_info->get( 'Version' ), false );
    wp_enqueue_style('bootstrap4', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-script', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js', array('jquery'), $theme_info->get( 'Version' ), true);
    wp_enqueue_script('cost_custom-script', plugins_url('/assets/js/cost_custom-script.js', __FILE__), array('jquery'), $theme_info->get( 'Version' ), true);
    wp_localize_script(
        'cost_custom-script',
        'opt',
        array('ajaxUrl' => admin_url('admin-ajax.php'),
        'noResults' => esc_html__('No data found', 'textdomain'),
        // 'cost_range' => $cost_range,
        // 'regular_price' => $regular_price,
        // 'sale_price' => $sale_price,
        )
    );
}
add_action('wp_enqueue_scripts', 'cost_enqueue_scripts');




// enqueue admin scripts
function load_custom_cost_wp_admin_style($hook) {
    $theme_info = wp_get_theme();
    // Load only on ?page=mypluginname
    // echo $hook;
    if( $hook != 'toplevel_page_cost-calculator') {
            return;
    }
    wp_enqueue_style( 'cost_admin_styles_css', plugins_url('/assets/css/cost_admin_styles.css', __FILE__), array(), $theme_info->get( 'Version' ), false );
    wp_enqueue_style('bootstrap4', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-script', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js', array('jquery'), $theme_info->get( 'Version' ), true);
    wp_enqueue_script('cost_admin-script', plugins_url('/assets/js/cost_adminScripts.js', __FILE__), array('jquery'), $theme_info->get( 'Version' ), true);
    wp_localize_script(
        'cost_admin-script',
        'opt',
        array('ajaxUrl' => admin_url('admin-ajax.php'),
        'noResults' => esc_html__('No data found', 'textdomain'),
                )
    );
}
add_action( 'admin_enqueue_scripts', 'load_custom_cost_wp_admin_style' );



function cost_calculator_admin_menu() {
    add_menu_page(
        __( 'Cost Calculator', 'my-textdomain' ),
        __( 'Cost Calculator', 'my-textdomain' ),
        'manage_options',
        'cost-calculator',
        'cost_calclator_admin_page_contents',
        'dashicons-schedule'
    );
}
add_action( 'admin_menu', 'cost_calculator_admin_menu' );

function cost_calclator_admin_page_contents() {
    global $wpdb;
    ?>
        <div class="wrap">
            <div id="registration_form" style='width:100%'>
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <div class="create-idea-container mt-3 mb-4">
                                <form class="form-inline" id="create_cost_range">
                                    <div class="form-group mx-sm-3 mb-3 mt-3">
                                        <h4 class="sr-only mb-2">New Cost</h4>
                                        <div class="row">
                                            <div class="col">
                                                <label for="inputEmail4" class="form-label">From:</label>
                                                <input type="text" class="form-control" name="price_from" placeholder="Enter Price(eg: 2000)" aria-label="Enter Price">
                                            </div>
                                            <div class="col">
                                                <label for="inputEmail4" class="form-label">To:</label>
                                                <input type="text" class="form-control" name="price_to" placeholder="Enter Price(eg: 2000)" aria-label="Enter Price">
                                            </div>
                                        </div>
                                        <div class="row mt-4">
                                            <div class="col">
                                                <label for="inputEmail4" class="form-label">Charges: (Leave empty to show contact us)</label>
                                                <input type="text" class="form-control" name="expert_fees" placeholder="Enter Price(eg: 2000)" aria-label="Enter Price">
                                            </div>
                                            <div class="col">
                                                <label for="inputEmail4" class="form-label">Crossed Amount: (This amount will be shown <s>crossed</s> on frontend)</label>
                                                <input type="text" class="form-control" name="crossed_price" placeholder="Enter Price(eg: 2000)" aria-label="Enter Price">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mb-2" style="margin-left: 16px;">Create Range</button>
                                </form>
                            </div>
							<h1>Cost Range</h1>							
							<table class="sp_table">
                                <tr>
                                    <th>S no</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Expert Fees</th>
                                    <th>Crossed Amount</th>
                                    <th colspan="2">Action</th>
                                </tr>
                                <?php
                                $querystr = "SELECT * FROM `custom_cost_calculator` ORDER BY cost_from ASC";
                                $query_results = $wpdb->get_results($querystr);
                                if (!empty($query_results)) {
                                    $i = 1;
                                    foreach ($query_results as $results){
                                ?>
                               <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $results->cost_from; ?></td>
                                    <td><?php echo $results->cost_to; ?></td>
                                    <td><?php echo $results->expert_fees; ?></td>
                                    <td><s><?php echo $results->crossed_amount; ?></s></td>
                                    <td><?php echo '<a href="javascript:void(0)" class="delete_range" data-id="'.$results->id.'">Delete</a>'; ?></td>       
                                </tr>
                                <?php
                                        $i++;
                                    }
                                }
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
    <?php
}


function thousandsCurrencyFormat($num) {

    if($num>999) {
  
          $x = round($num);
          $x_number_format = number_format($x);
          $x_array = explode(',', $x_number_format);
          $x_parts = array('k', 'm', 'b', 't');
          $x_count_parts = count($x_array) - 1;
          $x_display = $x;
          $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
          $x_display .= $x_parts[$x_count_parts - 1];
  
          return $x_display;
  
    }
  
    return $num;
}
// create cost range
add_action( 'wp_ajax_admin_create_cost_range_action', 'create_cost_range' );
add_action( 'wp_ajax_nopriv_admin_create_cost_range_action', 'create_cost_range' );
function create_cost_range(){
    global $wpdb;

    $result_check_array = array();

    $price_from = $_POST['price_from'] ? $_POST['price_from'] : "";
    $price_to = $_POST['price_to'] ? $_POST['price_to'] : "";
    $expert_fees = $_POST['expert_fees'] ? $_POST['expert_fees'] : "";
    $crossed_price = $_POST['crossed_price'] ? $_POST['crossed_price'] : "";

    if(!empty( $price_from ) && !empty( $price_to )){

        // from shouldn't be greater than to
        if($price_from < $price_to){
            $querystr = "SELECT * FROM `custom_cost_calculator`";
            $query_results = $wpdb->get_results($querystr);

            // if price ranges exist in DB
            if (!empty($query_results)) {
                foreach ($query_results as $results){
                    $db_price_from = $results->cost_from;
                    $db_price_to = $results->cost_to;

                    $db_cost_range = range($db_price_from,$db_price_to);
                    $inserted_cost_range = range($price_from,$price_to);
                    $array_compare=array_intersect($db_cost_range,$inserted_cost_range);
                    if(sizeof($array_compare) > 1){
                        array_push($result_check_array,1);
                    }
                    else{
                        array_push($result_check_array,0);
                    }
                }
                if(in_array( 1 ,$result_check_array )){
                    echo "error|Sorry, Your cost range is colliding with existing ranges.";
                    die();
                }
                else{
                    $data_return_from_query = $wpdb->insert("custom_cost_calculator", array(
                        "cost_from" => $price_from,
                        "cost_to" => $price_to,
                        "expert_fees" => $expert_fees,
                        "crossed_amount" => $crossed_price,
                        "cost_label" => "$".thousandsCurrencyFormat($price_from)."-$".thousandsCurrencyFormat($price_to),
                    ));
                    if($data_return_from_query ==  1){
                        echo "success" . "|" . "Price Range Saved.";
                        die();
                    }
                    else{
                        echo "error|There has been an error saving your range, Please try again later.";
                        die();
                    }
                }
                
            }
            else{
                $data_return_from_query = $wpdb->insert("custom_cost_calculator", array(
                    "cost_from" => $price_from,
                    "cost_to" => $price_to,
                    "expert_fees" => $expert_fees,
                    "crossed_amount" => $crossed_price,
                    "cost_label" => "$".thousandsCurrencyFormat($price_from)."-$".thousandsCurrencyFormat($price_to),
                ));
                if($data_return_from_query ==  1){
                    echo "success" . "|" . "Price Range Saved.";
                    die();
                }
                else{
                    echo "error|There has been an error saving your range, Please try again later.";
                    die();
                }
            }
        }
        else{
            echo "error|Invalid Range.";
            die();
        }
    } 
    else{
        echo "error|Please fill all required fields.";
    } 

    die();
}




// frontend shortcode
function cost_calculator_frontend_func(){
	global $wpdb;
    $total_range_inputs = 0;
    $get_cc_count = "SELECT COUNT(*) as total_rows FROM `custom_cost_calculator`";
    $get_cc_count_results = $wpdb->get_results($get_cc_count);
    if (!empty($get_cc_count_results)) {
        foreach ($get_cc_count_results as $get_cc_count_result){
            $total_range_inputs = $get_cc_count_result->total_rows ? $get_cc_count_result->total_rows - 1 : 0;
        }
    }
    $data_to_return = '
    <div class="cc_hero_container">
        <div class="cc_col_left">
            <h2 class="cc_main_heading">What is your monthly media budget?</h2>
            <h3 class="cc_budget_label">Loading...</h3>
            <input type="range" class="form-range" min="0" max="'.$total_range_inputs.'" step="1" id="cc_range" value="0">
        </div>
        <div class="cc_col_right">
            <p class="cc_crossed_price">Loading...</p>
            <h3 class="cc_sale_price">Loading...</h3>
            <a href="#">Get Started</a>
            <div class="cc_disclaimer"><small>Monthly payment, no setup fees, pay only when you approve the expert</small></div>
        </div>
    </div>
    ';
    return $data_to_return;
}
add_shortcode( 'CostCalculator', 'cost_calculator_frontend_func' );






// delete range
add_action( 'wp_ajax_admin_delete_range_action', 'admin_delete_range_funt' );
add_action( 'wp_ajax_nopriv_admin_delete_range_action', 'admin_delete_range_funt' );
function admin_delete_range_funt(){
    global $wpdb;
    $range_id = $_POST['range_id'] ? $_POST['range_id'] : "";
    $status = $wpdb->delete('custom_cost_calculator', array('id'=>$range_id));
    if($status == 1){
        echo "success|Range Deleted Successfully";
    }
    else{
        echo "error|Sorry! There seems to be a problem, Please try again";
    }

    die();
}




add_action( 'wp_ajax_get_cost_range_action', 'get_cost_range' );
add_action( 'wp_ajax_nopriv_get_cost_range_action', 'get_cost_range' );
function get_cost_range(){
    global $wpdb;
    $cost_range = array();
    $regular_price = array();
    $sale_price = array();
    $get_cost_ranges = "SELECT * FROM `custom_cost_calculator` ORDER BY `cost_from` ASC";
    $cost_results = $wpdb->get_results($get_cost_ranges);
    if (!empty($cost_results)) {
        foreach ($cost_results as $cc_result){
            array_push($cost_range, $cc_result->cost_label);
            array_push($regular_price, $cc_result->crossed_amount);
            array_push($sale_price, $cc_result->expert_fees);
        }
    }

    echo json_encode(array($cost_range,$regular_price,$sale_price));
    die();
}