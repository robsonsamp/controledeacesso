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

namespace Xfusionsolution\Controledeacesso\Usuarios;

use Xfusionsolution\Controledeacesso\Permissoes\PermitidoInterface;
use Xfusionsolution\Controledeacesso\Persistencias\PersistenciasInterface;
use Xfusionsolution\Controledeacesso\Permissoes\Perfis\PerfisInterface;
use Xfusionsolution\Controledeacesso\Permissoes\Perfis\PerfilInterface;
use Xfusionsolution\Controledeacesso\Permissoes\PermitidoTrait;
use Illuminate\Database\Eloquent\Model;

class ModelUsuario extends Model implements PerfisInterface, PermitidoInterface, PersistenciasInterface, UsuarioInterface
{
    use PermitidoTrait;

    /**
     * {@inheritDoc}
     */
    protected $table = 'seg_usuarios';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'seguranca';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'email',
        'password',
        'last_name',
        'first_name',
        'permissoes',
    ];

    /**
     * {@inheritDoc}
     */
    protected $hidden = [
        'password',
    ];

    /**
     * {@inheritDoc}
     */
    protected $persistenciasKey = 'usuario_id';

    /**
     * {@inheritDoc}
     */
    protected $persistenciasRelationship = 'seg_persistencias';

    /**
     * Array of login column names.
     *
     * @var array
     */
    protected $loginNames = ['email'];

    /**
     * The Eloquent perfis model name.
     *
     * @var string
     */
    protected static $perfisModel = 'Xfusionsolution\Controledeacesso\Permissoes\Perfis\ModelPerfil';

    /**
     * The Eloquent perfis model name.
     *
     * @var string
     */
    protected static $funcionalidadessModel = 'Xfusionsolution\Controledeacesso\Funcionaliodades\ModelFuncionalidade';

    /**
     * The Eloquent persistencias model name.
     *
     * @var string
     */
    protected static $persistenciasModel = 'Xfusionsolution\Controledeacesso\Persistencias\ModelPersistencia';

    /**
     * The Eloquent activations model name.
     *
     * @var string
     */
    protected static $activationsModel = 'Xfusionsolution\Controledeacesso\Activations\ModelActivation';

    /**
     * The Eloquent reminders model name.
     *
     * @var string
     */
    protected static $remindersModel = 'Xfusionsolution\Controledeacesso\Reminders\ModelReminder';

    /**
     * The Eloquent throttling model name.
     *
     * @var string
     */
    protected static $throttlingModel = 'Xfusionsolution\Controledeacesso\Throttling\ModelThrottle';

    /**
     * Returns an array of login column names.
     *
     * @return array
     */
    public function getLoginNames()
    {
        return $this->loginNames;
    }

    /**
     * Returns the perfis relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function perfis()
    {
        return $this->belongsToMany(static::$perfisModel, 'perfil_usuarios', 'usuario_id', 'perfil_id')->withTimestamps();
    }

    /**
     * Returns the persistencias relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function funcionalidades()
    {
        return $this->belongsToMany(static::$funcionalidadesModel, 'perfil_usuarios', 'usuario_id', 'perfil_id')->withTimestamps();
    }

    /**
     * Returns the persistencias relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function persistencias()
    {
        return $this->hasMany(static::$persistenciasModel, 'usuario_id');
    }

    /**
     * Returns the activations relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activations()
    {
        return $this->hasMany(static::$activationsModel, 'usuario_id');
    }

    /**
     * Returns the reminders relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reminders()
    {
        return $this->hasMany(static::$remindersModel, 'usuario_id');
    }

    /**
     * Returns the throttle relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function throttle()
    {
        return $this->hasMany(static::$throttlingModel, 'usuario_id');
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
    public function getPerfis()
    {
        return $this->perfis;
    }

    /**
     * {@inheritDoc}
     */
    public function inPerfil($perfil)
    {
        foreach ($this->perfis as $instance) {
            if ($perfil instanceof PerfilInterface) {
                return $instance->getPerfilId() === $perfil->getPerfilId();
            }

            if ($instance->getPerfilId() == $perfil || $instance->getPerfislug() == $perfil) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function generatePersistenciaCode()
    {
        return str_random(32);
    }

    /**
     * {@inheritDoc}
     */
    public function getUsuarioId()
    {
        return $this->getKey();
    }

    /**
     * {@inheritDoc}
     */
    public function getPersistenciasId()
    {
        return $this->getKey();
    }

    /**
     * {@inheritDoc}
     */
    public function getPersistenciasKey()
    {
        return $this->persistenciasKey;
    }

    /**
     * {@inheritDoc}
     */
    public function setPersistenciasKey($key)
    {
        $this->persistenciasKey = $key;
    }

    /**
     * {@inheritDoc}
     */
    public function setPersistenciasRelationship($persistenciasRelationship)
    {
        $this->persistenciasRelationship = $persistenciasRelationship;
    }

    /**
     * {@inheritDoc}
     */
    public function getPersistenciasRelationship()
    {
        return $this->persistenciasRelationship;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsuarioLogin()
    {
        return $this->getAttribute($this->getUsuarioLoginName());
    }

    /**
     * {@inheritDoc}
     */
    public function getUsuarioLoginName()
    {
        return reset($this->loginNames);
    }

    /**
     * {@inheritDoc}
     */
    public function getUsuarioPassword()
    {
        return $this->password;
    }

    /**
     * Returns the perfis model.
     *
     * @return string
     */
    public static function getPerfisModel()
    {
        return static::$perfisModel;
    }

    /**
     * Sets the perfis model.
     *
     * @param  string  $perfisModel
     * @return void
     */
    public static function setPerfisModel($perfisModel)
    {
        static::$perfisModel = $perfisModel;
    }

    /**
     * Sets the funcionalidades model.
     *
     * @param  string  $funcionalidadesModel
     * @return void
     */
    public static function setFuncionalidadesModel($funcionalidadesModel)
    {
        static::$funcionalidadesModel = $funcionalidadesModel;
    }

    /**
     * Returns the persistencias model.
     *
     * @return string
     */
    public static function getPersistenciasModel()
    {
        return static::$persistenciasModel;
    }

    /**
     * Sets the persistencias model.
     *
     * @param  string  $persistenciasModel
     * @return void
     */
    public static function setPersistenciasModel($persistenciasModel)
    {
        static::$persistenciasModel = $persistenciasModel;
    }

    /**
     * Returns the activations model.
     *
     * @return string
     */
    public static function getActivationsModel()
    {
        return static::$activationsModel;
    }

    /**
     * Sets the activations model.
     *
     * @param  string  $activationsModel
     * @return void
     */
    public static function setActivationsModel($activationsModel)
    {
        static::$activationsModel = $activationsModel;
    }

    /**
     * Returns the reminders model.
     *
     * @return string
     */
    public static function getRemindersModel()
    {
        return static::$remindersModel;
    }

    /**
     * Sets the reminders model.
     *
     * @param  string  $remindersModel
     * @return void
     */
    public static function setRemindersModel($remindersModel)
    {
        static::$remindersModel = $remindersModel;
    }

    /**
     * Returns the throttling model.
     *
     * @return string
     */
    public static function getThrottlingModel()
    {
        return static::$throttlingModel;
    }

    /**
     * Sets the throttling model.
     *
     * @param  string  $throttlingModel
     * @return void
     */
    public static function setThrottlingModel($throttlingModel)
    {
        static::$throttlingModel = $throttlingModel;
    }

    /**
     * {@inheritDoc}
     */
    public function delete()
    {
        $isSoftDeleted = array_key_exists('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this));

        if ($this->exists && ! $isSoftDeleted) {
            $this->activations()->delete();
            $this->persistencias()->delete();
            $this->reminders()->delete();
            $this->perfis()->detach();
            $this->funcionalidades()->delete();
            $this->throttle()->delete();
        }

        return parent::delete();
    }

    /**
     * Dynamically pass missing methods to the usuario.
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

            return call_usuario_func_array([$permissoes, $method], $parameters);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * Creates a permissoes object.
     *
     * @return \Xfusionsolution\Controledeacesso\Permissoes\PermissoesInterface
     */
    protected function createPermissoes()
    {
        $usuarioPermissoes = $this->permissoes;

        $perfilPermissoes = [];

        foreach ($this->perfis as $perfil) {
            $perfilPermissoes[] = $perfil->permissoes;
        }

        return new static::$permissoesClass($usuarioPermissoes, $perfilPermissoes);
    }
}
