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
        WC()->cart->add_to_cart($extra_product_id, $quantity);
    }
}

// Acción para actualizar la cantidad de productos extra en función de la cantidad de neumáticos en el carrito
add_action('woocommerce_before_calculate_totals', 'update_extra_product_quantities', 10, 1);

function update_extra_product_quantities($cart) {
    // Definir la taxonomía personalizada y los productos extra
    $taxonomy_slug = 'neumatico-vehiculo'; // Cambia esto por el slug de tu taxonomía personalizada

    $terms_with_extra_products = array(
        "vehiculo"    => 114913,
        "carga"       => 114912,
        "moto"        => 114911,
        "comercial"   => 114910,
        "todoterreno" => 114909,
        "camion"      => 114908,
        "turismo"     => 114907,
    );

    // Array para almacenar la cantidad de neumáticos por tipo
    $tyre_quantities = array_fill_keys(array_keys($terms_with_extra_products), 0);

    // Recorrer los productos en el carrito para contar los neumáticos
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product_id = $cart_item['product_id'];
        $product = wc_get_product($product_id);

        // Obtener los términos de la taxonomía personalizada asociados al producto
        $product_terms = wp_get_post_terms($product_id, $taxonomy_slug);

        if (!is_wp_error($product_terms)) {
            foreach ($product_terms as $product_term) {
                if (array_key_exists($product_term->slug, $terms_with_extra_products)) {
                    // Sumar la cantidad de neumáticos de este tipo
                    $tyre_quantities[$product_term->slug] += $cart_item['quantity'];
                }
            }
        }
    }

    // Ahora recorrer el carrito y actualizar la cantidad de productos extra
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product_id = $cart_item['product_id'];

        // Si el producto es una ecotasa, actualizamos su cantidad
        foreach ($terms_with_extra_products as $term => $extra_product_id) {
            if ($product_id == $extra_product_id && isset($tyre_quantities[$term])) {
                $new_quantity = $tyre_quantities[$term];
                if ($new_quantity > 0 && $cart_item['quantity'] != $new_quantity) {
                    $cart->set_quantity($cart_item_key, $new_quantity);
                }
            }
        }
    }
}

// Inyección de HTML en la descripción del producto
add_action('woocommerce_single_product_summary', 'inject_custom_html_in_product_description', 25);

function calculate_price_ecotasa($product, $taxonomy) {

    $price = 0;
    $base_price = $product->get_price();
    switch ($taxonomy) {
        case "vehiculo":
            $ecotasa_vehiculo = 1.35;
            $ecotasasiniva_vehiculo = ($base_price + $ecotasa_vehiculo);
            $ecotasaeiva_vehiculo = ($base_price + $ecotasa_vehiculo) * 0.21;
            $subpriceyoupay_vehiculo = $ecotasasiniva_vehiculo + $ecotasaeiva_vehiculo;
            $price = number_format($subpriceyoupay_vehiculo, 2);
            break;
        case "carga":
            $ecotasa_carga = 1.35;
            $ecotasasiniva_carga = ($base_price + $ecotasa_carga);
            $ecotasaeiva_carga = ($base_price + $ecotasa_carga) * 0.21;
            $subpriceyoupay_carga = $ecotasasiniva_carga + $ecotasaeiva_carga;
            $price = number_format($subpriceyoupay_carga, 2);
            break;
        case "moto":
            $ecotasa_moto = 0.86;
            $ecotasasiniva_moto = ($base_price + $ecotasa_moto);
            $ecotasaeiva_moto = ($base_price + $ecotasa_moto) * 0.21;
            $subpriceyoupay_moto = $ecotasasiniva_moto + $ecotasaeiva_moto;
            $price = number_format($subpriceyoupay_moto, 2);
            break;
        case "comercial":
            $ecotasa_comercial = 2.31;
            $ecotasasiniva_comercial = ($base_price + $ecotasa_comercial);
            $ecotasaeiva_comercial = ($base_price + $ecotasa_comercial) * 0.21;
            $subpriceyoupay_comercial = $ecotasasiniva_comercial + $ecotasaeiva_comercial;
            $price = number_format($subpriceyoupay_comercial, 2);
            break;
        case "todoterreno":
            $ecotasa_todoterreno = 2.31;
            $ecotasasiniva_todoterreno = ($base_price + $ecotasa_todoterreno);
            $ecotasaeiva_todoterreno = ($base_price + $ecotasa_todoterreno) * 0.21;
            $subpriceyoupay_todoterreno = $ecotasasiniva_todoterreno + $ecotasaeiva_todoterreno;
            $price = number_format($subpriceyoupay_todoterreno, 2);
            break;
        case "camion":
            $ecotasa_camion = 0;
            $ecotasasiniva_camion = ($base_price + $ecotasa_camion);
            $ecotasaeiva_camion = ($base_price + $ecotasa_camion) * 0.21;
            $subpriceyoupay_camion = $ecotasasiniva_camion + $ecotasaeiva_camion;
            $price = number_format($subpriceyoupay_camion, 2);
            break;
        case "turismo":
            $ecotasa_turismo = 1.44;
            $ecotasasiniva_turismo = ($base_price + $ecotasa_turismo);
            $ecotasaeiva_turismo = ($base_price + $ecotasa_turismo) * 0.21;
            $subpriceyoupay_turismo = $ecotasasiniva_turismo + $ecotasaeiva_turismo;
            $price = number_format($subpriceyoupay_turismo, 2);
            break;
    }

    return $price;
}

function get_product_price($product) {

    $taxonomy_slug = 'neumatico-vehiculo';
    $product_terms = wp_get_post_terms($product->get_id(), $taxonomy_slug);

    if (is_wp_error($product_terms)) {
        var_dump($product_terms);
        return;
    }

    $price = 0;
    foreach ($product_terms as $product_term) {
        var_dump($product_term->slug);
        $price = calculate_price_ecotasa($product, $product_term->slug);
    }

    return $price;
}

function inject_custom_html_in_product_description() {
    global $product;

    // Solo inyectar si es un producto válido
    if (!$product || !is_a($product, 'WC_Product')) {
        return;
    }

    // Obtener el precio del producto y otros valores necesarios
   $product_price = get_product_price($product);
   $unit_price = "€". $product_price;

    echo '
    <dl class="tm-extra-product-options-totals tm-custom-price-totals">
        <dt class="tm-unit-price">Precio unidad (inc. ecotasa)</dt>
        <dd class="tm-unit-price">
            <span class="price amount options">' . $unit_price . '</span>
        </dd>
        <dt class="tm-options-totals">Total Ecotasa (por cantidad de productos)</dt>
        
        <dt class="tm-final-totals">Total (por cantidad, inc. ecotasa)</dt>
        <dd class="tm-final-totals">
            <span class="price amount final"><span class="woocommerce-Price-amount amount"><bdi>' . $total_price . '</bdi></span></span>
        </dd>
    </dl>';
}






