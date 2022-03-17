<?php

namespace Phobrv\Brvagency\Facades;

use Illuminate\Support\Facades\Facade;

class Brvagency extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'brvagency';
    }
}
