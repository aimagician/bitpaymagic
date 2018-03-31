<?php namespace Aimagician\Bitpaymagic\Traits;

use Exception;

trait BitpaymagicTrait {
	public function __construct() {
	}

	# will simply create instance, load keys, and prepare all necessary objects
	public function initBitpaymagicClient() {

		$env          = env( "BITPAY_ENV" );
		$storage_pass = env( "BITPAY_STORAGE_PASS" );
		$bitpay_token = env( "BITPAY_GENERATED_TOKEN" );

		$storage_path = storage_path( 'keys' );

		if ( ! file_exists( $storage_path ) ) {
			throw new Exception( "Storage path for bitpay keys does not exits", 500 );
		}

		$storageEngine = new \Bitpay\Storage\EncryptedFilesystemStorage( $storage_pass ); // Password may need to be updated if you changed it
		$privateKey    = $storageEngine->load( $storage_path . '/bitpay.pri' );
		$publicKey     = $storageEngine->load( $storage_path . '/bitpay.pub' );
		$client        = new \Bitpay\Client\Client();
		if ( $env == "livenet" ) {
			$network = new \Bitpay\Network\Livenet();
		} else {
			$network = new \Bitpay\Network\Testnet();
		}
		$adapter = new \Bitpay\Client\Adapter\CurlAdapter();

		$client->setPrivateKey( $privateKey );
		$client->setPublicKey( $publicKey );
		$client->setNetwork( $network );
		$client->setAdapter( $adapter );
		// ---------------------------
		/**
		 * The last object that must be injected is the token object.
		 */
		$token = new \Bitpay\Token();
		$token->setToken( $bitpay_token ); // UPDATE THIS VALUE
		/**
		 * Token object is injected into the client
		 */
		$client->setToken( $token );

		return $client;
	}
}