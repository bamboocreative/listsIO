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
}
    // TODO: SMTP setup