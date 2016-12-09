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

trait PermitidoTrait
{
    /**
     * The cached permissoes instance for the given user.
     *
     * @var \Xfusionsolution\Controledeacesso\Permissoes\PermissoesInterface
     */
    protected $permissoesInstance;

    /**
     * The permissoes instance class name.
     *
     * @var string
     */
    protected static $permissoesClass = 'Xfusionsolution\Controledeacesso\Permissoes\StrictPermissoes';

    /**
     * Returns the permissoes.
     *
     * @return array
     */
    public function getPermissoes()
    {
        return $this->permissoes;
    }

    /**
     * Sets permissoes.
     *
     * @param  array  $permissoes
     * @return void
     */
    public function setPermissoes(array $permissoes)
    {
        $this->permissoes = $permissoes;
    }

    /**
     * Returns the permissoes class name.
     *
     * @return string
     */
    public static function getPermissoesClass()
    {
        return static::$permissoesClass;
    }

    /**
     * Sets the permissoes class name.
     *
     * @param  string  $permissoesClass
     * @return void
     */
    public static function setPermissoesClass($permissoesClass)
    {
        static::$permissoesClass = $permissoesClass;
    }

    /**
     * Creates the permissoes object.
     *
     * @return \Xfusionsolution\Controledeacesso\Permissoes\PermissoesInterface
     */
    abstract protected function createPermissoes();

    /**
     * {@inheritDoc}
     */
    public function getPermissoesInstance()
    {
        if ($this->permissoesInstance === null) {
            $this->permissoesInstance = $this->createPermissoes();
        }

        return $this->permissoesInstance;
    }

    /**
     * {@inheritDoc}
     */
    public function addPermissao($permissao, $value = true)
    {
        if (! array_key_exists($permissao, $this->permissoes)) {
            $this->permissoes = array_merge($this->permissoes, [$permissao => $value]);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function updatePermissao($permissao, $value = true, $create = false)
    {
        if (array_key_exists($permissao, $this->permissoes)) {
            $permissoes = $this->permissoes;

            $permissoes[$permissao] = $value;

            $this->permissoes = $permissoes;
        } elseif ($create) {
            $this->addPermissao ($permissao, $value);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removePermissao($permissao)
    {
        if (array_key_exists($permissao, $this->permissoes)) {
            $permissoes = $this->permissoes;

            unset($permissoes[$permissao]);

            $this->permissoes = $permissoes;
        }

        return $this;
    }
}
