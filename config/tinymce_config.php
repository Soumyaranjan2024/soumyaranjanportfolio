<?php
// config/tinymce_config.php

if (!defined('TINY_MCE_API_KEY')) {
    define('TINY_MCE_API_KEY', '2743fj4ssjr5zt3t2yojcyf98y01tcij4xi1j9fqo35iz6mo');
}

function getTinyMceConfig()
{
    return [
        'api_key' => TINY_MCE_API_KEY,
        'script_url' => "https://cdn.tiny.cloud/1/" . TINY_MCE_API_KEY . "/tinymce/6/tinymce.min.js"
    ];
}
?>
