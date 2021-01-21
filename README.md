# Study
## HTTP 目录 是http协议相关的一些demo
### 1.用php socket模仿http请求，请求类 Http.class.php, 
### 2.组装refer头信息，去获取防盗链图片，并保存到本地，参考 refer.php
### 3.用分块传输，做一个雏形的反向ajax 请求，类似一个及时通讯工具，参考msg.php
对于第3点，ob_flush() 和flush() 执行后，浏览器一直处于等待，不能及时输出。需要在web服务器Nginx的配置文件填写下面信息：<br>
  	proxy_buffering off;<br>
	gzip off;<br>
	fastcgi_keep_conn on;<br>
  
 相应PHP.ini 里面修改：output_buffering = Off <br>
 然后重启一下web 服务器就可以了
