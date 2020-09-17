<?php
//error_reporting(0);

class dhon{
  
  public function save($file,$isi_file,$method="a"){
    $filea = fopen($file,$method);
    fwrite($filea,$isi_file);
    fclose($filea);
  }
  public function get_cookie($curl){
    preg_match_all( '/^Set-Cookie:s*([^;]*)/mi' , $curl, $matches);
    $cookies = array();
    foreach ($matches[ 1] as $item) {
      parse_str($item, $cookie);
      $cookies=array_merge($cookies,$cookie);
    }
	  return $cookies;
  }
  public function curl($path,$head,$header=false,$post=false,$vob=false,$method=false){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $path);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $head);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 360);
    if($header == true){
      curl_setopt($curl, CURLOPT_HEADER, true);
    }
    if($vob == true){
      curl_setopt($curl, CURLOPT_VERBOSE, true);
    }
    if($post == true){
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    }
    if($method == true){
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    }
    curl_setopt($curl, CURLOPT_ENCODING,"GZIP");
    $exe  = curl_exec($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $res['cookie'] = $this->get_cookie($exe); /** get cookie in head **/
    $res['json']   = json_decode(substr($exe, $header_size),true); /** decode result array **/
    //$res['direct'] = $this->get_direct($curl,$exe); /** get link direct **/
    $res['code']   = curl_getinfo($curl, CURLINFO_HTTP_CODE); /** get code http respon **/
    $res['exec']   = $exe; /** result http respon **/
    $res['body']   = substr($exe, $header_size);
    return $res;
  }
  private function head($method,$ua, $cookie=null,$dta=null){
    switch($method){
      case "get":
        $head   = array();
        $head[] = "application/x-www-form-urlencoded; charset=UTF-8";
        $head[] = "user-agent:".$ua." XiaoMi/MiuiBrowser/10.8.1 LT-APP/43/101/YM-RT/";
        $head[] = "accept-language:en-US,en;q=0.9";
        $head[] = "x-requested-with:XMLHttpRequest";
        $head[] = "cookie: ".$cookie;
        return $head;
      break;
      case "post":
        $head   = array();
        $head[] = "accept:application/json, text/javascript, */*; q=0.01";
        $head[] = "application/x-www-form-urlencoded; charset=UTF-8";
        $head[] = "user-agent:".$ua." XiaoMi/MiuiBrowser/10.8.1 LT-APP/43/101/YM-RT/";
        $head[] = "accept-language:en-US,en;q=0.9";
        $head[] = "x-requested-with:XMLHttpRequest";
        $head[] = "Content-Length: ".strlen($dta);
        $head[] = "cookie: ".$cookie;
        return $head;
      break;
    }
  }
  public function mall($fake){
    $json = json_decode($fake,true);
    $pass = $json['password'];
    $nope = $json['phone'];
    $dta = "tel=".$nope."&pwd=".$pass."&jizhu=1";
    $ua = $json['user-agent'];
    $exe = $this->curl("https://gm99k.com/index/user/do_login.html",$this->head("post",$ua,null,$dta),true,$dta);
    if($exe['code'] == 200){
      
      //echo($exe['exec']);
      if($exe['json']['info'] == "login berhasil!"){
        echo "success login\n";
        
        $cookie = "tel=".$nope."; pwd=".$pass."; s9671eded=".$exe['cookie']['s9671eded'];
        for($i=1;$i<=50;$i++){
          echo "[ ".$i." ] ";
          $dta1 = "";
          $exe1 = $this->curl("https://gm99k.com/index/rot_order/submit_order.html?cid=1&m=0.6722961548646147",$this->head("post",$ua,$cookie,$dta1),true,$dta1);
        // echo($exe1['exec']);
          if($exe1['json']['info'] == "Pesanan berhasil"){
            $oid = $exe1['json']['oid'];
            $dta2 = "id=".$oid;
            $exe2 = $this->curl("https://gm99k.com/index/order/order_info",$this->head("post",$ua,$cookie,$dta2),true,$dta2);
            //echo $exe2['exec'];
            if($exe2['json']['code'] == 0 OR $exe2['json']['info'] == "Permintaan berhasil"){
              
              echo "Order  [ ".$exe2['json']['data']['goods_name']." ]\n";
              echo "Komisi [ ".$exe2['json']['data']['commission']." ]\n";
              
              $dta3 = "oid=".$oid;
              $exe3 = $this->curl("https://gm99k.com/index/order/do_order",$this->head("post",$ua,$cookie,$dta3),true,$dta3);
              if($exe3['json']['code'] == 0 OR $exe3['json']['info'] == "Operasi berhasil"){
                echo "Sukses\n";
              }
            }
          }elseif($exe1['json']['info'] == "Alamat pengiriman belum diset"){
            $exp = explode("Prov.",$json['alamat']);
            $exp1 = explode("\n",$exp[1]);
            $exp2 = trim($exp1[0]);
            $dta_ = "area=".urlencode($exp2)."&address=".urlencode($json['alamat']);
            $exe_ = $this->curl("https://gm99k.com/index/my/edit_address",$this->head("post",$ua,$cookie,$dta_),true,$dta_);
            if($exe_['json']['info'] == "Operasi berhasil"){
              echo "success save alamat\n";
            }else{
              echo "failed save alamat\n";
            }
            
          }elseif($exe1['json']['info'] == "Hari ini rebut pesanan telah mencapai batas" OR $exe1['json']['info'] == "Ada pesanan yang belum terselesaikan di akun ini, dan Anda tidak dapat melanjutkan untuk mengambil pesanan！"){
            sleep(rand(10,20));
            $this->regis();
          }elseif($exe1['json']['info'] == "Saldo yang tersedia tidak mencukupi!"){
            sleep(rand(10,20));
            $this->regis();
          }else{
            echo $exe1['exec']."\n";
          }
          sleep(rand(5,10));
        }
      }else{
        echo $exe['exec'];
        echo "gagal login\n";
      }
    }else{
      echo $exe['exec'];
      echo "login gagal\n";
    }
    sleep(rand(10,20));
    $this->regis();
  }
  public function regis(){
    
    $fake = $this->curl("https://api.indotech.my.id/v1/fakedata/xxxdata.php?a=fake",array("user-agent:rdhoni/0.1"),true);
    $jsn  = json_decode($fake['body'],true);
    
    $hd   = array();
    $hd[] = "upgrade-insecure-requests:1";
    $hd[] = "dnt:1";
    $hd[] = "user-agent:".$jsn['user-agent'];
    $hd[] = "accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9";
    $hd[] = "en-US,en;q=0.9";
    
    $exe_ = $this->curl("https://gm88k.com/index/user/register/invite_code/S7EV8J.html",$hd,true);
    //echo $exe_['cookie']['s9671eded']."\n";
    $apl = explode('window.location.href ="',$exe_['exec']);
    $apo = explode('";',$apl[1]);
    $apm = trim($apo[0]);
    $exe__ = $this->curl("https://gm88k.com".$apm,$hd,true);
    
    $ip = $jsn['ip_address'];
    $dta = "user_name=".urlencode($jsn['fullname'])."&tel=".$jsn['phone']."&pwd=".$jsn['password']."&deposit_pwd=".$jsn['password']."&invite_code=S7EV8J";
    //echo $dta; exit;
    $head   = array();
    $head[] = "accept-language:en-US,en;q=0.9";
    $head[] = "content-type:application/x-www-form-urlencoded; charset=UTF-8";
    $head[] = "x-requested-with:XMLHttpRequest";
   // $head[] = "accept:application/json, text/javascript, */*; q=0.01";
    $head[] = "accept:*/*";
    $head[] = "content-length=".strlen($dta);
    $head[] = "user-agent:".$jsn['user-agent'];
    $head[] = "referer: https://gm88k.com/index/user/register/invite_code/S7EV8J.html";
    $head[] = "cookie: s9671eded=".$exe__['cookie']['s9671eded'];
    $head[] = "REMOTE_ADDR: $ip";
    $head[] = "HTTP_X_FORWARDED_FOR: $ip";
    $exe = $this->curl("https://gm88k.com/index/user/do_register.html",$head,true,$dta);
    if($exe['json']['info'] == "Transaksi berhasil"){
      $this->save("akunmall1.txt",$fake['body']."\n","a");
      $this->mall($fake['body']);
    }else{ 
      echo $exe['exec'];
      echo "gagal\n";
      $this->regis();
    }
  }
  public function mal(){
   // $json = json_decode($fake,true);
    $pass = '';
    $nope = '';
    $ua  = "Mozilla/5.0 (Linux; Android 10; RMX1801 Build/QKQ1.191014.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/85.0.4183.101 Mobile Safari/537.36";
    $dta = "tel=".$nope."&pwd=".$pass."&jizhu=1";
    $exe = $this->curl("https://gm99k.com/index/user/do_login.html",$this->head("post",$ua,null,$dta),true,$dta);
    if($exe['code'] == 200){
      
      //echo($exe['exec']);
      if($exe['json']['info'] == "login berhasil!"){
        echo "success login\n";
        
        $cookie = "tel=".$nope."; pwd=".$pass."; s9671eded=".$exe['cookie']['s9671eded'];
        for($i=1;$i<=50;$i++){
          echo "[ ".$i." ] ";
          $dta1 = "";
          $exe1 = $this->curl("https://gm99k.com/index/rot_order/submit_order.html?cid=1&m=0.6722961548646147",$this->head("post",$ua,$cookie,$dta1),true,$dta1);
        // echo($exe1['exec']);
          if($exe1['json']['info'] == "Pesanan berhasil"){
            $oid = $exe1['json']['oid'];
            $dta2 = "id=".$oid;
            $exe2 = $this->curl("https://gm99k.com/index/order/order_info",$this->head("post",$ua,$cookie,$dta2),true,$dta2);
            //echo $exe2['exec'];
            if($exe2['json']['code'] == 0 OR $exe2['json']['info'] == "Permintaan berhasil"){
              
              echo "Order  [ ".$exe2['json']['data']['goods_name']." ]\n";
              echo "Komisi [ ".$exe2['json']['data']['commission']." ]\n";
              
              $dta3 = "oid=".$oid;
              $exe3 = $this->curl("https://gm99k.com/index/order/do_order",$this->head("post",$ua,$cookie,$dta3),true,$dta3);
              if($exe3['json']['code'] == 0 OR $exe3['json']['info'] == "Operasi berhasil"){
                echo "Sukses\n";
              }
            }
          }elseif($exe1['json']['info'] == "Alamat pengiriman belum diset"){
            $exp = explode("Prov.",$json['alamat']);
            $exp1 = explode("\n",$exp[1]);
            $exp2 = trim($exp1[0]);
            $dta_ = "area=".urlencode($exp2)."&address=".urlencode($json['alamat']);
            $exe_ = $this->curl("https://gm99k.com/index/my/edit_address",$this->head("post",$ua,$cookie,$dta_),true,$dta_);
            if($exe_['json']['info'] == "Operasi berhasil"){
              echo "success save alamat\n";
            }else{
              echo "failed save alamat\n";
            }
            
          }elseif($exe1['json']['info'] == "Hari ini rebut pesanan telah mencapai batas" OR $exe1['json']['info'] == "Ada pesanan yang belum terselesaikan di akun ini, dan Anda tidak dapat melanjutkan untuk mengambil pesanan！"){
            sleep(rand(10,20));
            $this->regis();
          }elseif($exe1['json']['info'] == "Saldo yang tersedia tidak mencukupi!"){
            sleep(rand(10,20));
            $this->regis();
          }else{
            echo $exe1['exec']."\n";
          }
          sleep(rand(5,10));
        }
      }else{
        echo $exe['exec'];
        echo "gagal login\n";
      }
    }else{
      echo $exe['exec'];
      echo "login gagal\n";
    }
  }
  public function claim(){
    $aaaa = file_get_contents("akunmall.txt");
    $aaab = file("akunmall.txt");
    $aaac = count($aaab);
    $aaad = explode("\n",$aaaa);
    for($i=0;$i<=$aaac;$i++){
      if($aaad[$i]){
        $this->all($aaad[$i]);
      }
    }
  }
  public function all($fake){
    $json = json_decode($fake,true);
    $pass = $json['password'];
    $nope = $json['phone'];
    $dta = "tel=".$nope."&pwd=".$pass."&jizhu=1";
    $exe = $this->curl("https://gm99k.com/index/user/do_login.html",$this->head("post",$ua,null,$dta),true,$dta);
    if($exe['code'] == 200){
      
      //echo($exe['exec']);
      if($exe['json']['info'] == "login berhasil!"){
        echo "success login\n";
        
        $cookie = "tel=".$nope."; pwd=".$pass."; s9671eded=".$exe['cookie']['s9671eded'];
        for($i=1;$i<=50;$i++){
          echo "[ ".$i." ] ";
          $dta1 = "";
          $exe1 = $this->curl("https://gm99k.com/index/rot_order/submit_order.html?cid=1&m=0.6722961548646147",$this->head("post",$ua,$cookie,$dta1),true,$dta1);
        //echo($exe1['exec'])."\n";
          if($exe1['json']['info'] == "Pesanan berhasil"){
            $oid = $exe1['json']['oid'];
            $dta2 = "id=".$oid;
            $exe2 = $this->curl("https://gm99k.com/index/order/order_info",$this->head("post",$ua,$cookie,$dta2),true,$dta2);
            //echo $exe2['exec'];
            if($exe2['json']['code'] == 0 OR $exe2['json']['info'] == "Permintaan berhasil"){
              
              echo "Order  [ ".$exe2['json']['data']['goods_name']." ]\n";
              echo "Komisi [ ".$exe2['json']['data']['commission']." ]\n";
              
              $dta3 = "oid=".$oid;
              $exe3 = $this->curl("https://gm99k.com/index/order/do_order",$this->head("post",$ua,$cookie,$dta3),true,$dta3);
              if($exe3['json']['code'] == 0 OR $exe3['json']['info'] == "Operasi berhasil"){
                echo "Sukses\n";
              }
            }
          }elseif($exe1['json']['info'] == "Alamat pengiriman belum diset"){
            $exp = explode("Prov.",$json['address']);
            $exp1 = explode("\n",$exp[1]);
            $exp2 = trim($exp1[0]);
            $dta_ = "area=".urlencode($exp2)."&address=".urlencode($json['alamat']);
            $exe_ = $this->curl("https://gm99k.com/index/my/edit_address",$this->head("post",$ua,$cookie,$dta_),true,$dta_);
            if($exe_['json']['info'] == "Operasi berhasil"){
              echo "success save alamat\n";
            }else{
              echo "failed save alamat\n";
            }
            
          }elseif($exe1['json']['info'] == "Hari ini rebut pesanan telah mencapai batas" OR $exe1['json']['info'] == "Ada pesanan yang belum terselesaikan di akun ini, dan Anda tidak dapat melanjutkan untuk mengambil pesanan！"){
            break;
            
          }elseif($exe1['json']['info'] == "Saldo yang tersedia tidak mencukupi!"){
            break;
            
          }else{
            echo $exe1['exec']."\n";
          }
         // sleep(rand(5,10));
        }
      }else{
        echo $exe['exec'];
        echo "gagal login\n";
      }
    }else{
      echo "login gagal\n";
    }
    
    
  }
}

$new = new dhon();
if($argv[1] == "ok"){
  echo $new->mal();
}elseif($argv[1] == "claim"){
  echo $new->claim();
}else{
  echo $new->regis();
}
