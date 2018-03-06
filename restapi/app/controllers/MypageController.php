<?php

require_once '../twitteroauth-0.7.4/autoload.php';
use Store\Models\Creates;
use Store\Models\Deletes;
use Phalcon\Http\Response;
use Phalcon\Http\Request;
use Abraham\TwitterOAuth\TwitterOAuth;


class MypageController extends ControllerBase
{
    public function indexAction()
    {


    }


    //マイページ表示
    public function showUserPageAction()
    {

      $response = new Response();

      $access_token = $this->session->get('access_token');

      $login_token = $this->session->get('login_token');

      if($login_token == NULL){
        echo'ログイン情報なし</br>';
      }else{
        echo'ログイン済み</br>';
      }

      //集計結果の取得
      //作成されたデータとその情報
      //createsを全件取得
      $creates = Creates::find(
        ['order' => 'id']
      );

      $data_creates = [];

      foreach ($creates as $create)
      {
        $creates_id[] = $create->id;
        $data_creates[] = [[
          'id'   => $create->id,
          'name' => $create->name,
          'description'=>$create->description,
          'price'=> $create->price,
          'imgFileName'=> $create->imgFileName,
          'created'=> $create->created
        ],'imgUrl' => 'http:///restapi/products/img/'.$create->imgFileName
        ];
      }

      var_dump($creates);


      //削除されたデータとその情報
      $deletes = Deletes::find(
        ['order' => 'id']
      );

      $data_deletes = [];
      foreach ($deletes as $delete)
      {
        $data_deletes[] = [[
          'id'   => $delete->id,
          'name' => $delete->name,
          'description'=>$delete->description,
          'price'=> $delete->price,
          'imgFileName'=> $delete->imgFileName,
          'created'=> $delete->created
        ],'imgUrl' => 'http:///restapi/products/img/'.$delete->imgFileName
        ];
      }



      //OAuthトークンとシークレットも使って TwitterOAuth をインスタンス化
      $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

      //ユーザー情報をGET
      $user = $connection->get("account/verify_credentials");

      if(isset($user->errors)){
        //エラー時の処理
        //ステータスメッセージを送る
        $response->setStatusCode(404, 'NOT FOUND');
        $response->setJsonContent(
          [
            'status' => 'Error',
            'message'=>($user->errors[0]->message),
          ],JSON_UNESCAPED_UNICODE
        );
        return $response;
      }else{
        $response->setStatusCode(200, 'OK');

        $response->setJsonContent(
          [
            'ユーザー名' => htmlspecialchars($user->name),
            '説明文'=>htmlspecialchars($user->description),
            '最新のツイート'=> htmlspecialchars($user->status->text),
            '画像url'=>htmlspecialchars($user->profile_image_url),
            'ログアウトurl'=>'http://'.$_SERVER['HTTP_HOST'].'/restapi/logout',
            '作成した商品数'=>count($creates),
            '作成した商品'=>$data_creates,
            '削除した商品数'=>count($deletes),
            '削除した商品'=>$data_deletes
          ],JSON_UNESCAPED_UNICODE
        );
        return $response;
      }
    }


    //期間検索機能
    public function searchDateAction(){
      $response = new Response();

      $date = $this->dispatcher->getParam('date');
      echo $date;

      $creates_ress = Creates::findByCreated($date);
      $data_creates = [];
      foreach ($creates_ress as $creates_res)
      {
        $data_creates[] = [[
          'id'   => $creates_res->id,
          'name' => $creates_res->name,
          'description'=>$creates_res->description,
          'price'=> $creates_res->price,
          'imgFileName'=> $creates_res->imgFileName,
          'created'=> $creates_res->created
        ],'imgUrl' => 'http:///restapi/products/img/'.$creates_res->imgFileName
        ];
      }


      $deletes_ress = Deletes::findByDeleted($date);
      $data_deletes = [];
      foreach ($deletes_ress as $deletes_res)
      {
        $data_deletes[] = [[
          'id'   => $deletes_res->id,
          'name' => $deletes_res->name,
          'description'=>$deletes_res->description,
          'price'=> $deletes_res->price,
          'imgFileName'=> $deletes_res->imgFileName,
          'deleted'=> $deletes_res->deleted
        ],'imgUrl' => 'http:///restapi/products/img/'.$deletes_res->imgFileName
        ];
      }

      if($creates_ress != null && $deletes_ress != null){

        $response->setStatusCode(200, 'OK');

        echo count($creates_ress);
        var_dump($data_creates);
        echo PHP_EOL;
        var_dump($data_deletes);

        $response->setJsonContent(
          [
            '作成した商品数'=>count($creates_ress),
            '作成した商品'=>$data_creates,
            '削除した商品数'=>count($deletes_ress),
            '削除した商品'=>$data_deletes
          ],JSON_UNESCAPED_UNICODE
        );
        return $response;
      }else{
        //エラー時の処理
        //ステータスメッセージを送る
        $response->setStatusCode(404, 'NOT FOUND');
        $response->setJsonContent(
          [
            'status' => 'Error',
            'message'=>'NOT FOUND',
          ],JSON_UNESCAPED_UNICODE
        );
        return $response;
      }
    }
}
