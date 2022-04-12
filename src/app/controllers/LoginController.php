<?php

use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;
class LoginController extends Controller
{
    public function indexAction()
    {
        
    }
    public function registerAction()
    {
        // print_r($this->request->getPost());
        // die();
        $login = new Login(); 
            $login->assign(
                $this->request->getPost(),
                 [
                     'email',
                     'password'
                 ]
            );

$login-> save();
$message = "fill details";
$adapter = new Stream('../app/logs/signup.log');
$logger = new Logger(
    'messages',
    [
        'main'=>$adapter,
    ]
    );
    $logger->error($message);
      $userdetails = $this->request->getPost();
        $this->session->details=$userdetails;

        if(isset($_POST['remember']))
        {
            $this->cookies->set(
                'remember-me',
                json_encode(
                    ['email'=>$userdetails['email'],
                    'password'=>$userdetails['password']
                    ]),
                time() +3600
            );
            $this->cookies;
}
}
}