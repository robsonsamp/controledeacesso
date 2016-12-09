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

use Illuminate\Database\Eloquent\Model;

class ModelPersistencia extends Model implements PersistenciaInterface
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'seg_persistences';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'seguranca';

    /**
     * The users model name.
     *
     * @var string
     */
    protected static $usuariosModel = 'Xfusionsolution\Controledeacesso\Usuarios\ModelUsuario';

    /**
     * {@inheritDoc}
     */
    public function user()
    {
        return $this->belongsTo(static::$usuariosModel);
    }

    /**
     * Get the users model.
     *
     * @return string
     */
    public static function getUsuariosModel()
    {
        return static::$usuariosModel;
    }

    /**
     * Set the users model.
     *
     * @param  string  $usuariosModel
     * @return void
     */
    public static function setUsuariosModel($usuariosModel)
    {
        static::$usuariosModel = $usuariosModel;
    }
}
