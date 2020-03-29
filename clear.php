<?php

/**
 * 返回html中全部a标签
 *
 * @param string $html
 * @return array
 */
function matchATag(string $html, string $tagName = 'a'): array
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

if (!is_dir('clear') && false === mkdir('clear')) {
    echo '创建目录clear失败' . PHP_EOL;
    die();
}

$titles = [];
for ($i = 0; $i < 2937; $i++) {
    $body = '';
    $title = '';
    $file = file_get_contents("details/$i.html");
    foreach (matchATag($file, 'table') as $table) {
        $body .= $table;
    }
    foreach (matchATag($file, 'h1') as $h1) {
        $title .= htmlspecialchars(rtrim(ltrim($h1, '<h1>'), '</h1>'));
    }
    if (empty($body)) {
        echo '提取失败：' . $i . '.html' . PHP_EOL;
        continue;
    }
    $titles[$i] = $title;
    $page = <<<CONTEXT
<html>
<head>
<meta charset="UTF-8">
<title>$title</title>
</head>
<body>
<h1>$title</h1>
<p>$body</p>
</body>
</html>
CONTEXT;
    file_put_contents("clear/$i.html", $page);
}

$links = '';
foreach ($titles as $k => $title) {
    $links .= "<li><a href=\"$k.html\">$title</a></li>";
}

$page = <<<CONTEXTINDEX
<html>
<head>
<meta charset="UTF-8">
</head>
<body>
<p><ul>$links</ul></p>
</body>
</html>
CONTEXTINDEX;
file_put_contents("clear/index.html", $page);

