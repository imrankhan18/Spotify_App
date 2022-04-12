<?php

use Phalcon\Mvc\Controller;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;
class SignupController extends Controller{

    public function IndexAction(){

    }

    public function registerAction(){
        $user = new Users();
        $escaper = new \App\Components\MyEscaper();
        // $this->escaper->sanitize();
        $data=array(
        "name"=>$escaper->sanitize($this->request->getPost('name')),
        "email"=>$escaper->sanitize($this->request->getPost('email')),
        "description"=>$escaper->sanitize($this->request->getPost('description'))
    );
        $user->assign(
            $data,
            [
                'name',
                'email',
                'description'
            ]
        );

        $success = $user->save();

        $this->view->success = $success;

        if($success){
            $this->view->message = "Register succesfully";
        }else{
            $this->view->message = "Not Register succesfully due to following reason: <br>".implode("<br>", $user->getMessages());
            $message = implode(" & ", $user->getMessages());
            $adapter = new Stream('../app/logs/login.log');
            $logger = new Logger(
                'messages',
                [
                    'main'=>$adapter,
                ]
                );
                $logger->error($message);
        }
    }
}