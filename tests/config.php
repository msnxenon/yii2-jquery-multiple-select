<?php

return [
    'id' => 'test-app',
    'basePath' => __DIR__,
    'vendorPath' => dirname(dirname(YII2_PATH)),
    'components' => [
        'request' => [
            'class' => 'yii\jquery\multipleselect\tests\Request',
            'enableCookieValidation' => false
        ]
    ]
];
