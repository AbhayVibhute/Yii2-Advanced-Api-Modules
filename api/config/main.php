<?php
$params = array_merge(
    //require __DIR__ . '/../../common/config/params.php',
    //require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php'
    //require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\modules\v1\controllers',
    'bootstrap' => ['log', 'gii'],
    'modules' => [
      'v1' => [
        'basePath'=> '@app/modules/v1',
        'class' => 'api\modules\v1\Module',
      ],
      'gii' => [
        'class' => 'yii\gii\Module',
      ],
    ],
    'components' => [
      'request' => [
        'baseUrl' => '/projects/blog/api/modules/v1',
        'parsers' => [
          'application/json' => 'yii\web\JsonParser',
        ],
        'csrfParam' => '_csrf-backend',
        'cookieValidationKey' => 'ixKgOtICRZ2Iz7MRlQyAu3A6BDGi_iH_',
      ],
      'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-api', 'httpOnly' => true],            
        ],
      'authManager' => [
          'class' => 'yii\rbac\DbManager',
          'defaultRoles' => ['guest']
        ],  
      'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                  'class' => 'yii\rest\UrlRule', 
                  'pluralize'=> true,
                  'controller' => ['v1/login', 'v1/blog'],
                  'extraPatterns' => [
                    'GET login' => 'login',
                    'GET getData' => 'get-data',
                    'POST generate_token' => 'generate-token'
                  ],
                ]
                //['class' => 'yii\rest\UrlRule', 'controller' => 'customer'],
            ],
        ],
    ],
];
