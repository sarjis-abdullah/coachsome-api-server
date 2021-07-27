<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Entities\SportCategory;
use App\Http\Controllers\Controller;

class AppBarController extends Controller {
	public function getInitialData() {
		$response = [];
		$response['categories'] = SportCategory::get(['id', 'name', 't_key'])->toArray();
		return $response;
	}
}
