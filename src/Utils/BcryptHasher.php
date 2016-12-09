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

namespace Xfusionsolution\Controledeacesso\Utils;

class BcryptHasher implements HasherInterface
{
    use Hasher;

    /**
     * The hash strength.
     *
     * @var int
     */
    public $strength = 8;

    /**
     * {@inheritDoc}
     */
    public function hash($value)
    {
        $salt = $this->createSalt();

        // Format the strength
        $strength = str_pad($this->strength, 2, '0', STR_PAD_LEFT);

        // Create prefix - "$2y$"" fixes blowfish weakness
        $prefix = PHP_VERSION_ID < 50307 ? '$2a$' : '$2y$';

        return crypt($value, $prefix.$strength.'$'.$salt.'$');
    }

    /**
     * {@inheritDoc}
     */
    public function check($value, $hashedValue)
    {
        return $this->slowEquals(crypt($value, $hashedValue), $hashedValue);
    }
}
