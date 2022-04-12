<?php

namespace App\Components;
use phalcon\Escaper;
class MyEscaper
{
    public function getCurrentDate()
    {
        return date('y-m-d');
    }
    public function sanitize($input)
    {
        $escaper=new Escaper();
        $data=$input;
        foreach ($data as $k=> $v) {
            $input[$k]=$escaper->escapeHtml($v);
        }
        return $input;

    }
}
