<?php

/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    2.0.13
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2016, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Xfusionsolution\Controledeacesso\Throttling;

use Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface;

interface ThrottleRepositoryInterface
{
    /**
     * Returns the global throttling delay, in seconds.
     *
     * @return int
     */
    public function globalDelay();

    /**
     * Returns the IP address throttling delay, in seconds.
     *
     * @param  string  $ipAddress
     * @return int
     */
    public function ipDelay($ipAddress);

    /**
     * Returns the throttling delay for the given usuario, in seconds.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return int
     */
    public function usuarioDelay(UsuarioInterface $usuario);

    /**
     * Logs a new throttling entry.
     *
     * @param  string  $ipAddress
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return void
     */
    public function log($ipAddress = null, UsuarioInterface $usuario = null);
}
