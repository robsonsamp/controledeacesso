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

namespace Xfusionsolution\Controlededeacesso\Funcionalidades;

use Xfusionsolution\Controledeacesso\Permissoes\PermitidoInterface;
use Xfusionsolution\Controledeacesso\Permissoes\PermitidoTrait;
use Illuminate\Database\Eloquent\Model;

class ModelFuncionalidade extends Model implements FuncionalidadeInterface, PermitidoInterface
{
    use PermitidoTrait;

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'name',
        'descricao',
        'permissoes',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'seg_funcionalidades';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $guarded = ['MNU_INT_ID_MENUITEM'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'MNU_INT_ID_MENUITEM';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'seguranca';

    /**
     * Array de parents
     * @var string
     */
    protected $parents = array();

    /**
     * @return string
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * @param string $parents
     */
    public function setParents($parents)
    {
        $this->parents = $parents;
    }

    /**
     * The Eloquent usuarios model name.
     *
     * @var string
     */
    protected static $usuariosModel = 'Xfusionsolution\Controlededeacesso\Usuarios\ModelUsuario';

    /**
     * The Eloquent funcionalidade model name.
     *
     * @var string
     */
    protected static $funcionalidadesModel = 'Xfusionsolution\Controlededeacesso\Funcionalidades\ModelFuncionalidade';
    /**
     * {@inheritDoc}
     */
    public function delete()
    {
        $isSoftDeleted = array_key_exists('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this));

        if ($this->exists && ! $isSoftDeleted) {
            $this->usuarios()->detach();
        }

        return parent::delete();
    }

    /**
     * The Usuarios relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function usuarios()
    {
        return $this->belongsToMany(static::$usuariosModel, 'funcionalidade_usuarios', 'funcionalidade_id', 'usuario_id')->withTimestamps();
    }

    /**
     * Get mutator for the "permissoes" attribute.
     *
     * @param  mixed  $permissoes
     * @return array
     */
    public function getPermissoesAttribute($permissoes)
    {
        return $permissoes ? json_decode($permissoes, true) : [];
    }

    /**
     * Set mutator for the "permissoes" attribute.
     *
     * @param  mixed  $permissoes
     * @return void
     */
    public function setPermissoesAttribute(array $permissoes)
    {
        $this->attributes['permissoes'] = $permissoes ? json_encode($permissoes) : '';
    }

    /**
     * {@inheritDoc}
     */
    public function getFuncionalidadeId()
    {
        return $this->getKey();
    }

    /**
     * {@inheritDoc}
     */
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsuarios()
    {
        return $this->usuarios;
    }

    /**
     * {@inheritDoc}
     */
    public static function getUsuariosModel()
    {
        return static::$usuariosModel;
    }

    /**
     * {@inheritDoc}
     */
    public static function setUsuariosModel($usuariosModel)
    {
        static::$usuariosModel = $usuariosModel;
    }

    /**
     * Dynamically pass missing methods to the permissoes.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $methods = ['hasAccess', 'hasAnyAccess'];

        if (in_array($method, $methods)) {
            $permissoes = $this->getPermissoesInstance();

            return call_user_func_array([$permissoes, $method], $parameters);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    protected function createPermissoes()
    {
        return new static::$permissoesClass($this->permissoes);
    }
}
