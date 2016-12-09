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

namespace Xfusionsolution\Controledeacesso\Cookies;

use Fuel\Core\Cookie;

class FuelPHPCookie implements CookieInterface
{
    /**
     * The cookie key.
     *
     * @var string
     */
    protected $key = 'cartalyst_sentinel';

    /**
     * Create a new FuelPHP cookie driver.
     *
     * @param  string  $key
     * @return void
     */
    public function __construct($key = null)
    {
        if (isset($key)) {
            $this->key = $key;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function put($value)
    {
        Cookie::set($this->key, json_encode($value), 2628000);
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        $value = Cookie::get($this->key);

        if ($value) {
            return json_decode($value);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function forget()
    {
        Cookie::delete($this->key);
    }
}
