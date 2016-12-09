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

namespace Xfusionsolution\Controledeacesso\Sessions;

use CI_Session as Session;

class CISession implements SessionInterface
{
    /**
     * The CodeIgniter session driver.
     *
     * @var \CI_Session
     */
    protected $store;

    /**
     * The session key.
     *
     * @var string
     */
    protected $key = 'cartalyst_sentinel';

    /**
     * Create a new CodeIgniter Session driver.
     *
     * @param  \CI_Session  $store
     * @param  string  $key
     * @return void
     */
    public function __construct(Session $store, $key = null)
    {
        $this->store = $store;

        if (isset($key)) {
            $this->key = $key;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function put($value)
    {
        $this->store->set_userdata($this->key, serialize($value));
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        $value = $this->store->userdata($this->key);

        if ($value) {
            return unserialize($value);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function forget()
    {
        $this->store->unset_userdata($this->key);
    }
}
