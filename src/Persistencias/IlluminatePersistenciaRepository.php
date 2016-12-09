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

use Xfusionsolution\Controledeacesso\Cookies\CookieInterface;
use Xfusionsolution\Controledeacesso\Persistencias\PersisciasInterface;
use Xfusionsolution\Controledeacesso\Sessions\SessionInterface;
use Cartalyst\Support\Traits\RepositoryTrait;

class IlluminatePersistenciaRepository implements PersistenciaRepositoryInterface
{
    use RepositoryTrait;

    /**
     * Single session.
     *
     * @var boolean
     */
    protected $single = false;

    /**
     * Session storage driver.
     *
     * @var \Xfusionsolution\Controledeacesso\Sessions\SessionInterface
     */
    protected $session;

    /**
     * Cookie storage driver.
     *
     * @var \Xfusionsolution\Controledeacesso\Cookies\CookieInterface
     */
    protected $cookie;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = 'Xfusionsolution\Controledeacesso\Persistencias\ModelPersistencia';

    /**
     * Create a new Sentinel persistencia repository.
     *
     * @param  \Xfusionsolution\Controledeacesso\Sessions\SessionInterface  $session
     * @param  \Xfusionsolution\Controledeacesso\Cookies\CookieInterface  $cookie
     * @param  string  $model
     * @param  bool  $single
     * @return void
     */
    public function __construct(
        SessionInterface $session,
        CookieInterface $cookie,
        $model = null,
        $single = false
    ) {
        if (isset($model)) {
            $this->model = $model;
        }

        if (isset($session)) {
            $this->session = $session;
        }

        if (isset($cookie)) {
            $this->cookie  = $cookie;
        }

        $this->single = $single;
    }

    /**
     * {@inheritDoc}
     */
    public function check()
    {
        if ($code = $this->session->get()) {
            return $code;
        }

        if ($code = $this->cookie->get()) {
            return $code;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findByPersistenciaCode($code)
    {
        $persistencia = $this->createModel()
            ->newQuery()
            ->where('code', $code)
            ->first();

        return $persistencia ? $persistencia : false;
    }

    /**
     * {@inheritDoc}
     */
    public function findUserByPersistenciaCode($code)
    {
        $persistencia = $this->findByPersistenciaCode($code);

        return $persistencia ? $persistencia->user : false;
    }

    /**
     * {@inheritDoc}
     */
    public function persist(PersistenciasInterface $persistencias, $remember = false)
    {
        if ($this->single) {
            $this->flush($persistencias);
        }

        $code = $persistencias->generatePersistenciaCode();

        $this->session->put($code);

        if ($remember === true) {
            $this->cookie->put($code);
        }

        $persistencia = $this->createModel();

        $persistencia->{$persistencias->getPersistenciasKey()} = $persistencias->getPersistenciasId();
        $persistencia->code = $code;

        return $persistencia->save();
    }

    /**
     * {@inheritDoc}
     */
    public function persistAndRemember(PersistenciasInterface $persistencias)
    {
        return $this->persist($persistencias, true);
    }

    /**
     * {@inheritDoc}
     */
    public function forget()
    {
        $code = $this->check();

        if ($code === null) {
            return;
        }

        $this->session->forget();
        $this->cookie->forget();

        return $this->remove($code);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($code)
    {
        return $this->createModel()
            ->newQuery()
            ->where('code', $code)
            ->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function flush(PersistenciasInterface $persistencias, $forget = true)
    {
        if ($forget) {
            $this->forget($persistencias);
        }

        foreach ($persistencias->{$persistencias->getPersistenciasRelationship()}()->get() as $persistencia) {
            if ($persistencia->code !== $this->check()) {
                $persistencia->delete();
            }
        }
    }
}
