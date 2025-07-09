<?php

namespace App\Http\Controllers;

use App\MTWebAPI;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


    public function mt5connection(){

        // dd(Auth::guard('admin')->check());
        // $server = Company::where('id',2)->first();
        $agent = env('AGENT');
        $logpath = env('PATH_TO_LOGS');

        $server = env('MT5_LIVE_SERVER');
        $port = env('MT5_LIVE_PORT');
        $mt_login = env('MT5_LIVE_LOGIN');
        $mt_login_pass = env('MT5_LIVE_LOGIN_PASSWORD');

        $this->api = new MTWebAPI($agent, $logpath, false);

        //$this->cn = $this->api->Connect2($server->ip, $server->port, 30000, $server->login, $server->password);
        $this->cn = $this->api->Connect2($server, $port, 30000,  $mt_login, $mt_login_pass);

    }
}
