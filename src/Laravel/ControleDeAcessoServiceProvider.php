<?php

/**
 * Part of the controledeacesso package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    controledeacesso
 * @version    2.0.13
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2016, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\controledeacesso\Laravel;

use Cartalyst\controledeacesso\Activations\IlluminateActivationRepository;
use Cartalyst\controledeacesso\Checkpoints\ActivationCheckpoint;
use Cartalyst\controledeacesso\Checkpoints\ThrottleCheckpoint;
use Cartalyst\controledeacesso\Cookies\IlluminateCookie;
use Cartalyst\controledeacesso\Hashing\NativeHasher;
use Cartalyst\controledeacesso\Persistences\IlluminatePersistenceRepository;
use Cartalyst\controledeacesso\Reminders\IlluminateReminderRepository;
use Cartalyst\controledeacesso\Roles\IlluminateRoleRepository;
use Cartalyst\controledeacesso\controledeacesso;
use Cartalyst\controledeacesso\Sessions\IlluminateSession;
use Cartalyst\controledeacesso\Throttling\IlluminateThrottleRepository;
use Cartalyst\controledeacesso\Users\IlluminateUserRepository;
use Exception;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class ControleDeAcessoServiceProvider extends ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $this->garbageCollect();
    }

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this->prepareResources();
        $this->registerPersistences();
        $this->registerUsers();
        $this->registerRoles();
        $this->registerCheckpoints();
        $this->registerReminders();
        $this->registercontroledeacesso();
        $this->setUserResolver();
    }

    /**
     * Prepare the package resources.
     *
     * @return void
     */
    protected function prepareResources()
    {
        // Publish config
        $config = realpath(__DIR__.'/../config/config.php');

        $this->mergeConfigFrom($config, 'xfusionsolution.controledeacesso');

        $this->publishes([
            $config => config_path('xfusionsolution.controledeacesso.php'),
        ], 'config');

        // Publish migrations
        $migrations = realpath(__DIR__.'/../migrations');

        $this->publishes([
            $migrations => $this->app->databasePath().'/migrations',
        ], 'migrations');
    }

    /**
     * Registers the persistences.
     *
     * @return void
     */
    protected function registerPersistences()
    {
        $this->registerSession();
        $this->registerCookie();

        $this->app->singleton('controledeacesso.persistence', function ($app) {
            $config = $app['config']->get('xfusionsolution.controledeacesso');

            $model  = $config['persistences']['model'];
            $single = $config['persistences']['single'];
            $users  = $config['users']['model'];

            if (class_exists($users) && method_exists($users, 'setPersistencesModel')) {
                forward_static_call_array([$users, 'setPersistencesModel'], [$model]);
            }

            return new IlluminatePersistenceRepository($app['controledeacesso.session'], $app['controledeacesso.cookie'], $model, $single);
        });
    }

    /**
     * Registers the session.
     *
     * @return void
     */
    protected function registerSession()
    {
        $this->app->singleton('controledeacesso.session', function ($app) {
            $key = $app['config']->get('xfusionsolution.controledeacesso.session');

            return new IlluminateSession($app['session.store'], $key);
        });
    }

    /**
     * Registers the cookie.
     *
     * @return void
     */
    protected function registerCookie()
    {
        $this->app->singleton('controledeacesso.cookie', function ($app) {
            $key = $app['config']->get('xfusionsolution.controledeacesso.cookie');

            return new IlluminateCookie($app['request'], $app['cookie'], $key);
        });
    }

    /**
     * Registers the users.
     *
     * @return void
     */
    protected function registerUsers()
    {
        $this->registerHasher();

        $this->app->singleton('controledeacesso.users', function ($app) {
            $config = $app['config']->get('xfusionsolution.controledeacesso');

            $users        = $config['users']['model'];
            $roles        = $config['roles']['model'];
            $persistences = $config['persistences']['model'];
            $permissions  = $config['permissions']['class'];

            if (class_exists($roles) && method_exists($roles, 'setUsersModel')) {
                forward_static_call_array([$roles, 'setUsersModel'], [$users]);
            }

            if (class_exists($persistences) && method_exists($persistences, 'setUsersModel')) {
                forward_static_call_array([$persistences, 'setUsersModel'], [$users]);
            }

            if (class_exists($users) && method_exists($users, 'setPermissionsClass')) {
                forward_static_call_array([$users, 'setPermissionsClass'], [$permissions]);
            }
            
            return new IlluminateUserRepository($app['controledeacesso.hasher'], $app['events'], $users);
        });
    }

    /**
     * Registers the hahser.
     *
     * @return void
     */
    protected function registerHasher()
    {
        $this->app->singleton('controledeacesso.hasher', function () {
            return new NativeHasher;
        });
    }

    /**
     * Registers the roles.
     *
     * @return void
     */
    protected function registerRoles()
    {
        $this->app->singleton('controledeacesso.roles', function ($app) {
            $config = $app['config']->get('xfusionsolution.controledeacesso');

            $model = $config['roles']['model'];
            $users = $config['users']['model'];

            if (class_exists($users) && method_exists($users, 'setRolesModel')) {
                forward_static_call_array([$users, 'setRolesModel'], [$model]);
            }

            return new IlluminateRoleRepository($model);
        });
    }

    /**
     * Registers the checkpoints.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function registerCheckpoints()
    {
        $this->registerActivationCheckpoint();
        $this->registerThrottleCheckpoint();

        $this->app->singleton('controledeacesso.checkpoints', function ($app) {
            $activeCheckpoints = $app['config']->get('xfusionsolution.controledeacesso.checkpoints');

            $checkpoints = [];

            foreach ($activeCheckpoints as $checkpoint) {
                if (! $app->offsetExists("controledeacesso.checkpoint.{$checkpoint}")) {
                    throw new InvalidArgumentException("Invalid checkpoint [$checkpoint] given.");
                }

                $checkpoints[$checkpoint] = $app["controledeacesso.checkpoint.{$checkpoint}"];
            }

            return $checkpoints;
        });
    }

    /**
     * Registers the activation checkpoint.
     *
     * @return void
     */
    protected function registerActivationCheckpoint()
    {
        $this->registerActivations();

        $this->app->singleton('controledeacesso.checkpoint.activation', function ($app) {
            return new ActivationCheckpoint($app['controledeacesso.activations']);
        });
    }

    /**
     * Registers the activations.
     *
     * @return void
     */
    protected function registerActivations()
    {
        $this->app->singleton('controledeacesso.activations', function ($app) {
            $config = $app['config']->get('xfusionsolution.controledeacesso');

            $model   = $config['activations']['model'];
            $expires = $config['activations']['expires'];

            return new IlluminateActivationRepository($model, $expires);
        });
    }

    /**
     * Registers the throttle checkpoint.
     *
     * @return void
     */
    protected function registerThrottleCheckpoint()
    {
        $this->registerThrottling();

        $this->app->singleton('controledeacesso.checkpoint.throttle', function ($app) {
            return new ThrottleCheckpoint(
                $app['controledeacesso.throttling'],
                $app['request']->getClientIp()
            );
        });
    }

    /**
     * Registers the throttle.
     *
     * @return void
     */
    protected function registerThrottling()
    {
        $this->app->singleton('controledeacesso.throttling', function ($app) {
            $model = $app['config']->get('xfusionsolution.controledeacesso.throttling.model');

            foreach (['global', 'ip', 'user'] as $type) {
                ${"{$type}Interval"} = $app['config']->get("xfusionsolution.controledeacesso.throttling.{$type}.interval");
                ${"{$type}Thresholds"} = $app['config']->get("xfusionsolution.controledeacesso.throttling.{$type}.thresholds");
            }

            return new IlluminateThrottleRepository(
                $model,
                $globalInterval,
                $globalThresholds,
                $ipInterval,
                $ipThresholds,
                $userInterval,
                $userThresholds
            );
        });
    }

    /**
     * Registers the reminders.
     *
     * @return void
     */
    protected function registerReminders()
    {
        $this->app->singleton('controledeacesso.reminders', function ($app) {
            $config = $app['config']->get('xfusionsolution.controledeacesso');

            $model   = $config['reminders']['model'];
            $expires = $config['reminders']['expires'];

            return new IlluminateReminderRepository($app['controledeacesso.users'], $model, $expires);
        });
    }

    /**
     * Registers controledeacesso.
     *
     * @return void
     */
    protected function registercontroledeacesso()
    {
        $this->app->singleton('controledeacesso', function ($app) {
            $controledeacesso = new controledeacesso(
                $app['controledeacesso.persistence'],
                $app['controledeacesso.users'],
                $app['controledeacesso.roles'],
                $app['controledeacesso.activations'],
                $app['events']
            );

            if (isset($app['controledeacesso.checkpoints'])) {
                foreach ($app['controledeacesso.checkpoints'] as $key => $checkpoint) {
                    $controledeacesso->addCheckpoint($key, $checkpoint);
                }
            }

            $controledeacesso->setActivationRepository($app['controledeacesso.activations']);
            $controledeacesso->setReminderRepository($app['controledeacesso.reminders']);

            $controledeacesso->setRequestCredentials(function () use ($app) {
                $request = $app['request'];

                $login = $request->getUser();
                $password = $request->getPassword();

                if ($login === null && $password === null) {
                    return;
                }

                return compact('login', 'password');
            });

            $controledeacesso->creatingBasicResponse(function () {
                $headers = ['WWW-Authenticate' => 'Basic'];

                return new Response('Invalid credentials.', 401, $headers);
            });

            return $controledeacesso;
        });

        $this->app->alias('controledeacesso', 'Cartalyst\controledeacesso\controledeacesso');
    }

    /**
     * {@inheritDoc}
     */
    public function provides()
    {
        return [
            'controledeacesso.session',
            'controledeacesso.cookie',
            'controledeacesso.persistence',
            'controledeacesso.hasher',
            'controledeacesso.users',
            'controledeacesso.roles',
            'controledeacesso.activations',
            'controledeacesso.checkpoint.activation',
            'controledeacesso.throttling',
            'controledeacesso.checkpoint.throttle',
            'controledeacesso.checkpoints',
            'controledeacesso.reminders',
            'controledeacesso',
        ];
    }

    /**
     * Garbage collect activations and reminders.
     *
     * @return void
     */
    protected function garbageCollect()
    {
        $config = $this->app['config']->get('xfusionsolution.controledeacesso.activations.lottery');

        $this->sweep($this->app['controledeacesso.activations'], $config);

        $config = $this->app['config']->get('xfusionsolution.controledeacesso.reminders.lottery');

        $this->sweep($this->app['controledeacesso.reminders'], $config);
    }

    /**
     * Sweep expired codes.
     *
     * @param  mixed  $repository
     * @param  array  $lottery
     * @return void
     */
    protected function sweep($repository, $lottery)
    {
        if ($this->configHitsLottery($lottery)) {
            try {
                $repository->removeExpired();
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Determine if the configuration odds hit the lottery.
     *
     * @param  array  $lottery
     * @return bool
     */
    protected function configHitsLottery(array $lottery)
    {
        return mt_rand(1, $lottery[1]) <= $lottery[0];
    }

    /**
     * Sets the user resolver on the request class.
     *
     * @return void
     */
    protected function setUserResolver()
    {
        $this->app->rebinding('request', function ($app, $request) {
            $request->setUserResolver(function () use ($app) {
                return $app['controledeacesso']->getUser();
            });
        });
    }
}
