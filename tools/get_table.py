# coding=utf-8

import os
import logging
from lxml import etree
import json
import re

def verify_and_get_info(tree, file):
    # 返回
    public_date = ''
    title_texts = []
    origin_h1_texts = []
    table_datas = []
    warnings = []

    elements = tree.findall('.//div[@class]/p/span/i')
    dates = []
    for e in elements:
        if e.tail is not None and re.match('^[0-9]+-[0-9]+-[0-9]+$', e.tail.strip()) is not None:
            dates.append(e.tail.strip())
    if len(dates) != 1:
        w = '应该有一个发布时间，但是实际找到了：' + str(len(dates)) + '，文件：' + file
        logging.warning(w)
        warnings.append(w)
    else:
        public_date = dates[0]

    titles = tree.findall('.//h1')
    if len(titles) != 1:
        w = '应该有一个标题<h1></h1>，但是实际找到了：' + str(len(titles)) + '，文件：' + file
        logging.warning(w)
        warnings.append(w)

    for title in titles:
        origin_h1_texts.append(title.text.strip())
        if re.match('^上海黄金交易所[0-9]+年[0-9]+月[0-9]+日交易行情$', title.text.strip()) is not None:
            title_texts.append(title.text.strip())
            continue
        if re.match('^上海黄金交易所[0-9]+月[0-9]+日交易行情$', title.text.strip()) is not None:
            title_texts.append(title.text.strip())
            continue
        if re.match('^[0-9]+年[0-9]+月[0-9]+日交易行情$', title.text.strip()) is not None:
            title_texts.append(title.text.strip())

    if len(title_texts) != 1:
        w = '按照设定的格式，应该找到一个标题，但是实际找到了：' + str(len(title_texts)) + '，标题：' + ','.join(title_texts) + '。文件：' + file
        logging.warning(w)
        warnings.append(w)

    tables = tree.findall('.//table')
    if len(tables) != 1:
        w = '应该有一个表格<table></table>，但是实际找到了：' + str(len(tables)) + '，文件：' + file
        logging.warning(w)
        warnings.append(w)

    for table in tables:
        table_data = {'head': [], 'body': []}
        for head in table.findall('.//tr/th'):
            table_data['head'].append(head.text.strip())
        for row in table.findall('.//tr'):
            data = []
            for td in row.findall('.//td'):
                if td.text is None:
                    data.append('')
                else:
                    data.append(td.text.strip())
            table_data['body'].append(data)
        table_datas.append(table_data)
    return {
        'public_date': public_date,
        'title_texts': title_texts,
        'table_datas': table_datas,
        'file': file,
        'warnings': warnings,
        'origin_h1_texts': origin_h1_texts
    }

def handle_file(file):
    logging.info('处理文件：' + file)
    tree = etree.parse(file, etree.HTMLParser())
    return verify_and_get_info(tree, file)

if __name__ == '__main__':
    #logging.basicConfig(level=logging.DEBUG)
    with open('parse_result.jl', 'w') as fp:
        for file in os.listdir('details'):
            if file.endswith('.html'):
                result = handle_file('details' + os.sep + file)
                fp.write(json.dumps(result) + os.linesep)

