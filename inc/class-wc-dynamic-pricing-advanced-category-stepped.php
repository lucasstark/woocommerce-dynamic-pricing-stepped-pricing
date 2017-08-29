<?php

class WC_Dynamic_Pricing_Advanced_Category_Stepped extends WC_Dynamic_Pricing_Advanced_Base {

	private static $instance;

	public static function instance() {
		if ( self::$instance == null ) {
			self::$instance = new WC_Dynamic_Pricing_Advanced_Category_Stepped( 'advanced_category' );
		}

		return self::$instance;
	}

	public $adjustment_sets;

	public function __construct( $module_id ) {
		parent::__construct( $module_id );
		$sets = get_option( '_a_category_pricing_rules' );
		if ( $sets && is_array( $sets ) && sizeof( $sets ) > 0 ) {
			foreach ( $sets as $id => $set_data ) {
				$obj_adjustment_set           = new WC_Dynamic_Pricing_Adjustment_Set_Category( $id, $set_data );
				$this->adjustment_sets[ $id ] = $obj_adjustment_set;
			}
		}
	}

	public function adjust_cart( $temp_cart ) {

		if ( $this->adjustment_sets && count( $this->adjustment_sets ) ) {

			$valid_sets = wp_list_filter( $this->adjustment_sets, array(
				'is_valid_rule'     => true,
				'is_valid_for_user' => true
			) );
			if ( empty( $valid_sets ) ) {
				return;
			}


			//Now process bulk rules
			foreach ( $valid_sets as $set_id => $set ) {
				if ( $set->mode != 'block' ) {
					continue;
				}


				//check if this set is valid for the current user;
				$is_valid_for_user = $set->is_valid_for_user();

				if ( !( $is_valid_for_user ) ) {
					continue;
				}

				//Lets actuall process the rule. 
				//Setup the matching quantity
				$targets = $set->targets;

				//Get the quantity to compare
				$collector = $set->get_collector();
				$q         = 0;


				$total_quantity_of_matching_categories    = 0;
				$total_quantity_of_matching_targets       = 0;
				$total_quantity_which_can_be_discounted   = 0;
				$total_quantity_which_has_been_discounted = 0;

				foreach ( $temp_cart as $lck => &$l_cart_item ) {

					if ( apply_filters( 'woocommerce_dynamic_pricing_is_object_in_terms', is_object_in_term( $l_cart_item['product_id'], 'product_cat', $collector['args']['cats'] ), $l_cart_item['product_id'], $collector['args']['cats'] ) ) {
						if ( apply_filters( 'woocommerce_dynamic_pricing_count_categories_for_cart_item', true, $l_cart_item, $lck ) ) {
							$total_quantity_of_matching_categories += (int) $l_cart_item['quantity'];
						}
					}

					$terms = $this->get_product_category_ids( $l_cart_item['data'] );
					if ( count( array_intersect( $targets, $terms ) ) > 0 ) {
						$l_cart_item['can_be_discounted']   = true;
						$total_quantity_of_matching_targets += (int) $l_cart_item['quantity'];
					}

				}

				$rule = reset( $set->pricing_rules ); //block rules can only have one line item.
				if ( $total_quantity_of_matching_categories < $rule['from'] ) {
					continue;
				}


				if ( $rule['repeating'] == 'yes' ) {
					$b = floor( $total_quantity_of_matching_categories / ( $rule['from'] ) ); //blocks - this is how many times has the required amount been met.
				} else {
					$b = 1;
				}

				$total_quantity_which_can_be_discounted = $b * $rule['from'];

				$cart_to_process = wp_list_filter( $temp_cart, array(
					'can_be_discounted' => true
				) );

				foreach ( $cart_to_process as $cart_item_key => $cart_item ) {

					if ( $total_quantity_which_has_been_discounted >= $total_quantity_which_can_be_discounted ) {
						break;
					}

					$product = $cart_item['data'];

					$process_discounts = apply_filters( 'woocommerce_dynamic_pricing_process_product_discounts', true, $cart_item['data'], 'advanced_category', $this, $cart_item );
					if ( !$process_discounts ) {
						continue;
					}

					$price_adjusted = false;
					$original_price = $this->get_price_to_discount( $cart_item, $cart_item_key );
					$price_adjusted = $this->get_block_adjusted_price( $cart_item, $original_price, $rule, $total_quantity_which_can_be_discounted - $total_quantity_which_has_been_discounted );
					if ( $price_adjusted !== false && floatval( $original_price ) != floatval( $price_adjusted ) ) {
						$total_quantity_which_has_been_discounted += $cart_item['quantity'];

						WC_Dynamic_Pricing::apply_cart_item_adjustment( $cart_item_key, $original_price, $price_adjusted, 'advanced_category', $set_id );
					}

				}
			}
		}
	}

	protected function get_block_adjusted_price( $cart_item, $price, $rule, $a ) {
		if ( $a > $cart_item['quantity'] ) {
			$a = $cart_item['quantity'];
		}

		$amount       = apply_filters( 'woocommerce_dynamic_pricing_get_rule_amount', $rule['amount'], $rule, $cart_item, $this );
		$num_decimals = apply_filters( 'woocommerce_dynamic_pricing_get_decimals', (int) get_option( 'woocommerce_price_num_decimals' ) );

		switch ( $rule['type'] ) {
			case 'fixed_adjustment':
				$adjusted            = floatval( $price ) - floatval( $amount );
				$adjusted            = $adjusted >= 0 ? $adjusted : 0;
				$line_total          = 0;
				$full_price_quantity = $cart_item['quantity'] - $a;

				$discount_quantity = $a;

				$line_total = ( $discount_quantity * $adjusted ) + ( $full_price_quantity * $price );
				$result     = $line_total / $cart_item['quantity'];
				$result     = $result >= 0 ? $result : 0;

				break;
			case 'percent_adjustment':
				$amount     = $amount / 100;
				$adjusted   = round( floatval( $price ) - ( floatval( $amount ) * $price ), (int) $num_decimals );
				$line_total = 0;

				$full_price_quantity = $cart_item['available_quantity'] - $a;
				$discount_quantity   = $a;

				$line_total = ( $discount_quantity * $adjusted ) + ( $full_price_quantity * $price );
				$result     = $line_total / $cart_item['quantity'];

				if ( $cart_item['available_quantity'] != $cart_item['quantity'] ) {

				}

				$result = $result >= 0 ? $result : 0;
				break;
			case 'fixed_price':
				$adjusted            = round( $amount, (int) $num_decimals );
				$line_total          = 0;
				$full_price_quantity = $cart_item['quantity'] - $a;
				$discount_quantity   = $a;
				$line_total          = ( $discount_quantity * $adjusted ) + ( $full_price_quantity * $price );
				$result              = $line_total / $cart_item['quantity'];
				$result              = $result >= 0 ? $result : 0;

				break;
			default:
				$result = false;
				break;
		}

		return $result;
	}

	public function get_adjusted_price( $cart_item_key, $cart_item ) {

	}

}