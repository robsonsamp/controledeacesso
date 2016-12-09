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
 * @package    Controle de Acesso
 * @version    0.0.1
 * @author     Robson Sampaio
 * @license    BSD License (3-clause)
 * @copyright  (c) 2016, Robson Sampaio
 * @link       http://xfusionsolution.com.br
 */

namespace Xfusionsolution\Controledeacesso\Usuarios;

interface UsuarioInterface
{
    /**
     * Returns the usuario primary key.
     *
     * @return int
     */
    public function getUsuarioId();

    /**
     * Returns the usuario login.
     *
     * @return string
     */
    public function getUsuarioLogin();

    /**
     * Returns the usuario login attribute name.
     *
     * @return string
     */
    public function getUsuarioLoginName();

    /**
     * Returns the usuario password.
     *
     * @return string
     */
    public function getUsuarioPassword();
}
