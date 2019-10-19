<?php

namespace Tests\Dispatcher\Fixtures;

use Core\Controller;
use Core\Request;

class FooController extends Controller {
    public function index(Request $request) {
        return response($request->all());
    }
}
