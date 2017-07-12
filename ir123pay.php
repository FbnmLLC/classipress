<?php
/*
Plugin Name: 123Pay.IR - ClassiPress
Description: پلاگین پرداخت، سامانه پرداخت یک دو سه پی برای ClassiPress
AppThemes ID: ir123pay
Version: 1.0
Author: سامانه پرداخت یک دو سه پی
Author URI: https://123pay.ir
Text Domain: appthemes-ir123pay
*/
add_action( 'init', 'appthemes_pp_setup' );
function appthemes_pp_setup() {
	// Check for right version of Vantage
	if ( ! current_theme_supports( 'app-payments' ) ) {
		add_action( 'admin_notices', 'appthemes_pp_display_version_warning' );

		return;
	}
	appthemes_pp_add_currencies();
	require dirname( __FILE__ ) . '/ir123pay-gateway.php';
	appthemes_register_gateway( 'APP_Ir123pay' );
}

function appthemes_pp_display_version_warning() {
	$message = __( 'AppThemes 123PAY Payment Gateway could not run.', 'appthemes-ir123pay' );
	if ( ! current_theme_supports( 'app-payments' ) ) {
		$message = __( 'AppThemes 123PAY Payment Gateway does not support the current theme. Please use a compatible AppThemes Product.', 'appthemes-ir123pay' );
	}
	echo '<div class="error fade"><p>' . $message . '</p></div>';
	deactivate_plugins( plugin_basename( __FILE__ ) );
}

class APP_Ir123pay_Fields {
	const CREATE_URL = '';
	const VERIFY_URL = '';
}

function appthemes_pp_add_currencies() {
	$currencies = array(
		'IRR' => array(
			'symbol' => 'IRR',
			'name'   => __( 'Iranian Rial', 'appthemes-ir123pay' )
		),
		'IRT' => array(
			'symbol' => 'IRT',
			'name'   => __( 'Iranian Toman', 'appthemes-ir123pay' )
		),
	);
	foreach ( $currencies as $currency_code => $args ) {
		APP_Currencies::add_currency( $currency_code, $args );
	}
}
