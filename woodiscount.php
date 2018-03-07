<?php
   /*
     Plugin Name: Discount for WooCommerce Products
     Plugin URI: https://sarathlal.com/
     Description: Allow discount as per cart items
     Author: Sarathlal N
     Version: 1.0
     Author URI: https://sarathlal.com/
    */
   
   class WooDiscount {
   
       public function __construct() {
           add_action('admin_init', array($this, 'discount_setting'));
           add_action('admin_menu', array($this, 'create_options_page'));
           add_action( 'woocommerce_cart_calculate_fees', array($this, 'apply_discount'));
           
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
		   <form id="submit-data" method="post" action="options.php">
			  <?php settings_fields('woo_discount_group'); ?>
			  <p>
				 <label for="woo_products">Enter Product Id to allow discount</label><br>
				 <input type="text" id="woo_products" name="woo_products" value="<?php echo get_option('woo_products'); ?>" />
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
			$discountable_product = get_option('woo_products');
			$price_for_discount = get_option('woo_price');
			$discount_amount = get_option('woo_discount');
			$discount_amount = -1 * abs($discount_amount);
			foreach($cartitems as $cartitem) {
				$productid = $cartitem['product_id'];
				if($productid == $discountable_product) {
				$item_price = get_post_meta($productid , '_price', true);
				//Keep for upgrade
				//$item_regular_price = get_post_meta($productid , '_regular_price', true);
				//$item_sale_price = get_post_meta($productid , '_regular_price', true);
				$item_price = get_post_meta($productid , '_price', true);
					if($item_price < $price_for_discount) {
					WC()->cart->add_fee( 'Discount', $discount_amount );
					}
				}
			}
		}	
}
$WooDiscount = new WooDiscount;
