<?php


//Update currency
add_action('wp_ajax_change_currency', 'change_currency_callback');
add_action('wp_ajax_nopriv_change_currency', 'change_currency_callback');

function change_currency_callback() {
    if (!isset($_POST['currency'])) {
        wp_send_json_error(['message' => 'Currency not provided.']);
    }

    $currency = sanitize_text_field($_POST['currency']);


    WC()->session->set('chosen_currency', $currency);

    wp_send_json_success(
        [
            'message' => 'Currency updated.',
            'currency' => WC()->session->get('chosen_currency')
        ]
    );
}


add_filter('woocommerce_currency', 'set_custom_currency');

function set_custom_currency($currency) {
    if (WC()->session && WC()->session->get('chosen_currency')) {
        return WC()->session->get('chosen_currency');
    }
    return $currency;
}





//Get current currency

add_action('wp_ajax_get_current_currency', 'handle_get_current_currency');
add_action('wp_ajax_nopriv_get_current_currency', 'handle_get_current_currency');

function handle_get_current_currency() {
    $current_currency = get_woocommerce_currency();

    if (!$current_currency) {
        wp_send_json_error(['message' => 'Could not retrieve currency.']);
    }

    wp_send_json_success([
        'currency' => $current_currency,
    ]);
}

function select_currency_switch(){
    ?>
    <div id="currency_selector">
        <select name="default_currency" id="">
            <option value="SGD">SGD</option>
            <option value="USD" selected="selected">USD</option>
            <option value="EUR">EUR</option>
        </select>
    </div>
    <?php
}
add_shortcode('select_currency_switch','select_currency_switch');