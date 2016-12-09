<?php

namespace Xfusionsolution\Controledeacesso\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Funcionalidade extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'controledeacesso.funcionalidades';
    }
}
