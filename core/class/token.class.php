<?php

class token
{
    /**
     * @throws Exception
     */
    public static function getSession(): void
    {
        $mullerintuitivApi = new mullerintuitivApi();
        $token = $mullerintuitivApi->getToken(config::byKey('login','mullerintuitiv'), config::byKey('mdp','mullerintuitiv'));
        config::save('access_token',$token['access_token'],'mullerintuitiv');
        config::save('refresh_token',$token['refresh_token'],'mullerintuitiv');
        config::save('expires_in', time()+$token['expires_in']-30,'mullerintuitiv');
    }

    /**
     * @throws Exception
     */
    public static function getAccesToken(): string
    {
        $mullerintuitivApi = new mullerintuitivApi();
        if (config::byKey('access_token','mullerintuitiv') === ''){
            token::getSession();
        }

        if (config::byKey('expires_in','mullerintuitiv') <= time()){
            $refreshtoken = $mullerintuitivApi->getRefreshToken(config::byKey('refresh_token','mullerintuitiv'));
            config::save('access_token',$refreshtoken['access_token'],'mullerintuitiv');
            config::save('refresh_token',$refreshtoken['refresh_token'],'mullerintuitiv');
            config::save('expires_in', time()+$refreshtoken['expires_in']-30,'mullerintuitiv');
        }

        return config::byKey('access_token','mullerintuitiv');
    }
}