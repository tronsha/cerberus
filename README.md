Cerberus
========

Cerberus is an IRC bot written for PHP

Version: 1.4 Development

[![Build Status](https://travis-ci.org/tronsha/cerberus.svg?branch=master)](https://travis-ci.org/tronsha/cerberus)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/6117c02a-7ed1-4502-86de-8065c68098ae/mini.png)](https://insight.sensiolabs.com/projects/6117c02a-7ed1-4502-86de-8065c68098ae)

## Install

### Use Composer

If you don't have Composer yet, download it following the instructions on [getcomposer.org][5] or just run the following command:

    curl -s http://getcomposer.org/installer | php

Then, use the `create-project` command to generate a new project:

    php composer.phar create-project tronsha/cerberus --stability=dev
    
The Composer installation is without Webchat Frontend

### Clone from Github

Clone the projects `Cerberus` and `Hades` form Github:

    git clone https://github.com/tronsha/cerberus.git
    git clone https://github.com/tronsha/hades.git
    
Change to the cerberus directory:

    cd cerberus

Then, use the `install` command:

    php composer.phar install
    
## Require

* PHP 5.4.0 or greater. 
* For the Hades Frontend: PHP 5.5.0 or greater.
* A Database. MySQL or PostgreSQL.

## Libraries

* [Doctrine Database Abstraction Layer 2.5.*][6]
* [Symfony Console Component 2.7.*][7]

## Creator

**Stefan Hüsges**

:computer: [Homepage][1]

:octocat: [GitHub][2]

## Thanks

**Daniel Basten**

:octocat: [GitHub][3]

## License
[![GNU General Public License](http://www.gnu.org/graphics/gplv3-127x51.png)][4]

[1]: http://www.mpcx.net
[2]: https://github.com/tronsha
[3]: https://github.com/axhm3a
[4]: http://www.gnu.org/licenses/gpl-3.0
[5]: http://getcomposer.org/
[6]: http://www.doctrine-project.org/projects/dbal.html
[7]: http://symfony.com/components/Console
[8]: https://github.com/symfony/symfony/pull/13607
