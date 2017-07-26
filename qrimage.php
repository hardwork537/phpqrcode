<?php
/*
 * PHP QR Code encoder
 *
 * Image output of code using GD2
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
 
    define('QR_IMAGE', true);

    class QRimage {

        public static $imgColor = [132, 209, 42]; //绿色
        public static $bgColor = [255,255,255]; //白色
        //public static $bgColor = [240,255,255]; //天蓝色

        public static function  setColor($imgColor = [], $bgColor = [])
        {
            if(!empty($imgColor))
            {
                self::$imgColor = $imgColor;
            }

            if(!empty($bgColor))
            {
                self::$bgColor = $bgColor;
            }
        }
    
        //----------------------------------------------------------------------
        public static function png($frame, $filename = false, $pixelPerPoint = 4, $outerFrame = 4,$saveandprint=FALSE) 
        {
            $image = self::image($frame, $pixelPerPoint, $outerFrame);
            
            if ($filename === false) {
                Header("Content-type: image/png");
                ImagePng($image);
            } else {
                if($saveandprint===TRUE){
                    ImagePng($image, $filename);
                    header("Content-type: image/png");
                    ImagePng($image);
                }else{
                    ImagePng($image, $filename);
                }
            }
            
            ImageDestroy($image);
        }
    
        //----------------------------------------------------------------------
        public static function jpg($frame, $filename = false, $pixelPerPoint = 8, $outerFrame = 4, $q = 85) 
        {
            $image = self::image($frame, $pixelPerPoint, $outerFrame);
            
            if ($filename === false) {
                Header("Content-type: image/jpeg");
                ImageJpeg($image, null, $q);
            } else {
                ImageJpeg($image, $filename, $q);            
            }
            
            ImageDestroy($image);
        }
    
        //----------------------------------------------------------------------
        private static function image($frame, $pixelPerPoint = 4, $outerFrame = 4)
        {
            //hardcode
            $pixelPerPoint = 15;
            $times = 31; //放大倍数(用于画角的圆)

            $h = count($frame);
            $w = strlen($frame[0]);

            $imgW = $w + 2*$outerFrame;
            $imgH = $h + 2*$outerFrame;

            $base_image = imagecreatetruecolor($imgW*$times, $imgH*$times);

            $col[0] = ImageColorAllocate($base_image,self::$bgColor[0],self::$bgColor[1],self::$bgColor[2]);
            $col[1] = ImageColorAllocate($base_image,self::$imgColor[0],self::$imgColor[1],self::$imgColor[2]);

            imagefill($base_image, 0, 0, $col[0]);

            //用圆画图
            $target_image = self::_drawPicWithCircle($base_image, $frame, $outerFrame, $times, $col[1]);

            //用logo画图
            //$target_image = self::_drawPicWithLogo($base_image, $frame, $outerFrame, $times, $col[1]);

            //return $base_image;

            //$target_image = imagecreatetruecolor($imgW * $times, $imgH * $times);
            //imagecopyresampled($target_image, $base_image, 0, 0, 0, 0, $imgW * $times, $imgH * $times, $imgW, $imgH);
            //ImageDestroy($base_image);

            //左上角
            $target_image = self::_drawCornerCircle($target_image, 1, $imgH, $imgW, $times, $outerFrame);

            //右上角
            $target_image = self::_drawCornerCircle($target_image, 2, $imgH, $imgW, $times, $outerFrame);

            //左下角
            $target_image = self::_drawCornerCircle($target_image, 3, $imgH, $imgW, $times, $outerFrame);

            $dest_image = imagecreatetruecolor($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);

            imagecopyresampled($dest_image, $target_image, 0, 0, 0, 0, $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW * $times, $imgH * $times);
            ImageDestroy($target_image);

            //看点logo
            $logo = imagecreatefromstring(file_get_contents('kd_logo.png'));
			//$logo = imagecreatefromstring(file_get_contents('shuaiyan.jpg'));
            $logo_width = imagesx($logo);
            $logo_height = imagesy($logo);
            $from_width = ($imgW - 7 - $outerFrame) * $pixelPerPoint;

            imagecopyresampled($dest_image, $logo, $from_width, $from_width, 0, 0, 7 * $pixelPerPoint, 7 * $pixelPerPoint, $logo_width, $logo_height);

            return $dest_image;
        }

        private static function image_bak($frame, $pixelPerPoint = 4, $outerFrame = 4)
        {
            //hardcode
            $pixelPerPoint = 20;
            $times = 31; //放大倍数(用于画角的圆)

            $h = count($frame);
            $w = strlen($frame[0]);
            
            $imgW = $w + 2*$outerFrame;
            $imgH = $h + 2*$outerFrame;
            
            $base_image = imagecreatetruecolor($imgW, $imgH);
            
            $col[0] = ImageColorAllocate($base_image,self::$bgColor[0],self::$bgColor[1],self::$bgColor[2]);
            $col[1] = ImageColorAllocate($base_image,self::$imgColor[0],self::$imgColor[1],self::$imgColor[2]);

            imagefill($base_image, 0, 0, $col[0]);

            //画图
            for($y=0; $y<$h; $y++) {
                for($x=0; $x<$w; $x++) {
                    $inCorner = ($x <= 6 && $y <= 6)
                        || ($x >= ($imgW - $outerFrame - 9) && $y <= 6)
                        || ($x <= 6 && ($y >= ($imgW - $outerFrame - 9)))
                        || ($x >= ($imgW - $outerFrame - 9) && ($y >= ($imgW - $outerFrame - 9)));
                    if ($frame[$y][$x] == '1' && !$inCorner) {
                        ImageSetPixel($base_image,$x+$outerFrame,$y+$outerFrame,$col[1]);
                    }
                }
            }

            $target_image = imagecreatetruecolor($imgW * $times, $imgH * $times);
            imagecopyresampled($target_image, $base_image, 0, 0, 0, 0, $imgW * $times, $imgH * $times, $imgW, $imgH);
            ImageDestroy($base_image);

            //左上角
            $target_image = self::_drawCornerCircle($target_image, 1, $imgH, $imgW, $times, $outerFrame);

            //右上角
            $target_image = self::_drawCornerCircle($target_image, 2, $imgH, $imgW, $times, $outerFrame);

            //左下角
            $target_image = self::_drawCornerCircle($target_image, 3, $imgH, $imgW, $times, $outerFrame);

            $dest_image = imagecreatetruecolor($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);
            /*
            $rgb = imagecolorat($target_image, 700, 700);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            var_dump([$r, $g, $b]);*/
            //test
            $base_image1 = imagecreatetruecolor(31, 31);
            $col[0] = ImageColorAllocate($base_image1,self::$bgColor[0],self::$bgColor[1],self::$bgColor[2]);
            $col[1] = ImageColorAllocate($base_image1,self::$imgColor[0],self::$imgColor[1],self::$imgColor[2]);

            imagefill($base_image1, 0, 0, $col[0]);
            imagefilledellipse($target_image,10*($times-1),2*$times,$times,$times,$col[1]);

            //imagecopyresampled($base_image, $base_image1, 0, 0, 0, 0, 1, 1, 31, 31);
            //test

//return $target_image;

            imagecopyresampled($dest_image, $target_image, 0, 0, 0, 0, $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW * $times, $imgH * $times);
            ImageDestroy($target_image);

            //看点logo
            $logo = imagecreatefromstring(file_get_contents('kd_logo.png'));
            $logo_width = imagesx($logo);
            $logo_height = imagesy($logo);
            $from_width = ($imgW - 7 - $outerFrame) * $pixelPerPoint;

            imagecopyresampled($dest_image, $logo, $from_width, $from_width, 0, 0, 7 * $pixelPerPoint, 7 * $pixelPerPoint, $logo_width, $logo_height);

            return $dest_image;
        }

        private static function _drawPicWithCircle($source, $frame, $outerFrame, $times, $color)
        {
            $h = count($frame);
            $w = strlen($frame[0]);
            $imgW = $w + 2*$outerFrame;
            $imgH = $h + 2*$outerFrame;

            for($y = 0; $y < $h; $y++)
            {
                for($x = 0; $x < $w; $x++)
                {
                    $inCorner = ($x <= 6 && $y <= 6)
                        || ($x >= ($imgW - $outerFrame - 9) && $y <= 6)
                        || ($x <= 6 && ($y >= ($imgH - $outerFrame - 9)))
                        || ($x >= ($imgW - $outerFrame - 9) && ($y >= ($imgH - $outerFrame - 9)));
                    if($frame[$y][$x] == '1' && !$inCorner)
                    {
                        $pointX = ($outerFrame+$x)*$times + ($times-1)/2;
                        $pointY = ($outerFrame+$y)*$times + ($times-1)/2;
                        $diameter = $times;

                        //var_dump($x, $y, $pointX, $pointY, $times);exit;
                        //var_dump([$pointX, $pointY, $diameter,$color]);echo PHP_EOL;
                        imagefilledellipse($source,$pointX,$pointY,$diameter,$diameter,$color);
                    }
                }
            }

            return $source;
        }

        private static function _drawPicWithLogo($source, $frame, $outerFrame, $times, $color)
        {
            $h = count($frame);
            $w = strlen($frame[0]);
            $imgW = $w + 2*$outerFrame;
            $imgH = $h + 2*$outerFrame;

            $images = [];
            $images[0] = [
                'logo' => imagecreatefromstring(file_get_contents('1.png')),
            ];
            $images[0]['w'] = imagesx($images[0]['logo']);
            $images[0]['h'] = imagesy($images[0]['logo']);

            $images[1] = [
                'logo' => imagecreatefromstring(file_get_contents('2.png')),
            ];
            $images[1]['w'] = imagesx($images[1]['logo']);
            $images[1]['h'] = imagesy($images[1]['logo']);

            $images[2] = [
                'logo' => imagecreatefromstring(file_get_contents('3.png')),
            ];
            $images[2]['w'] = imagesx($images[2]['logo']);
            $images[2]['h'] = imagesy($images[2]['logo']);

            //$from_width = ($imgW - 7 - $outerFrame) * $pixelPerPoint;

            //imagecopyresampled($dest_image, $logo, $from_width, $from_width, 0, 0, 7 * $pixelPerPoint, 7 * $pixelPerPoint, $logo_width, $logo_height);

            for($y = 0; $y < $h; $y++)
            {
                for($x = 0; $x < $w; $x++)
                {
                    $inCorner = ($x <= 6 && $y <= 6)
                        || ($x >= ($imgW - $outerFrame - 9) && $y <= 6)
                        || ($x <= 6 && ($y >= ($imgH - $outerFrame - 9)))
                        || ($x >= ($imgW - $outerFrame - 9) && ($y >= ($imgH - $outerFrame - 9)));
                    if($frame[$y][$x] == '1' && !$inCorner)
                    {
                        $logo = imagecreatefromstring(file_get_contents('shuaiyan.jpg'));
                        $logo_width = imagesx($logo);
                        $logo_height = imagesy($logo);

                        $from_width = ($outerFrame + $x) * $times;
                        $from_height = ($outerFrame + $y) * $times;

                        imagecopyresampled($source, $logo, $from_width, $from_height, 0, 0, $times, $times, $logo_width, $logo_height);
                    }
                }
            }

            return $source;
        }

        /**
         * 画三个定位较的颜色
         * @param $source
         * @param $type
         * @param $imgH
         * @param $imgW
         * @param $times
         * @param $outerFrame
         * @return mixed
         */
        private static function _drawCornerCircle($source, $type, $imgH, $imgW, $times, $outerFrame)
        {
            //图片颜色
            $imgColor = ImageColorAllocate($source,self::$imgColor[0],self::$imgColor[1],self::$imgColor[2]);
            $bgColor = ImageColorAllocate($source,self::$bgColor[0],self::$bgColor[1],self::$bgColor[2]);

            //type - 1:左上角 2:右上角 3:左下角
            if(1 == $type)
            {
                //左上角绘制圆
                $pointX = intval((7*$times + 1) / 2) + $outerFrame * $times - 1;
                $pointY = intval((7*$times + 1) / 2) + $outerFrame * $times - 1;

                $diameter = 7 * $times;
                imagefilledellipse($source,$pointX,$pointY,$diameter,$diameter,$imgColor);

                $diameter = 5 * $times;
                imagefilledellipse($source,$pointX,$pointY,$diameter,$diameter,$bgColor);

                $diameter = 3 * $times;
                imagefilledellipse($source,$pointX,$pointY,$diameter,$diameter,$imgColor);
            } elseif(2 == $type) {
                //右上角绘制圆
                $pointX = intval((7*$times + 1) / 2) + $outerFrame * $times - 1;
                $pointY = $imgH*$times - intval((7*$times + 1) / 2) - $outerFrame * $times - 1;

                $diameter = 7 * $times;
                imagefilledellipse($source,$pointX,$pointY,$diameter,$diameter,$imgColor);

                $diameter = 5 * $times;
                imagefilledellipse($source,$pointX,$pointY,$diameter,$diameter,$bgColor);

                $diameter = 3 * $times;
                imagefilledellipse($source,$pointX,$pointY,$diameter,$diameter,$imgColor);
            } elseif(3 == $type) {
                //左下角绘制圆
                $pointX = $imgW*$times - intval((7*$times + 1) / 2) - $outerFrame * $times - 1;
                $pointY = intval((7*$times + 1) / 2) + $outerFrame * $times - 1;

                $diameter = 7 * $times;
                imagefilledellipse($source,$pointX,$pointY,$diameter,$diameter,$imgColor);

                $diameter = 5 * $times;
                imagefilledellipse($source,$pointX,$pointY,$diameter,$diameter,$bgColor);

                $diameter = 3 * $times;
                imagefilledellipse($source,$pointX,$pointY,$diameter,$diameter,$imgColor);
            }

            return $source;
        }
    }