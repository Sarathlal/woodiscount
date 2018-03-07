<?php
   /*
     Plugin Name: Discount for WooCommerce Products
     Plugin URI: https://sarathlal.com/
     Description: Allow discount as per cart items
     Author: Sarathlal N
     Version: 1.2
     Author URI: https://sarathlal.com/
    */
   
   class WooDiscount {
   
       public function __construct() {
		   add_action('admin_enqueue_scripts', array($this, 'enqueue_script'));
           add_action('admin_init', array($this, 'discount_setting'));
           add_action('admin_menu', array($this, 'create_options_page'));
           add_action( 'woocommerce_cart_calculate_fees', array($this, 'apply_discount'));
           
       }
       
		//Enque script
		function enqueue_script() {
			wp_enqueue_script('discount_script', plugin_dir_url(__FILE__) . 'js/select2.js');
			wp_enqueue_style('discount_css', plugin_dir_url(__FILE__) . 'css/select2.min.css', false, '1.0.0');
		}       
   
       //Register settings for discount with in new group - woo_discount_group
       function discount_setting() {
           register_setting('woo_discount_group', 'woo_products', array($this, 'products_callback'));
           register_setting('woo_discount_group', 'woo_price', array($this, 'price_callback'));
           register_setting('woo_discount_group', 'woo_discount', array($this, 'discount_callback'));
       }
       
       //Create Option Page for discount
       function create_options_page() {
           add_options_page('Discount Settings', 'Discount Settings', 'manage_options', 'discount-options', array($this, 'option_page_content'));
       }
   
       //Render option Page content
       function option_page_content() {
           ?>
		<div>
		   <h2>Add discount for WooCommerce products</h2>
		   
		   
		   
			<?php $selected_products = get_option('woo_products'); ?>
			
		   <form id="submit-data" method="post" action="options.php">
			  <?php settings_fields('woo_discount_group'); ?>
			  <p>
				 <label for="woo_products">Select Products</label><br>
				<?php  
				$query = array(
						'post_type'      => 'product',
						'posts_per_page' => -1,
					);
				$queryObject = new WP_Query($query);
				if ($queryObject->have_posts()) {
					echo "<select  class='js-example-basic-single' id='woo_products' name='woo_products[]' multiple>";
					while ($queryObject->have_posts()) {
						$queryObject->the_post();
						global $product;
						$item_id = get_the_id();
						if(in_array($item_id, $selected_products)){
							$selected = "selected";
						} else {
							$selected = "";
						}
						echo "<option value='".get_the_id()."' ".$selected.">".get_the_title()."</option>";
					}
					echo "</select>";
				}    
				?>
			  </p>
			  <p>
				 <label for="woo_price">Check Price</label><br>
				 <input type="text" id="woo_price" name="woo_price" value="<?php echo get_option('woo_price'); ?>" />
			  </p>
			  <p>
				 <label for="woo_discount">Total Discount</label><br>
				 <input type="text" id="woo_discount" name="woo_discount" value="<?php echo get_option('woo_discount'); ?>" />
			  </p>
			  <?php submit_button(); ?>
		   </form>
		</div>
		<?php
		}
		
		//Apply discount
		function apply_discount() {
			global $woocommerce;
			$cartitems = $woocommerce->cart->get_cart();
			$discountable_product_array = get_option('woo_products');
			$price_for_discount = get_option('woo_price');
			$discount_amount = get_option('woo_discount');
			$discount_amount = -1 * abs($discount_amount);
			$trigger = false;
			foreach($cartitems as $cartitem) {
				$productid = $cartitem['product_id'];
				if(in_array($productid, $discountable_product_array)) {
				$item_price = get_post_meta($productid , '_price', true);
				//Keep for upgrade
				//$item_regular_price = get_post_meta($productid , '_regular_price', true);
				//$item_sale_price = get_post_meta($productid , '_regular_price', true);
				$item_price = get_post_meta($productid , '_price', true);
					if($item_price < $price_for_discount) {
						$trigger = true;
					}
				}
			}
			if($trigger) {
				WC()->cart->add_fee( 'Discount', $discount_amount );
			}
		}	
}
$WooDiscount = new WooDiscount;
