import scrapy
import logging

class ItemsSpider(scrapy.Spider):
    # 这个名称会在终端命令输入的时候被使用到，它必须在项目内唯一。
    # 例如： scrapy crawl items 开始执行
    name = "items"

    def start_requests(self):
        self._cookie_dist = {}
        urls = [
            'http://www.sge.com.cn/sjzx/mrhqsj?p=1'
        ]
        for url in urls:
            yield scrapy.Request(url=url, callback=self.parse,
                headers={'Referer':url})

    def parse(self, response):
        #临时调试用的
        #page = response.url.split("=")[-1]
        #url = response.url.split("?")[0]
        #filename = f'items_{page}.html'
        #with open(filename, 'wb') as f:
        #    f.write(response.body)

        # 可以在命令上加 -a max_page=10 限制只获取前10页，0代表不限制
        max_page = int(getattr(self, 'max_page', '0'))

        # 获取上一次请求的请求头中Cookie内容，然后用响应头的Cookie合并覆盖，得到当前最新的Cookie
        cookie_dist = dict(response.request.cookies, **self._parse_cookie(response.headers.getlist('Set-Cookie')))

        all_item_count = 0

        # 提取页面中的每一个项目——“项目”是指类似“上海黄金交易所20XX年XX月XX日交易行情”这种进入详情页面的文字和链接。
        for maybe_item in response.css('li.border_ea_b'):
            if self._is_item(maybe_item):
                item_data = self._get_item_data(maybe_item)
                item_data['referer'] = response.url
                item_data['cookie'] = cookie_dist
                all_item_count = all_item_count + 1
                yield item_data

        have_next_page = False

        # 判断当前请求结果里是否存在可以点击的下一页按钮，如果有那么让爬虫跟随下一页继续爬取数据
        # 通过“noLeft_border”获取div，也许会获取到多个，这里需要遍历每一个div判断div是不是拥有下一页按钮，
        # 如果有那么这个div就是我们想要的那个div
        for maybe_next in response.css('div.noLeft_border'):
            if '下一页' == maybe_next.css('::text').get(): # 是想要的div，下面处理完成后可以break
                next_page_number = self._get_next_page_number(maybe_next.attrib)
                if next_page_number > 0:
                    have_next_page = True
                    if 0 == max_page or next_page_number - 1 < max_page:
                        yield response.follow(response.url.split("?")[0] + '?p=' + str(next_page_number), self.parse,
                            headers={'Referer':response.url}, cookies=cookie_dist)
                break

        # 正常的情况是每页10条，除非是最后一页
        if have_next_page and all_item_count != 10:
            logging.warning('当前页面项目数量不等于10，页面地址：' + response.url +  '，项目数量：' + str(all_item_count))
        if not have_next_page and all_item_count > 10:
            logging.warning('最后一页面项目数量大于10，页面地址：' + response.url +  '，项目数量：' + str(all_item_count))

    def _get_next_page_number(self, onclick_attrib):
        """输入参数onclick_attrib是一个列表。如果成功获取到页数那么返回一个代表页数的整数，否则返回-1。"""
        page_number = -1
        if 'onclick' in onclick_attrib:
            for ch in onclick_attrib['onclick'].split('\''):
                if ch.isdigit():
                    page_number = int(ch)
                    break
        return page_number

    def _is_item(self, maybe_item):
        """判断是否成功拿到一个项目"""
        hrefs = maybe_item.css('a::attr("href")')
        # 存在一个有问题的页面，这个页面按照此规则获取的是2个，只有第一个是我们想要的
        if len(hrefs) != 1 and len(hrefs) != 2:
            return False
        spans = maybe_item.css('span')
        # 存在一个有问题的页面，这个页面按照此规则获取的是3个
        if len(spans) != 2 and len(spans) != 3:
            return False
        if len(maybe_item.css('span.txt')) != 1:
            return False
        if len(maybe_item.css('span.fr')) != 1:
            return False
        return True

    def _get_item_data(self, item):
        """拿到项目中的数据"""
        return {
            'url': item.css('a::attr("href")').get(),
            'title': item.css('span.txt::text').get(),
            'date': item.css('span.fr::text').get()
        }

    def _parse_cookie(self, byte_list):
        """解析Cookie，转换为普通的字典"""
        result = {}
        for byte_cookie in byte_list:
            utf8_string = byte_cookie.decode()
            setting = utf8_string.split(';')
            key_and_others = setting[0].split('=')
            if len(key_and_others) < 2:
                continue
            key = key_and_others[0]
            value = key_and_others[1]
            result[key] = value
        return result
