<?php
namespace fdask\surveybot;

use fdask\surveybot\Settings;

use \Facebook\Facebook;

/**
* code relating to the FacebookAPI
**/
class FB {
	/** @var object the facebook connection object **/
	protected static $fb = null;

   /**
   * returns or creates then returns a Facebook\Facebook instance
   *
   * @return Facebook\Facebook
   **/
   public static function getFb() {
      // if we don't have an fb value already set, load one from the settings file
      if (is_null(static::$fb)) {
         static::$fb = new \Facebook\Facebook(array(
            'app_id' => Settings::get_ini_value('facebook', 'app_id'),
            'app_secret' => Settings::get_ini_value('facebook', 'app_secret'),
            'default_graph_version' => Settings::get_ini_value('facebook', 'graph_version')
         ));

         static::$fb->setDefaultAccessToken(Settings::get_ini_value('facebook', 'page_access_token'));
      }

      return static::$fb;
   }

	/**
	* sends a new facebook messenger text
	*
	* @param integer $recipientId who to send the message to
	* @param string $text the message to send
	* @return boolean
	**/
	public static function sendMessage($recipientId, $text) {
		$fb = self::getFb();

		$params = array(
			'recipient' => array(
				'id' => $recipientId
			),
			'message' => array(
				'text' => $text
			)
		);

		try {
			$response = $fb->post("/me/messages", $params);

			$ret = $response->getDecodedBody();

			error_log($ret);

			return $ret;
		} catch (\Exception $e) {
			error_log(print_r($e, true));
		}

		return false;
	}

	/**
	* gets the details about a user communicating with you
	*
	* @param integer $userId
	* @return false|array
	**/
	public static function getUser($userId) {
		$fb = self::getFb();

		try {
			$response = $fb->get("/$userId?fields=first_name,last_name,profile_pic,locale,timezone,gender");

			if ($response) {
				return $response->getDecodedBody();
			}
		} catch (\Exception $e) {
			error_log(print_r($e, true));
		}

		return false;
	}

	/**
	* outputs info about the given facebook token
	*
	* @link https://developers.facebook.com/docs/facebook-login/access-tokens/debugging-and-error-handling
	* @param string $token
	* @return false|array
	**/
	public static function debugToken($token) {
		$fb = self::getFb();

		try {
			$response = $fb->get("/debug_token?input_token=$token");

			$ret = $response->getDecodedBody();

			if ($ret) {
				return $ret;
			}
		} catch (\Exception $e) {
			error_log(print_r($e, true));
		}

		return false;
	}

	/**
	* code to iterate through a faceboko result set
	*
	* @param object $response
	* @return false|array
	**/
	private static function _graphPager($response) {
		$fb = self::getFb();

		$ret = array();

		try {
			$feedEdge = $response->getGraphEdge();

			do {
				foreach ($feedEdge as $user) {
					$ret[] = $user->asArray();
				}
			} while ($feedEdge = $fb->next($feedEdge));

			return $ret;
		} catch (\Exception $e) {
			error_log(print_r($e, true));
		}

		return false;
	}
}
