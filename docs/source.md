###使用压缩包安装

进入[框架下载页面](http://git.oschina.net/ideaa/crossphp/tags "crossphp tag list"), 下载最新版框架, 解压到Skeleton同级目录, 打开Skeleton根目录下的`crossboot.php`文件, 找到下面的代码进行修改.
	
	//使用composer install来进行安装
	//require PROJECT_PATH . '/vendor/autoload.php';
	
	//使用压缩包安装时候,需要引入框架根目录下的boot.php文件
	require PROJECT_PATH . '/../crossphp/boot.php';