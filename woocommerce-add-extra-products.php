<?php
/*
Plugin Name: WooCommerce Productos Extra Automáticos
Description: Agrega productos extra automáticamente al carrito dependiendo de la categoría del producto.
Version: 1.1
Author: Jorge Luis Bou-saad
*/

// El código para agregar productos extra automáticamente
add_action('woocommerce_add_to_cart', 'add_extra_product_for_categories', 10, 6);

function add_extra_product_for_categories($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    $product = wc_get_product($product_id);

    // Definir las categorías y los productos extra que se agregan al carrito
    $categories_with_extra_products = array(
        'todoterreno' => 123, // ID del producto extra para la categoría "todoterreno"
        'moto'        => 456, // ID del producto extra para la categoría "moto"
        'carga'       => 789, // ID del producto extra para la categoría "carga"
        'camion'      => 101, // ID del producto extra para la categoría "camión"
        // Puedes agregar más categorías y productos extra según lo necesites
    );

    // Recorrer las categorías definidas
    foreach ($categories_with_extra_products as $category => $extra_product_id) {
        if (has_term($category, 'product_cat', $product_id)) {
            // Verificar si el producto extra ya está en el carrito
            $found = false;
            foreach (WC()->cart->get_cart() as $cart_item) {
                if ($cart_item['product_id'] == $extra_product_id) {
                    $found = true;
                    break;
                }
            }

            // Si el producto extra no está en el carrito, añadirlo
            if (!$found) {
                WC()->cart->add_to_cart($extra_product_id, $quantity);
            }
        }
    }
}
