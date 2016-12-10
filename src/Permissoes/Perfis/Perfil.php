<?php

namespace Xfusionsolution\Controledeacesso\Permissoes\Perfis;

use Illuminate\Database\Eloquent\Model;
use DB;

class Perfil extends Model  {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'seg_perfis';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $guarded = ['PER_INT_ID_PERFIL'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'PER_INT_ID_PERFIL';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'seguranca';



}
