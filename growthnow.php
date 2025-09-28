<?php
/**
 * Plugin Name: GrowthNow
 * Description: Full-featured AI chat widget for WooCommerce with Gemini API and product suggestions.
 * Version: 2.3
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

// Start session
if (!session_id()) session_start();

// Include AJAX handler
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handler.php';

// Enqueue scripts and styles
function growthnow_enqueue_scripts() {
    wp_enqueue_style('growthnow-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('growthnow-chat', plugin_dir_url(__FILE__) . 'assets/js/chat-widget.js', array('jquery'), null, true);

    wp_localize_script('growthnow-chat', 'growthnow_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('growthnow_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'growthnow_enqueue_scripts');

// Add chat bubble & chat widget HTML
function growthnow_add_chat_widget() {
    ?>
    <!-- Floating Bubble -->
    <div id="growthnow-bubble">
        <img src="<?php echo plugin_dir_url(__FILE__) ?>assets/img/chat-icon.png" alt="Chat" />
    </div>

    <!-- Chat Widget -->
    <div id="growthnow-chat-widget">
        <div id="growthnow-chat-header">
            <span>GrowthNow Chat</span>
            <span id="growthnow-chat-close">
                <img src="<?php echo plugin_dir_url(__FILE__) ?>assets/img/close-icon.png" alt="Close" width="20" height="20" />
            </span>
        </div>
        <div id="growthnow-chat-body"></div>
        <div id="growthnow-typing-indicator" style="display: none;">AI is typing...</div>
        <div id="growthnow-chat-input-container">
            <textarea id="growthnow-chat-input" placeholder="Ask me anything..."></textarea>
            <button id="growthnow-chat-send">
                <img src="<?php echo plugin_dir_url(__FILE__) ?>assets/img/chat-icon.png" alt="Send" width="20" height="20" />
            </button>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'growthnow_add_chat_widget');
