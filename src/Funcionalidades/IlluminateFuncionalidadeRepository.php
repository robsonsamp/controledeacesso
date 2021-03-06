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

use Xfusionsolution\Suporte\Traits\RepositoryTrait;

class IlluminateFuncionalidadeRepository implements FuncionalidadeRepositoryInterface
{
    use RepositoryTrait;

    /**
     * The Eloquent Funcionalidade model name.
     *
     * @var string
     */
    protected $model = 'Xfusionsolution\Controledeacesso\Funcionalidades\ModelFuncionalidade';

    /**
     * Create a new Illuminate role repository.
     *
     * @param  string  $model
     * @return void
     */
    public function __construct($model = null)
    {
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
    public function findByDescricao($descricao)
    {
        return $this
            ->createModel()
            ->newQuery()
            ->where('descricao', $descricao)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function findByName($name)
    {
        return $this
            ->createModel()
            ->newQuery()
            ->where('name', $name)
            ->first();
    }
}
