<?php

namespace WcPagaleve\API;

use WcPagaleve\API\Routes\CheckoutFields;

class Routes
{
	public function register()
	{
		new CheckoutFields();
	}
}
