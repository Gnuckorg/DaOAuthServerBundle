Install and use the bundle
==========================

Step 1: Add in composer
-----------------------

Add the bundle and its dependencies in the composer.json file:

```js
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

```sh
composer update
```

Step 2: Declare in the kernel
-----------------------------

Declare the bundles in your kernel:

```php
// app/AppKernel.php

$bundles = array(
    // ...
        new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
        new FOS\UserBundle\FOSUserBundle(),
        new Da\AuthCommonBundle\DaAuthCommonBundle(),
        new Da\OAuthServerBundle\DaOAuthServerBundle(),
);
```

Step 3: Set the config
----------------------

Here is the minimal config you will need to use the bundle:

```yaml
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

Step 4: Import the routing
--------------------------

You have to import some routes in order to run the bundle:

```yaml
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

Step 5: Set the security
------------------------

```yaml
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
            pattern:   ^/oauth/v2/auth$
            anonymous: ~

        client_api:
            pattern:   ^/api/clients
            da_api:    true
            stateless: true

        user_api:
            pattern:   ^/api/users
            da_api:    true
            stateless: true

        api:
            pattern:   ^/api
            fos_oauth: true
            stateless: true

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
        - { path: ^/api, role: IS_AUTHENTICATED_FULLY }
        - { path: ^/login, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/register, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/profile, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/resetting, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/oauth/v2/auth$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/oauth/v2/auth/(\w|_|-)+/disconnect$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/, role: IS_AUTHENTICATED_FULLY }
```

> The firewall `api` protects the SSO API with the oauth mechanism.
> The firewall `oauth_authorize_api` is used as a default authspace of name `api`.

Step 6: Build the database
--------------------------

You must set a database to store the data for the oauth mechanism. Do the following, if you want to use the standard ORM features:

```yaml
# app/config/config.yml

# ...

# Doctrine Configuration
doctrine:
    dbal:
        driver:   %database_driver%
        host:     %database_host%
        port:     %database_port%
        dbname:   %database_name%
        user:     %database_user%
        password: %database_password%
        charset:  UTF8
    orm:
        auto_generate_proxy_classes: %kernel.debug%
        auto_mapping: true
```

Define the `%...%` parameters in your `parameters.yml`. Then, run the command:

```sh
    php app/console doctrine:schema:update
```