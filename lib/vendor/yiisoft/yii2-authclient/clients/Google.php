<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * Google allows authentication via Google OAuth.
 *
 * In order to use Google OAuth you must create a project at <https://console.developers.google.com/project>
 * and setup its credentials at <https://console.developers.google.com/project/[yourProjectId]/apiui/credential>.
 * In order to enable using scopes for retrieving user attributes, you should also enable Google+ API at
 * <https://console.developers.google.com/project/[yourProjectId]/apiui/api/plus>
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'google' => [
 *                 'class' => 'yii\authclient\clients\Google',
 *                 'clientId' => 'google_client_id',
 *                 'clientSecret' => 'google_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ```
 *
 * @see https://console.developers.google.com/project
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Google extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://accounts.google.com/o/oauth2/auth';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://accounts.google.com/o/oauth2/token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://www.googleapis.com/plus/v1';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->scope === null) {
            $this->scope = implode(' ', [
                'profile',
                'email',
            ]);
        }
    }
    
    public static function getAddonSettings(){
        return [
        'recaptcha' => [
                'RECAPTCHA_PUBLIC_KEY' => ['value' => '', 'description' => 'Public key'],
                'RECAPTCHA_SECRET_KEY' => ['value' => '', 'description' => 'Secret Key'],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        return $this->api('people/me', 'GET');
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'google';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Google';
    }
    
    public function prepareAttributes($attributes){
        $prepared = [];
        if (is_array($attributes)){
            $prepared['gender'] = @$attributes['gender'] == 'male'?'m':'f';
            $prepared['email'] = @$attributes['emails'][0]['value'];
            $prepared['firstname'] = @$attributes['name']['givenName'];
            $prepared['lastname'] = @$attributes['name']['familyName'];
            $prepared['avatar'] = @$attributes['image']['url'];
        }
        
        return $prepared;
    }
}
