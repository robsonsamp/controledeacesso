<?php

namespace Xfusionsolution\Controledeacesso\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class GrupoPerfil extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'controledeacesso.permissoes.grupos';
    }
}
