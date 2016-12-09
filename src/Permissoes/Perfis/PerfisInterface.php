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
 * @package    Contperfil de Acesso
 * @version    0.0.1
 * @author     Robson Sampaio
 * @license    BSD License (3-clause)
 * @copyright  (c) 2016, Robson Sampaio
 * @link       http://xfusionsolution.com.br
 */

namespace Xfusionsolution\Controledeacesso\Permissoes\Perfis;

interface PerfisInterface
{
    /**
     * Returns all the associated perfils.
     *
     * @return \IteratorAggregate
     */
    public function getPerfis();

    /**
     * Checks if the user is in the given perfil.
     *
     * @param  mixed  $perfil
     * @return bool
     */
    public function inPerfil($perfil);
}
