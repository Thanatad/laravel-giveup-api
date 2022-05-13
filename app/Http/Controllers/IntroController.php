<?php

namespace App\Http\Controllers;
use Jenssegers\Agent\Agent;
class IntroController extends Controller
{

    public function index()
    {
        $agent = new Agent();

        $data = [
            'platform' => $agent->platform(),
        ];

        return view('welcome',['data' => $data]);

    }

}
