# 黄金价格爬取工具

这个工具爬取的是每天的价格，不是实时的。来源于上海黄金交易所。

## 使用方法

*仓库已经存在的数据使用这些步骤会导致文件里的数据被覆盖或被添加。*

1. 执行`php page.php`，爬分页数据到当前目录下的`page`目录。如果`page`不存在会自动建立。
2. 执行`php link.php>links.txt`，解析页面中的链接，并保存为`links.txt`。
3. 执行`php details.php`，下载价格信息，保存到`details`目录，目录不存在会自动建立。
4. 执行`php clear.php`，清理数据。清理后页面位于`clear`下，不存在目录会自动建立。
5. 执行`php csv.php`，提取价格。生成文件夹`result`。
6. 执行`php summary.php`，从`result`内提取数据，保存到文件`summary.csv`。

`summary.php`目前提取了日期、开盘价和收盘价，修改一下可以提取其它的价格。
