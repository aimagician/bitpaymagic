<?php namespace Aimagician\Bitpaymagic\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File as File;


class BitpayMagicSetupCommand extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'bitpaymagic:setup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Setup everything';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {

		$env                = env( "BITPAY_ENV" );
		$pairing_code       = env( "BITPAY_PAIRING_CODE" );
		$pairing_code_label = env( "BITPAY_PAIRING_CODE_LABEL" );
		$storage_pass       = env( "BITPAY_STORAGE_PASS" );

		$storage_path = storage_path( 'keys' );
		File::isDirectory( $storage_path ) or File::makeDirectory( $storage_path, 0777, true, true );

		##########
		## STEP 1
		##########

		/**
		 * Start by creating a PrivateKey object
		 */
		$privateKey = new \Bitpay\PrivateKey( $storage_path . '/bitpay.pri' );
		// Generate a random number
		$privateKey->generate();
		// You can generate a private key with only one line of code like so
		$privateKey = \Bitpay\PrivateKey::create( $storage_path . '/bitpay.pri' )->generate();
		// NOTE: This has overridden the previous $privateKey variable, although its
		//       not an issue in this case since we have not used this key for
		//       anything yet.
		/**
		 * Once we have a private key, a public key is created from it.
		 */
		$publicKey = new \Bitpay\PublicKey( $storage_path . '/bitpay.pub' );
		// Inject the private key into the public key
		$publicKey->setPrivateKey( $privateKey );
		// Generate the public key
		$publicKey->generate();
		// NOTE: You can again do all of this with one line of code like so:
		//       `$publicKey = \Bitpay\PublicKey::create('/tmp/bitpay.pub')->setPrivateKey($privateKey)->generate();`
		/**
		 * Now that you have a private and public key generated, you will need to store
		 * them somewhere. This optioin is up to you and how you store them is up to
		 * you. Please be aware that you MUST store the private key with some type
		 * of security. If the private key is comprimised you will need to repeat this
		 * process.
		 */
		/**
		 * It's recommended that you use the EncryptedFilesystemStorage engine to persist your
		 * keys. You can, of course, create your own as long as it implements the StorageInterface
		 */
		$storageEngine = new \Bitpay\Storage\EncryptedFilesystemStorage( $storage_pass );
		$storageEngine->persist( $privateKey );
		$storageEngine->persist( $publicKey );
		/**
		 * This is all for the first tutorial, you can run this script from the command
		 * line `php examples/tutorial/001.php` This will generate and create two files
		 * located at `/tmp/bitpay.pri` and `/tmp/bitpay.pub`
		 */

		########
		# STEP 2
		########

		/**
		 * To load up keys that you have previously saved, you need to use the same
		 * storage engine. You also need to tell it the location of the key you want
		 * to load.
		 */

//		$storageEngine = new \Bitpay\Storage\EncryptedFilesystemStorage( $storage_pass );
//		$privateKey    = $storageEngine->load( '/tmp/bitpay.pri' );
//		$publicKey     = $storageEngine->load( '/tmp/bitpay.pub' );

		/**
		 * Create the client, there's a lot to it and there are some easier ways, I am
		 * showing the long form here to show how various things are injected into the
		 * client.
		 */
		$client = new \Bitpay\Client\Client();
		/**
		 * The network is either livenet or testnet. You can also create your
		 * own as long as it implements the NetworkInterface. In this example
		 * we will use testnet
		 */
		if ( $env == "livenet" ) {
			$network = new \Bitpay\Network\Livenet();
		} else {
			$network = new \Bitpay\Network\Testnet();
		}

		/**
		 * The adapter is what will make the calls to BitPay and return the response
		 * from BitPay. This can be updated or changed as long as it implements the
		 * AdapterInterface
		 */
		$adapter = new \Bitpay\Client\Adapter\CurlAdapter();
		/**
		 * Now all the objects are created and we can inject them into the client
		 */
		$client->setPrivateKey( $privateKey );
		$client->setPublicKey( $publicKey );
		$client->setNetwork( $network );
		$client->setAdapter( $adapter );
		/**
		 * Visit https://test.bitpay.com/api-tokens and create a new pairing code. Pairing
		 * codes can only be used once and the generated code is valid for only 24 hours.
		 */
		$pairingCode = $pairing_code;
		/**
		 * Currently this part is required, however future versions of the PHP SDK will
		 * be refactor and this part may become obsolete.
		 */
		$sin = \Bitpay\SinKey::create()->setPublicKey( $publicKey )->generate();
		/**** end ****/
		try {
			$token = $client->createToken(
				array(
					'pairingCode' => $pairingCode,
					'label'       => $pairing_code_label,
					'id'          => (string) $sin,
				)
			);
		} catch ( Exception $e ) {
			/**
			 * The code will throw an exception if anything goes wrong, if you did not
			 * change the $pairingCode value or if you are trying to use a pairing
			 * code that has already been used, you will get an exception. It was
			 * decided that it makes more sense to allow your application to handle
			 * this exception since each app is different and has different requirements.
			 */
			$this->error( "Exception occured: " . $e->getMessage() );
			$this->error( "Pairing failed. Please check whether you're trying to pair a production pairing code on test." );

			$request  = $client->getRequest();
			$response = $client->getResponse();
			/**
			 * You can use the entire request/response to help figure out what went
			 * wrong, but for right now, we will just var_dump them.
			 */
			$this->error( (string) $request );
			$this->error( (string) $response );
			/**
			 * NOTE: The `(string)` is include so that the objects are converted to a
			 *       user friendly string.
			 */
			exit( 1 ); // We do not want to continue if something went wrong
		}
		/**
		 * You will need to persist the token somewhere, by the time you get to this
		 * point your application has implemented an ORM such as Doctrine or you have
		 * your own way to persist data. Such as using a framework or some other code
		 * base such as Drupal.
		 */
		$persistThisValue = $token->getToken();
		$this->info( 'Your Token: ' . $persistThisValue );
	}

}