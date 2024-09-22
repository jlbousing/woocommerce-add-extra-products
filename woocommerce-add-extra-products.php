<?php
/*
Plugin Name: WooCommerce Productos Extra Automáticos por Taxonomía
Description: Agrega productos extra automáticamente al carrito dependiendo de la taxonomía personalizada del producto.
Version: 1.1
Author: Jorge Luis Bou-saad
*/

// Acción para agregar productos extra automáticamente
add_action('woocommerce_add_to_cart', 'add_extra_product_for_taxonomy', 10, 6);

function add_extra_product_for_taxonomy($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    $product = wc_get_product($product_id);

    // Definir la taxonomía personalizada y los productos extra que se agregan al carrito
    $taxonomy_slug = 'neumatico-vehiculo'; // Cambia esto por el slug de tu taxonomía personalizada


    // ID de productos extra para cada término
    $terms_with_extra_products = array(
        "vehiculo"    => 114913,
        "carga"       => 114912,
        "moto"        => 114911,
        "comercial"   => 114910,
        "todoterreno" => 114909,
        "camion"      => 114908,
        "turismo"     => 114907,
    );

    // Obtener los términos de la taxonomía personalizada asociados al producto
    $product_terms = wp_get_post_terms($product_id, $taxonomy_slug);

    if (is_wp_error($product_terms)) {
        var_dump($product_terms);
        return;
    }

    // Crear un array para almacenar los IDs de productos extra que se van a añadir
    $products_to_add = [];

    // Verificar si el producto tiene términos de la taxonomía personalizada
    foreach ($product_terms as $product_term) {
        if (array_key_exists($product_term->slug, $terms_with_extra_products)) {
            $extra_product_id = $terms_with_extra_products[$product_term->slug];

            // Comprobar si el producto extra ya está en el carrito
            $found = false;
            foreach (WC()->cart->get_cart() as $cart_item) {
                if ($cart_item['product_id'] == $extra_product_id) {
                    $found = true;
                    break;
                }
            }

            // Si el producto extra no está en el carrito, añadirlo a la lista
            if (!$found) {
                $products_to_add[] = $extra_product_id;
            }
        }
    }

    // Añadir los productos extra al carrito
    foreach ($products_to_add as $extra_product_id) {
        WC()->cart->add_to_cart($extra_product_id);
    }
}
