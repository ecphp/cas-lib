Installation
============

The easiest way to install it is through Composer_

.. code-block:: bash

    composer require ecphp/cas-lib

Based on the context this package is used, you might also need to install
a package which provides `PSR7 implementations`_.

There are `many packages implementing PSR7`_, you can pick the one you prefer,
exemple:

.. code-block:: bash

    composer require nyholm/psr7

Next, you'll need an implementation of PSR17_. PSR17 provides the required
factories for the HTTP protocol. In order to facilitate the customizations,
you can either implements your own PSR17 implementation or use `loophp/psr17`_
which provides a default one:

.. code-block:: bash

    composer require loophp/psr17

.. _Composer: https://getcomposer.org
.. _PSR7 implementations: https://www.php-fig.org/psr/psr-7/
.. _many packages implementing PSR7: https://packagist.org/providers/psr/http-message-implementation
.. _PSR17: https://www.php-fig.org/psr/psr-17/
.. _`loophp/psr17`: https://github.com/loophp/psr17