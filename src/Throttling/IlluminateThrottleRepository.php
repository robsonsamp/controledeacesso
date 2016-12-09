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

namespace Xfusionsolution\Controledeacesso\Throttling;

use Carbon\Carbon;
use Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface;
use Cartalyst\Support\Traits\RepositoryTrait;

class IlluminateThrottleRepository implements ThrottleRepositoryInterface
{
    use RepositoryTrait;

    /**
     * The interval which failed logins are checked, to prevent brute force.
     *
     * @var int
     */
    protected $globalInterval = 900;

    /**
     * The global thresholds configuration array.
     *
     * If an array is set, the key is the number of failed login attemps
     * and the value is the delay in seconds before another login can
     * occur.
     *
     * If an integer is set, it represents the number of attempts
     * before throttling locks out in the current interval.
     *
     * @var int|array
     */
    protected $globalThresholds = [
        10 => 1,
        20 => 2,
        30 => 4,
        40 => 8,
        50 => 16,
        60 => 32,
    ];

    /**
     * Cached global throttles collection within the interval.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $globalThrottles;

    /**
     * The interval at which point one IP address' failed logins are checked.
     *
     * @var int
     */
    protected $ipInterval = 900;

    /**
     * Works identical to global thresholds, except specific to an IP address.
     *
     * @var int|array
     */
    protected $ipThresholds = 5;

    /**
     * The cached IP address throttle collections within the interval.
     *
     * @var array
     */
    protected $ipThrottles = [];

    /**
     * The interval at which point failed logins for one usuario are checked.
     *
     * @var int
     */
    protected $usuarioInterval = 900;

    /**
     * Works identical to global and IP address thresholds, regarding a usuario.
     *
     * @var int|array
     */
    protected $usuarioThresholds = 5;

    /**
     * The cached usuario throttle collections within the interval.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $usuarioThrottles = [];

    /**
     * Create a new Illuminate throttle repository.
     *
     * @param  string  $model
     * @param  int  $globalInterval
     * @param  int|array  $globalThresholds
     * @param  int  $ipInterval
     * @param  int|array  $ipThresholds
     * @param  int  $usuarioInterval
     * @param  int|array  $usuarioThresholds
     * @return void
     */
    public function __construct(
        $model = 'Xfusionsolution\Controledeacesso\Throttling\ModelThrottle',
        $globalInterval = null,
        $globalThresholds = null,
        $ipInterval = null,
        $ipThresholds = null,
        $usuarioInterval = null,
        $usuarioThresholds = null
    ) {
        $this->model = $model;

        if (isset($globalInterval)) {
            $this->setGlobalInterval($globalInterval);
        }

        if (isset($globalThresholds)) {
            $this->setGlobalThresholds($globalThresholds);
        }

        if (isset($ipInterval)) {
            $this->setIpInterval($ipInterval);
        }

        if (isset($ipThresholds)) {
            $this->setIpThresholds($ipThresholds);
        }

        if (isset($usuarioInterval)) {
            $this->setUsuarioInterval($usuarioInterval);
        }

        if (isset($usuarioThresholds)) {
            $this->setUsuarioThresholds($usuarioThresholds);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function globalDelay()
    {
        return $this->delay('global');
    }

    /**
     * {@inheritDoc}
     */
    public function ipDelay($ipAddress)
    {
        return $this->delay('ip', $ipAddress);
    }

    /**
     * {@inheritDoc}
     */
    public function usuarioDelay(UsuarioInterface $usuario)
    {
        return $this->delay('usuario', $usuario);
    }

    /**
     * {@inheritDoc}
     */
    public function log($ipAddress = null, UsuarioInterface $usuario = null)
    {
        $global = $this->createModel();
        $global->fill([
            'type' => 'global',
        ]);
        $global->save();

        if ($ipAddress !== null) {
            $ipAddressThrottle = $this->createModel();
            $ipAddressThrottle->fill([
                'type' => 'ip',
                'ip'   => $ipAddress,
            ]);
            $ipAddressThrottle->save();
        }

        if ($usuario !== null) {
            $usuarioThrottle = $this->createModel();
            $usuarioThrottle->fill([
                'type' => 'usuario',
            ]);
            $usuarioThrottle->usuario_id = $usuario->getUsuarioId();
            $usuarioThrottle->save();
        }
    }

    /**
     * Returns the global interval.
     *
     * @return int
     */
    public function getGlobalInterval()
    {
        return $this->globalInterval;
    }

    /**
     * Sets the global interval.
     *
     * @param  int  $globalInterval
     * @return void
     */
    public function setGlobalInterval($globalInterval)
    {
        $this->globalInterval = (int) $globalInterval;
    }

    /**
     * Returns the global thresholds.
     *
     * @return int|array
     */
    public function getGlobalThresholds()
    {
        return $this->globalThresholds;
    }

    /**
     * Sets the global thresholds.
     *
     * @param  int|array  $globalThresholds
     * @return void
     */
    public function setGlobalThresholds($globalThresholds)
    {
        $this->globalThresholds = is_array($globalThresholds) ? $globalThresholds : (int) $globalThresholds;
    }

    /**
     * Returns the IP address interval.
     *
     * @return int
     */
    public function getIpInterval()
    {
        return $this->ipInterval;
    }

    /**
     * Sets the IP address interval.
     *
     * @param  int  $ipInterval
     * @return void
     */
    public function setIpInterval($ipInterval)
    {
        $this->ipInterval = (int) $ipInterval;
    }

    /**
     * Returns the IP address thresholds.
     *
     * @return int|array
     */
    public function getIpThresholds()
    {
        return $this->ipThresholds;
    }

    /**
     * Sets the IP address thresholds.
     *
     * @param  int|array  $ipThresholds
     * @return void
     */
    public function setIpThresholds($ipThresholds)
    {
        $this->ipThresholds = is_array($ipThresholds) ? $ipThresholds : (int) $ipThresholds;
    }

    /**
     * Returns the usuario interval.
     *
     * @return int
     */
    public function getUsuarioInterval()
    {
        return $this->usuarioInterval;
    }

    /**
     * Sets the usuario interval.
     *
     * @param  int  $usuarioInterval
     * @return void
     */
    public function setUsuarioInterval($usuarioInterval)
    {
        $this->usuarioInterval = (int) $usuarioInterval;
    }

    /**
     * Returns the usuario thresholds.
     *
     * @return int|array
     */
    public function getUsuarioThresholds()
    {
        return $this->usuarioThresholds;
    }

    /**
     * Sets the usuario thresholds.
     *
     * @param  int|array  $usuarioThresholds
     * @return void
     */
    public function setUsuarioThresholds($usuarioThresholds)
    {
        $this->usuarioThresholds = is_array($usuarioThresholds) ? $usuarioThresholds : (int) $usuarioThresholds;
    }

    /**
     * Returns a delay for the given type.
     *
     * @param  string  $type
     * @param  mixed  $argument
     * @return int
     */
    protected function delay($type, $argument = null)
    {
        // Based on the given type, we will generate method and property names
        $method = 'get'.studly_case($type).'Throttles';

        $thresholds = $type.'Thresholds';

        $throttles = $this->{$method}($argument);

        if (! $throttles->count()) {
            return 0;
        }

        if (is_array($this->$thresholds)) {
            // Great, now we compare our delay against the most recent attempt
            $last = $throttles->last();

            foreach (array_reverse($this->$thresholds, true) as $attempts => $delay) {
                if ($throttles->count() <= $attempts) {
                    continue;
                }

                if ($last->created_at->diffInSeconds() < $delay) {
                    return $this->secondsToFree($last, $delay);
                }
            }
        } elseif ($throttles->count() > $this->$thresholds) {
            $interval = $type.'Interval';

            $first = $throttles->first();

            return $this->secondsToFree($first, $this->{$interval});
        }

        return 0;
    }

    /**
     * Returns the global throttles collection.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getGlobalThrottles()
    {
        if ($this->globalThrottles === null) {
            $this->globalThrottles = $this->loadGlobalThrottles();
        }

        return $this->globalThrottles;
    }

    /**
     * Loads and returns the global throttles collection.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function loadGlobalThrottles()
    {
        $interval = Carbon::now()
            ->subSeconds($this->globalInterval);

        return $this->createModel()
            ->newQuery()
            ->where('type', 'global')
            ->where('created_at', '>', $interval)
            ->get();
    }

    /**
     * Returns the IP address throttles collection.
     *
     * @param  string  $ipAddress
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getIpThrottles($ipAddress)
    {
        if (! array_key_exists($ipAddress, $this->ipThrottles)) {
            $this->ipThrottles[$ipAddress] = $this->loadIpThrottles($ipAddress);
        }

        return $this->ipThrottles[$ipAddress];
    }

    /**
     * Loads and returns the IP address throttles collection.
     *
     * @param  string  $ipAddress
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function loadIpThrottles($ipAddress)
    {
        $interval = Carbon::now()
            ->subSeconds($this->ipInterval);

        return $this
            ->createModel()
            ->newQuery()
            ->where('type', 'ip')
            ->where('ip', $ipAddress)
            ->where('created_at', '>', $interval)
            ->get();
    }

    /**
     * Returns the usuario throttles collection.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getUsuarioThrottles(UsuarioInterface $usuario)
    {
        $key = $usuario->getUsuarioId();

        if (! array_key_exists($key, $this->usuarioThrottles)) {
            $this->usuarioThrottles[$key] = $this->loadUsuarioThrottles($usuario);
        }

        return $this->usuarioThrottles[$key];
    }

    /**
     * Loads and returns the usuario throttles collection.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function loadUsuarioThrottles(UsuarioInterface $usuario)
    {
        $interval = Carbon::now()
            ->subSeconds($this->usuarioInterval);

        return $this
            ->createModel()
            ->newQuery()
            ->where('type', 'usuario')
            ->where('usuario_id', $usuario->getUsuarioId())
            ->where('created_at', '>', $interval)
            ->get();
    }

    /**
     * Returns the seconds to free based on the given throttle and
     * the presented delay in seconds, by comparing it to now.
     *
     * @param  \Xfusionsolution\Controledeacesso\Throttling\EloquentThrottle  $throttle
     * @param  int  $interval
     * @return int
     */
    protected function secondsToFree(EloquentThrottle $throttle, $interval)
    {
        return $throttle->created_at->addSeconds($interval)->diffInSeconds();
    }
}
