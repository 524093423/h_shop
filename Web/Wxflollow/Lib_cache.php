<?php
class Lib_Cache {
    public static function read($file, $expires) {
        if(file_exists($file)) {
            $time = filemtime($file);
            if(time() - $time > $expires) {
                return "";
            }else {
                return file_get_contents($file);
            }
        }
        return "";
    }

    public static function write($file, $value) {
        @file_put_contents($file, $value);
    }
    public static function write1($file, $value) {
        @file_put_contents($file, $value);
    }
}
?>