<?php

/**
 * Part of the Control Access package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Contfuncionalidade de Acesso
 * @version    0.0.1
 * @author     Robson Sampaio
 * @license    BSD License (3-clause)
 * @copyright  (c) 2016, Robson Sampaio
 * @link       http://xfusionsolution.com.br
 */

namespace Xfusionsolution\Contfuncionalidadedeacesso\Funcionalidades;

interface FuncionalidadesInterface
{
    /**
     * Returns all the associated funcionalidades.
     *
     * @return \IteratorAggregate
     */
    public function getFuncionalidades();

    /**
     * Checks if the user is in the given funcionalidade.
     *
     * @param  mixed  $funcionalidade
     * @return bool
     */
    public function inFuncionalidade($funcionalidade);
}
