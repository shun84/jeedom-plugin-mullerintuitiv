<?php

use GuzzleHttp\Exception\GuzzleException;

require_once __DIR__ . '/../../core/api/mullerintuitivApi.php';

class token
{
    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function getSession(){
        $mullerintuitivApi = new mullerintuitivApi();
        $token = $mullerintuitivApi->getToken(config::byKey('login','mullerintuitiv'), config::byKey('mdp','mullerintuitiv'));
        $tokens = json_decode($token->getBody()->getContents(), true);
        config::save('access_token',$tokens['access_token'],'mullerintuitiv');
        config::save('refresh_token',$tokens['refresh_token'],'mullerintuitiv');
        config::save('expires_in', time()+$tokens['expires_in'],'mullerintuitiv');
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function getAccesToken(): string
    {
        $mullerintuitivApi = new mullerintuitivApi();
        if (config::byKey('access_token','mullerintuitiv') === ''){
            $this->getSession();
        }

        if (config::byKey('expires_in','mullerintuitiv') <= time()){
            $refreshtoken = $mullerintuitivApi->getRefreshToken(config::byKey('refresh_token','mullerintuitiv'));
            if ($refreshtoken->getStatusCode() !== 200){
                config::remove('access_token');
            }
            $refreshtokens = json_decode($refreshtoken->getBody()->getContents(), true);
            config::save('access_token',$refreshtokens['access_token'],'mullerintuitiv');
            config::save('refresh_token',$refreshtokens['refresh_token'],'mullerintuitiv');
            config::save('expires_in', time()+$refreshtokens['expires_in'],'mullerintuitiv');
        }

        return config::byKey('access_token','mullerintuitiv');
    }
}