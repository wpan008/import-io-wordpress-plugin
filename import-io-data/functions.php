<?php
	
// Issues a query request to import.io
function query($connectorGuid, $input, $userGuid, $apiKey) {
    $url = "https://query.import.io/store/connector/" . $connectorGuid . "/_query?_user=" . urlencode($userGuid) . "&_apikey=" . urlencode($apiKey);
    //error_log("import.io request".$url);
    $ch = curl_init($url);
    //error_log("import.io ch".$ch);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "import-io-client: import.io PHP client",
        "import-io-client-version: 2.0.0"
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode(array("input" => $input)));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $result = curl_exec($ch);
    //error_log("import.io error".curl_error($ch));
    curl_close($ch);
    //error_log("import.io result".$result);
    return json_decode($result, true);
}

function import_io_runner(){
    error_log("import_io_runner started");
    $userGuid = get_option('import_io_setting_user_guid');
    $apiKey = get_option('import_io_setting_api_key');
    $sitesImportIoOptions = get_option('import_io_sites');
    if($sitesImportIoOptions){
        foreach($sitesImportIoOptions as $siteOption){
            import_io_crawl_site($userGuid, $apiKey, $siteOption['import_io_site_connector_guid'], $siteOption['import_io_page_connector_guid'], 
                $siteOption['import_io_author_id'], $siteOption['import_io_site_name'], $siteOption['import_io_site_urls'], $siteOption['import_io_categories'],
                $siteOption['import_io_tags'], $siteOption['import_io_site_full_page_args']);    
        }
    }
    error_log("import_io_runner finished");
}

add_action('import_io_run_event', 'import_io_runner');    

function import_io_crawl_site($userGuid, $apiKey, $siteConnectorGuid, $pageConnectorGuid, $authorId, $siteName = NULL, $siteUrl, $categoryIds, $tags, $fullPageArgs = NULL){
    error_log("start crawling site ".$siteUrl);
    $searchResult = query($siteConnectorGuid, array("webpage/url" => $siteUrl), $userGuid, $apiKey, false);
    if(!is_null($searchResult)){
        if(array_key_exists('results', $searchResult)){
            $articleLinks = array_slice($searchResult['results'], 0, 5); 
            foreach ($articleLinks as $item) {
                $articleId = $item['title_url'];
                $args = array(
                    'meta_key'   => 'external_id',
                    'meta_value' => $articleId,
                    'post_type'  => 'post',
                );
                $query = new WP_Query($args);
                if (!$query->have_posts()) {
                    $pageUrl = is_null($fullPageArgs) ? $articleId : $articleId . $fullPageArgs;
                    error_log("start crawling page ".$pageUrl);
                    $articleResult = query($pageConnectorGuid, array("webpage/url" => $pageUrl), $userGuid, $apiKey, false);
                    if(!is_null($articleResult)){
                        if(!array_key_exists('error', $articleResult)){
                            if(array_key_exists('results', $articleResult) && !is_null($articleResult['results'])){
                                if(!is_null($articleResult['results'][0]) && array_key_exists('title', $articleResult['results'][0]) && array_key_exists('content', $articleResult['results'][0])){									
                                    $my_post = array(
                                        'post_title'    => $articleResult['results'][0]['title'],
                                        'post_content'  => $articleResult['results'][0]['content'],
                                        'post_author'   => $authorId,
                                        'post_category' => $categoryIds,
                                        'post_status'   => 'publish'
                                    );
                                    $post_id = wp_insert_post( $my_post );
                                    if(!get_post_meta($post_id, 'external_id')){
                                        add_post_meta($post_id, 'external_id', $articleId);
                                    }
                                    if(array_key_exists('author', $articleResult['results'][0])){
                                        if(!get_post_meta($post_id, 'external_author')){
                                            add_post_meta($post_id, 'external_author', $articleResult['results'][0]['author']);
                                        }
                                    }
                                    if(array_key_exists('publish_datetime', $articleResult['results'][0])){
                                        if(!get_post_meta($post_id, 'external_publish_date')){
                                            add_post_meta($post_id, 'external_publish_date', $articleResult['results'][0]['publish_datetime']);
                                        }
                                    }
                                    if(!is_null($siteName)){
                                        if(!get_post_meta($post_id, 'external_site_name')){
                                            add_post_meta($post_id, 'external_site_name', $siteName);
                                        }	
                                    }
                                    wp_set_post_tags($post_id, $tags);
                                    error_log("Post ".$articleResult['results'][0]['title']." doesn't exist, add id ".$articleId);									
                                }
                            }
                        }
                    }
                }else{
                    error_log("Post ".$item['title_url']." exist");
                }
            }
        }   	
    }else{
        error_log("Site ".$siteUrl." return no result");
    }
    error_log("finish crawling site ".$siteUrl);
}
	
?>
