<?php
namespace Shop\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;
use Facebook\Facebook;
use Shop\Model\UserModel;
use Think\Controller;
require_once (__DIR__ . '/php-graph-sdk-5.0.0/src/Facebook/autoload.php');
header('content-type:text/html;charset=utf-8');
session_start();
//登陆类
class FacebookController extends BaseController {
	public function _initialize(){
		parent::_initialize();
	}
	//facebook config
	private $fb_app_id='104737649997971';
	private $fb_app_secret='7155b5b9b7c9cdd7e1de8a460b3308b5';
	private $fb_version='v2.8';

	//login with facebook
	public function login_with_facebook(){
		//fackbook config
		$fb = new Facebook([
			'app_id' => $this->fb_app_id,
			'app_secret' => $this->fb_app_secret,
			'default_graph_version' => $this->fb_version,
		]);

		$helper = $fb->getRedirectLoginHelper();
		$permissions = ['email', 'user_likes']; // optional
		//get url
		$loginUrl = $helper->getLoginUrl('http://www.galacasa.com/index.php/Facebook/facebook_callback', $permissions);
		if($loginUrl){
			$this->list_json_result(1,'success',$loginUrl);
		}else{
			$this->list_json_result(-1,'fail','');
		}
	}

	//facebook token callback
	public function facebook_callback(){
		//faceebook config
		$fb = new Facebook([
			'app_id' => $this->fb_app_id,
			'app_secret' => $this->fb_app_secret,
			'default_graph_version' => $this->fb_version,
		]);
		// get token
		$helper = $fb->getRedirectLoginHelper();
		try {
			$accessToken = $helper->getAccessToken();
			// OAuth 2.0 client handler
			//$oAuth2Client = $fb->getOAuth2Client();
			// Exchanges a short-lived access token for a long-lived onevar_dump(4);
			//$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		//
		if (isset($accessToken)) {
			try {
				// Returns a `Facebook\FacebookResponse` object
				$response = $fb->get('/me?fields=id,name', $accessToken);
			} catch (Facebook\Exceptions\FacebookResponseException $e) {
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
			} catch (Facebook\Exceptions\FacebookSDKException $e) {
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			}
			$user = $response->getGraphUser();
			$Login = new LoginController();
			$Login->oauth_do($user['id'], 'FB');
		}else{
			echo "Login with Facebook : Fail";
			echo "<br>";
			echo "<a href=".__APP__." >Back to Home</a>";
			die();
		}
	}
	//end
}


