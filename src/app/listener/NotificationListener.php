<?php

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;
use Phalcon\Di\Injectable;

class NotificationListener extends Injectable
{
    public function spotifytoken()
    {
        // echo "hghus ";
        // die;
        $token = Signup::findFirst('userid');
        $refreshtoken = $token->refreshtoken;
        // print_r($token);
        // die;
        $clientId = '6ffe73b6125d488287b94a895cec5662';
        $clientSecret = 'fb97c1cabef048a5ac8212430bfe733e';
        $url = "https://accounts.spotify.com";
            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . base64_encode($clientId . ":" . $clientSecret)
            ];

            $client = new Client(
                [

                    'base_uri' => $url,
                    'headers' => $headers
                ]
            );
        
            $arg = ['grant_type' => 'refresh_token', 'refresh_token' => $refreshtoken];
            $response = $client->request('POST', "/api/token", ['form_params' => $arg]);
            $response =  $response->getBody();
            $response = json_decode($response, true);
            echo "<pre>";
            // print_r($response);
            
            $act= ['access_token'=>$response['access_token']];
            $this->session->tokens = $act;
            // print_r($this->session->accesstoken);
            // die;
            $token->accesstoken = $this->session->accesstoken;
            $token->update();
            $this->session->token=$token->accesstoken;
            // $this->response->redirect('/api');
        
    }
}
