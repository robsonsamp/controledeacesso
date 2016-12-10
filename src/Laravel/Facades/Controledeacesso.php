<?php

namespace Xfusionsolution\Controledeacesso\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Controledeacesso extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'controledeacesso';
    }
}
