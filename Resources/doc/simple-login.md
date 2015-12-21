Make login looking like simple non oauth login
==============================================

Step 1: Disable manual authorization step
-----------------------------------------

It is possible to disable the manual authorization for trusted client setting the column `trusted` of table `Client` to true.

Step 2: Configure client login path
-----------------------------------

It is possible to define a login path in order to forward the authentication form handling (not the authentication) to the client setting the column `client_login_path` of table `Client` to a URL path value (relative to the client) like, for instance, `/login/fwd`.