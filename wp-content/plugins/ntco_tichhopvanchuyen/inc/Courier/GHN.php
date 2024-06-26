<?php

namespace Ntvco\Courier;

use Ntvco\Address\AddressMapper;
use Ntvco\Courier\Exception\InvalidAddressDataException;
use Ntvco\Courier\Response\CollectionResponseData;
use Ntvco\Courier\Response\ShippingOrderResponseData;
use Ntvco\OptionsResolver\OptionsResolver;

class GHN extends AbstractCourier {
	/* Const */
	const BASE_URL = 'https://online-gateway.ghn.vn';
	const DEV_BASE_URL = 'https://dev-online-gateway.ghn.vn';

	/**
	 * @var int
	 */
	protected $shop_id;

	/**
	 * @var array
	 */
	protected $shop_info;

	/**
	 * GHN constructor.
	 *
	 * @param string   $access_token
	 * @param int|null $shop_id
	 * @param false    $is_debug
	 */
	public function __construct( $access_token, $shop_id = null, $is_debug = false ) {
		$this->access_token = $access_token;
		$this->shop_id = $shop_id;
		$this->is_debug = $is_debug;
	}

	/**
	 * @return int
	 */
	public function get_shop_id() {
		return $this->shop_id;
	}

	/**
	 * @param int $shop_id
	 * @return $this
	 */
	public function set_shop_id( $shop_id ) {
		$this->shop_id = $shop_id;

		$this->shop_info = null;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_base_url() {
		return $this->is_debug() ? self::DEV_BASE_URL : self::BASE_URL;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_stores( $parameters = [] ) {
		if ( ! $parameters instanceof RequestParameters ) {
			$parameters = new RequestParameters( $parameters );
		}

		$values = $parameters->validate(
			function ( OptionsResolver $options ) {
				$options->define( 'limit' )->asInt()->default( 50 );
				$options->define( 'offset' )->asInt()->default( 0 );
			}
		);

		$response = $this->request(
			'/shiip/public-api/v2/shop/all',
			json_encode( $values )
		);

		self::assertResponseHasKey( $response, 'data' );

		return self::newCollectionResponseData( $response['data']['shops'] ?: [] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_shipping_fee( $parameters ) {
		if ( ! $parameters instanceof RequestParameters ) {
			$parameters = new RequestParameters( $parameters );
		}

		$data = $parameters->validate(
			function ( OptionsResolver $options ) {
				$options->define( 'to_district_id' )->asNumeric()->required();
				$options->define( 'to_ward_code' )->asNumeric()->required();

				$options->define( 'service_id' )->asInt()->default( 0 );
				$options->define( 'service_type_id' )->asInt()->default( 0 );

				$options->define( 'weight' )->asInt()->required();
				$options->define( 'width' )->asInt()->required();
				$options->define( 'height' )->asInt()->required();
				$options->define( 'length' )->asInt()->required();

				$options->define( 'coupon' )->asString();
				$options->define( 'insurance_value' )->asInt()->default( 0 );
			}
		);

		// Convert internal address to GHN address code.
		$this->remap_address_code( $data );

		// Prepare sending data.
		$this->with_header(
			'ShopId',
			$parameters->get( 'shop_id' ) ?: $this->get_shop_id()
		);

		$response = $this->request(
			'/shiip/public-api/v2/shipping-order/fee',
			json_encode( $data )
		);
		self::assertResponseHasKey( $response, 'data' );

		return self::newJsonResponseData( $response['data'] ?: [] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_lead_time( $parameters ) {
		if ( ! $parameters instanceof RequestParameters ) {
			$parameters = new RequestParameters( $parameters );
		}

		$data = $parameters->validate(
			function ( OptionsResolver $options ) {
				$options->define( 'to_district_id' )->asNumeric()->required();
				$options->define( 'to_ward_code' )->asNumeric()->required();

				$options->define( 'service_id' )->asInt();
				$options->define( 'service_type_id' )->asInt();
			}
		);

		// Convert internal address to GHN address code.
		$this->remap_address_code( $data );

		$this->with_header(
			'ShopId',
			$this->get_shop_id() ?: (int) $parameters->get( 'shop_id' )
		);

		$response = $this->request(
			'/shiip/public-api/v2/shipping-order/leadtime',
			json_encode( $data )
		);

		self::assertResponseHasKey( $response, 'data' );

		return self::newJsonResponseData( $response['data'] ?: [] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_order( $parameters ) {
		if ( ! $parameters instanceof RequestParameters ) {
			$parameters = new RequestParameters( $parameters );
		}

		$data = $parameters->validate(
			function ( OptionsResolver $options ) {
				$options->define( 'order_code' )->asString()->required();
			}
		);

		$this->with_header(
			'ShopId',
			(int) $parameters->get( 'shop_id' ) ?: $this->get_shop_id()
		);

		$response = $this->request(
			'/shiip/public-api/v2/shipping-order/detail',
			json_encode( $data )
		);

		self::assertResponseHasKey( $response, 'data' );

		return new ShippingOrderResponseData(
			$response['data']['order_code'],
			$response['data']
		);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see https://api.ghn.vn/home/docs/detail?id=63
	 */
	public function create_order( $parameters ) {
		if ( ! $parameters instanceof RequestParameters ) {
			$parameters = new RequestParameters( $parameters );
		}

		$data = $this->validate_for_creation( $parameters );
		$this->remap_address_code( $data );

		$this->with_header(
			'ShopId',
			$parameters->get( 'shop_id' ) ?: $this->get_shop_id()
		);

		$response = $this->request(
			'/shiip/public-api/v2/shipping-order/create',
			json_encode( $data )
		);
		self::assertResponseHasKey( $response, 'data' );

		return new ShippingOrderResponseData(
			$response['data']['order_code'],
			$response['data']
		);
	}

	/**
	 * @param RequestParameters $parameters
	 * @return array
	 */
	protected function validate_for_creation( RequestParameters $parameters ) {
		return $parameters->validate(
			function ( OptionsResolver $options ) {
				// Required fields.
				$options->define( 'to_name' )->asString()->required();
				$options->define( 'to_phone' )->asString()->required();
				$options->define( 'to_address' )->asString()->required();
				$options->define( 'to_district_id' )->asNumeric()->required();
				$options->define( 'to_ward_code' )->asNumeric()->required();

				$options->define( 'weight' )->asInt()->required();
				$options->define( 'width' )->asInt()->required();
				$options->define( 'height' )->asInt()->required();
				$options->define( 'length' )->asInt()->required();

				$options->define( 'payment_type_id' )->asInt()->required()->allowedValues( 1, 2 );
				$options->define( 'service_type_id' )->asInt();
				$options->define( 'service_id' )->asInt();

				$options->define( 'cod_amount' )->asInt();
				$options->define( 'order_value' )->asInt();
				$options->define( 'insurance_value' )->asInt();

				$options->define( 'items' )
					->allowedTypes( 'array' )
					->required();

				$options
					->define( 'required_note' )
					->asString()
					->default( 'KHONGCHOXEMHANG' )
					->allowedValues( 'KHONGCHOXEMHANG', 'CHOXEMHANGKHONGTHU', 'CHOTHUHANG' );

				// Optional fields.
				$options->define( 'note' )->asString( true );
				$options->define( 'content' )->asString(); // the product title.
				$options->define( 'client_order_code' )->asString();
				$options->define( 'pick_station_id' )->asInt();
				$options->define( 'coupon' )->asString();
				$options->define( 'pick_shift' )->asInt();

				$options->define( 'return_phone' )->asString();
				$options->define( 'return_address' )->asString();
				$options->define( 'return_district_id' )->asInt();
				$options->define( 'return_ward_code' )->asInt();
			}
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function cancel_order( $parameters ) {

		if ( ! $parameters instanceof RequestParameters ) {
			$parameters = new RequestParameters( $parameters );
		}

		$data = $parameters->validate(
			function ( OptionsResolver $options ) {
				$options->define( 'order_code' )->asString()->required();
			}
		);

		$this->with_header(
			'ShopId',
			(int) $parameters->get( 'shop_id' ) ?: $this->get_shop_id()
		);

		$response = $this->request(
			'/shiip/public-api/v2/switch-status/cancel',
			json_encode( [ 'order_codes' => [ $data['order_code'] ] ] )
		);

		self::assertResponseHasKey( $response, 'data' );

		return self::newJsonResponseData( $response['data'] ?: [] );
	}

	/**
	 * @param RequestParameters|array|mixed $parameters
	 * @return CollectionResponseData
	 */
	public function get_available_services( $parameters ) {
		if ( ! $parameters instanceof RequestParameters ) {
			$parameters = new RequestParameters( $parameters );
		}

		$data = $parameters->validate(
			function ( OptionsResolver $options ) {
				$options->define( 'from_district' )->asNumeric();
				$options->define( 'to_district' )->asNumeric()->required();
			}
		);

		// Remap address to GHN address code.
		$addressMapper = new AddressMapper( 'ghn' );

		$data['from_district'] = empty( $data['from_district'] )
			? absint( $this->get_shop_info()['district_id'] ?? null )
			: absint( $addressMapper->get_district_code( $data['from_district'] ) );

		$data['to_district'] = (int) $addressMapper->get_district_code( $data['to_district'] );
		//print_r($this->get_shop_info());die();
		InvalidAddressDataException::throwIf( ! $data['from_district'] || ! $data['to_district'] );

		$data['shop_id'] = absint( $parameters->get( 'shop_id' ) ) ?: $this->get_shop_id();

		$response = $this->request(
			'/shiip/public-api/v2/shipping-order/available-services',
			json_encode( $data )
		);

		self::assertResponseHasKey( $response, 'data' );

		return self::newCollectionResponseData(
			array_values(
				array_filter( $response['data'] ?: [], function ( $item ) {
					return ! empty( $item['short_name'] );
				} )
			)
		);
	}

	/**
	 * @return array|null
	 */
	public function get_shop_info() {
		if ( $this->shop_info ) {
			return $this->shop_info;
		}

		$response = $this->request(
			'/shiip/public-api/v2/shop',
			[ 'id' => $this->shop_id ],
			'GET'
		);

		return $this->shop_info = $response['data'] ?? null;
	}

	/**
	 * @return CollectionResponseData
	 */
	public function get_province() {
		$response = $this->request( '/shiip/public-api/master-data/province' );

		return self::newCollectionResponseData( $response['data'] ?: [] );
	}

	/**
	 * @param int $province
	 * @return CollectionResponseData
	 */
	public function get_district( $province ) {
		$response = $this->request(
			'/shiip/public-api/master-data/district',
			json_encode( [ 'province_id' => $province ] )
		);

		return self::newCollectionResponseData( $response['data'] ?: [] );
	}

	/**
	 * @param int $district
	 * @return CollectionResponseData
	 */
	public function get_wards( $district ) {
		$response = $this->request(
			'/shiip/public-api/master-data/ward',
			json_encode( [ 'district_id' => $district ] )
		);

		return self::newCollectionResponseData( $response['data'] ?: [] );
	}

	/**
	 * @param array $data
	 */
	protected function remap_address_code( array &$data ) {
		$addressMapper = new AddressMapper( 'ghn' );

		// to address
		$data['to_district_id'] = (int) $addressMapper->get_district_code( $data['to_district_id'] );
		InvalidAddressDataException::throwIf( ! $data['to_district_id'] );

		$data['to_ward_code'] = $addressMapper->get_ward_code( $data['to_ward_code'] );
		InvalidAddressDataException::throwIf( ! $data['to_ward_code'] );

		// return address.
		if ( $data['return_district_id'] ?? null ) {
			$data['return_district_id'] = (int) $addressMapper->get_district_code( $data['return_district_id'] );
		}

		if ( $data['return_ward_code'] ?? null ) {
			$data['return_ward_code'] = $addressMapper->get_ward_code( $data['return_ward_code'] );
		}
	}
}
