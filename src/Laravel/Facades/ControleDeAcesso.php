<?php

namespace Xfusionsolution\Controledeacesso\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class ControleDeAcesso extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'controledeacesso';
    }
}
