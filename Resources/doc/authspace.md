Define a new authspace
======================

Step 1: Add a route for the login check of the authspace
--------------------------------------------------------

```yaml
# app/config/routing.yml

login_check_foo:
    pattern: /oauth/v2/auth/foo/login_check
```

Step 2: Add a firewall for the authspace
----------------------------------------

```yaml
# app/config/security.yml

security:
    firewalls:
        ...

        oauth_authorize_foo:
            pattern: ^/oauth/v2/auth/foo
            form_login:
                provider:      fos_userbundle
                csrf_provider: form.csrf_provider
                login_path:    /login/foo
                check_path:    /oauth/v2/auth/foo/login_check
            logout:
                path:   /oauth/v2/auth/foo/logout
                target: /logout_redirect
                invalidate_session: false
```