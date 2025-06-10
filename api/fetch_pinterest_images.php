<?php

$data = [];

$rss_url = "https://www.pinterest.jp/". $_GET["pint_user"] ."/feed.rss/";
$rssData = simplexml_load_string(file_get_contents($rss_url));

$format = rss_format_get($rssData);
switch ($format) {
    case "ATOM":
        $info_data = atom_info_get($rssData);
        $feed_data = atom_feed_get($rssData);
        break;
    case "RSS1.0":
        $info_data = rss1_info_get($rssData);
        $feed_data = rss1_feed_get($rssData);
        break;
    case "RSS2.0":
        $info_data = rss2_info_get($rssData);
        $feed_data = rss2_feed_get($rssData);
        break;
    default:
        print("FORMAT ERROR\n");
        exit;
}

header('Content-type: application/json');

$response = [
    'error_status' => "0",
    'response_feed_count' => count($feed_data),
    'request_url' => $rss_url,
    'rss_format' => $format,
    'response_info' => $info_data,
    'response_feed' => $feed_data
];

echo json_encode($response) ;

/*
 function
*/
function rss_format_get($rssData) {
    if ($rssData->entry) {
        //ATOM
        return "ATOM";
    } elseif ($rssData->item) {
        //RSS1.0
        return "RSS1.0";
    } elseif ($rssData->channel->item) {
        //RSS2.0
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