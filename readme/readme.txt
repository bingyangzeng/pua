+----------------------------------+
 Anwsion 问答系统简介
+----------------------------------+
Anwsion 问答系统是一套开源的社交化问答软件系统。作为国内首个推出基于 PHP 的社交化问答系统，Anwsion 期望能够给更多的站长或者企业提供一套完整的社交问答系统，帮助社区或者企业搭建相关的知识库建设。

+----------------------------------+
 Anwsion 问答系统的下载
+----------------------------------+
您可以随时从我们的官方下载站下载到最新版本，以及各种补丁

http://www.anwsion.com/download/

+----------------------------------+
 Anwsion 问答系统的环境需求
+----------------------------------+
1. 可用的 www 服务器，如 Apache、IIS、nginx, 推荐使用性能高效的 nginx.
2. PHP 5.2.2 及以上
3. MySQL 5.0 及以上, 服务器需要支持 MySQLi 或 PDO_MySQL
4. GD 图形库支持或 ImageMagick 支持, 推荐使用 ImageMagick, 在处理大文件的时候表现良好

+----------------------------------+
 Anwsion 问答系统的安装
+----------------------------------+
1. 上传 upload 目录中的文件到服务器
2. 设置目录属性（windows 服务器可忽略这一步）
	以下这些目录需要可读写权限
	./
	./system
	./system/config 含子目录
3. 执行安装脚本 http://您的域名/install/
   请在浏览器中运行 install 程序，即访问 http://您的域名/install/
4. 参照页面提示，进行安装，直至安装完毕

+----------------------------------+
 Anwsion 问答系统的升级
+----------------------------------+

升级过程非常简单, 覆盖所有文件之后运行 http://您的域名/index.php?/upgrade/ 按照提示操作即可

注意: 升级程序仅适用于 0.4 Beta 7 及以上版本升级

(*) 0.7 Beta 升级注意事项: http://wenda.anwsion.com/question/964

+----------------------------------+
 Anwsion 系统使用常见问题
+----------------------------------+

1. 显示数据库驱动不支持

MySQLi: http://zhidao.baidu.com/question/100646975.html
PDO_MySQL: http://zhidao.baidu.com/question/328353076.html

两种安装任意一种即可保证运行

+----------------------------------+
 Anwsion Rewrite 开启方法
+----------------------------------+

第一步：
Apache:

<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>

Nginx:

location  /  {
	if (!-e $request_filename)
	{
		rewrite (.*) /index.php;
	}
}

第二步：

访问 http://您的域名/?/admin/，在全局 - 站点功能 - 开启 Rewrite 伪静态，并选择对应的 URL 显示规则。


+----------------------------------+
 Anwsion 软件的技术支持
+----------------------------------+
当您在安装、升级、日常使用当中遇到疑难，请您到以下站点获取技术支持。

Anwsion 讨论区：http://www.anwsion.com

