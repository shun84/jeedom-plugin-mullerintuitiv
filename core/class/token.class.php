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
        log::add('mullerintuitiv','debug','Avant expires_in');
        log::add('mullerintuitiv','debug','Time expires_in '.config::byKey('expires_in','mullerintuitiv'));
        log::add('mullerintuitiv','debug','Time '.time());

        if (config::byKey('expires_in','mullerintuitiv') <= time()){
            $refreshtoken = $mullerintuitivApi->getRefreshToken(config::byKey('refresh_token','mullerintuitiv'));
            log::add('mullerintuitiv','debug','If expires_in');
            $refreshtokens = json_decode($refreshtoken->getBody()->getContents(), true);
            config::save('access_token',$refreshtokens['access_token'],'mullerintuitiv');
            config::save('refresh_token',$refreshtokens['refresh_token'],'mullerintuitiv');
            config::save('expires_in', time()+$refreshtokens['expires_in'],'mullerintuitiv');
        } else {
            log::add('mullerintuitiv','debug','Else');
            config::remove('access_token','mullerintuitiv');
        }

        return config::byKey('access_token','mullerintuitiv');
    }
}