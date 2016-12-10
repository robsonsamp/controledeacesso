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
 * @package    Contperfil de Acesso
 * @version    0.0.1
 * @author     Robson Sampaio
 * @license    BSD License (3-clause)
 * @copyright  (c) 2016, Robson Sampaio
 * @link       http://xfusionsolution.com.br
 */

namespace Xfusionsolution\Controledeacesso;

use BadMethodCallException;
use Xfusionsolution\Controledeacesso\Activations\ActivationRepositoryInterface;
use Xfusionsolution\Controledeacesso\Checkpoints\CheckpointInterface;
use Xfusionsolution\Controledeacesso\Funcionalidades\FuncionalidadeRepositoryInterface;
use Xfusionsolution\Controledeacesso\Persistencias\PersistenceRepositoryInterface;
use Xfusionsolution\Controledeacesso\Reminders\ReminderRepositoryInterface;
use Xfusionsolution\Controledeacesso\Permissoes\Perfis\PerfilRepositoryInterface;
use Xfusionsolution\Controledeacesso\Throttling\ThrottleRepositoryInterface;
use Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface;
use Xfusionsolution\Controledeacesso\Usuarios\UsuarioRepositoryInterface;

use Cartalyst\Support\Traits\EventTrait;
use Closure;
use Illuminate\Events\Dispatcher;
use InvalidArgumentException;
use RuntimeException;

class Controledeacesso
{
    use EventTrait;

    /**
     * The current cached, logged in usuario.
     *
     * @var \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface
     */
    protected $usuario;

    /**
     * The current cached, logged in usuario.
     *
     * @var \Xfusionsolution\Controledeacesso\Funcionalidades\FuncionalidadeInterface
     */
    protected $funcionalidade;

    /**
     * The Persistence repository.
     *
     * @var \Xfusionsolution\Controledeacesso\Persistencias\PersistenceRepositoryInterface
     */
    protected $persistencias;

    /**
     * The Usuario repository.
     *
     * @var \Xfusionsolution\Controledeacesso\Usuarios\UsuarioRepositoryInterface
     */
    protected $usuarios;

    /**
     * The Perfil repository.
     *
     * @var \Xfusionsolution\Controledeacesso\Permissoes\Perfis\PerfilRepositoryInterface
     */
    protected $perfis;

    /**
     * The Funcionalidades repository.
     *
     * @var \Xfusionsolution\Controledeacesso\Funcionalidades\FuncionalidadeRepositoryInterface
     */
    protected $funcionalidades;

    /**
     * The Activations repository.
     *
     * @var \Xfusionsolution\Controledeacesso\Activations\ActivationRepositoryInterface
     */
    protected $activations;

    /**
     * Cached, available methods on the usuario repository, used for dynamic calls.
     *
     * @var array
     */
    protected $usuarioMethods = [];

    /**
     * Array that holds all the enabled checkpoints.
     *
     * @var array
     */
    protected $checkpoints = [];

    /**
     * Flag for the checkpoint status.
     *
     * @var bool
     */
    protected $checkpointsStatus = true;

    /**
     * The Reminders repository.
     *
     * @var \Xfusionsolution\Controledeacesso\Reminders\ReminderRepositoryInterface
     */
    protected $reminders;

    /**
     * The closure to retrieve the request credentials.
     *
     * @var \Closure
     */
    protected $requestCredentials;

    /**
     * The closure used to create a basic response for failed HTTP auth.
     *
     * @var \Closure
     */
    protected $basicResponse;

    /**
     * The Throttle repository.
     *
     * @var \Xfusionsolution\Controledeacesso\Throttling\ThrottleRepositoryInterface
     */
    protected $throttle;

    /**
     * Create a new Sentinel instance.
     *
     * @param  \Xfusionsolution\Controledeacesso\Persistencias\PersistenceRepositoryInterface  $persistence
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioRepositoryInterface  $usuarios
     * @param  \Xfusionsolution\Controledeacesso\Permissoes\Perfis\PerfilRepositoryInterface  $perfis
     * @param  \Xfusionsolution\Controledeacesso\Activations\ActivationRepositoryInterface  $activations
     * @param  \Illuminate\Events\Dispatcher  $dispatcher
     * @return void
     */
    public function __construct(
        PersistenceRepositoryInterface $persistencias,
        UsuarioRepositoryInterface $usuarios,
        PerfilRepositoryInterface $perfis,
        ActivationRepositoryInterface $activations,
        FuncionalidadeRepositoryInterface $funcionalidades,
        Dispatcher $dispatcher
    ) {
        $this->persistencias = $persistencias;

        $this->usuarios = $usuarios;

        $this->perfis = $perfis;

        $this->activations = $activations;

        $this->funcionalidades = $funcionalidades;

        $this->dispatcher = $dispatcher;
    }

    /**
     * Registers a usuario. You may provide a callback to occur before the usuario
     * is saved, or provide a true boolean as a shortcut to activation.
     *
     * @param  array  $credentials
     * @param  \Closure|bool  $callback
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInteface|bool
     * @throws \InvalidArgumentException
     */
    public function register(array $credentials, $callback = null)
    {
        if ($callback !== null && ! $callback instanceof Closure && ! is_bool($callback)) {
            throw new InvalidArgumentException('You must provide a closure or a boolean.');
        }

        $this->fireEvent('sentinel.registering', $credentials);

        $valid = $this->usuarios->validForCreation($credentials);

        if ($valid === false) {
            return false;
        }

        $argument = $callback instanceof Closure ? $callback : null;

        $usuario = $this->usuarios->create($credentials, $argument);

        if ($callback === true) {
            $this->activate($usuario);
        }

        $this->fireEvent('sentinel.registered', $usuario);

        return $usuario;
    }

    /**
     * Registers and activates the usuario.
     *
     * @param  array  $credentials
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInteface|bool
     */
    public function registerAndActivate(array $credentials)
    {
        return $this->register($credentials, true);
    }

    /**
     * Activates the given usuario.
     *
     * @param  mixed  $usuario
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function activate($usuario)
    {
        if (is_string($usuario) || is_array($usuario)) {
            $usuarios = $this->getUsuarioRepository();

            $method = 'findBy'.(is_string($usuario) ? 'Id' : 'Credentials');

            $usuario = $usuarios->{$method}($usuario);
        }

        if (! $usuario instanceof UsuarioInterface) {
            throw new InvalidArgumentException('No valid usuario was provided.');
        }

        $this->fireEvent('sentinel.activating', $usuario);

        $activations = $this->getActivationRepository();

        $activation = $activations->create($usuario);

        $this->fireEvent('sentinel.activated', [ $usuario, $activation ]);

        return $activations->complete($usuario, $activation->getCode());
    }

    /**
     * Checks to see if a usuario is logged in.
     *
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|bool
     */
    public function check()
    {
        if ($this->usuario !== null) {
            return $this->usuario;
        }

        if (! $code = $this->persistencias->check()) {
            return false;
        }

        if (! $usuario = $this->persistencias->findUsuarioByPersistenceCode($code)) {
            return false;
        }

        if (! $this->cycleCheckpoints('check', $usuario)) {
            return false;
        }

        return $this->usuario = $usuario;
    }

    /**
     * Checks to see if a usuario is logged in, bypassing checkpoints
     *
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|bool
     */
    public function forceCheck()
    {
        return $this->bypassCheckpoints(function ($sentinel) {
            return $sentinel->check();
        });
    }

    /**
     * Checks if we are currently a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    /**
     * Authenticates a usuario, with "remember" flag.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|array  $credentials
     * @param  bool  $remember
     * @param  bool  $login
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|bool
     */
    public function authenticate($credentials, $remember = false, $login = true)
    {
        $response = $this->fireEvent('sentinel.authenticating', $credentials, true);

        if ($response === false) {
            return false;
        }

        if ($credentials instanceof UsuarioInterface) {
            $usuario = $credentials;
        } else {
            $usuario = $this->usuarios->findByCredentials($credentials);

            $valid = $usuario !== null ? $this->usuarios->validateCredentials($usuario, $credentials) : false;

            if ($usuario === null || $valid === false) {
                $this->cycleCheckpoints('fail', $usuario, false);

                return false;
            }
        }

        if (! $this->cycleCheckpoints('login', $usuario)) {
            return false;
        }

        if ($login === true) {
            $method = $remember === true ? 'loginAndRemember' : 'login';

            if (! $usuario = $this->{$method}($usuario)) {
                return false;
            }
        }

        $this->fireEvent('sentinel.authenticated', $usuario);

        return $this->usuario = $usuario;
    }

    /**
     * Authenticates a usuario, with the "remember" flag.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|array  $credentials
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|bool
     */
    public function authenticateAndRemember($credentials)
    {
        return $this->authenticate($credentials, true);
    }

    /**
     * Forces an authentication to bypass checkpoints.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|array  $credentials
     * @param  bool  $remember
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|bool
     */
    public function forceAuthenticate($credentials, $remember = false)
    {
        return $this->bypassCheckpoints(function ($sentinel) use ($credentials, $remember) {
            return $sentinel->authenticate($credentials, $remember);
        });
    }

    /**
     * Forces an authentication to bypass checkpoints, with the "remember" flag.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|array  $credentials
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|bool
     */
    public function forceAuthenticateAndRemember($credentials)
    {
        return $this->forceAuthenticate($credentials, true);
    }

    /**
     * Attempt a stateless authentication.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|array  $credentials
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|bool
     */
    public function stateless($credentials)
    {
        return $this->authenticate($credentials, false, false);
    }

    /**
     * Attempt to authenticate using HTTP Basic Auth.
     *
     * @return mixed
     */
    public function basic()
    {
        $credentials = $this->getRequestCredentials();

        // We don't really want to add a throttling record for the
        // first failed login attempt, which actually occurs when
        // the usuario first hits a protected route.
        if ($credentials === null) {
            return $this->getBasicResponse();
        }

        $usuario = $this->stateless($credentials);

        if ($usuario) {
            return;
        }

        return $this->getBasicResponse();
    }

    /**
     * Returns the request credentials.
     *
     * @return array
     */
    public function getRequestCredentials()
    {
        if ($this->requestCredentials === null) {
            $this->requestCredentials = function () {
                $credentials = [];

                if (isset($_SERVER['PHP_AUTH_USUARIO'])) {
                    $credentials['login'] = $_SERVER['PHP_AUTH_USUARIO'];
                }

                if (isset($_SERVER['PHP_AUTH_PW'])) {
                    $credentials['password'] = $_SERVER['PHP_AUTH_PW'];
                }

                if (count($credentials) > 0) {
                    return $credentials;
                }
            };
        }

        $credentials = $this->requestCredentials;

        return $credentials();
    }

    /**
     * Sets the closure which resolves the request credentials.
     *
     * @param  \Closure  $requestCredentials
     * @return void
     */
    public function setRequestCredentials(Closure $requestCredentials)
    {
        $this->requestCredentials = $requestCredentials;
    }

    /**
     * Sends a response when HTTP basic authentication fails.
     *
     * @return mixed
     * @throws \RuntimeException
     */
    public function getBasicResponse()
    {
        // Default the basic response
        if ($this->basicResponse === null) {
            $this->basicResponse = function () {
                if (headers_sent()) {
                    throw new RuntimeException('Attempting basic auth after headers have already been sent.');
                }

                header('WWW-Authenticate: Basic');
                header('HTTP/1.0 401 Unauthorized');

                echo 'Invalid credentials.';
                exit;
            };
        }

        $response = $this->basicResponse;

        return $response();
    }

    /**
     * Sets the callback which creates a basic response.
     *
     * @param  \Closure  $basicResonse
     * @return void
     */
    public function creatingBasicResponse(Closure $basicResponse)
    {
        $this->basicResponse = $basicResponse;
    }

    /**
     * Persists a login for the given usuario.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @param  bool  $remember
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|bool
     */
    public function login(UsuarioInterface $usuario, $remember = false)
    {
        $method = $remember === true ? 'persistAndRemember' : 'persist';

        $this->persistencias->{$method}($usuario);

        $response = $this->usuarios->recordLogin($usuario);

        if ($response === false) {
            return false;
        }

        return $this->usuario = $usuario;
    }

    /**
     * Persists a login for the given usuario, with the "remember" flag.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface|bool
     */
    public function loginAndRemember(UsuarioInterface $usuario)
    {
        return $this->login($usuario, true);
    }

    /**
     * Logs the current usuario out.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @param  bool  $everywhere
     * @return bool
     */
    public function logout(UsuarioInterface $usuario = null, $everywhere = false)
    {
        $currentUsuario = $this->check();

        if ($usuario && $usuario !== $currentUsuario) {
            $this->persistencias->flush($usuario, false);

            return true;
        }

        $usuario = $usuario ?: $currentUsuario;

        if ($usuario === false) {
            return true;
        }

        $method = $everywhere === true ? 'flush' : 'forget';

        $this->persistencias->{$method}($usuario);

        $this->usuario = null;

        return $this->usuarios->recordLogout($usuario);
    }

    /**
     * Pass a closure to Sentinel to bypass checkpoints.
     *
     * @param  \Closure  $callback
     * @param  array  $checkpoints
     * @return mixed
     */
    public function bypassCheckpoints(Closure $callback, $checkpoints = [])
    {
        $originalCheckpoints = $this->checkpoints;

        $activeCheckpoints = [];

        foreach (array_keys($originalCheckpoints) as $checkpoint) {
            if ($checkpoints && ! in_array($checkpoint, $checkpoints)) {
                $activeCheckpoints[$checkpoint] = $originalCheckpoints[$checkpoint];
            }
        }

        // Temporarily replace the registered checkpoints
        $this->checkpoints = $activeCheckpoints;

        // Fire the callback
        $result = $callback($this);

        // Reset checkpoints
        $this->checkpoints = $originalCheckpoints;

        return $result;
    }

    /**
     * Checks if checkpoints are enabled.
     *
     * @return bool
     */
    public function checkpointsStatus()
    {
        return $this->checkpointsStatus;
    }

    /**
     * Enables checkpoints.
     *
     * @return void
     */
    public function enableCheckpoints()
    {
        $this->checkpointsStatus = true;
    }

    /**
     * Disables checkpoints.
     *
     * @return void
     */
    public function disableCheckpoints()
    {
        $this->checkpointsStatus = false;
    }

    /**
     * Add a new checkpoint to Sentinel.
     *
     * @param  string  $key
     * @param  \Xfusionsolution\Controledeacesso\Checkpoints\CheckpointInterface  $checkpoint
     * @return void
     */
    public function addCheckpoint($key, CheckpointInterface $checkpoint)
    {
        $this->checkpoints[$key] = $checkpoint;
    }

    /**
     * Removes a checkpoint.
     *
     * @param  string  $key
     * @return void
     */
    public function removeCheckpoint($key)
    {
        if (isset($this->checkpoints[$key])) {
            unset($this->checkpoints[$key]);
        }
    }

    /**
     * Cycles through all the registered checkpoints for a usuario. Checkpoints
     * may throw their own exceptions, however, if just one returns false,
     * the cycle fails.
     *
     * @param  string  $method
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @param  bool  $halt
     * @return bool
     */
    protected function cycleCheckpoints($method, UsuarioInterface $usuario = null, $halt = true)
    {
        if (! $this->checkpointsStatus) {
            return true;
        }

        foreach ($this->checkpoints as $checkpoint) {
            $response = $checkpoint->{$method}($usuario);

            if ($response === false && $halt === true) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the currently logged in usuario, lazily checking for it.
     *
     * @param  bool  $check
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface
     */
    public function getUsuario($check = true)
    {
        if ($check === true && $this->usuario === null) {
            $this->check();
        }

        return $this->usuario;
    }

    /**
     * Sets the usuario associated with Sentinel (does not log in).
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @return void
     */
    public function setUsuario(UsuarioInterface $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Returns the usuario repository.
     *
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuarioRepositoryInterface
     */
    public function getUsuarioRepository()
    {
        return $this->usuarios;
    }

    /**
     * Sets the usuario repository.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioRepositoryInterface  $usuarios
     * @return void
     */
    public function setUsuarioRepository(UsuarioRepositoryInterface $usuarios)
    {
        $this->usuarios = $usuarios;

        $this->usuarioMethods = [];
    }

    /**
     * Returns the perfil repository.
     *
     * @return \Xfusionsolution\Controledeacesso\Permissoes\Perfis\PerfilRepositoryInterface
     */
    public function getPerfilRepository()
    {
        return $this->perfis;
    }

    /**
     * Returns the funcionalidade repository.
     *
     * @return \Xfusionsolution\Controledeacesso\Funcionalidades\FuncionalidadeRepositoryInterface
     */
    public function getFuncionalidadeRepository()
    {
        return $this->funcionalidades;
    }

    /**
     * Sets the perfil repository.
     *
     * @param  \Xfusionsolution\Controledeacesso\Permissoes\Perfis\PerfilRepositoryInterface  $perfis
     * @return void
     */
    public function setPerfilRepository(PerfilRepositoryInterface $perfis)
    {
        $this->perfis = $perfis;
    }

    /**
     * Returns the persistencias repository.
     *
     * @return \Xfusionsolution\Controledeacesso\Persistencias\PersistenceRepositoryInterface
     */
    public function getPersistenceRepository()
    {
        return $this->persistencias;
    }

    /**
     * Sets the persistencias repository.
     *
     * @param  \Xfusionsolution\Controledeacesso\Persistencias\PersistenceRepositoryInterface  $persistencias
     * @return void
     */
    public function setPersistenceRepository(PersistenceRepositoryInterface $persistencias)
    {
        $this->persistencias = $persistencias;
    }

    /**
     * Returns the activations repository.
     *
     * @return \Xfusionsolution\Controledeacesso\Activations\ActivationRepositoryInterface
     */
    public function getActivationRepository()
    {
        return $this->activations;
    }

    /**
     * Sets the activations repository.
     *
     * @param  \Xfusionsolution\Controledeacesso\Activations\ActivationRepositoryInterface  $activations
     * @return void
     */
    public function setActivationRepository(ActivationRepositoryInterface $activations)
    {
        $this->activations = $activations;
    }

    /**
     * Returns the reminders repository.
     *
     * @return \Xfusionsolution\Controledeacesso\Reminders\ReminderRepositoryInterface
     */
    public function getReminderRepository()
    {
        return $this->reminders;
    }

    /**
     * Sets the reminders repository.
     *
     * @param  \Xfusionsolution\Controledeacesso\Reminders\ReminderRepositoryInterface  $reminders
     * @return void
     */
    public function setReminderRepository(ReminderRepositoryInterface $reminders)
    {
        $this->reminders = $reminders;
    }

    /**
     * Returns the throttle repository.
     *
     * @return \Xfusionsolution\Controledeacesso\Throttling\ThrottleRepositoryInterface
     */
    public function getThrottleRepository()
    {
        return $this->throttle;
    }

    /**
     * Sets the throttle repository.
     *
     * @param  \Xfusionsolution\Controledeacesso\Throttling\ThrottleRepositoryInterface  $throttle
     * @return void
     */
    public function setThrottleRepository(ThrottleRepositoryInterface $throttle)
    {
        $this->throttle = $throttle;
    }

    /**
     * Returns all accessible methods on the associated usuario repository.
     *
     * @return array
     */
    protected function getUsuarioMethods()
    {
        if (empty($this->usuarioMethods)) {
            $usuarios = $this->getUsuarioRepository();

            $methods = get_class_methods($usuarios);

            $this->usuarioMethods = array_diff($methods, ['__construct']);
        }

        return $this->usuarioMethods;
    }

    /**
     * Dynamically pass missing methods to Sentinel.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        $methods = $this->getUsuarioMethods();

        if (in_array($method, $methods)) {
            $usuarios = $this->getUsuarioRepository();

            return call_usuario_func_array([$usuarios, $method], $parameters);
        }

        if (starts_with($method, 'findUsuarioBy')) {
            $usuario = $this->getUsuarioRepository();

            $method = 'findBy'.substr($method, 10);

            return call_usuario_func_array([$usuario, $method], $parameters);
        }

        if (starts_with($method, 'findPerfilBy')) {
            $perfis = $this->getPerfilRepository();

            $method = 'findBy'.substr($method, 10);

            return call_usuario_func_array([$perfis, $method], $parameters);
        }

        $methods = ['getPerfis', 'inPerfil', 'hasAccess', 'hasAnyAccess'];

        $className = get_class($this);

        if (in_array($method, $methods)) {
            $usuario = $this->getUsuario();

            if ($usuario === null) {
                throw new BadMethodCallException("Method {$className}::{$method}() can only be called if a usuario is logged in.");
            }

            return call_usuario_func_array([$usuario, $method], $parameters);
        }

        throw new BadMethodCallException("Call to undefined method {$className}::{$method}()");
    }
}
