<?php
/**
 * Entry point for PHP connector, put your customizations here.
 *
 * @license     MIT License
 * @author      Pavel Solomienko <https://github.com/servocoder/>
 * @copyright   Authors
 */

// only for debug
// error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
// ini_set('display_errors', '1');

require_once 'vendor/autoload.php';
require_once __DIR__ . '/events.php';

// fix display non-latin chars correctly
// https://github.com/servocoder/RichFilemanager/issues/7
setlocale(LC_CTYPE, 'en_US.UTF-8');

// fix for undefined timezone in php.ini
// https://github.com/servocoder/RichFilemanager/issues/43
if(!ini_get('date.timezone')) {
    date_default_timezone_set('GMT');
}


// This function is called for every server connection. It must return true.
//
// Implement this function to authenticate the user, for example to check a
// password login, or restrict client IP address.
//
// This function only authorizes the user to connect and/or load the initial page.
// Authorization for individual files or dirs is provided by the two functions below.
//
// NOTE: If using session variables, the session must be started first (session_start()).
function fm_authenticate()
{
    // Customize this code as desired.
    return true;

    // If this function returns false, the user will just see an error.
    // If this function returns an array with "redirect" key, the user will be redirected to the specified URL:
    // return ['redirect' => 'http://domain.my/login'];
}


// This function is called before any filesystem read operation, where
// $filepath is the file or directory being read. It must return true,
// otherwise the read operation will be denied.
//
// Implement this function to do custom individual-file permission checks, such as
// user/group authorization from a database, or session variables, or any other custom logic.
//
// Note that this is not the only permissions check that must pass. The read operation
// must also pass:
//   * Filesystem permissions (if any), e.g. POSIX `rwx` permissions on Linux
//   * The $filepath must be allowed according to config['patterns'] and config['extensions']
//
function fm_has_read_permission($filepath)
{
    // Customize this code as desired.
    return true;
}


// This function is called before any filesystem write operation, where
// $filepath is the file or directory being written to. It must return true,
// otherwise the write operation will be denied.
//
// Implement this function to do custom individual-file permission checks, such as
// user/group authorization from a database, or session variables, or any other custom logic.
//
// Note that this is not the only permissions check that must pass. The write operation
// must also pass:
//   * Filesystem permissions (if any), e.g. POSIX `rwx` permissions on Linux
//   * The $filepath must be allowed according to config['patterns'] and config['extensions']
//   * config['read_only'] must be set to false, otherwise all writes are disabled
//
function fm_has_write_permission($filepath)
{
    // Customize this code as desired.
    return true;
}


$config = [];

// example to override the default config
//$config = [
//    'security' => [
//        'readOnly' => true,
//        'extensions' => [
//            'policy' => 'ALLOW_LIST',
//            'restrictions' => [
//                'jpg',
//                'jpe',
//                'jpeg',
//                'gif',
//                'png',
//            ],
//        ],
//    ],
//];
$config_s3 = [
    'logger' => [
        'enabled' => true,
        'file' => '/var/log/filemanager.log',
    ],
];
$config = [
    'security' => [
        'readOnly' => false,
        'extensions' => [
            'policy' => 'ALLOW_LIST',
            'restrictions' => [
                'jpg',
                'jpe',
                'jpeg',
                'gif',
               'png',
                'html',
            ],
        ],
    ],
];
$config_s3 = [
    'images' => [
        'thumbnail' => [
            'dir' => 's3_thumbs',
            'useLocalStorage' => false,
        ],
    ],
    'credentials' => [
        'region' => 'ap-south-1',
        'bucket' => 'meetfilemanager',
        'credentials' => [
            'key' => 'AKIAJURZGGKHQSRQ6ZHQ',
            'secret' =>'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',
        ],
        'defaultAcl' => \RFM\Repository\S3\StorageHelper::ACL_PUBLIC_READ,
        'debug' => false,
    ],
];
$config_s3['encryption'] = 'AES256';

$app = new \RFM\Application();

// uncomment to use events
//$app->registerEventsListeners();

//$local = new \RFM\Repository\Local\Storage($config);

//$local->setRoot('http://34.209.247.97/filemanager/connectors/php/filemanager.php?mode=readfile&path=/', false,true);
// example to setup files root folder

//$app->setStorage($local);

// set application API
//$app->api = new RFM\Api\LocalApi();
// AWS S3 storage instance
$s3 = new \RFM\Repository\S3\Storage($config_s3);
$app->setStorage($s3);
// AWS S3 API
$s3->setRoot('userfiles', true);
$app->api = new RFM\Api\AwsS3Api();
$app->run();
