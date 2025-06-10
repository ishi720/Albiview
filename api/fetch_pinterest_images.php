<?php

declare(strict_types=1);

$rssUrl = "https://www.pinterest.jp/". $_GET["pint_user"] ."/feed.rss/";
$rssData = simplexml_load_string(file_get_contents($rssUrl));

$format = getRssFormat($rssData);
switch ($format) {
    case "ATOM":
        $infoData = atom_info_get($rssData);
        $feedData = atom_feed_get($rssData);
        break;
    case "RSS1.0":
        $infoData = rss1_info_get($rssData);
        $feedData = rss1_feed_get($rssData);
        break;
    case "RSS2.0":
        $infoData = rss2_info_get($rssData);
        $feedData = rss2_feed_get($rssData);
        break;
    default:
        print("FORMAT ERROR\n");
        exit;
}

header('Content-type: application/json');

echo json_encode([
    'error_status' => "0",
    'response_feed_count' => count($feedData),
    'request_url' => $rssUrl,
    'rss_format' => $format,
    'response_info' => $infoData,
    'response_feed' => $feedData
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ;

/**
 * RSSデータの形式を判定して返す
 *
 * @param SimpleXMLElement $rssData パース済みのRSSデータ
 * @return string|null RSSの形式 ("ATOM"、"RSS1.0"、"RSS2.0")、または不明な場合は null
 */
function getRssFormat(SimpleXMLElement $rssData): ?string {
    if ($rssData->entry) {
        return "ATOM";
    } elseif ($rssData->item) {
        return "RSS1.0";
    } elseif ($rssData->channel->item) {
        return "RSS2.0";
    } else {
        return null;
    }
}

// info_get
function rss1_info_get($rssData) {
    foreach ($rssData->channel as $channel) {
        $work = array();
        foreach ($channel as $key => $value) {
            $work[$key] = (string)$value;
        }
        $data[] = $work;
    }
    return $data;
}
function rss2_info_get($rssData) {
    foreach ($rssData->channel as $channel) {
        $work = array();
        foreach ($channel as $key => $value) {
            $work[$key] = (string)$value;
        }
        $data[] = $work;
    }
    return $data;
}
function atom_info_get($rssData) {
    foreach ($rssData as $item) {
        $work = array();
        $work['title'] = (string)$item;
        $data[] = $work;
    }
    return $data;
}

// feed_get
function rss1_feed_get($rssData) {
    foreach ($rssData->item as $item) {
        $work = array();

        foreach ($item as $key => $value) {
            $work[$key] = (string)$value;
        }

        //dc
        foreach ($item->children('dc',true) as $key => $value) {
            $work['dc:'. $key] = (string)$value;
        }

        //content
        foreach ($item->children('content',true) as $key => $value) {
            $work['content:'. $key] = (string)$value;
        }

        $data[] = $work;
    }
    return $data;
}
function rss2_feed_get($rssData) {
    foreach ($rssData->channel->item as $item) {
        $work = array();
        foreach ($item as $key => $value) {
            $work[$key] = (string)$value;
        }
        $data[] = $work;
    }
    return $data;
}
function atom_feed_get($rssData){
    foreach ($rssData->entry as $item){
        $work = array();
        foreach ($item as $key => $value) {
            if ($key == "link") {
                $work[$key] = (string)$value->attributes()->href;;
            } else {
                $work[$key] = (string)$value;
            }
        }
        $data[] = $work;
    }
    return $data;
}