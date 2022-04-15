Configuration
=============

.. code:: yaml

   base_url: https://casserver.herokuapp.com/cas
   protocol:
     login:
       path: /login
     serviceValidate:
       path: /p3/serviceValidate
       default_parameters:
         pgtUrl: https://my-app/casProxyCallback
     logout:
       path: /logout
       default_parameters:
         service: https://my-app/homepage
     proxy:
       path: /proxy
     proxyValidate:
       path: /proxyValidate
       default_parameters:
         pgtUrl: https://my-app/casProxyCallback