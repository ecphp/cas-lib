Tests, code quality and code style
==================================

Every time changes are introduced into the library, the continuous integration
system run and validate the tests.

A PHP quality tool, Grumphp_, is used to orchestrate all these tasks at each
commit on the local machine, but also on the continuous integration tool in use.

To run the tests locally:

.. code-block:: bash

    composer grumphp

.. _ecphp/php-conventions: https://github.com/ecphp/php-conventions
.. _Grumphp: https://github.com/phpro/grumphp
