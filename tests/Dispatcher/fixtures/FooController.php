<?php

namespace Tests\Dispatcher\Fixtures;

use Framework\Controller;
use Framework\Request;

class FooController extends Controller {
    public function index(Request $request) {
        return response($request->all());
    }
}
