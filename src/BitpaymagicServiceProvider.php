<?php namespace Aimagician\Bitpaymagic;

use Aimagician\Bitpaymagic\Console\Commands\BitpayMagicSetupCommand;
use Illuminate\Support\ServiceProvider;

class BitpaymagicServiceProvider extends ServiceProvider {


	protected $commands = [
		BitpayMagicSetupCommand::class
	];

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot() {
		$this->publishes( [
			__DIR__ . '/config/bitpaymagic.php' => config_path( 'bitpaymagic.php' ),
		], 'config' );

//		require __DIR__ . '/Http/routes.php';
	}

	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register() {
		$this->mergeConfigFrom( __DIR__ . '/config/bitpaymagic.php', 'bitpaymagic' );

		if ( $this->app->runningInConsole() ) {
			$this->commands( $this->commands );
		}
	}

	public function provides() {
	}
}
