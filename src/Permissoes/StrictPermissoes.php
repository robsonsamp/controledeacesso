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

class StrictPermissoes implements PermissoesInterface
{
    use PermissoesTrait;

    /**
     * {@inheritDoc}
     */
    protected function createPreparedPermissoes()
    {
        $prepared = [];

        if (! empty($this->secondaryPermissoes)) {
            foreach ($this->secondaryPermissoes as $permissoes) {
                $this->preparePermissoes($prepared, $permissoes);
            }
        }

        if (! empty($this->permissoes)) {
            $this->preparePermissoes($prepared, $this->permissoes);
        }

        return $prepared;
    }
}
