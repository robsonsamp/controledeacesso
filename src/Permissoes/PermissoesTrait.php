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

trait PermissoesTrait
{
    /**
     * The permissoes.
     *
     * @var array
     */
    protected $permissoes = [];

    /**
     * The secondary permissoes.
     *
     * @var array
     */
    protected $secondaryPermissoes = [];

    /**
     * An array of cached, prepared permissoes.
     *
     * @var array
     */
    protected $preparedPermissoes;

    /**
     * Create a new permissoes instance.
     *
     * @param  array  $permissoes
     * @param  array  $secondaryPermissoes
     * @return void
     */
    public function __construct(array $permissoes = null, array $secondaryPermissoes = null)
    {
        if (isset($permissoes)) {
            $this->permissoes = $permissoes;
        }

        if (isset($secondaryPermissoes)) {
            $this->secondaryPermissoes = $secondaryPermissoes;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function hasAccess($permissoes)
    {
        if (is_string($permissoes)) {
            $permissoes = func_get_args();
        }

        $prepared = $this->getPreparedPermissoes();

        foreach ($permissoes as $permissao) {
            if (! $this->checkPermissao($prepared, $permissao)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAnyAccess($permissoes)
    {
        if (is_string($permissoes)) {
            $permissoes = func_get_args();
        }

        $prepared = $this->getPreparedPermissoes();

        foreach ($permissoes as $permissao) {
            if ($this->checkPermissao($prepared, $permissao)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the secondary permissoes.
     *
     * @return array
     */
    public function getSecondaryPermissoes()
    {
        return $this->secondaryPermissoes;
    }

    /**
     * Sets secondary permissoes.
     *
     * @param  array  $secondaryPermissoes
     * @return void
     */
    public function setSecondaryPermissoes(array $secondaryPermissoes)
    {
        $this->secondaryPermissoes = $secondaryPermissoes;

        $this->preparedPermissoes = null;
    }

    /**
     * Lazily grab the prepared permissoes.
     *
     * @return array
     */
    protected function getPreparedPermissoes()
    {
        if ($this->preparedPermissoes === null) {
            $this->preparedPermissoes = $this->createPreparedPermissoes();
        }

        return $this->preparedPermissoes;
    }

    /**
     * Does the heavy lifting of preparing permissoes.
     *
     * @param  array  $prepared
     * @param  array  $permissoes
     * @return void
     */
    protected function preparePermissoes(array &$prepared, array $permissoes)
    {
        foreach ($permissoes as $keys => $value) {
            foreach ($this->extractClassPermissoes($keys) as $key) {
                // If the value is not in the array, we're opting in
                if (! array_key_exists($key, $prepared)) {
                    $prepared[$key] = $value;

                    continue;
                }

                // If our value is in the array and equals false, it will override
                if ($value === false) {
                    $prepared[$key] = $value;
                }
            }
        }
    }

    /**
     * Takes the given permissao key and inspects it for a class & method. If
     * it exists, methods may be comma-separated, e.g. Class@method1,method2.
     *
     * @param  string  $key
     * @return array
     */
    protected function extractClassPermissoes($key)
    {
        if (! str_contains($key, '@')) {
            return (array) $key;
        }

        $keys = [];

        list($class, $methods) = explode('@', $key);

        foreach (explode(',', $methods) as $method) {
            $keys[] = "{$class}@{$method}";
        }

        return $keys;
    }

    /**
     * Checks a permissao in the prepared array, including wildcard checks and permissoes.
     *
     * @param  array  $prepared
     * @param  string  $permissao
     * @return bool
     */
    protected function checkPermissao(array $prepared, $permissao)
    {
        if (array_key_exists($permissao, $prepared) && $prepared[$permissao] === true) {
            return true;
        }

        foreach ($prepared as $key => $value) {
            if ((str_is($permissao, $key) || str_is($key, $permissao)) && $value === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the prepared permissoes.
     *
     * @return void
     */
    abstract protected function createPreparedPermissoes();
}
