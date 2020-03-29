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
    $match = preg_match_all('#<' . $tagName . '[\S|\s]*?>\S*?</' . $tagName . '>#i', $html, $result);
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

function getAttribute(string $tag, string $attribute): array
{
    $result = [];
    $match = preg_match_all('#' . $attribute . '="[\S|\s]*?"#i', $tag, $result);
    if (0 == $match) {
        return [];
    }
    $aTags = [];
    foreach ($result as $matchs) {
        foreach ($matchs as $match) {
            $aTags[] = rtrim(ltrim($match, $attribute . '="'), '"');
        }
    }
    return $aTags;
}

for ($i = 0; $i <= 293; $i++) {
    foreach (matchATag(file_get_contents("page/$i.html")) as $aTag) {
        $classMatch = false;
        foreach (getAttribute($aTag, 'class') as $class) {
            if ($class == 'title fs14  color333 clear') {
                $classMatch = true;
                break;
            }
        }
        if ($classMatch) {
            $links = getAttribute($aTag, 'href');
            foreach ($links as $link) {
                if (false !== mb_stripos($link, 'www.sge.com.cn')) {
                    continue;
                }
                echo 'https://www.sge.com.cn' . $link . PHP_EOL;
            }
        }
    }
}
