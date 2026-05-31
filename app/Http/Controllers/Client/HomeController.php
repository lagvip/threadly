<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\Home\ClientHomeService;

class HomeController extends Controller
{
    public function __construct(protected ClientHomeService $home)
    {
    }

    public function index()
    {
        return view('client.home', $this->home->indexData());
    }

    public function about()
    {
        return view('client.about');
    }
}
