<?php
$secondPassport = 'passport';
$secondSource = 'source';
$domain = DOMAIN;

return [
    'app_name' => 'Passport',

    'app_title' => '喀客通行',
    'app_description' => '喀客通行',
    'app_keywords' => '喀客通行',

    'passport_url' => "http://{$secondPassport}.{$domain}",
    'passport_source' => "http://{$secondSource}.{$domain}/kake/passport",

    'thrift_ip' => '106.14.65.39',
    'thrift_port' => '8888',

    'service_app_id' => 'kk_0c1afa4b1e9df99',
    'service_app_secret' => '3f687ba6a31fa9e38e4b608abab87e1c',
];