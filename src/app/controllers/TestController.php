<?php

use Phalcon\Mvc\Controller;


class TestController extends Controller
{
    public function loaderTestAction()
    {
        $datehelper = new \App\Components\DateHelper();
        echo $datehelper->getCurrentDate();
    }   

}
