# 石墨文档“文件夹分享”插件服务端代码

这是chrome浏览器扩展程序“石墨文档-文件夹分享”的服务端代码。

插件地址：https://github.com/ionepub/shimo-fav/releases

## 使用方法

本服务端代码可以在传统PHP环境中使用，也可以在新浪sae平台使用。

### 调整插件中的服务器地址

首先需要在`shimo-fav`中找到`/js/background.js`文件，修改`baseUrl`地址为你自己的服务器地址：

```javascript
var baseUrl = "http://shimofav.applinzi.com/";
```

### PHP环境

1. [下载代码](https://github.com/ionepub/shimo-fav-server/archive/master.zip "下载代码")并将代码放置到已经搭建好的PHP环境中
2. 新建数据库，默认的数据库名为 `fav`
3. 将`data/fav.sql` 导入到数据库中
4. 修改 `conf/config.php` 中的数据库连接配置信息
5. 完成

### SAE

新浪SAE地址：`http://sae.sina.com.cn/` ，用这个邀请链接注册有免费云豆奖励： `http://t.cn/R4jXOIj`

**在SAE上使用的不是数据库，而是KVDB，可以直接上传代码。**

1. 创建应用之后，用SVN或直接打包上传到SAE
2. 完成

## 安全性

如果不想其他人的插件对你自建的服务器产生影响，可以在入口文件中调整代码：

```php
$referer = $_SERVER['HTTP_ORIGIN'];

if(preg_match("/chrome-extension:\/\/[a-z]{32}$/", $referer)){
	header('Access-Control-Allow-Origin:' . $referer);
}

// $referer = 'chrome-extension://pomeehplgpmjmokdggcjepcglmcnkdoc';
// header('Access-Control-Allow-Origin:' . $referer);
```

改为：

```php
$referer = $_SERVER['HTTP_ORIGIN'];

// if(preg_match("/chrome-extension:\/\/[a-z]{32}$/", $referer)){
	// header('Access-Control-Allow-Origin:' . $referer);
// }

$referer = 'chrome-extension://pomeehplgpmjmokdggcjepcglmcnkdoc';
header('Access-Control-Allow-Origin:' . $referer);
```

并将其中的 `pomeehplgpmjmokdggcjepcglmcnkdoc` 替换为自己的插件id（请勿泄露插件key文件）。

![插件id](https://dn-shimo-image.qbox.me/otoprSAY6kQwGy7n/image.png "插件id")