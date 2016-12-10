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

use Xfusionsolution\Controledeacesso\Activations\ActivationRepositoryInterface;
use Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface;

class ActivationCheckpoint implements CheckpointInterface
{
    use AuthenticatedCheckpoint;

    /**
     * The activation repository.
     *
     * @var \Xfusionsolution\Controledeacesso\Activations\ActivationRepositoryInterface
     */
    protected $activations;

    /**
     * Create a new activation checkpoint.
     *
     * @param  \Xfusionsolution\Controledeacesso\Activations\ActivationRepositoryInterface  $activations
     * @return void
     */
    public function __construct(ActivationRepositoryInterface $activations)
    {
        $this->activations = $activations;
    }

    /**
     * {@inheritDoc}
     */
    public function login(UsuarioInterface $usuario)
    {
        return $this->checkActivation($usuario);
    }

    /**
     * {@inheritDoc}
     */
    public function check(UsuarioInterface $usuario)
    {
        return $this->checkActivation($usuario);
    }

    /**
     * Checks the activation status of the given usuario.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return bool
     * @throws \Xfusionsolution\Controledeacesso\Checkpoints\NotActivatedException
     */
    protected function checkActivation(UsuarioInterface $usuario)
    {
        $completed = $this->activations->completed($usuario);

        if (! $completed) {
            $exception = new NotActivatedException('Your account has not been activated yet.');

            $exception->setUsuario($usuario);

            throw $exception;
        }
    }
}
