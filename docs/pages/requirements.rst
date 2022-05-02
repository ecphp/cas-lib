Requirements
============

PHP
---

PHP >= 7.4 is required for this library.

PHP Extensions
--------------

- json
- libxml
- simplexml

Packages
--------

In order to get the CAS Lib library running, you will require some dependencies.

To give a maximum freedom to the users using CAS Lib, each required dependencies is a well defined standardized PHP
class.

+------------------+-----------+---------------------------------+------------------------+
| Dependency       | PSR       | Implementations                 | Example package        |
+==================+===========+=================================+========================+
| Cache            | `PSR-6`_  | `cache-implementation`_         | `symfony/cache`_       |
+------------------+-----------+---------------------------------+------------------------+
| HTTP Message     | `PSR-7`_  | `http-message-implementations`_ | `nyholm/psr7`_         |
+------------------+-----------+---------------------------------+------------------------+
| HTTP Factory     | `PSR-17`_ | `http-factory-implementations`_ | `loophp/psr17`_        |
+------------------+-----------+---------------------------------+------------------------+
| HTTP Client      | `PSR-18`_ | `http-client-implementations`_  | `symfony/http-client`_ |
+------------------+-----------+---------------------------------+------------------------+

You are free to use any package you want, as long as they are implementing the proper requirement.

CAS Lib only returns standardized HTTP responses, you will need to emit the response back to the client.

You may use custom code for that, but you can also use any of the following packages for this

-  `zendframework/zend-httphandlerrunner`_
-  `http-interop/response-sender`_

.. _zendframework/zend-httphandlerrunner: https://packagist.org/packages/zendframework/zend-httphandlerrunner
.. _http-interop/response-sender: https://packagist.org/packages/http-interop/response-sender
.. _nyholm/psr7: https://packagist.org/packages/nyholm/psr7
.. _loophp/psr17: https://packagist.org/packages/loophp/psr17
.. _symfony/cache: https://packagist.org/packages/symfony/cache
.. _symfony/http-client: https://packagist.org/packages/symfony/http-client
.. _cache-implementation: https://packagist.org/providers/psr/cache-implementation
.. _http-client-implementations: https://packagist.org/providers/psr/http-client-implementation
.. _http-factory-implementations: https://packagist.org/providers/psr/http-factory-implementation
.. _http-message-implementations: https://packagist.org/providers/psr/http-message-implementation
.. _PSR-17: https://www.php-fig.org/psr/psr-17/
.. _PSR-18: https://www.php-fig.org/psr/psr-18/
.. _PSR-6: https://www.php-fig.org/psr/psr-6/
.. _PSR-7: https://www.php-fig.org/psr/psr-7/
