<?php
date_default_timezone_set('America/Denver');

function count_xmls() {
	$dir = opendir('.');
	$files = array();
	while (false != ($file = readdir($dir))) {
		if (substr(strrchr($file,'.'),1) == 'xml')  
			$files[] = $file;
		} 
	return count($files);
}

$i=0;

while ($i<=(count_xmls()-1)) {
	$xmlfile = simplexml_load_file('ooyala_feed.'.$i.'.xml');
	$namespaces = $xmlfile->getNamespaces(true);
	foreach ($xmlfile->channel->item as $item) {
		$metadata = array();
		
		if ($metadata['guid'] = (string)trim($item->guid)) {
			echo "\n".chr(27).'[0;32mFound a new video with the ID: '.chr(27).'[1;37m'.$metadata['guid'].chr(27).'[0m'."\n";
		}
		$metadata['title'] = (string)trim($item->title);
		echo "\n".'Title: '.chr(27).'[1;37m'.$metadata['title'].chr(27).'[0m'."\n";
		$metadata['description'] = (string)trim($item->description);
		echo 'Description: '.chr(27).'[1;37m'.$metadata['description'].chr(27).'[0m'."\n";
		$metadata['url'] = (string)trim($item->children($namespaces['media'])->content->attributes()->url);
		$metadata['pubdate'] = (string)trim($item->pubDate);
		$metadata['upubdate'] = strtotime($metadata['pubdate']);
		
		$folderdate = date('Y-m', $metadata['upubdate']);
		$folderstring = strval($folderdate);
		$folder = './'.$folderstring;

		echo "\n".chr(27).'[0;33mChecking for directory...'.chr(27).'[0m'."\n";
		if (!file_exists($folder)) {
			mkdir($folder,0777,true);
			echo 'Creating new directory: '.chr(27).'[0;36m'.$folderstring.chr(27).'[0m'."\n";
		} else {
			echo chr(27).'[1;35mDirectory already exists!'.chr(27).'[0m'."\n";
		}

		echo "\n".chr(27).'[0;33mChecking for video...'.chr(27).'[0m'."\n";
		if (!file_exists($folder.'/'.$metadata['guid'].'.mov')) {
			copyfile_chunked($metadata['url'],$folder.'/'.$metadata['guid'].'.mov');
			echo "\n".chr(27).'[1;33mSuccessfuly saved video file!'.chr(27).'[0m'."\n";
		} else {
			echo chr(27).'[1;35mVideo file already exists!'.chr(27).'[0m'."\n";
		}

		echo "\n".chr(27).'[0;33mChecking for metadata...'.chr(27).'[0m'."\n";
		if (!file_exists($folder.'/'.$metadata['guid'].'.json')) {			
			file_put_contents(($folder.'/'.$metadata['guid'].'.json'), json_encode($metadata));
			echo chr(27).'[1;33mSuccessfuly saved metadata!'.chr(27).'[0m'."\n";
		} else {
			echo chr(27).'[1;35mMetadata file already exists!'.chr(27).'[0m'."\n";
		}
		echo "\n".chr(27).'[1;32mDone with video '.chr(27).'[1;37m'.$metadata['guid'].chr(27).'[0;32m!'.chr(27).'[0m'."\n"."\n".'=================================================='."\n"."\n";
	}
	$i++;
}

/**
 * Copy remote file over HTTP one small chunk at a time.
 *
 * @param $infile The full URL to the remote file
 * @param $outfile The path where to save the file
 */
function copyfile_chunked($infile, $outfile) {
    $chunksize = 10 * (1024 * 1024); // 10 Megs

    /**
     * parse_url breaks a part a URL into it's parts, i.e. host, path,
     * query string, etc.
     */
    $parts = parse_url($infile);
    $i_handle = fsockopen($parts['host'], 80, $errstr, $errcode, 5);
    $o_handle = fopen($outfile, 'wb');

    if ($i_handle == false || $o_handle == false) {
        return false;
    }

    if (!empty($parts['query'])) {
        $parts['path'] .= '?' . $parts['query'];
    }

    /**
     * Send the request to the server for the file
     */
    $request = "GET {$parts['path']} HTTP/1.1\r\n";
    $request .= "Host: {$parts['host']}\r\n";
    $request .= "User-Agent: Mozilla/5.0\r\n";
    $request .= "Keep-Alive: 115\r\n";
    $request .= "Connection: keep-alive\r\n\r\n";
    fwrite($i_handle, $request);

    /**
     * Now read the headers from the remote server. We'll need
     * to get the content length.
     */
    $headers = array();
    while(!feof($i_handle)) {
        $line = fgets($i_handle);
        if ($line == "\r\n") break;
        $headers[] = $line;
    }

    /**
     * Look for the Content-Length header, and get the size
     * of the remote file.
     */
    $length = 0;
    foreach($headers as $header) {
        if (stripos($header, 'Content-Length:') === 0) {
            $length = (int)str_replace('Content-Length: ', '', $header);
            break;
        }
    }

    /**
     * Start reading in the remote file, and writing it to the
     * local file one chunk at a time.
     */
    $cnt = 0;
    echo chr(27).'[1;34mGrabbing '.$length.' video file chunks: '.chr(27).'[0m'."\n";
    while(!feof($i_handle)) {
        $buf = '';
        $buf = fread($i_handle, $chunksize);
        $bytes = fwrite($o_handle, $buf);
        if ($bytes == false) {
            return false;
        }
        $cnt += $bytes;
        if ($cnt % 50 == 0) echo '.';
        /**
         * We're done reading when we've reached the content length
         */
        if ($cnt >= $length) {
        	echo "\n".'Done!'."\n";
        	break;
        }
    }

    fclose($i_handle);
    fclose($o_handle);
    return $cnt;
}

?>