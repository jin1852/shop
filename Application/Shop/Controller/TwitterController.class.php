<?php
namespace Shop\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;
use Facebook\Facebook;
use Shop\Model\UserModel;
use Think\Controller;
require(__DIR__."/twitteroauth/autoload.php");
header('content-type:text/html;charset=utf-8');
//登陆类
class TwitterController extends BaseController {
	public function _initialize(){
		parent::_initialize();
	}

	//twitter config
	private $tw_app_id='NYRM3qcknSqU5TU3LF0sFWzga';  //Twittwe Consumer Key (API Key)
	private $tw_app_secret='1vEHM2rDLoaoHZ4W8PyGt05qNx00gd32GnvQwxP1ojK8VV6yfX'; //Twitter Consumer Secret (API Secret)
	private $tw_access_token='754872777333551104-kYozPwcrL8VdQzOqnN8c5gj5hJz8VSP'; // Twitter Access Token
	private $tw_access_token_secret='6fQsb5ePfcAcu1cVAbXIlmgO0i2zro6L03SaAEsvCvEEM'; //Twitter Access Token Secret

	//login with twitter
	public function login_with_twitter(){
		$connection = new TwitterOAuth($this->tw_app_id, $this->tw_app_secret,$this->tw_access_token,$this->tw_access_token_secret);
		$temporary_credentials = $connection->oauth('oauth/request_token', array("oauth_callback" =>'http://www.galacasa.com//index.php/Twitter/twitter_callback'));
		//get url
		$url = $connection->url("oauth/authorize", array("oauth_token" => $temporary_credentials['oauth_token']));
		if($url){
			$this->list_json_result(1,'success',$url);
		}else{
			$this->list_json_result(-1,'fail','');
		}
	}

	//twitter token callback
	public function twitter_callback(){
		$connection = new TwitterOAuth($this->tw_app_id, $this->tw_app_secret,$this->tw_access_token,$this->tw_access_token_secret);
		//get token
		$params=array("oauth_verifier" => $_GET['oauth_verifier'],"oauth_token"=>$_GET['oauth_token']);
		if($params['oauth_verifier'] && $params['oauth_token']) {
			$access_token = $connection->oauth("oauth/access_token", $params);
			$connection = new TwitterOAuth($this->tw_app_id, $this->tw_app_secret, $access_token['oauth_token'], $access_token['oauth_token_secret']);
			//get user info
			$content = $connection->get("account/verify_credentials");
			$Login = new LoginController();
			$Login->oauth_do($content->id, 'TW');
		}else{
			echo "Login with Twitter : Fail";
			echo "<br>";
			echo "<a href=".__APP__." >Back to Home</a>";
			die();
		}
	}
	//end
}


