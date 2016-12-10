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

namespace Xfusionsolution\Controledeacesso\Checkpoints;

interface CheckpointInterface
{
    /**
     * Checkpoint after a usuario is logged in. Return false to deny persistence.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return bool
     */
    public function login(UsuarioInterface $usuario);

    /**
     * Checkpoint for when a usuario is currently stored in the session.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return bool
     */
    public function check(UsuarioInterface $usuario);

    /**
     * Checkpoint for when a failed login attempt is logged. Usuario is not always
     * passed and the result of the method will not affect anything, as the
     * login failed.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return void
     */
    public function fail(UsuarioInterface $usuario = null);
}
