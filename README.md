DaOAuthServerBundle
===================

DaOAuthServerBundle is a Symfony2's bundle allowing to use the oauth mechanism in different "authspaces".

Installation
------------

Installation is a quick 4 steps process!

### Step 1: Add in composer

Add the bundle and its dependencies in the composer.json file:

``` js
// composer.json

"require": {
    // ...
    "friendsofsymfony/oauth-server-bundle": "dev-master",
    "friendsofsymfony/user-bundle": "~2.0@dev",
    "da/auth-common-bundle": "dev-master",
    "da/oauth-server-bundle": "dev-master"
},
```

Update your vendors:

``` bash
composer update      # WIN
composer.phar update # LINUX
```

### Step 2: Declare in the kernel

Declare the bundles in your kernel:
DO NOT invert the FOSUserBundle and DaOAuthServerBundle declaration order (bad hack, sorry).

``` php
// app/AppKernel.php

$bundles = array(
    // ...
        new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
        new FOS\UserBundle\FOSUserBundle(),
        new Da\AuthCommonBundle\DaAuthCommonBundle(),
        new Da\OAuthServerBundle\DaOAuthServerBundle(),
);
```

### Step 3: Set the config

Here is the minimal config you will need to use the bundle:

``` yaml
# app/config/config.yml

# FOSAuthServer Configuration
fos_oauth_server:
    db_driver:           orm
    client_class:        Da\OAuthServerBundle\Entity\Client
    access_token_class:  Da\OAuthServerBundle\Entity\AccessToken
    refresh_token_class: Da\OAuthServerBundle\Entity\RefreshToken
    auth_code_class:     Da\OAuthServerBundle\Entity\AuthCode
    service:
        user_provider: da_oauth_server.user_provider.authspace_email

# FOSUser Configuration
fos_user:
    db_driver: orm
    firewall_name: oauth_authorize
    user_class: Da\OAuthServerBundle\Entity\User
    profile:
        form:
            validation_groups:  [AuthspaceProfile, Default]
    registration:
        form:
            validation_groups:  [AuthspaceRegistration, Default]

# DaOSAuthServer Configuration
da_oauth_server:
    authspace_class: Da\OAuthServerBundle\Entity\AuthSpace

# To well display the serialized_array type
twig:
    form:
        resources:
            - 'DaOAuthServerBundle:Form:fields.html.twig'
```

### Step 4: Import the routing

You have to import some routes in order to run the bundle:

``` yaml
# app/config/routing.yml

# DaOAuthServer Routes
da_oauth_server:
    type: rest
    resource: "@DaOAuthServerBundle/Resources/config/routing.yml"
    prefix: /

fos_oauth_server_token:
    resource: "@FOSOAuthServerBundle/Resources/config/routing/token.xml"

login_check_api:
    pattern: /oauth/v2/auth/api/login_check

# FOSUser Routes
fos_user_profile:
    resource: "@FOSUserBundle/Resources/config/routing/profile.xml"
    prefix: /profile

fos_user_register:
    resource: "@FOSUserBundle/Resources/config/routing/registration.xml"
    prefix: /register

fos_user_resetting:
    resource: "@FOSUserBundle/Resources/config/routing/resetting.xml"
    prefix: /resetting

fos_user_change_password:
    resource: "@FOSUserBundle/Resources/config/routing/change_password.xml"
    prefix: /profile
```

### Step5: Set the security

``` yaml
# app/config/security.yml

security:
    session_fixation_strategy: none # If you want to use the proxy login/registration (do the same on the client side)

    encoders:
        FOS\UserBundle\Model\UserInterface: sha512

    providers:
        fos_userbundle:
            id: da_oauth_server.user_provider.authspace_email #fos_user.user_provider.username

    firewalls:
        dev:
            pattern:  ^/(_(profiler|wdt)|css|images|js)/
            security: false

        login:
            pattern:   ^/login
            anonymous: ~

        logout_redirect:
            pattern:  ^/logout_redirect$
            security: false

        oauth_token:
            pattern:  ^/oauth/v2/token
            security: false

        disconnect:
            pattern:  ^/oauth/v2/disconnect
            security: false

        logout:
            pattern:  ^/oauth/v2/logout
            security: false

        oauth:
            pattern:    ^/oauth/v2/auth$
            anonymous:  ~

        client_api:
            pattern:   ^/api/clients
            da_api:    true
            stateless: true

        user_api:
            pattern:   ^/api/users
            da_api:    true
            stateless: true

        api:
            pattern:    ^/api
            fos_oauth:  true
            stateless:  true

        oauth_authorize_api:
            pattern: ^/oauth/v2/auth/api
            form_login:
                provider:      fos_userbundle
                csrf_provider: form.csrf_provider
                login_path:    /login/api
                check_path:    /oauth/v2/auth/api/login_check
            logout:
                path:   /oauth/v2/auth/api/logout
                target: /logout_redirect
                # BUG: https://github.com/sensiolabs/SensioDistributionBundle/commit/2a518e7c957b66c9478730ca95f67e16ccdc982b
                invalidate_session: false
            anonymous:  ~

    access_control:
        - { path: ^/login, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/register, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/resetting, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/oauth/v2/auth$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/oauth/v2/auth/(\w|_|-)+/disconnect$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/, role: IS_AUTHENTICATED_FULLY }
```

Use the oauth mechanism to protect your API
-------------------------------------------

``` yaml
# app/config/security.yml

security:
    firewalls:
        ...

        api:
            pattern:    ^/api
            fos_oauth:  true
            stateless:  true

    access_control:
        # ...
        - { path: ^/api, role: IS_AUTHENTICATED_FULLY }
```

Define a second authspace
-------------------------

### Step1: Add a route for the login check of the authspace

``` yaml
# app/config/routing.yml

login_check_white_brand_client:
    pattern: /oauth/v2/auth/white_brand_client/login_check
```

### Step2: Add a firewall for the authspace

``` yaml
# app/config/security.yml

security:
    firewalls:
        ...

        oauth_authorize_white_brand_client:
            pattern: ^/oauth/v2/auth/white_brand_client
            form_login:
                provider:      fos_userbundle
                csrf_provider: form.csrf_provider
                login_path:    /login/white_brand_client
                check_path:    /oauth/v2/auth/white_brand_client/login_check
            logout:
                path:   /oauth/v2/auth/white_brand_client/logout
                target: /logout_redirect
                # BUG: https://github.com/sensiolabs/SensioDistributionBundle/commit/2a518e7c957b66c9478730ca95f67e16ccdc982b
                invalidate_session: false
```

Create a test sets
------------------

### Step1: Add Doctrine fixtures to composer

Add the bundle and its dependencies in the composer.json file:

``` js
// composer.json

"require": {
	// ...
    "doctrine/data-fixtures": "dev-master",
    "doctrine/doctrine-fixtures-bundle": "dev-master"
},
```

Update your vendors:

``` bash
composer update      # WIN
composer.phar update # LINUX
```

### Step2: Add Doctrine fixtures to the kernel

``` php
// app/AppKernel.php

$bundles = array(
    // ...
        new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
);
```

### Step3: Build your test sets classes 

The authspace:

``` php
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

``` php
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

``` php
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

### Step4: Set your fixtures in database

Use the following command for that (do that only on a dev environment):

``` bash
php app/console doctrine:fixtures:load
```

Other Considerations
--------------------

* You must have set a database to store the data for the oauth mechanism and run a php app/console doctrine:schema:update if you use the ORM.

Suggestion
----------

Maybe you would like to communicate from API to API or from Client to API without accessing a user protected data (so without oauth)?
Take a look at the [DaApiServerBundle](https://github.com/Gnuckorg/DaApiServerBundle)!
