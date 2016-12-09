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

namespace Xfusionsolution\Controledeacesso\Usuarios;

use Carbon\Carbon;
use Xfusionsolution\Controledeacesso\Utils\HasherInterface;
use Xfusionsolution\Suporte\Traits\EventTrait;
use Xfusionsolution\Suporte\RepositoryTrait;
use Closure;
use Illuminate\Events\Dispatcher;
use InvalidArgumentException;

class IlluminateUsuarioRepository implements UsuariosRepositoryInterface
{
    use EventTrait, RepositoryTrait;

    /**
     * The hasher instance.
     *
     * @var \Xfusionsolution\Controledeacesso\Utils\HasherInterface
     */
    protected $hasher;

    /**
     * The Eloquent usuario model name.
     *
     * @var string
     */
    protected $model = 'Xfusionsolution\Controledeacesso\Usuarios\EloquentUsuario';

    /**
     * Create a new Illuminate usuario repository.
     *
     * @param  \Xfusionsolution\Controledeacesso\Utils\HasherInterface  $hasher
     * @param  \Illuminate\Events\Dispatcher  $dispatcher
     * @param  string  $model
     * @return void
     */
    public function __construct(
        HasherInterface $hasher,
        Dispatcher $dispatcher = null,
        $model = null
    ) {
        $this->hasher = $hasher;

        $this->dispatcher = $dispatcher;

        if (isset($model)) {
            $this->model = $model;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findById($id)
    {
        return $this
            ->createModel()
            ->newQuery()
            ->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findByCredentials(array $credentials)
    {
        if (empty($credentials)) {
            return;
        }

        $instance = $this->createModel();

        $loginNames = $instance->getLoginNames();

        list($logins, $password, $credentials) = $this->parseCredentials($credentials, $loginNames);

        if (empty($logins)) {
            return;
        }

        $query = $instance->newQuery();

        if (is_array($logins)) {
            foreach ($logins as $key => $value) {
                $query->where($key, $value);
            }
        } else {
            $query->whereNested(function ($query) use ($loginNames, $logins) {
                foreach ($loginNames as $name) {
                    $query->orWhere($name, $logins);
                }
            });
        }

        return $query->first();
    }

    /**
     * {@inheritDoc}
     */
    public function findByPersistenceCode($code)
    {
        return $this->createModel()
            ->newQuery()
            ->whereHas('persistences', function ($q) use ($code) {
                $q->where('code', $code);
            })
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function recordLogin(UsuarioInterface $usuario)
    {
        $usuario->last_login = Carbon::now();

        return $usuario->save() ? $usuario : false;
    }

    /**
     * {@inheritDoc}
     */
    public function recordLogout(UsuarioInterface $usuario)
    {
        return $usuario->save() ? $usuario : false;
    }

    /**
     * {@inheritDoc}
     */
    public function validateCredentials(UsuarioInterface $usuario, array $credentials)
    {
        return $this->hasher->check($credentials['password'], $usuario->password);
    }

    /**
     * {@inheritDoc}
     */
    public function validForCreation(array $credentials)
    {
        return $this->validateUsuario($credentials);
    }

    /**
     * {@inheritDoc}
     */
    public function validForUpdate($usuario, array $credentials)
    {
        if ($usuario instanceof UsuarioInterface) {
            $usuario = $usuario->getUsuarioId();
        }

        return $this->validateUsuario($credentials, $usuario);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $credentials, Closure $callback = null)
    {
        $usuario = $this->createModel();

        $this->fireEvent('sentinel.usuario.creating', compact('usuario', 'credentials'));

        $this->fill($usuario, $credentials);

        if ($callback) {
            $result = $callback($usuario);

            if ($result === false) {
                return false;
            }
        }

        $usuario->save();

        $this->fireEvent('sentinel.usuario.created', compact('usuario', 'credentials'));

        return $usuario;
    }

    /**
     * {@inheritDoc}
     */
    public function update($usuario, array $credentials)
    {
        if (! $usuario instanceof UsuarioInterface) {
            $usuario = $this->findById($usuario);
        }

        $this->fireEvent('sentinel.usuario.updating', compact('usuario', 'credentials'));

        $this->fill($usuario, $credentials);

        $usuario->save();

        $this->fireEvent('sentinel.usuario.updated', compact('usuario', 'credentials'));

        return $usuario;
    }

    /**
     * Parses the given credentials to return logins, password and others.
     *
     * @param  array  $credentials
     * @param  array  $loginNames
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function parseCredentials(array $credentials, array $loginNames)
    {
        if (isset($credentials['password'])) {
            $password = $credentials['password'];

            unset($credentials['password']);
        } else {
            $password = null;
        }

        $passedNames = array_intersect_key($credentials, array_flip($loginNames));

        if (count($passedNames) > 0) {
            $logins = [];

            foreach ($passedNames as $name => $value) {
                $logins[$name] = $credentials[$name];
                unset($credentials[$name]);
            }
        } elseif (isset($credentials['login'])) {
            $logins = $credentials['login'];
            unset($credentials['login']);
        } else {
            $logins = [];
        }

        return [$logins, $password, $credentials];
    }

    /**
     * Validates the usuario.
     *
     * @param  array  $credentials
     * @param  int  $id
     * @return bool
     * @throws \InvalidArgumentException
     */
    protected function validarUsuario(array $credentials, $id = null)
    {
        $instance = $this->createModel();

        $loginNames = $instance->getLoginNames();

        // We will simply parse credentials which checks logins and passwords
        list($logins, $password, $credentials) = $this->parseCredentials($credentials, $loginNames);

        if ($id === null) {
            if (empty($logins)) {
                throw new InvalidArgumentException('No [login] credential was passed.');
            }

            if (empty($password)) {
                throw new InvalidArgumentException('You have not passed a [password].');
            }
        }

        return true;
    }

    /**
     * Fills a usuario with the given credentials, intelligently.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuarioInterface  $usuario
     * @param  array  $credentials
     * @return void
     */
    public function fill(UsuarioInterface $usuario, array $credentials)
    {
        $this->fireEvent('sentinel.usuario.filling', compact('usuario', 'credentials'));

        $loginNames = $usuario->getLoginNames();

        list($logins, $password, $attributes) = $this->parseCredentials($credentials, $loginNames);

        if (is_array($logins)) {
            $usuario->fill($logins);
        } else {
            $loginName = reset($loginNames);

            $usuario->fill([
                $loginName => $logins,
            ]);
        }

        $usuario->fill($attributes);

        if (isset($password)) {
            $password = $this->hasher->hash($password);

            $usuario->fill(compact('password'));
        }

        $this->fireEvent('sentinel.usuario.filled', compact('usuario', 'credentials'));
    }

    /**
     * Returns the hasher instance.
     *
     * @return \Xfusionsolution\Controledeacesso\Utils\HasherInterface
     */
    public function getHasher()
    {
        return $this->hasher;
    }

    /**
     * Sets the hasher instance.
     *
     * @param \Xfusionsolution\Controledeacesso\Utils\HasherInterface  $hasher
     * @return void
     */
    public function setHasher(HasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }
}
