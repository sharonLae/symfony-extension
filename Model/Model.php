<?php

namespace Yanyabo\SymfonyBundle\Model;

use Doctrine\ORM\EntityRepository;

class Model extends EntityRepository
{
    /**
     * 自定义 model 对应的 entity
     */
    public $entity = '';

    public $_pars = [];

    public function __construct($container, $class = null)
    {
        $nameSpace  = explode('\\', get_class($this));
        $className  = array_pop($nameSpace);
        $entityName = substr($className, 0 ,strlen($className)-5);

        $this->_entityName = $this->entity ? $this->entity : '\\'.$nameSpace[0].'\\'.$nameSpace[1].'\\Entity\\'.$entityName;
        $this->_container  = $container;
        $this->_em         = $this->_container->get('doctrine')->getManager();
        $this->_class      = $this->_em->getClassMetadata($this->_entityName);
        $this->_qb         = $this->_em->createQueryBuilder();
    }

    /**
     * 用于分页
     * 
     * @param query $query   具体查询的 query 对象
     * @param int   $perPage 每页的 item 数量
     * @return pagination    knp bundle 的 pagination 对象，可以获取 items 和 view
     */
    protected function getPagination($perPage)
    {
        $page = $this->_container->get('request')->get('page', 1);

        $paginator = $this->_container->get('knp_paginator');

        $query = $this->_qb->setParameters($this->_pars)->getQuery();

        return $paginator->paginate($query, $page, $perPage, array('distinct' => false));
    }

    protected function getResults($max = null)
    {
        return $this->_qb->getQuery()
                    ->useResultCache(true, 300)
                    ->useQueryCache(true)
                    ->setParameters($this->_pars)
                    ->setMaxResults($max)
                    ->getResult();
    }

    protected function getOneResult()
    {
        return $this->_qb->getQuery()
                    ->useResultCache(true, 300)
                    ->useQueryCache(true)
                    ->setParameters($this->_pars)
                    ->setMaxResults(1)
                    ->getOneOrNullResult();
    }
}