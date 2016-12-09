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

namespace Xfusionsolution\Controledeacesso\Activations;

use Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface;

interface ActivationRepositoryInterface
{
    /**
     * Create a new activation record and code.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return \Xfusionsolution\Controledeacesso\Activations\ActivationInterface
     */
    public function create(UsuarioInterface $usuario);

    /**
     * Checks if a valid activation for the given usuario exists.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @param  string  $code
     * @return \Xfusionsolution\Controledeacesso\Activations\ActivationInterface|bool
     */
    public function exists(UsuarioInterface $usuario, $code = null);

    /**
     * Completes the activation for the given usuario.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @param  string  $code
     * @return bool
     */
    public function complete(UsuarioInterface $usuario, $code);

    /**
     * Checks if a valid activation has been completed.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return \Xfusionsolution\Controledeacesso\Activations\ActivationInterface|bool
     */
    public function completed(UsuarioInterface $usuario);

    /**
     * Remove an existing activation (deactivate).
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return bool|null
     */
    public function remove(UsuarioInterface $usuario);

    /**
     * Remove expired activation codes.
     *
     * @return int
     */
    public function removeExpired();
}
