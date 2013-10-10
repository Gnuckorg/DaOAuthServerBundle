<?php

namespace Da\OAuthServerBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Da\AuthCommonBundle\Model\AuthSpaceInterface;
use Da\AuthCommonBundle\Model\AuthSpaceManagerInterface;

/**
 * The manager for the authspaces.
 *
 * @author Thomas Prelot
 */
class AuthSpaceManager implements AuthSpaceManagerInterface
{
    protected $objectManager;
    protected $class;
    protected $repository;

    /**
     * Constructor.
     *
     * @param ObjectManager $om
     * @param string        $class
     */
    public function __construct(ObjectManager $om, $class)
    {
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function findAuthSpaceBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }
}