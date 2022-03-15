# 上海黄金交易所爬虫

- 作用：获取所有历史的、每一天公布的详情。
- 安装： `pip install -r requirements.txt` 。
- 运行： `scrapy crawl items -o items.jl` 。或者 `scrapy crawl items -o items.jl -a max_page=2` 只获取前面的2页也可以。
- 按顺序运行： `python tools/items.py` 和 `python tools/sort.py` 。
- 执行 `scrapy crawl details -o files.jl` 获取详情页面，支持指定 `-a max_deep=10` 获取发布日期最近的10个详情页面。
- 最后，如果需要的可以用 `python tools/get_table.py` 脚本提取数据。

备注：

1. 如果安装时遇到下载依赖包失败的情况，可以尝试更换国内的源。例如清华大学，或阿里云的源： `pip install -r requirements.txt -i http://mirrors.aliyun.com/pypi/simple/ --trusted-host mirrors.aliyun.com` 。
2. `python` 的版本理论上可以是 `3.6` 到 `3.10` 。我本地是 `3.6` 和 `3.9` ，别的版本没有试过。

对于每天增量更新数据，可以：

    scrapy crawl items -o items.jl -a max_page=1
    python tools/items.py
    python tools/sort.py
    scrapy crawl details -o files.jl
