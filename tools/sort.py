# coding=utf-8

import json
from datetime import datetime
import os

def get_date(json_dist):
    return datetime.strptime(json_dist['date'], '%Y-%m-%d')

if __name__ == '__main__':
    data = []

    # 读取数据到data
    with open('items_uniq.jl', 'r') as fp:
        endpos = fp.seek(0, 2)
        fp.seek(0)
        while fp.tell() < endpos:
            try:
                data.append(json.loads(fp.readline()))
            except json.decoder.JSONDecodeError:
                pass

    # 按照date属性逆排序
    data.sort(reverse=True, key=get_date)

    # 保存
    with open('items_sort.jl', 'w') as fp:
        for json_dist in data:
            fp.write(json.dumps(json_dist) + os.linesep)
