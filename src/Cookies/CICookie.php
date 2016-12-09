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

use CI_Input as Input;

class CICookie implements CookieInterface
{
    /**
     * The CodeIgniter input object.
     *
     * @var \CI_Input
     */
    protected $input;

    /**
     * The cookie options.
     *
     * @var array
     */
    protected $options = [
        'name'   => 'xfusionsolution_controledeacesso',
        'domain' => '',
        'path'   => '/',
        'prefix' => '',
        'secure' => false,
    ];

    /**
     * Create a new CodeIgniter cookie driver.
     *
     * @param  \CI_Input  $input
     * @param  string|array  $options
     * @return void
     */
    public function __construct(Input $input, $options = [])
    {
        $this->input = $input;

        if (is_array($options)) {
            $this->options = array_merge($this->options, $options);
        } else {
            $this->options['name'] = $options;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function put($value)
    {
        $options = array_merge($this->options, [
            'value'  => json_encode($value),
            'expire' => 2628000,
        ]);

        $this->input->set_cookie($options);
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        $value = $this->input->cookie($this->options['name']);

        if ($value) {
            return json_decode($value);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function forget()
    {
        $this->input->set_cookie([
            'name'   => $this->options['name'],
            'value'  => '',
            'expiry' => '',
        ]);
    }
}
