<?php

namespace OLOG\Auth;

class Auth
{
    /**
     * @return null|int Returns null if no user currently logged
     */
    static public function currentUserId(){
        $session_id_from_cookie = self::getSessionIdFromCookie();
        if (!$session_id_from_cookie) {
            return null;
        }

        return self::getSessionUserIdBySessionId($session_id_from_cookie);
    }

    public static function sessionCookieName(){
        return \OLOG\ConfWrapper::value('php-auth.ssid_cookie_name', 'php-auth-session-id');
    }

    public static function getSessionIdFromCookie()
    {
        // TODO: constants
        // TODO: rewiew best practices!!!
        $cookie_name = self::sessionCookieName();

        if (!array_key_exists($cookie_name, $_COOKIE)) {
            return '';
        }
        return $_COOKIE[$cookie_name];
    }

    static public function sessionCacheKey($user_session_id){
        return 'php_auth_user_' . $user_session_id;
    }

    /**
     * @param $user_session_id
     * @return null|int Returns null if session not found or has no user
     */
    public static function getSessionUserIdBySessionId($user_session_id)
    {
        $user_id = \OLOG\Cache\CacheWrapper::get(self::sessionCacheKey($user_session_id));
        if (!$user_id) {
            return null;
        }

        return $user_id;
    }

    public static function logout()
    {
        $user_session_id = self::getSessionIdFromCookie();
        if ($user_session_id) {
            self::clearUserSession($user_session_id);
        }
    }

    public static function clearUserSession($user_session_id)
    {
        self::clearAuthCookie();
        self::removeUserFromAuthCache($user_session_id);
    }

    public static function clearAuthCookie()
    {
        $cookie_name = self::sessionCookieName();
        $cookie_domain = self::sessionCookieDomain();
        setcookie($cookie_name, "", 1000, '/', $cookie_domain);
    }

    public static function removeUserFromAuthCache($user_session_id)
    {
        \OLOG\Cache\CacheWrapper::delete(self::sessionCacheKey($user_session_id));
    }

    public static function startUserSession($user_id)
    {
        $user_session_id = uniqid('as_', true);

        self::storeUserSessionId($user_id, $user_session_id);
        self::setAuthCookieValueBySessionId($user_session_id);
    }

    public static function storeUserSessionId($user_id, $user_session_id)
    {
        $stored = \OLOG\Cache\CacheWrapper::set(self::sessionCacheKey($user_session_id), $user_id, 60 * 60 * 24 * 30);
        return $stored;
    }

    public static function setAuthCookieValueBySessionId($user_session_id)
    {
        $cookie_name = self::sessionCookieName();
        $cookie_domain = self::sessionCookieDomain();
        setcookie($cookie_name, $user_session_id, time() + 2678400, '/', $cookie_domain);
    }

    public static function sessionCookieDomain(){
        return \OLOG\ConfWrapper::value('auth.session_id_cookie_domain', null);
    }

    /**
     * @param $login
     * @param $password_from_form
     * @return null
     */
    public static function getUserIdByCredentials($login, $password_from_form)
    {
        $data = \OLOG\DB\DBWrapper::readObject(
            \OLOG\Auth\Constants::DB_NAME_PHPAUTH,
            'SELECT id, password_hash FROM ' . User::DB_TABLE_NAME . ' WHERE login = ?',
            array($login)
        );

        if ($data === false) {
            return null;
        }

        $password_check_result = password_verify($password_from_form, $data->password_hash);

        if (!$password_check_result) {
            return null;
        }

        return $data->id;
    }
}