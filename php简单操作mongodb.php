<!-- 
1、本文采用mongoClient类来实现mongodb的简单操作，
2、需要事先熟悉《mongodb基础命令——进阶篇》的内容
3、其中更新数据部分只给出了一个$set的例子，但是跟操作命令是一样的，注意理解与尝试
4、在最下来有对函数进行简单的介绍，其中的一些内容会在本人学习完后续章节后回来补充的
 -->
<?php
try {
	// 连接mongodb数据库
    $mongo = new MongoClient();
    // 选择数据库
    $db_name=$mongo->test;
    // 或者这样也可以
    // $db_name=$mongo->selectDB('test');
    // 选择集合
    $collection_name=$db_name->student;
    // 或者和上面一样
    // $collection_name=$$db_name->selectCollection('collection_name');
    echo '<pre>';
    // 查看全部dbs
    $dbs=$mongo->listDBs();
    // var_dump($dbs);
    $collections=$db_name->listCollections();
    // var_dump($collections);
    // 定义被插入的数据，而且php的数组形式与json格式类似，所以很容易理解
    $input = array(
    	'name' =>'yang' ,
    	'sex'=>'man',
    	'sorce' => array(
    		'math' =>60 ,
    		'pe'=>30 
    		)
    	);
    // 插入数据，$result会显示插入数据的结果
    // insert的第二个参数内容请看--函数1
    // $result=$collection_name->insert($input);
    // var_dump($result);
    // 查询单条数据，跟shell命令里的findOne()一样
    $findOne=$collection_name->findOne();
    // var_dump($findOne);
    // 查找全部数据，记住一点，find()函数的返回值不是跟findOne()函数一样的数组。而是一个对象，所以不能直接
    // 打印出来，至于如何读取其中的内容，可以使用foreach循环
    $find=$collection_name->find();
    // 可以跟mongo shell中一样为find()函数传递第一个筛选参数
    $situation = array(
    	'name' => 'yang', 
    	);
    // 选择返回的字段内容
    $field = array('sorce' => 1 );
    // 详细解释看--函数2
    $find=$collection_name->find($situation,$field);
    // while ($each=$find->getNext()) {
    // 	var_dump($each);
    // }
    $sort=$collection_name->find()->sort(array('math' => -1, ));
    $limit=$collection_name->find()->sort(array('math' => -1, ))->limit(2);
    $skip=$collection_name->find()->sort(array('math' => -1, ))->skip(2);
    $count=$collection_name->find()->sort(array('math' => -1, ))->count();
    // echo $count;
    // foreach ($skip as $value) {
    // 	var_dump($value);
    // }
    // 条件操作符的使用
    $situation2=array(
    	// 注意这里字段的设置跟shell中一样
    	'item.quantity'=>array('$gt'=>5)
    	);
    $gt=$db_name->orders->find($situation2);
    /**********************************************数据的更新*******************************************/
    // 注意，接下来这段代码会更新整个匹配到的文档，就跟update没有使用$set一样
    // 详情查看函数3
    $update=$db_name->orders->update(
    	array('_class'=>'com.mongo.model.Orders'),
    	array('_class'=>'hello world')
    	);
    // 注意$set的位置，是不是与shell命令中一致
    $update=$db_name->orders->update(
    	array('_class'=>'com.mongo.model.Orders'),
    	array('$set'=>array('_class'=>'hello world'))
    	);
    // 从这里可以看到，如果会shell命令的话，那么这一节的重点就是将shell命令与php数组之间的相互转化了
    /**********************************************数据的删除*******************************************/
    // 删除集合中的数据
    $remove=$db_name->orders->remove(array('_class'=>'com.mongo.model.Orders'));
    // 删除整个集合
    $db_name->orders->drop();
    // 本来还有一些集合之间使用DBRef联查以及GRidFS的内容的，但是那个还是等以后要用了再来补充好了

} catch (MongoConnectionException $e) {
    echo $e->getMessage();
}
/*
----------------------------------------------------函数1----------------------------------------
插入数据函数 insert
$mongo->$db_name->$collection_name->insert($input.$options)
-----------------------------------------------------------
$option中的参数以及默认值
$option=array(
	'fsync'=> false,
	'j'	   => false,
	'w'    => 1,
	'wtimeout'=>10000,
	'timeout'=>10000
)

'fsync' 
这个参数设置为真时，php将会告诉mongodb将当前写入数据直接写入硬盘，即使还没有全部从php文档写入mongodb数据库
'j'
这个参数设置为真市，php将会告诉mongodb在数据插入成功之前就将本次修改写入日志
'w'
如果设置成0，写操作将不会被确认，在这里还不会深究
'wtimeout'
跟上面的'w'绑定使用的，后面会介绍的
'timeout'
客户端等待服务器响应的超时时间，也就是如果php的服务器等待mongodb数据库写入数据的时间，如果超过了timeout规定的时间，就算本次写入失败
---------------------------------------------------函数2----------------------------------------
筛选数据函数 find
$mongo->$db_name->$collection_name->find($situation,$field)
-----------------------------------------------------------
$situation可以为空，表示返回全部数据，也可以是一个数组，表示筛选条件，就跟mongodb命令一样
$field也可以为空，表示返回全部字段，也可以跟第一个参数一样，传入数组，规定返回的字段
注意，即使上面使用$field限制返回字段，_id字段还是会自动返回的
---------------------------------------------------函数3----------------------------------------
更新数据 update
$mongo->$db_name->$collection_name->update($criteria,$update,$option)
---------------------------------------------------------------------
结合shell命令就很好理解了
$criteria 表示筛选进行更新的文档
$update 就是要更新后的数据
$option=array(
	'upsert'=>false,
	'multiple'=>true,
	'fsync'=>false,
	'w'=>1,
	'wtimeout'=>10000,
	'timeout'=>10000
)
后几个的作用在函数1里讲过了就不赘述了，upsert为true表示如果当前文档存在就更新，不存在就创建，multiple为真表示匹配该条件的文档都会被更新，即不止更新满足条件的一个文档。
---------------------------------------------------函数3----------------------------------------
删除数据 remove
$mongo->$db_name->$collection_name->remove($remove,$option)
-----------------------------------------------------------
$remove表示跟find()第一个参数一样的筛选条件
$option=array(
	'justOne'=>false,
	'fsync'=>false,
	'w'=>1,
	'j'=>false,
	'wtimeout'=>10000,
	'timeout'=>10000
)
上面有的我就不赘述了，justOne根据名字来，就一个，如果为true表示就删除一个匹配$remove的文档

*/