<?php

class APP_Ir123Pay extends APP_Boomerang {
	protected $options;

	public function __construct() {
		parent::__construct( 'ir123pay', array(
			'admin'    => __( 'Ir123Pay', 'appthemes-ir123pay' ),
			'dropdown' => __( 'Ir123Pay', 'appthemes-ir123pay' )
		) );
		add_action( 'template_redirect', array(
			$this,
			'listen'
		) );
	}

	public function listen() {
		global $wp_query;
		if ( ! is_singular( APPTHEMES_ORDER_PTYPE ) && array_key_exists( 'x_receipt_link_url', $_GET ) ) {
			wp_redirect( add_query_arg( $_GET, $_GET['x_receipt_link_url'] ) );
		}
	}

	public function create_form( $order, $options ) {
		$merchant_id  = $options['merchant_id'];
		$amount       = $order->get_total();
		$callback_url = urlencode( $order->get_return_url() . '&x_receipt_link_url' );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, 'https://123pay.ir/api/v1/create/payment' );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, "merchant_id=$merchant_id&amount=$amount&callback_url=$callback_url" );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		$response = curl_exec( $ch );
		curl_close( $ch );

		$result = json_decode( $response );
		if ( ! $result->status ) {
			echo $result->message;
			exit();
		}

		$fields = array();
		$form   = array(
			'accept-charset' => 'utf-8',
			'action'         => $result->payment_url,
			'id'             => 'create-listing',
			'method'         => 'POST',
			'name'           => 'pp_payform'
		);
		$this->redirect( $form, $fields, __( 'You are now being redirected to 123PAY.', 'appthemes-ir123pay' ) );
	}

	protected function is_valid( $order, $options ) {
		$order       = appthemes_get_order( intval( $_GET['transaction'] ) );
		$merchant_id = trim( $options['merchant_id'] );
		$State       = $_REQUEST['State'];
		$RefNum      = $_SESSION['RefNum'];
		$Total       = $order->get_total();

		if ( $State == 'OK' ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, 'https://123pay.ir/api/v1/verify/payment' );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, "merchant_id=$merchant_id&RefNum=$RefNum" );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			$response = curl_exec( $ch );
			curl_close( $ch );

			$result = json_decode( $response );
			if ( $result->status && $Total == $result->amount ) {
				return true;
			}
		}

		return false;
	}

	protected function is_returning() {
		return array_key_exists( 'x_receipt_link_url', $_GET );
	}

	public function form() {
		return array(
			array(
				'title'  => __( 'Ir123Pay', 'appthemes-ir123pay' ),
				'fields' => array(
					array(
						'title' => __( 'merchant_id', 'appthemes-ir123pay' ),
						'name'  => 'merchant_id',
						'type'  => 'text'
					)
				)
			)
		);
	}
}

?>
