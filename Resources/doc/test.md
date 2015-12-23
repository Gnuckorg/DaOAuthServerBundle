Create a test sets
==================

Step 1: Add Doctrine fixtures to composer
-----------------------------------------

Add the bundle and its dependencies in the composer.json file:

```js
// composer.json

"require": {
    // ...
    "doctrine/data-fixtures": "dev-master",
    "doctrine/doctrine-fixtures-bundle": "dev-master"
},
```

Update your vendors:

```sh
composer update
```

Step 2: Add Doctrine fixtures to the kernel
-------------------------------------------

```php
// app/AppKernel.php

$bundles = array(
    // ...
        new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
);
```

Step 3: Build your test sets classes
------------------------------------

The authspace:

```php
# /src/My/OwnBundle/DataFixtures/ORM/LoadAuthSpaceData.php

namespace My\OwnBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Da\OAuthServerBundle\Entity\AuthSpace;

class LoadAuthSpaceData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $authSpace = new AuthSpace();
        $authSpace
            ->setId(1)
            ->setCode('api')
            ->setName('API')
        ;
        $manager->persist($authSpace);
        $this->addReference('authspace_api', $authSpace);

        $authSpace = new AuthSpace();
        $authSpace
            ->setId(2)
            ->setCode('white_brand_client')
            ->setName('Client')
        ;
        $manager->persist($authSpace);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}

```

The client:

```php
# /src/My/OwnBundle/DataFixtures/ORM/LoadClientData.php

namespace My\OwnBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Da\OAuthServerBundle\Entity\Client;

class LoadClientData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->createClient();
        $client->setName('API');
        $client->setRedirectUris(array('http://my-client-domain'));
        $client->setAllowedGrantTypes(array('token', 'authorization_code'));
        $client->setAuthSpace($this->getReference('authspace_api'));
        $clientManager->updateClient($client);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
```

The user:

```php
# /src/My/OwnBundle/DataFixtures/ORM/LoadUserData.php

namespace My\OwnBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Da\OAuthServerBundle\Entity\User;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();

        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $password = $encoder->encodePassword('superSecurisedPassword', $user->getSalt());

        $user
            ->setUsername('me')
            ->setPassword($password)
            ->setEmail('my@email.com')
            ->setEnabled(true)
            ->setAuthSpace($this->getReference('authspace_api'))
            ->setRawData(array(
                'firstName' => 'John',
                'lastName' => 'Doe'
            ))
        ;
        $manager->persist($user);
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
```

Step 4: Set your fixtures in database
-------------------------------------

Use the following command for that (do that only on a dev environment):

```sh
php app/console doctrine:fixtures:load
```