<?php

class Auth
{
    public function __invoke($route){
        session_start();
        if(!array_key_exists("CurrentUser", $_SESSION)){
            $app = \Slim\Slim::getInstance();
            $app->halt(500, "error ... login required");
        }
    }
}
