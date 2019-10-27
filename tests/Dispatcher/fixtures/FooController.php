<?php

namespace Tests\Dispatcher\Fixtures;

use Framework\Controller;
use Framework\Request;

class FooController extends Controller {
    public function index(Request $request) {
        return response($request->all());
    }

    public function store(Request $request, $var1, $var2, $var3) {
        return response([$var1, $var2, $var3]);
    }
}
