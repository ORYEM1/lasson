<?php

namespace App\Controllers;

class Store extends RestrictedBaseController
{
    public function index()
    {
        $vars['title'] = 'Home';
        return view('page',$vars);
    }
}
