<?php
interface proto{
    //建立连接
    public function connect();
    //get 方法
    public function  get();
    //post 方法
    public function post($data,$dataType);
    //设置
    public function close();
}

class Http implements proto{
    //url 信息
    private $urlInfo;
    // 行
    private $line=[];
    // 头部
    private $head=[];
    // 主体 string
    private $body='';
    //sock 句柄
    private $fd;
    //
    private $errno = null;
    //
    private $errstr = null;
    //http 版本
    private $httpVersion="HTTP/1.1";

    private $respond = '';

    public function __construct($url)
    {
        //解析URL信息
        $this->parseUrl($url);
        //建立连接
        $this->connect();
    }

    public function connect()
    {
        //打开socket 连接
        $this->fd = fsockopen($this->urlInfo['host'],$this->urlInfo['port'],$errno,$errstr,3);
        if (!$this->fd){
            echo $errstr;
            exit;
        }
    }

    private function setHead($method="GET"){
        $this->head[] = "HOST:".$this->urlInfo['host'];
        //Connection:close 的意义在于，告诉服务端，请求完成后直接关闭连接
        //对于fsocketopen 后 feof 会一直返回true,知道连接超时，所以读取会陷入死循环，加了这个标识后就不会进入死循环
        $this->head[] = "Connection:close";
        if ($method == "POST"){
//            $this->head[] = "Content-Type:application/x-www-form-urlencoded";
            $this->head[] = "Content-Length:".strlen($this->body);
        }
    }

    /*
     * 提供给外部使用
     * access：public
     *
     * */
    public function setHeadInfo($strInfo){
        $this->head[] = $strInfo;
    }

    /*
     * 解析URL，格式化成array
     * @param string $url url地址
     * */
    private function parseUrl($url){
       $this->urlInfo = parse_url($url);

       $defaultPort = 80;
       if ($this->urlInfo['scheme'] == "https"){
           $defaultPort = 443;
       }
       isset($this->urlInfo['port'])?1:$this->urlInfo['port']=$defaultPort;
    }


    /*
     * 设置请求行
     * @param string $method 请求方法，默认GET
     * */
    private function setLine($method='GET')
    {
        $line = $method." ".$this->urlInfo['path'];
        if (isset($this->urlInfo['query'])){
            $line = $line."?".$this->urlInfo['query'];
        }
        if (isset($this->urlInfo['fragment'])){
            $line  = $line."#".$this->urlInfo['fragment'];
        }
        $line = $line." ".$this->httpVersion;
        $this->line[] = $line;


    }

    //get请求
    public function get()
    {
        //设置请求头
        $this->setLine("GET");

        //设置头部
        $this->setHead();

        //发送信息
        $this->request();

        return $this->respond;
    }

    /*
    * post请求
    * @param array $data
     * @param integer $dataType 数据类型
    * **/
    public function post($data,$dataType)
    {
        //请求行
        $this->setLine("POST");
        //请求主体
        if ($dataType == 1){
            $this->body = http_build_query($data);
        }else {
            $this->body = json_encode($data);
        }

        //请求头
        $this->setHead("POST");
        //发送请求
        $this->request();

        return $this->respond;
    }

    public function request(){
        //合并数组
        $request = array_merge($this->line,$this->head,array(''),array($this->body),array(''));

        //转换成字符串，换行
        $request = implode("\r\n",$request);
//        print_r($this->urlInfo);
//        echo $request;
//        exit;
        //发送请求
        fwrite($this->fd,$request);

        //消息管道中读出来数据
        $respond = null;
        try {
            while (!feof($this->fd)){
                $this->respond .= fgets($this->fd,1024);
            }
        }catch (\Exception $e){
            echo $e->getMessage();
            return false;
        }

        file_put_contents('./a.html',$this->respond);
        $this->close();

        return $this->respond;
    }

    //关闭连接
    public function close()
    {
        fclose($this->fd);
    }
}


//$url = "https://zutuanxue.com/home/4/8_308";
//$url = "http://news.163.com/photo/#Current";
//模仿 post请求
/*
$url = "http://47.106.176.36/Http/post.php";
$http = new Http("$url");
$http->setHeadInfo("Content-Type:application/x-www=form-urlencoded");
$data = [
  'username'=>'zhangsan',
    'password'=>'123456',
    'sex'=>'1',
    'ege'=>29
];
echo $http->post($data);
*/
/*

Accept: application/json
Accept-Encoding: gzip, deflate, br
Accept-Language: zh-CN,zh;q=0.9
Connection: keep-alive
Content-Length: 35
Content-Type: application/json; charset=UTF-8
Cookie: _ga=GA1.2.647632525.1597932482; __yadk_uid=fhMaeXTWXxVpmIIcFVn0jyUT0e7kUxef; web_login_version=MTYxMDUzMDY1OA%3D%3D--5b4893e09558f557f168e25d5fea7b8462a56554; remember_user_token=W1sxMTU1NzM0Nl0sIiQyYSQxMSRWTEQvWXBJT0JVMzZMaFlzVmZsTkpPIiwiMTYxMDk1MDY5NS4xMTkyMzA1Il0%3D--dc4b3a8ac5ebcc132a20af2a548018c4ff26cfe6; read_mode=day; default_font=font2; locale=zh-CN; _m7e_session_core=c0eeecc7c6716c7a48f986d1fb29c6cd; Hm_lvt_0c0e9d9b1e7d617b3e6842e85b9fb068=1610686316,1610702723,1610767538,1610950696; sensorsdata2015jssdkcross=%7B%22distinct_id%22%3A%22176a76ed360870-0d52ddb8b90eaf-16396153-1296000-176a76ed3627e9%22%2C%22first_id%22%3A%22%22%2C%22props%22%3A%7B%22%24latest_traffic_source_type%22%3A%22%E8%87%AA%E7%84%B6%E6%90%9C%E7%B4%A2%E6%B5%81%E9%87%8F%22%2C%22%24latest_search_keyword%22%3A%22%E6%9C%AA%E5%8F%96%E5%88%B0%E5%80%BC%22%2C%22%24latest_referrer%22%3A%22https%3A%2F%2Fwww.baidu.com%2Flink%22%2C%22%24latest_utm_source%22%3A%22desktop%22%2C%22%24latest_utm_medium%22%3A%22search-input%22%2C%22%24latest_utm_campaign%22%3A%22maleskine%22%2C%22%24latest_utm_content%22%3A%22note%22%7D%2C%22%24device_id%22%3A%22176a76ed360870-0d52ddb8b90eaf-16396153-1296000-176a76ed3627e9%22%7D; _gid=GA1.2.1397190787.1610950699; _gat=1; Hm_lpvt_0c0e9d9b1e7d617b3e6842e85b9fb068=1610950699
Host: www.jianshu.com
Origin: https://www.jianshu.com
Referer: https://www.jianshu.com/p/348fe3d2c8b0
sec-ch-ua: "Google Chrome";v="87", " Not;A Brand";v="99", "Chromium";v="87"
sec-ch-ua-mobile: ?0
Sec-Fetch-Dest: empty
Sec-Fetch-Mode: cors
Sec-Fetch-Site: same-origin
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36

 * */

//$http = new Http("https://www.jianshu.com/shakespeare/notes/78033488/comments");
//$http->setHeadInfo("Accept:application/json");
//$http->setHeadInfo("Accept-Encoding: gzip, deflate, br");
//$http->setHeadInfo("Accept-Language: zh-CN,zh;q=0.9");
//$http->setHeadInfo("Connection: keep-alive");
//$http->setHeadInfo("Content-Type: application/json; charset=UTF-8");
//$http->setHeadInfo("Origin: https://www.jianshu.com");
//$http->setHeadInfo("Referer: https://www.jianshu.com/p/348fe3d2c8b0");
//$http->setHeadInfo("sec-ch-ua: \"Google Chrome\";v=\"87\", \" Not;A Brand\";v=\"99\", \"Chromium\";v=\"87\"");
//$http->setHeadInfo("sec-ch-ua-mobile: ?0");
//$http->setHeadInfo("Sec-Fetch-Dest: empty");
//$http->setHeadInfo("Sec-Fetch-Mode: cors");
//$http->setHeadInfo("Sec-Fetch-Site: same-origin");
//$http->setHeadInfo("User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36");
//$http->setHeadInfo("Cookie: _ga=GA1.2.647632525.1597932482; __yadk_uid=fhMaeXTWXxVpmIIcFVn0jyUT0e7kUxef; web_login_version=MTYxMDUzMDY1OA%3D%3D--5b4893e09558f557f168e25d5fea7b8462a56554; remember_user_token=W1sxMTU1NzM0Nl0sIiQyYSQxMSRWTEQvWXBJT0JVMzZMaFlzVmZsTkpPIiwiMTYxMDk1MDY5NS4xMTkyMzA1Il0%3D--dc4b3a8ac5ebcc132a20af2a548018c4ff26cfe6; read_mode=day; default_font=font2; locale=zh-CN; _m7e_session_core=c0eeecc7c6716c7a48f986d1fb29c6cd; Hm_lvt_0c0e9d9b1e7d617b3e6842e85b9fb068=1610686316,1610702723,1610767538,1610950696; _gid=GA1.2.1397190787.1610950699; sensorsdata2015jssdkcross=%7B%22distinct_id%22%3A%22176a76ed360870-0d52ddb8b90eaf-16396153-1296000-176a76ed3627e9%22%2C%22first_id%22%3A%22%22%2C%22props%22%3A%7B%22%24latest_traffic_source_type%22%3A%22%E7%9B%B4%E6%8E%A5%E6%B5%81%E9%87%8F%22%2C%22%24latest_search_keyword%22%3A%22%E6%9C%AA%E5%8F%96%E5%88%B0%E5%80%BC_%E7%9B%B4%E6%8E%A5%E6%89%93%E5%BC%80%22%2C%22%24latest_referrer%22%3A%22%22%2C%22%24latest_utm_source%22%3A%22desktop%22%2C%22%24latest_utm_medium%22%3A%22search-input%22%2C%22%24latest_utm_campaign%22%3A%22maleskine%22%2C%22%24latest_utm_content%22%3A%22note%22%7D%2C%22%24device_id%22%3A%22176a76ed360870-0d52ddb8b90eaf-16396153-1296000-176a76ed3627e9%22%7D; _gat=1; Hm_lpvt_0c0e9d9b1e7d617b3e6842e85b9fb068=1610953267");
//$data=["content"=> "谢谢楼主，刚好用的上!"];
//$http->post($data);