add_action( 'woocommerce_before_calculate_totals', 'check_free_gifted_product_promo_rejuvenecedora' );
function check_free_gifted_product_promo_rejuvenecedora( $cart ) {
    global $is_target_product_id;

    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    // Initialising variable
    $is_on_sale = false;
    $is_target_product_id = false;

    // Settings
    $free_product_id   = array (2817, 5870); //Gentle Cleanser y Neceser Priori
    $target_product_id_rutina = 5853; //(Rutina Rejuvenecedora)
    $target_product_id_individuales = array (5319, 2814); //(Moisturizing Cream y Brightening Serum)
    $is_target_product_in_cart = false;

    $start_time = strtotime('2023-04-20 12:15:00'); // Establezca la fecha y hora de inicio aquí
    $expiration_time = strtotime('2023-04-30 23:59:59'); // Establezca la fecha y hora de expiración aquí

    // Loop through cart items (first loop)
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        // Getting an instance of the product object
        $product =  $cart_item['data'];

        // When Product Kit is in cart
        if ($cart_item['product_id'] == $target_product_id_rutina || in_array($cart_item['product_id'], $target_product_id_individuales) ){
            $is_target_product_id = true;
            $key_to_remove = $cart_item_key;
            $is_target_product_in_cart = true;
        }

        // When free product is in cart
        elseif ( in_array( $cart_item['product_id'], $free_product_id ) && $is_target_product_id == true ) {
            if ($cart_item['quantity'] > 1) {
                // Reset quantity to 1 if it has been modified
                $cart->set_quantity($cart_item_key, 1);
            }
            $cart_item['data']->set_price(0); // Optionally set the price to zero
        }
    }

    // Check if both individual products are in cart
    if (count(array_intersect($target_product_id_individuales, array_column($cart->get_cart(), 'product_id'))) == count($target_product_id_individuales)) {
        // Add free products to cart
        foreach ($free_product_id as $product_id) {
            if (!in_array($product_id, array_column($cart->get_cart(), 'product_id'))) {
                $cart->add_to_cart($product_id);
            }
        }
    } else {
        // Remove free products from cart if conditions are not met
        foreach ($free_product_id as $product_id) {
            if (in_array($product_id, array_column($cart->get_cart(), 'product_id'))) {
                foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
                    if ($cart_item['product_id'] == $product_id) {
                        $cart->remove_cart_item($cart_item_key);
                    }
                }
            }
        }
    }

    // Remove product with ID 4363 from cart if conditions are met
    if (in_array(4363, array_column($cart->get_cart(), 'product_id')) && count(array_intersect($target_product_id_individuales, array_column($cart->get_cart(), 'product_id'))) != 1) {
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] == 4363) {
                $cart->remove_cart_item($cart_item_key);
            }
        }
    }
}
