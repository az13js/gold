# coding=utf-8

# 此脚本的目的是进行去重处理，因为items.jl是反复爬数据不断追加的，有重复。
# 去重的方法是按照URL来，URL具有唯一性。标题和日期经过检查发现，是有重复的。
# 而且存在日期、标题一样但是内容不一样的。

import json
import os

if __name__ == '__main__':
    urls = []
    write_fp = open('items_uniq.jl', 'w')
    with open('items.jl', 'r') as fp:
        endpos = fp.seek(0, 2)
        fp.seek(0)
        while fp.tell() < endpos:
            line = fp.readline()
            try:
                json_dist = json.loads(line)
                if json_dist['url'] not in urls:
                    write_fp.write(json.dumps(json_dist) + os.linesep)
                    urls.append(json_dist['url'])
            except json.decoder.JSONDecodeError:
                pass
    write_fp.close()

