<?php

/**
 * 返回html中标签
 *
 * @param string $html
 * @return array
 */
function matchTag(string $html, string $tagName = 'a'): array
{
    $result = [];
    $match = preg_match_all('#<' . $tagName . '[\S|\s]*?>[\S|\s]*?</' . $tagName . '>#i', $html, $result);
    if (0 == $match) {
        return [];
    }
    $aTags = [];
    foreach ($result as $matchs) {
        foreach ($matchs as $match) {
            $aTags[] = $match;
        }
    }
    return $aTags;
}

/**
 * 返回html中标签
 *
 * @param string $html
 * @return array
 */
function matchInnerText(string $html): string
{
    $result = [];
    $match = preg_match_all('#<[\S|\s]*?>[\S|\s]*?</[\S|\s]*?>#i', $html, $result);
    if (0 == $match) {
        return [];
    }
    $aTags = [];
    foreach ($result as $matchs) {
        foreach ($matchs as $match) {
            $innerTexts = [];
            preg_match_all('#>[\S|\s]*?</#i', $match, $innerTexts);
            foreach ($innerTexts as $v) {
                foreach ($v as $vv) {
                    $asResult = rtrim(ltrim($vv, '>'), '</');
                    $noise = [];
                    preg_match_all('#<[\S|\s]*?>#i', $asResult, $noise);
                    foreach ($noise as $noiseBlock) {
                        foreach ($noiseBlock as $realNoise) {
                            $asResult = str_replace($realNoise, '', $asResult);
                        }
                    }
                    $aTags[] = $asResult;
                }
            }
        }
    }
    return implode('', $aTags);
}

if (!is_dir('result') && false === mkdir('result')) {
    echo '创建目录 result 失败' . PHP_EOL;
    die();
}

for ($i = 0; $i < 2937; $i++) {
    if (!file_exists("clear/$i.html")) {
        echo "无文件：clear/$i.html" . PHP_EOL;
        continue;
    }
    $file = file_get_contents("clear/$i.html");
    $titles = matchTag($file, 'h1');
    if (!isset($titles[0])) {
        echo "clear/$i.html 没有标题" . PHP_EOL;
        die();
    }
    $title = rtrim(ltrim($titles[0], '<h1>'), '</h1>');
    $title = str_replace('年', '/', $title);
    $title = str_replace('月', '/', $title);
    $title = str_replace('日', '', $title);
    $title = str_replace('上海黄金交易所', '', $title);
    $title = str_replace('交易行情', '', $title);
    if (empty($title)) {
        echo "clear/$i.html 标题是空的" . PHP_EOL;
        die();
    }
    $date = explode('/', $title);
    if (count($date, COUNT_NORMAL) == 2) {
        $date[2] = $date[1];
        $date[1] = $date[0];
        $date[0] = '2002';
    }
    if (count($date) > 3) {
        echo "clear/$i.html 日期参数太多" . PHP_EOL;
        continue;
    }
    $date[2] = trim($date[2]);
    $date[1] = trim($date[1]);
    $date[0] = trim($date[0]);
    foreach ($date as $d) {
        if (!is_numeric($d)) {
            echo "clear/$i.html 日期参数非数字" . PHP_EOL;
            var_dump($date);
            die();
        }
    }
    $date[2] = sprintf('%02d', $date[2]);
    $date[1] = sprintf('%02d', $date[1]);
    $date[0] = sprintf('%02d', $date[0]);
    $dateTime = implode('', $date);
    $tables = matchTag($file, 'table');
    if (count($tables, COUNT_NORMAL) > 1) {
        echo "clear/$i.html 表格太多" . PHP_EOL;
        continue;
    }
    if (empty($tables)) {
        echo "clear/$i.html 表格不存在" . PHP_EOL;
        continue;
    }
    $table = $tables[0];
    $datas = [];
    $fp = fopen("result/$dateTime.csv", 'awb');
    if (false === $fp) {
        echo "打开 result/$dateTime.csv 失败" . PHP_EOL;
        die();
    }
    fputcsv($fp, [$dateTime]);
    foreach (matchTag($file, 'tr') as $tr) {
        $contents = array_merge(matchTag($tr, 'th'), matchTag($tr, 'td'));
        $columns = [];
        foreach ($contents as $t) {
            $temp = trim(matchInnerText($t));
            $temp = str_replace("\r", '', $temp);
            $temp = str_replace("\n", '', $temp);
            $temp = str_replace("\t", '', $temp);
            $temp = str_replace(' ', '', $temp);
            $columns[] = $temp;
        }
        fputcsv($fp, $columns);
    }
    fclose($fp);
}
