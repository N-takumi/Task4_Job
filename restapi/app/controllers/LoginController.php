<?php

require_once '../twitteroauth-0.7.4/autoload.php';

use Store\Models\Creates;
use Store\Models\Deletes;

use Phalcon\Http\Response;
use Phalcon\Http\Request;
use Abraham\TwitterOAuth\TwitterOAuth;


class LoginController extends ControllerBase
{
    public function indexAction()
    {


    }

    //ログイン処理
    public function loginAction()
    {
      //TwitterOAuth をインスタンス化
      $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

      $request_token = $connection->oauth('oauth/request_token');

      $this->session->set('oauth_token', $request_token['oauth_token']);
      $this->session->set('oauth_token_secret', $request_token['oauth_token_secret']);

      //Twitter.com 上の認証画面のURLを取得
      $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

      //Twitter.com の認証画面へリダイレクト
      header( 'location: '. $url );
    }


    //コールバック処理
    public function callbackAction()
    {
      $request_token = [];
      $request_token['oauth_token'] = $this->session->get('oauth_token');
      $request_token['oauth_token_secret'] = $this->session->get('oauth_token_secret');

      //Twitterから返されたOAuthトークンと、あらかじめloginActionで入れておいたセッション上のものと一致するかをチェック
      if (isset($_REQUEST['oauth_token']) && $request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
        die( 'Error!' );
      }

      if(isset($_REQUEST['oauth_verifier'])){
        //OAuth トークンも用いて TwitterOAuth をインスタンス化
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);

        $this->session->set('access_token', $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier'])));

        //トークン認証用のトークン作成及び格納
        $this->session->set('login_token',hash('sha256', session_id()));
        $this->session->set('login_token_id',session_id());

        //セッションIDをリジェネレート
        session_regenerate_id();

        //マイページへリダイレクト
        header('location: http://'.$_SERVER['HTTP_HOST'].'/restapi/mypage');
      }else{
        die('oauth_verifier error!');
      }


    }



    //ログアウト
    public function logoutAction()
    {
      if($this->session->destroy()){
        echo'ログアウト完了';
      }
      header('location: http://'.$_SERVER['HTTP_HOST'].'/restapi/Login');
    }

}
