<?php

use Phalcon\Mvc\Controller;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class SignupController extends Controller
{

    public function IndexAction()
    {
    }

    public function registerAction()
    {
        $user = new Signup();
        $escaper = new \App\Components\MyEscaper();
        // $this->escaper->sanitize();
        $data = array(
            "username" => $escaper->sanitize($this->request->getPost('username')),
            "email" => $escaper->sanitize($this->request->getPost('email')),
            "password" => $escaper->sanitize($this->request->getPost('password'))
        );
        $user->assign(
            $data,
            [
                'username',
                'email',
                'password'
            ]
        );

        $success = $user->save();

        $this->view->success = $success;

        if ($success) {
            $this->view->message = "Register succesfully";
        } else {
            $this->view->message = "Not Register succesfully due to following reason: <br>" . implode("<br>", $user->getMessages());
            $message = implode(" & ", $user->getMessages());
            $adapter = new Stream('../app/logs/login.log');
            $logger = new Logger(
                'messages',
                [
                    'main' => $adapter,
                ]
            );
            $logger->error($message);
        }
    }

    public function userdashboardAction()
    {
        try {
            $user = new ApiController;
            $this->view->user = $user->showprofileAction();
            $url = "https://api.spotify.com/";
            $user = Signup::findFirst('userid');
            $accesstoken = $user->accesstoken;

            $client = new Client([
                'base_uri' => $url
            ]);
            $result = $client->request('GET', "/v1/recommendations?seed_artists=4NHQUGzhtTLFvgF5SZesLK&seed_tracks=0c6xIDDpzE81m2q797ordA&seed_genres=sufi", [
                'headers' => [
                    'Authorization' => "Bearer " . $accesstoken
                ]
            ]);

            $this->view->data = json_decode($result->getBody(), true);
        } catch (ClientException $e) {

            $this->eventsManager->fire('spotify:spotifytoken', $this);
        }
    }
}
