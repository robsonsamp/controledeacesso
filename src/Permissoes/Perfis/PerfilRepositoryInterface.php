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

namespace Xfusionsolution\Controledeacesso\Permissoes\Perfis;

interface PerfilRepositoryInterface
{
    /**
     * Finds a role by the given primary key.
     *
     * @param  int  $id
     * @return \Xfusionsolution\Controledeacesso\Permissoes\Perfis\PerfilInterface
     */
    public function findById($id);

    /**
     * Finds a role by the given slug.
     *
     * @param  string  $slug
     * @return \Xfusionsolution\Controledeacesso\Permissoes\Perfis\PerfilInterface
     */
    public function findBySlug($slug);

    /**
     * Finds a role by the given name.
     *
     * @param  string  $name
     * @return \Xfusionsolution\Controledeacesso\Permissoes\Perfis\PerfilInterface
     */
    public function findByName($name);
}
