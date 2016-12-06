<?php

namespace Controledeacesso\Permissoes\Grupos;

use Illuminate\Database\Eloquent\Model;

class GrupoPerfil extends Model  {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'seg_grupoperfil';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $guarded = ['GRP_INT_ID_GRUPO'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'GRP_INT_ID_GRUPO';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'seguranca';

}