<?php

if (!is_dir('result')) {
    echo '文件夹 result 不存在' . PHP_EOL;
    die();
}

$dir = opendir('result');
if (false === $dir) {
    echo '打开文件夹 result 失败' . PHP_EOL;
    die();
}

file_put_contents('summary.csv', '日期,合约,开盘价,收盘价' . PHP_EOL);
while ($file = readdir($dir)) {
    if (in_array($file, ['.', '..'])) {
        continue;
    }
    if ('csv' == pathinfo($file, PATHINFO_EXTENSION)) {
        $fp = fopen("result/$file", 'rb');
        if (false === $fp) {
            echo '打开文件失败，' . "result/$file" . PHP_EOL;
            continue;
        }
        $dateLine = fgetcsv($fp);
        if (!isset($dateLine[0])) {
            echo "result/$file 格式错误" . PHP_EOL;
            fclose($fp);
            continue;
        }
        $row = [$dateLine[0]];
        $open = $close = 0;
        for ($i = 0; $i < 4; $i++) {
            $header = fgetcsv($fp);
            foreach ($header as $k => $v) {
                if (false !== mb_stripos($v, '开盘') || false !== mb_stripos(mb_strtolower($v), 'open')) {
                    $open = $k;
                }
                if (false !== mb_stripos($v, '收盘') || false !== mb_stripos(mb_strtolower($v), 'close')) {
                    $close = $k;
                }
            }
            if ($open != 0 && $close != 0) {
                break;
            }
        }
        if ($open == 0 || $close == 0) {
            echo "result/$file 格式错误，表头格式异常" . PHP_EOL;
            fclose($fp);
            continue;
        }
        $match = false;
        while ($rowData = fgetcsv($fp)) {
            if (
                isset($rowData[0])
                && mb_stripos(str_replace('.', '', mb_strtolower($rowData[0])), 'au9999') !== false
                && mb_stripos(str_replace('.', '', mb_strtolower($rowData[0])), 'iau9999') === false
            ) {
                if (!isset($rowData[$open]) || !isset($rowData[$close])) {
                    break;
                }
                $row[] = str_replace('&nbsp;', '', $rowData[0]);
                $row[] = str_replace('&nbsp;', '', $rowData[$open]);
                $row[] = str_replace('&nbsp;', '', $rowData[$close]);
                $match = true;
                break;
            }
        }
        if (!$match) {
            echo "result/$file 格式错误，数据格式异常" . PHP_EOL;
            fclose($fp);
            continue;
        }
        fclose($fp);
        file_put_contents('summary.csv', implode(',', $row) . PHP_EOL, FILE_APPEND);
    }
}

closedir($dir);
