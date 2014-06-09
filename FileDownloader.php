<?php
/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */
if(!class_exists("FileDownloader")){
class FileDownloader{

    public static function download($fileName,$content){
        $tmpName = "cache/".$fileName."_".date("y-m-d-h-i-s");
        file_put_contents($tmpName,$content);
        header("Pragma: public");
        header("Expires: 0");
        header("Content-Type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: ".filesize($tmpName));
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        readfile($tmpName);
        unlink($tmpName);
        exit();
    }

}
}