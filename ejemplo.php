<?php
// Add free gifted product for specific products in the cart Rutina Rejuvenecedora Priori
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
	//Preparar $time_ok = current_time('timestamp') > $start_time && current_time('timestamp') < $expiration_time
	
    $cart_subtotal     = 0; // Initializing

    // Loop through cart items (first loop)
    //foreach ( $cart->get_cart() as $cart_item_key => $cart_item ){
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
            $free_key = $cart_item_key;
            $free_qty = $cart_item['quantity'];
            $cart_item['data']->set_price(0); // Optionally set the price to zero
        } else {
            //$cart_subtotal += $cart_item['line_total'] + $cart_item['line_tax'];
        }
    }

    // If the free product is not already in cart, subtotal match and the customer insn't added an excluded product, addd it
    //if ( ! isset($free_key) && $product->is_on_sale() == false && $is_target_product_id == true ) {
    if ( ! isset($free_key) && $product->is_on_sale() == false && ( $cart_item['product_id'] == 5853 || in_array($cart_item['product_id'], $target_product_id_individuales) ) && current_time('timestamp') > $start_time && current_time('timestamp') < $expiration_time ) {
        //$cart->add_to_cart( $free_product_id );//Añade 1 solo producto al carrito
		//Añade más de 1 producto a la vez al carrito
		foreach ($free_product_id as $product_id) {
			$cart->add_to_cart($product_id);
		}
    }

    // If the free product is already in cart and the customer was added an excluded product, remove it
    elseif ( isset($free_key) && $product->is_on_sale() == true ) {
        $cart->remove_cart_item( $free_key );
    }
	
    // If the free product is already in cart and the customer was added an excluded product, remove it
    //elseif ( isset($free_key) && $is_target_product_id == false && ( ! isset($free_key) && $free_product_id == 2817 ) ) {
    //  $cart->remove_cart_item( $free_key );
    //}

    // If the free product is already in cart and the customer was added an excluded product, remove it
	//$cart_items = $cart->get_cart_contents();
	//if (count($cart_items) == 2 && isset($cart_items[2817]) && isset($cart_items[5870])) {
	//	$cart->remove_cart_item(5870);
	//}
	
	$cart_items = $cart->get_cart_contents();
	$product_ids = array();
	foreach ($cart_items as $item) {
		$product_ids[] = $item['product_id'];
	}

	//Este código verifica si el producto 5853 está presente en el carrito. Si no está presente o si se cumple alguna de las otras condiciones que mencionaste, entonces se elimina el producto 5870 del carrito.
	$found_5853 = false;
	foreach ($cart_items as $cart_item_key => $item) {
		if ($item['product_id'] == 5853) {
			$found_5853 = true;
			break;
		}
	}
	if (!$found_5853 || (isset($free_key) && $is_target_product_id == false) || (count($product_ids) == 2 && in_array(2817, $product_ids) && in_array(5870, $product_ids))) {
		foreach ($cart_items as $cart_item_key => $item) {
			if ($item['product_id'] == 5870) {
				$cart->remove_cart_item($cart_item_key);
				break;
			}
		}
	}

    // If the free product is already in cart and the customer was added an excluded product, remove it
    elseif ( isset($free_key) && ( current_time('timestamp') < $start_time || current_time('timestamp') > $expiration_time ) ) {
        $cart->remove_cart_item( $free_key );
    }

    // Keep free product quantity to 1.
	//Para 1 producto
    //elseif ( isset($free_qty) && $free_qty > 1 ) {
    //    $cart->set_quantity( $free_key, 1 );
    //}
	//Para más de 1
	foreach ($free_product_id as $product_id) {
		if (isset($cart_item['product_id']) && $cart_item['product_id'] == $product_id) {
			if ($cart_item['quantity'] > 1) {
				$cart->set_quantity($cart_item_key, 1);
			}
		}
	}
}
