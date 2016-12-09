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

interface FuncionalidadeInterface
{
    /**
     * Returns the funcionalidade's primary key.
     *
     * @return int
     */
    public function getFuncionalidadeId();

    /**
     * Returns the funcionalidade's descricao.
     *
     * @return string
     */
    public function getDescricao();

    /**
     * Returns all usuarios for the funcionalidade.
     *
     * @return \IteratorAggregate
     */
    public function getUsuarios();

    /**
     * Returns the usuarios model.
     *
     * @return string
     */
    public static function getUsuariosModel();

    /**
     * Sets the usuarios model.
     *
     * @param  string  $usuariosModel
     * @return void
     */
    public static function setUsuariosModel($usuariosModel);
}
