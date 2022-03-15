import scrapy
import os
import json
import hashlib

class DetailsSpider(scrapy.Spider):
    # 这个名称会在终端命令输入的时候被使用到，它必须在项目内唯一。
    # 例如： scrapy crawl details 开始执行
    name = "details"

    def start_requests(self):
        website = 'http://www.sge.com.cn'
        # 可以在命令上加 -a max_deep=10 限制只获取前10个，0代表不限制
        max_deep = int(getattr(self, 'max_deep', '0'))
        deep = 0
        with open('items_sort.jl', 'r') as fp:
            endpos = fp.seek(0, 2)
            fp.seek(0)
            while fp.tell() < endpos:
                try:
                    json_dist = json.loads(fp.readline())
                    deep = deep + 1
                    if 0 != max_deep and deep > max_deep:
                        return
                    yield scrapy.Request(url=website + json_dist['url'], callback=self.parse,
                        headers={'Referer':json_dist['referer']}, cookies=json_dist['cookie'])
                except json.decoder.JSONDecodeError:
                    pass

    def parse(self, response):
        path_data = response.url.split("/")
        if len(path_data) == 0:
            id_str = '0'
        else:
            id_str = path_data[-1]

        md5 = hashlib.md5()
        md5.update(response.body)

        # 文件名称按照URL里面的数字+MD5防重复来生成
        file = 'details' + os.sep + id_str + '.' + md5.hexdigest() + '.html'
        with open(file, 'wb') as fp:
            fp.write(response.body)

        yield {'file': file, 'url': response.url}
