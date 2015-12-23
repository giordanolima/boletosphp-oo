<?php

namespace GiordanoLima\BoletosPHP;

class BoletosException extends \Exception
{
    public static function requiredField($message)
    {
        return new self('[Missing field] '.$message);
    }

    public static function requiredSetData($message)
    {
        return new self('[Missing set data] '.$message);
    }
}
