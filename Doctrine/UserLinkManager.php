<?php

namespace Da\OAuthServerBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserInterface;

/**
 * The manager for the user link.
 *
 * @author Thomas Prelot
 */
class UserLinkManager
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
     * Create a user link from criteria.
     *
     * @param UserInterface $user  The user.
     * @param string        $key   The key.
     * @param string        $value The value.
     *
     * @return \Da\OAuthServerBundle\Entity\UserLink|null The user link.
     */
    public function createUserLink(UserInterface $user, $key, $value)
    {
        $userLink = new $this->class();

        $userLink
            ->setUser($user)
            ->setKey($key)
            ->setValue($value)
        ;

        try {
            $this->objectManager->persist($userLink);
            $this->objectManager->flush();
        // Handle case of an already associated key for the user.
        } catch (\Exception $e) {
            return null;
        }

        return $userLink;
    }

    /**
     * Find a user link from criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return \Da\OAuthServerBundle\Entity\UserLink|null The user link.
     */
    public function findUserLinkBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Find user links from criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return array The user links.
     */
    public function findUserLinksBy(array $criteria = array())
    {
        return $this->repository->findBy($criteria);
    }
}