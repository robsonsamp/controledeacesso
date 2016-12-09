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

namespace Xfusionsolution\Controledeacesso\Reminders;

use Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface;

interface ReminderRepositoryInterface
{
    /**
     * Create a new reminder record and code.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return string
     */
    public function create(UsuarioInterface $usuario);

    /**
     * Check if a valid reminder exists.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @param  string  $code
     * @return bool
     */
    public function exists(UsuarioInterface $usuario, $code = null);

    /**
     * Complete reminder for the given usuario.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @param  string  $code
     * @param  string  $password
     * @return bool
     */
    public function complete(UsuarioInterface $usuario, $code, $password);

    /**
     * Remove expired reminder codes.
     *
     * @return int
     */
    public function removeExpired();
}
