<?php

class PaymentLibrary extends Eloquent
{
	protected $table = 'payment_libraries';
	public $timestamps = true;

	public function gateways()
	{
		return $this->hasMany('Gateway', 'payment_library_id');
	}
}
