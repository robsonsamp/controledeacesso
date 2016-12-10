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

use Xfusionsolution\Controledeacesso\Throttling\ThrottleRepositoryInterface;
use Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface;

class ThrottleCheckpoint implements CheckpointInterface
{
    /**
     * The throttle repository.
     *
     * @var \Xfusionsolution\Controledeacesso\Throttling\ThrottleRepositoryInterface
     */
    protected $throttle;

    /**
     * The cached IP address, used for checkpoints checks.
     *
     * @var string
     */
    protected $ipAddress;

    /**
     * Constructor.
     *
     * @param  \Xfusionsolution\Controledeacesso\Throttling\ThrottleRepositoryInterface  $throttle
     * @param  string  $ipAddress
     * @return void
     */
    public function __construct(ThrottleRepositoryInterface $throttle, $ipAddress = null)
    {
        $this->throttle = $throttle;

        if (isset($ipAddress)) {
            $this->ipAddress = $ipAddress;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function login(UsuarioInterface $usuario)
    {
        return $this->checkThrottling('login', $usuario);
    }

    /**
     * {@inheritDoc}
     */
    public function check(UsuarioInterface $usuario)
    {
        return $this->checkThrottling('check', $usuario);
    }

    /**
     * {@inheritDoc}
     */
    public function fail(UsuarioInterface $usuario = null)
    {
        // We'll check throttling firstly from any previous attempts. This
        // will throw the required exceptions if the usuario has already
        // tried to login too many times.
        $this->checkThrottling('login', $usuario);

        // Now we've checked previous attempts, we'll log this latest attempt.
        // It'll be picked up the next time if the usuario tries again.
        $this->throttle->log($this->ipAddress, $usuario);
    }

    /**
     * Checks the throttling status of the given usuario.
     *
     * @param  string  $action
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|null  $usuario
     * @return bool
     */
    protected function checkThrottling($action, UsuarioInterface $usuario = null)
    {
        // If we are just checking an existing logged in person, the global delay
        // shouldn't stop them being logged in at all. Only their IP address and
        // usuario a
        if ($action === 'login') {
            $globalDelay = $this->throttle->globalDelay();

            if ($globalDelay > 0) {
                $this->throwException("Too many unsuccessful attempts have been made globally, logins are locked for another [{$globalDelay}] second(s).", 'global', $globalDelay);
            }
        }

        // Suspicious activity from a single IP address will not only lock
        // logins but also any logged in usuarios from that IP address. This
        // should deter a single hacker who may have guessed a password
        // within the configured throttling limit.
        if (isset($this->ipAddress)) {
            $ipDelay = $this->throttle->ipDelay($this->ipAddress);

            if ($ipDelay > 0) {
                $this->throwException("Suspicious activity has occured on your IP address and you have been denied access for another [{$ipDelay}] second(s).", 'ip', $ipDelay);
            }
        }

        // We will only suspend people logging into a usuario account. This will
        // leave the logged in usuario unaffected. Picture a famous person who's
        // account is being locked as they're logged in, purely because
        // others are trying to hack it.
        if ($action === 'login' && isset($usuario)) {
            $usuarioDelay = $this->throttle->usuarioDelay($usuario);

            if ($usuarioDelay > 0) {
                $this->throwException("Too many unsuccessful login attempts have been made against your account. Please try again after another [{$usuarioDelay}] second(s).", 'usuario', $usuarioDelay);
            }
        }

        return true;
    }

    /**
     * Throws a throttling exception.
     *
     * @param  string  $message
     * @param  string  $type
     * @param  int  $delay
     * @throws \Xfusionsolution\Controledeacesso\Checkpoints\ThrottlingException
     */
    protected function throwException($message, $type, $delay)
    {
        $exception = new ThrottlingException($message);

        $exception->setDelay($delay);

        $exception->setType($type);

        throw $exception;
    }
}
