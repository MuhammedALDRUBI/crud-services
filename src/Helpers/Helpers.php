<?php

namespace CRUDServices\Helpers;

use CRUDServices\ConfigManagers\ConfigManager;
use Exception;
use Illuminate\Support\MessageBag;

class Helpers
{
    /**
     * @return string
     * @throws Exception
     */
    static public function getExceptionClass() : string
    {
        $customExceptionClass = ConfigManager::Singleton()->getValue("custom_exception_class");
        return is_subclass_of($customExceptionClass , Exception::class)
               ? $customExceptionClass
               : Exception::class;
    }

}