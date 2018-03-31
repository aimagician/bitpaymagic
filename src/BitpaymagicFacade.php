<?php namespace Aimagician\Bitpaymagic;

use \Illuminate\Support\Facades\Facade;

class BitpaymagicFacade extends Facade {
	protected static function getFacadeAccessor() {
		return 'bitpaymagic';
	}
}