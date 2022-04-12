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
        $this->session->userdb=Signup::find();
        $userdb=$this->session->userdb;
        
        // print_r($userdb[0]->password);
        // die;
        $this->session->userdetails = $this->request->getPost();
        $userdetails=$this->session->userdetails;
        if ($userdetails['email']==$userdb[0]->email && $userdetails['password']==$userdb[0]->password &&  $userdetails['remember']!=null) {


            
            $message = "fill details";
            $adapter = new Stream('../app/logs/signup.log');
            $logger = new Logger(
                'messages',
                [
                    'main' => $adapter,
                ]
            );
            $logger->error($message);
            if (isset($_POST['remember'])) {
                $this->cookies->set(
                    'remember-me',
                    json_encode(
                        [
                            'email' => $userdetails['email'],
                            'password' => $userdetails['password']
                        ]
                    ),
                    time() + 3600
                );
                $this->cookies;
                $this->response->redirect('/api/');
            } elseif($this->session->has('cookies'))
            {
                $this->response->redirect('/api/search');
            }

        }
    }
}
