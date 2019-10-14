<?php

namespace Tests\Dispatcher\Fixtures;

use Core\Controller;

class FooController extends Controller {
    public function index($request) {
        return $request;
    }
}
