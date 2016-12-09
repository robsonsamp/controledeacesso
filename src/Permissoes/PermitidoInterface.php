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

namespace Xfusionsolution\Controledeacesso\Permissoes;

interface PermitidoInterface
{
    /**
     * Returns the permissoes instance.
     *
     * @return \Xfusionsolution\Controledeacesso\Permissoes\PermissoesInterface
     */
    public function getPermissoesInstance();

    /**
     * Adds a permissao.
     *
     * @param  string  $permissao
     * @param  bool  $value
     * @return \Xfusionsolution\Controledeacesso\Permissoes\PermissibleInterface
     */
    public function addPermissao($permissao, $value = true);

    /**
     * Updates a permissao.
     *
     * @param  string  $permissao
     * @param  bool  $value
     * @param  bool  $create
     * @return \Xfusionsolution\Controledeacesso\Permissoes\PermissibleInterface
     */
    public function updatePermissao($permissao, $value = true, $create = false);

    /**
     * Removes a permissao.
     *
     * @param  string  $permissao
     * @return \Xfusionsolution\Controledeacesso\Permissoes\PermissibleInterface
     */
    public function removePermissao($permissao);
}
