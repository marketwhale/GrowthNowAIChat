<?php
if (!defined('ABSPATH')) exit;

// Gemini API key
define('GEMINI_API_KEY', 'AIzaSyA2vmScQRlnniWTaWLNwkpr-9PdhPyKsTk');

// Handle chat messages
add_action('wp_ajax_growthnow_chat', 'growthnow_handle_chat');
add_action('wp_ajax_nopriv_growthnow_chat', 'growthnow_handle_chat');

function growthnow_handle_chat() {
    check_ajax_referer('growthnow_nonce', 'nonce');

    $message = sanitize_text_field($_POST['message']);
    if (!isset($_SESSION['growthnow_history'])) $_SESSION['growthnow_history'] = [];
    $_SESSION['growthnow_history'][] = ['user' => $message];

    // Get AI response
    $ai_response = growthnow_get_gemini_response($message);

    // Get product suggestions
    $product_suggestions = growthnow_get_product_suggestions($message);
    if ($product_suggestions) {
        $ai_response .= "<br>" . $product_suggestions;
    }

    $_SESSION['growthnow_history'][] = ['ai' => $ai_response];
    wp_send_json_success($ai_response);
}

// Gemini API call
function growthnow_get_gemini_response($message) {
    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $message]
                ]
            ]
        ]
    ];

    $response = wp_remote_post(
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . GEMINI_API_KEY,
        [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($data),
            'timeout' => 20
        ]
    );

    if (is_wp_error($response)) {
        return "AI service unavailable.";
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
        return $body['candidates'][0]['content']['parts'][0]['text'];
    }

    return "Sorry, I didn't understand that.";
}

// WooCommerce product suggestions
function growthnow_get_product_suggestions($message) {
    $args = ['post_type' => 'product', 'posts_per_page' => 3];
    $products = get_posts($args);

    if (!$products) return "";

    $cards = '<div class="growthnow-product-grid">';
    foreach ($products as $p) {
        $product = wc_get_product($p->ID);
        $cards .= '<div class="growthnow-product-card">';
        $cards .= '<img src="' . get_the_post_thumbnail_url($p->ID, "medium") . '" />';
        $cards .= '<div class="growthnow-product-title">' . get_the_title($p->ID) . '</div>';
        $cards .= '<div class="growthnow-product-price">' . $product->get_price_html() . '</div>';
        $cards .= '<button class="growthnow-add-to-cart" data-product-id="' . $p->ID . '">Add to Cart</button>';
        $cards .= '</div>';
    }
    $cards .= '</div>';

    return $cards;
}

// Add to Cart AJAX
add_action('wp_ajax_growthnow_add_to_cart', 'growthnow_add_to_cart');
add_action('wp_ajax_nopriv_growthnow_add_to_cart', 'growthnow_add_to_cart');

function growthnow_add_to_cart() {
    check_ajax_referer('growthnow_nonce', 'nonce');
    $product_id = intval($_POST['product_id']);

    if (WC()->cart->add_to_cart($product_id)) {
        wp_send_json_success("Product added to cart!");
    } else {
        wp_send_json_success("Failed to add product.");
    }
}
