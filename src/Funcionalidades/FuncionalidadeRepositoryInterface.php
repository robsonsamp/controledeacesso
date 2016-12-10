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
 * @package    Contfuncionalidade de Acesso
 * @version    0.0.1
 * @author     Robson Sampaio
 * @license    BSD License (3-clause)
 * @copyright  (c) 2016, Robson Sampaio
 * @link       http://xfusionsolution.com.br
 */

namespace Xfusionsolution\Controledeacesso\Funcionalidades;

interface FuncionalidadeRepositoryInterface
{
    /**
     * Finds a role by the given primary key.
     *
     * @param  int  $id
     * @return \Xfusionsolution\Controledeacesso\Funcionalidades\FuncionalidadeInterface
     */
    public function findById($id);

    /**
     * Finds a role by the given descricao.
     *
     * @param  string  $descricao
     * @return \Xfusionsolution\Controledeacesso\Funcionalidades\FuncionalidadeInterface
     */
    public function findByDescricao($descricao);

    /**
     * Finds a role by the given name.
     *
     * @param  string  $name
     * @return \Xfusionsolution\Controledeacesso\Funcionalidades\FuncionalidadeInterface
     */
    public function findByName($name);
}
