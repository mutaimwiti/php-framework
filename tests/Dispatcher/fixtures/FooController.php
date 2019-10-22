<?php

namespace Tests\Dispatcher\Fixtures;

use Acme\Controller;
use Acme\Request;

class FooController extends Controller {
    public function index(Request $request) {
        return response($request->all());
    }
}
