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

namespace Xfusionsolution\Controledeacesso\Laravel;

use Xfusionsolution\Controledeacesso\Activations\IlluminateActivationRepository;
use Xfusionsolution\Controledeacesso\Checkpoints\ActivationCheckpoint;
use Xfusionsolution\Controledeacesso\Checkpoints\ThrottleCheckpoint;
use Xfusionsolution\Controledeacesso\Cookies\IlluminateCookie;
use Xfusionsolution\Controledeacesso\Utils\NativeHasher;
use Xfusionsolution\Controledeacesso\Persistencias\IlluminatePersistenciaRepository;
use Xfusionsolution\Controledeacesso\Reminders\IlluminateReminderRepository;
use Xfusionsolution\Controledeacesso\Permissoes\Perfis\IlluminatePerfilRepository;
use Xfusionsolution\Controledeacesso\Controledeacesso;
use Xfusionsolution\Controledeacesso\Sessions\IlluminateSession;
use Xfusionsolution\Controledeacesso\Throttling\IlluminateThrottleRepository;
use Xfusionsolution\Controledeacesso\Usuarios\IlluminateUsuarioRepository;
use Exception;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class ControledeacessoServiceProvider extends ServiceProvider
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
        $this->registerPersistencias();
        $this->registerUsuarios();
        $this->registerPerfis();
        $this->registerFuncionalidades();
        $this->registerCheckpoints();
        $this->registerReminders();
        $this->registerControledeacesso();
        $this->setUsuarioResolver();
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
     * Registers the persistencias.
     *
     * @return void
     */
    protected function registerPersistencias()
    {
        $this->registerSession();
        $this->registerCookie();

        $this->app->singleton('controledeacesso.persistencia', function ($app) {
            $config = $app['config']->get('xfusionsolution.controledeacesso');

            $model  = $config['persistencias']['model'];
            $single = $config['persistencias']['single'];
            $usuarios  = $config['usuarios']['model'];

            if (class_exists($usuarios) && method_exists($usuarios, 'setPersistenciasModel')) {
                forward_static_call_array([$usuarios, 'setPersistenciasModel'], [$model]);
            }

            return new IlluminatePersistenciaRepository($app['controledeacesso.session'], $app['controledeacesso.cookie'], $model, $single);
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
     * Registers the usuarios.
     *
     * @return void
     */
    protected function registerUsuarios()
    {
        $this->registerHasher();

        $this->app->singleton('controledeacesso.usuarios', function ($app) {
            $config = $app['config']->get('xfusionsolution.controledeacesso');

            $usuarios        = $config['usuarios']['model'];
            $perfis        = $config['perfis']['model'];
            $funcionalidades = $config['funcionalidades']['model'];
            $persistencias = $config['persistencias']['model'];
            $permissions  = $config['permissions']['class'];

            if (class_exists($perfis) && method_exists($perfis, 'setUsuariosModel')) {
                forward_static_call_array([$perfis, 'setUsuariosModel'], [$usuarios]);
            }

            if (class_exists($funcionalidades) && method_exists($funcionalidades, 'setUsuariosModel')) {
                forward_static_call_array([$funcionalidades, 'setUsuariosModel'], [$usuarios]);
            }

            if (class_exists($persistencias) && method_exists($persistencias, 'setUsuariosModel')) {
                forward_static_call_array([$persistencias, 'setUsuariosModel'], [$usuarios]);
            }

            if (class_exists($usuarios) && method_exists($usuarios, 'setPermissionsClass')) {
                forward_static_call_array([$usuarios, 'setPermissionsClass'], [$permissions]);
            }

            return new IlluminateUsuarioRepository($app['controledeacesso.hasher'], $app['events'], $usuarios);
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
     * Registers the perfis.
     *
     * @return void
     */
    protected function registerPerfis()
    {
        $this->app->singleton('controledeacesso.perfis', function ($app) {
            $config = $app['config']->get('xfusionsolution.controledeacesso');

            $model = $config['perfis']['model'];
            $usuarios = $config['usuarios']['model'];

            if (class_exists($usuarios) && method_exists($usuarios, 'setPerfisModel')) {
                forward_static_call_array([$usuarios, 'setPerfisModel'], [$model]);
            }

            return new IlluminatePerfilRepository($model);
        });
    }

    /**
     * Registers the funcionalidades.
     *
     * @return void
     */
    protected function registerFuncionalidades()
    {
        $this->app->singleton('controledeacesso.funcionalidades', function ($app) {
            $config = $app['config']->get('xfusionsolution.controledeacesso');

            $model = $config['funcionalidades']['model'];
            $usuarios = $config['usuarios']['model'];

            if (class_exists($usuarios) && method_exists($usuarios, 'setFuncionalidadesModel')) {
                forward_static_call_array([$usuarios, 'setFuncionalidadesModel'], [$model]);
            }

            return new IlluminatePerfilRepository($model);
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

            foreach (['global', 'ip', 'usuario'] as $type) {
                ${"{$type}Interval"} = $app['config']->get("xfusionsolution.controledeacesso.throttling.{$type}.interval");
                ${"{$type}Thresholds"} = $app['config']->get("xfusionsolution.controledeacesso.throttling.{$type}.thresholds");
            }

            return new IlluminateThrottleRepository(
                $model,
                $globalInterval,
                $globalThresholds,
                $ipInterval,
                $ipThresholds,
                $usuarioInterval,
                $usuarioThresholds
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

            return new IlluminateReminderRepository($app['controledeacesso.usuarios'], $model, $expires);
        });
    }

    /**
     * Registers controledeacesso.
     *
     * @return void
     */
    protected function registerControledeacesso()
    {
        $this->app->singleton('controledeacesso', function ($app) {
            $controledeacesso = new controledeacesso(
                $app['controledeacesso.persistencia'],
                $app['controledeacesso.usuarios'],
                $app['controledeacesso.permissoes.perfis'],
                $app['controledeacesso.funcionalidades'],
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

                $login = $request->getUsuario();
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

        $this->app->alias('controledeacesso', 'Xfusionsolution\Controledeacesso\Controledeacesso');
    }

    /**
     * {@inheritDoc}
     */
    public function provides()
    {
        return [
            'controledeacesso.session',
            'controledeacesso.cookie',
            'controledeacesso.persistencia',
            'controledeacesso.hasher',
            'controledeacesso.usuarios',
            'controledeacesso.permissoes.perfis',
            'controledeacesso.funcionalidades',
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
     * Sets the usuario resolver on the request class.
     *
     * @return void
     */
    protected function setUsuarioResolver()
    {
        $this->app->rebinding('request', function ($app, $request) {
            $request->setUsuarioResolver(function () use ($app) {
                return $app['controledeacesso']->getUsuario();
            });
        });
    }
}
