#! /bin/bash

DEPLOYMENT_ENV = $1

composer update
COMPOSER_STATUS = $?

if [[ $COMPOSER_STATUS -eq 1 ]]
then
    php composer.phar update
    COMPOSER_STATUS=$?
fi

if [[ $COMPOSER_STATUS -eq 1 ]]
then
    echo "Unable to update vendors. Make sure 'composer update' or 'php composer.phar update' works."
    exit
fi

PHPUNIT_OUTPUT = $(bin/phpunit -c app)
PHPUNIT_STATUS = $?

if [[ PHPUNIT_STATUS -eq 0 ]]
then
    if [[ $RESULT =~ FAILURES ]]
    then
        echo "PHPUnit testing failed.  Please review the output and try again"
    else
        echo "No PHPUnit failures detected.  Deploying to Elastic Beanstalk."
        git aws.push --environment $DEPLOYMENT_ENV
    fi
else
    echo "Unable to initialize PHPUnit."
    exit
fi