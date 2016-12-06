<?php

namespace Controledeacesso\Funcionalidades;

use Illuminate\Database\Eloquent\Model;

class ModelFuncionalidade extends Model  {

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

}
