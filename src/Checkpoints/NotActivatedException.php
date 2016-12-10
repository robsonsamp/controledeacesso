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

use Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface;
use RuntimeException;

class NotActivatedException extends RuntimeException
{
    /**
     * The usuario which caused the exception.
     *
     * @var \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface
     */
    protected $usuario;

    /**
     * Returns the usuario.
     *
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Sets the usuario associated with Sentinel (does not log in).
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface
     * @return void
     */
    public function setUsuario(UsuarioInterface $usuario)
    {
        $this->usuario = $usuario;
    }
}
