<?php

class PageDownloadTool
{
    private $curlResource;

    public function __construct($curl)
    {
        $this->curlResource = $curl;
        $params = [
            CURLOPT_AUTOREFERER => true,
            CURLOPT_FILETIME => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FORBID_REUSE => false,
            CURLOPT_FRESH_CONNECT => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYSTATUS => false, // 设置false禁止验证证书状态，但是需要高版本支持
            CURLOPT_CONNECTTIMEOUT => 10, // 尝试连接等待秒数
            CURLOPT_DNS_CACHE_TIMEOUT => 300, // 内存缓存DNS的时间
            CURLOPT_MAXCONNECTS => 100, // 最大连接数
            CURLOPT_MAXREDIRS => 20, // 最大重定向次数
            CURLOPT_TIMEOUT => 60, // curl函数执行的最长时间
            //CURLOPT_COOKIEJAR => 'cookie.txt', 没必要设置
            //CURLOPT_REFERER => '', // referer头
            //CURLOPT_URL => '',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0;',
        ];
        if (false === curl_setopt_array($this->curlResource, $params)) {
            throw new Exception('设置失败');
        }
    }

    public function get(string $url, string $refer = ''): string
    {
        if (false === curl_setopt($this->curlResource, CURLOPT_URL, $url)) {
            throw new Exception('设置URL');
        }
        if (!empty($refer) && false === curl_setopt($this->curlResource, CURLOPT_REFERER, $refer)) {
            throw new Exception('设置referer失败');
        }
        if (false == ($result = curl_exec($this->curlResource))) {
            throw new Exception('请求页面失败');
        }
        return $result;
    }
}

if (!is_dir('page') && false === mkdir('page')) {
    echo '创建目录page失败' . PHP_EOL;
    die();
}

if (false === ($h = curl_init())) {
    echo '创建curl资源失败' . PHP_EOL;
    die();
}

$base = 'https://www.sge.com.cn/sjzx/mrhqsj';
$urls = [];
for ($i = 1; $i <= 294; $i++) {
    $urls[] = $base . '?' . http_build_query(['p' => $i]);
}

try {
    $tool = new PageDownloadTool($h);
    foreach ($urls as $k => $url) {
        echo $url . PHP_EOL;
        $result = $tool->get($url, $urls[$k - 1] ?? $base);
        file_put_contents("page/$k.html", $result);
    }
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}

curl_close($h);
