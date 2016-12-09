<?php

namespace Xfusionsolution\Controledeacesso\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Perfil extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'controledeacesso.permissoes.perfis';
    }
}
