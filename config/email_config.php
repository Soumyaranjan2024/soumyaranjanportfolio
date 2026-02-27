<?php
// config/email_config.php

define('EMAIL_PROVIDER', 'gmail');
define('GMAIL_USERNAME', 'soumyaranjanpadhi936@gmail.com');
define('GMAIL_APP_PASSWORD', 'tpvfzevkdebtuoyt'); // Your app password

// Production settings (for InfinityFree)
if (!defined('AUTO_REPLY_ENABLED')) define('AUTO_REPLY_ENABLED', true);
if (!defined('COMPANY_NAME')) define('COMPANY_NAME', 'Soumya Portfolio');
if (!defined('REPLY_TO_EMAIL')) define('REPLY_TO_EMAIL', 'soumyaranjanpadhi936@gmail.com');

function getEmailConfig()
{
    return [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => GMAIL_USERNAME,
        'password' => GMAIL_APP_PASSWORD,
        'from_email' => GMAIL_USERNAME,
        'from_name' => COMPANY_NAME,
        'reply_to' => REPLY_TO_EMAIL
    ];
}

function testEmailConfig()
{
    $config = getEmailConfig();
    return !empty($config['host']) &&
        !empty($config['username']) &&
        !empty($config['password']);
}
?>