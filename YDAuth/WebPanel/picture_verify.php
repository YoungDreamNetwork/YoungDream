<?php
 session_start();
$_SESSION["verify"] = mt_rand(0,9).mt_rand(0,9).mt_rand(0,9).mt_rand(0,9);
$authnum = $_SESSION["verify"]; 
$img_height=70;
$img_width=25;

$aimg = imagecreate($img_height,$img_width);   
imagecolorallocate($aimg, 255,255,255);   
$black = imagecolorallocate($aimg, 0,0,0); 


for ($i=1; $i<=100; $i++) {
    imagestring($aimg,1,mt_rand(1,$img_height),mt_rand(1,$img_width),"@",imagecolorallocate($aimg,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255)));
}

for ($i=0;$i<strlen($authnum);$i++){
    imagestring($aimg, mt_rand(3,5),$i*$img_height/4+mt_rand(2,7),mt_rand(1,$img_width/2-2), $authnum[$i],imagecolorallocate($aimg,mt_rand(0,100),mt_rand(0,150),mt_rand(0,200)));
}
imagerectangle($aimg,0,0,$img_height-1,$img_width-1,$black);
Header("Content-type: image/PNG");
ImagePNG($aimg);  
ImageDestroy($aimg); 
?>
