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

namespace Xfusionsolution\Controledeacesso\Persistencias;

interface PersistenciasInterface
{
    /**
     * Returns the persistencias key name.
     *
     * @return string
     */
    public function getPersistenciasKey();

    /**
     * Returns the persistencias key value.
     *
     * @return string
     */
    public function getPersistenciasId();

    /**
     * Returns the persistencias relationship name.
     *
     * @return string
     */
    public function getPersistenciasRelationship();

    /**
     * Generates a random persist code.
     *
     * @return string
     */
    public function generatePersistenciaCode();
}
