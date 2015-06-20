<?php

/**
 * Toolkit
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Tools extends Model {

    static function randomString($length = 20) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    static function uriParse($uri) {
        $answerPos = strpos($uri, '?');
        $params = array_slice(explode('/', substr($uri, 0, $answerPos ? $answerPos : strlen($uri) )), 1);

        foreach ($params as $key => $param) {
            if ($param != '') {
                $params[$key] = urldecode($param);
            } else {
                unset($params[$key]);
            }
        }
        return $params;
    }

    static function createDir($path) {
        if (file_exists($path))
            return true;

        $path = explode('/', $path);
        $cur = '';
        foreach ($path as $item) {
            $cur .= $item . '/';
            if (!file_exists($cur)) {
                mkdir($cur);
            }
        }
        return true;
    }

    static function resizeImage($img_path, $max_width = 1000, $max_height = 1000, $crop = false, $pos = 'center') {
        ini_set("gd.jpeg_ignore_warning", 1);
        list( $img_width, $img_height, $img_type, $img_tag ) = getimagesize($img_path);
        switch ($img_type) {
            case 1:
                $img_type = 'gif';
                break;
            case 3:
                $img_type = 'png';
                break;
            case 2:
            default:
                $img_type = 'jpeg';
                break;
        }
        $imagecreatefromX = "imagecreatefrom{$img_type}";
        $src_res = $imagecreatefromX($img_path);

        if ($img_width / $max_width > $img_height / $max_height)
            $separator = $img_width / $max_width;
        else
            $separator = $img_height / $max_height;

        if ($crop === true || $crop == 'q') {
            if ($img_width > $img_height) {
                $imgX = floor(( $img_width - $img_height ) / 2);
                $imgY = 0;
                $img_width = $img_height;
                $new_width = $max_width;
                $new_height = $max_height;
            } else {
                $imgX = 0;
                $imgY = floor(( $img_height - $img_width ) / 2);
                $img_height = $img_width;
                $new_width = $max_width;
                $new_height = $max_height;
            }
        } elseif ($crop == 'c') {
//Вычисляем некий коэффициент масштабирования
            $k1 = $img_width / $max_width;
            $k2 = $img_height / $max_height;
            $k = $k1 > $k2 ? $k2 : $k1;
            $ow = $img_width;
            $oh = $img_height;
//Вычисляем размеры области для нового изображения
            $img_width = intval($max_width * $k);
            $img_height = intval($max_height * $k);
            $new_width = $max_width;
            $new_height = $max_height;
//Находим начальные координаты (центрируем новое изображение)
            $imgX = (int) (($ow / 2) - ($img_width / 2) );
            if ($pos == 'center') {
                $imgY = (int) (($oh / 2) - ($img_height / 2));
            } else {
                $imgY = 0;
            }
        } else {
            $imgX = 0;
            $imgY = 0;
            $new_width = floor($img_width / $separator);
            $new_height = floor($img_height / $separator);
        }

        $new_res = imagecreatetruecolor($new_width, $new_height);
        imageAlphaBlending($new_res, false);
        imagesavealpha($new_res, true);
        imagecopyresampled($new_res, $src_res, 0, 0, $imgX, $imgY, $new_width, $new_height, $img_width, $img_height);

        if ($img_type == 'jpeg') {
            imageinterlace($new_res, 1); // чересстрочное формирование изображение
            imagejpeg($new_res, $img_path, 100);
        } else {
            $imageX = "image{$img_type}";
            $imageX($new_res, $img_path);
        }

        imagedestroy($new_res);
        imagedestroy($src_res);
        return $img_type;
    }

    static function sendMail($from, $to, $subject, $text, $charset = 'utf-8', $ctype = 'text/html') {
        $headers = "From: {$from}\r\n";
        $headers .= "Content-type: {$ctype}; charset={$charset}\r\n";
        $headers .= "Mime-Version: 1.0\r\n";
        return mail($to, $subject, $text, $headers);
    }

    static function redirect($href = '/', $text = false, $status = 'info') {

        if ($text !== false) {
            Msg::add($text, $status);
        }

        header("Location: {$href}");
        exit("Перенаправление на: <a href = '{$href}'>{$href}</a>");
    }

}
