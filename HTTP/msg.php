<?php

// 设置不超时
set_time_limit(0);

//分块传输开始
ob_start();


echo "<br />";

//输出
ob_flush(); //刷新到buffer
flush();     // 输出到客户端浏览器

//mysql 建立连接，本地数据库
$con = mysqli_connect("localhost",'root','lymlym','test','3306');

//循环一直读取
while (1){
    $sql = "select * from msg where flag=0";
    $res = mysqli_query($con,$sql);

    // 返回整个结果集，并取得关联的字段名称
    $rows = mysqli_fetch_all($res,1);

    //$row = mysqli_fetch_assoc($res); // 取出关联字段的一行数据

    //输出给浏览器端
    if (!empty($rows) && is_array($rows)){
        $ids = '';
        foreach ($rows as $key=>$row){
            echo "<br />";
            echo $row['content'];
            echo "<br />";
            $id = $row['id'];
            $ids.=$id.",";
            //输出
            ob_flush();
            flush();
        }

        // 对数据的状态做批量修改
        $ids = rtrim($ids,",");
        if (!empty($ids)){
            $ids = "(".$ids.")";
            mysqli_query($con,"update msg set flag=1 where id in $ids");
        }
    }

    //释放查询结果集
    mysqli_free_result($res);

    //每秒执行一次操作
    sleep(1);

}