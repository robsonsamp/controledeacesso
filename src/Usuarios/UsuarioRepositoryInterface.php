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

use Closure;

interface UsuariosRepositoryInterface
{
    /**
     * Finds a usuarios by the given primary key.
     *
     * @param  int  $id
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuariosInterface
     */
    public function findById($id);

    /**
     * Finds a usuarios by the given credentials.
     *
     * @param  array  $credentials
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuariosInterface
     */
    public function findByCredentials(array $credentials);

    /**
     * Finds a usuarios by the given persistence code.
     *
     * @param  string  $code
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuariosInterface
     */
    public function findByPersistenceCode($code);

    /**
     * Records a login for the given usuarios.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuariosInterface  $usuarios
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuariosInterface|bool
     */
    public function recordLogin(UsuariosInterface $usuarios);

    /**
     * Records a logout for the given usuarios.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuariosInterface  $usuarios
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuariosInterface|bool
     */
    public function recordLogout(UsuariosInterface $usuarios);

    /**
     * Validate the password of the given usuarios.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuariosInterface  $usuarios
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UsuariosInterface $usuarios, array $credentials);

    /**
     * Validate if the given usuarios is valid for creation.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validForCreation(array $credentials);

    /**
     * Validate if the given usuarios is valid for updating.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuariosInterface|int  $usuarios
     * @param  array  $credentials
     * @return bool
     */
    public function validForUpdate($usuarios, array $credentials);

    /**
     * Creates a usuarios.
     *
     * @param  array  $credentials
     * @param  \Closure  $callback
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuariosInterface
     */
    public function create(array $credentials, Closure $callback = null);

    /**
     * Updates a usuarios.
     *
     * @param  \Xfusionsolution\Controledeacesso\Usuarios\UsuariosInterface|int  $usuarios
     * @param  array  $credentials
     * @return \Xfusionsolution\Controledeacesso\Usuarios\UsuariosInterface
     */
    public function update($usuarios, array $credentials);
}
