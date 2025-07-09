<?php

// the class used to communicate with MTManager API

namespace App;

class MTManagerAPI{
    // initialise a connection to the MTManager API
    public function __construct($host, $port, $agent, $is_write_log = true, $file_path = '/tmp/', $file_prefix = ''){
        $this->host = $host;
        $this->port = $port;
        $this->agent = $agent;
        $this->is_write_log = $is_write_log;
        $this->file_path = $file_path;
        $this->file_prefix = $file_prefix;
        $this->status_write = 1;
        $this->connect();
    }
}