<?php
use Phalcon\Cli\Task;
use Store\Models\Creates;
use Store\Models\Deletes;
use Store\Models\Products;



class MainTask extends Task
{
    public function mainAction()
    {

        //時間をとってくる
        date_default_timezone_set('Asia/Tokyo');
        $nowDate = 0;//現在の時刻格納
        $data_products_temps = [];//1日前の集計結果格納
        $data_products_temps_id = [];//1日前の集計結果格納(idのみ)

        //無限ループ
        while(true){
          //1日おきに処理をさせる(18時に1日の集計を行う)
          if((date("H") == 15 && date("i") == 51 && date("s") == 00)){
            $data_products = [];//productsデータ格納用配列
            $data_creates_bef = [];//createsデータ格納用配列
            $data_creates_aft = [];//createsデータ格納用配列
            $data_deletes = [];//deletesデータ格納用配列

            echo '今日の集計結果は以下になります。'.PHP_EOL;
            echo date("Y/m/d/H/i/s");
            echo PHP_EOL;

            $nowDate = date("Y/m/d/H/i/s");

            $products = Products::find(
              ['order' => 'id']//id順で取得
            );

            //productsデータを要素ごとに格納
            $products_id = [];//productsのidのみを格納する
            foreach ($products as $product)
            {
              $products_id[] = $product->id;
              $data_products[] = [[
                'id'   => $product->id,
                'name' => $product->name,
                'description'=>$product->description,
                'price'=> $product->price,
                'imgFileName'=> $product->imgFileName,
                'addDate'=> $product->addDate
              ],'imgUrl' => 'http:///restapi/products/img/'.$product->imgFileName
              ];
            }

            echo'全件'.PHP_EOL;
            var_dump($data_products);
            echo PHP_EOL;

            //なかった要素が増えてたらcreatesに追加---------------------------------------------------------
            //createsを全件取得
            $creates = Creates::find(
              ['order' => 'id']
            );

            $creates_id = [];//createsのidのみを格納
            foreach ($creates as $create)
            {
              $creates_id[] = $create->id;
              $data_creates_bef[] = [[
                'id'   => $create->id,
                'name' => $create->name,
                'description'=>$create->description,
                'price'=> $create->price,
                'imgFileName'=> $create->imgFileName,
                'created'=> $create->created
              ],'imgUrl' => 'http:///restapi/products/img/'.$create->imgFileName
              ];
            }

            echo'作成されたデータ'.PHP_EOL;
            if(count($data_creates_bef) == 0){
              //createsが空の時(初回起動時)
              foreach ($products as $product)
              {
                $create = new Creates();
                $create->id = $product->id;
                $create->name = $product->name;
                $create->description = $product->description;
                $create->price = $product->price;
                $create->imgFileName = $product->imgFileName;
                $create->created = $product->addDate;
                $create->save();
                var_dump($data_products);
              }
            }else{
              //createsに要素がある時
              //idで差分を見る

              //productsの中にcreatesに無い要素を探す
              $array_results = array_diff($products_id,$creates_id);

              //productsの中から該当するidで検索してその要素をcreatesに追加する
              foreach($array_results as $array_result){
                foreach ($products as $product)
                {
                  if($product->id == $array_result){
                    $create = new Creates();
                    $create->id = $product->id;
                    $create->name = $product->name;
                    $create->description = $product->description;
                    $create->price = $product->price;
                    $create->imgFileName = $product->imgFileName;
                    $create->created = $product->addDate;
                    $create->save();

                    $data_creates_aft[] = [[
                      'id'   => $product->id,
                      'name' => $product->name,
                      'description'=>$product->description,
                      'price'=> $product->price,
                      'imgFileName'=> $product->imgFileName,
                      'created'=> $product->addDate
                    ],'imgUrl' => 'http:///restapi/products/img/'.$product->imgFileName
                    ];

                  }
                }
              }
            }
            var_dump($data_creates_aft);


            echo PHP_EOL;
            echo'削除された要素'.PHP_EOL;
            //------------------------------------------------------------------------------
            //減ってる要素があればdeletesに追加(deletedに前の時間を入れる)-------------------------
              $array_delete_results = array_diff($data_products_temps_id,$products_id);

              foreach($array_delete_results as $array_delete_result){
                foreach($data_products_temps as $data_products_temp){
                  if($data_products_temp->id == $array_delete_result){
                    $delete = new Deletes();
                    $delete->id = $data_products_temp->id;
                    $delete->name = $data_products_temp->name;
                    $delete->description = $data_products_temp->description;
                    $delete->price = $data_products_temp->price;
                    $delete->imgFileName = $data_products_temp->imgFileName;
                    $delete->deleted = $nowDate;
                    $delete->save();

                    $data_deletes[] = [[
                      'id'   => $data_products_temp->id,
                      'name' => $data_products_temp->name,
                      'description'=>$data_products_temp->description,
                      'price'=> $data_products_temp->price,
                      'imgFileName'=> $data_products_temp->imgFileName,
                      'deleted'=> $nowDate,
                    ],'imgUrl' => 'http:///restapi/products/img/'.$data_products_temp->imgFileName
                    ];
                  }
                }
              }
              var_dump($data_deletes);
              echo PHP_EOL;

            //-----------------------------------------------------------------------------

            $data_products_temps = $products;
            $data_products_temps_id = $products_id;

            sleep(86395);//1日経過する5秒前までスリープさせる
          }


        };

    }
}
