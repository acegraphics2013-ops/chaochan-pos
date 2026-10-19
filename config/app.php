<?php
return [
'name'=>env('APP_NAME','ChaoChan POS'), 'env'=>env('APP_ENV','production'),
'debug'=>(bool)env('APP_DEBUG',false), 'url'=>env('APP_URL','http://localhost'),
'public_url'=>env('APP_PUBLIC_URL'),
'timezone'=>env('APP_TIMEZONE','Asia/Manila'), 'locale'=>env('APP_LOCALE','en'),
'fallback_locale'=>env('APP_FALLBACK_LOCALE','en'), 'faker_locale'=>env('APP_FAKER_LOCALE','en_PH'),
'key'=>env('APP_KEY'), 'cipher'=>'AES-256-CBC',
'previous_keys'=>array_filter(explode(',',env('APP_PREVIOUS_KEYS',''))),
'maintenance'=>['driver'=>env('APP_MAINTENANCE_DRIVER','file'),'store'=>env('APP_MAINTENANCE_STORE','database')]
];
