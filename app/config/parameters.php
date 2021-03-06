<?php
if (empty($_SERVER['LISTSIO_ENV'])) {
    $container->setParameter('listsio.env', 'dev');
} else {
    $container->setParameter('listsio.env', $_SERVER['LISTSIO_ENV']);
}
if (! empty($_SERVER['IS_EBS_ENV'])) {
    $container->setParameter('database_host', $_SERVER['RDS_HOSTNAME']);
    $container->setParameter('database_port', $_SERVER['RDS_PORT']);
    $container->setParameter('database_name', $_SERVER['RDS_DB_NAME']);
    $container->setParameter('database_user', $_SERVER['RDS_USERNAME']);
    $container->setParameter('database_password', $_SERVER['RDS_PASSWORD']);
    $container->setParameter('twitter.key', $_SERVER['TWITTER_API_KEY']);
    $container->setParameter('twitter.secret', $_SERVER['TWITTER_API_SECRET']);
    $container->setParameter('facebook.api_id', $_SERVER['FACEBOOK_API_ID']);
    $container->setParameter('facebook.api_secret', $_SERVER['FACEBOOK_API_SECRET']);
    $container->setParameter('mailer_transport', $_SERVER['SYMFONY__MAILER_TRANSPORT']);
    $container->setParameter('mailer_host', $_SERVER['SYMFONY__MAILER_HOST']);
    $container->setParameter('mailer_password', $_SERVER['SYMFONY__MAILER_PASSWORD']);
    $container->setParameter('mailer_user', $_SERVER['SYMFONY__MAILER_USER']);
    $container->setParameter('secret', $_SERVER['SYMFONY__SECRET']);
}