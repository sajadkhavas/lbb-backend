<?php
// Run as lbbapi. Only public, verified images of published products.
umask(0022);
chdir('/var/www/lbb/backend/current');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$root = realpath(storage_path('app/public'));
$manifests = storage_path('app/lbb-responsive-manifests');
if (!$root || !is_dir($manifests) || !function_exists('imagewebp')) throw new RuntimeException('Missing image optimizer prerequisites');
$widths = [320=>78,640=>80,960=>82,1200=>84];
$ok=0;$reused=0;$errors=0;$variants=0;
$assets=\App\Models\ProductMediaAsset::query()->where('verification_state','verified')->whereHas('product',fn($q)=>$q->where('publication_status','published')->where('is_active',true))->with('media');
foreach ($assets->lazyById(30) as $asset) {
 try {
  $m=$asset->getFirstMedia('asset');
  if (!$m || $m->disk!=='public') continue;
  $source=realpath($m->getPath());
  if (!$source || dirname($source)!==$root.'/'.$m->id || is_link($m->getPath())) throw new RuntimeException('Unexpected media path');
  $dir=dirname($source); $url=$m->getUrl();
  if (!preg_match('~^https://api\.lbbclo\.com/storage/'.(int)$m->id.'/[^/?#]+$~D',$url)) throw new RuntimeException('Unexpected media URL');
  // Public media must be traversable by Nginx; never recurse or touch private storage.
  if ((fileperms($dir)&0111)!==0111 && !chmod($dir,(fileperms($dir)&07777)|0111)) throw new RuntimeException('Directory permission');
  if ((fileperms($source)&0044)!==0044 && !chmod($source,(fileperms($source)&07777)|0044)) throw new RuntimeException('File permission');
  clearstatcache(true,$source); $stat=stat($source); $fingerprint=[$stat['ino'],$stat['size'],$stat['mtime'],$stat['ctime']];
  $manifest=$manifests.'/'.$m->id.'.json';
  $prior=is_file($manifest)?json_decode(file_get_contents($manifest),true):null;
  if (is_array($prior) && ($prior['fingerprint']??null)===$fingerprint && ($prior['url']??null)===$url && ($prior['version']??null)===1) {
   $valid=true; foreach($prior['variants']??[] as $v) {if(!is_file($dir.'/'.$v['file']) || !is_readable($dir.'/'.$v['file'])){$valid=false;break;}}
   if($valid){$reused++;$variants+=count($prior['variants']??[]);continue;}
  }
  $info=@getimagesize($source); if(!$info) throw new RuntimeException('Invalid image');
  [$w,$h]=$info; $out=[];
  $eligible=$w*$h<=20000000 && in_array($info['mime'],['image/jpeg','image/png','image/webp'],true);
  if ($eligible && $info['mime']==='image/jpeg') $eligible=function_exists('exif_read_data') && (int)((@exif_read_data($source)?:[])['Orientation']??1)===1;
  if ($eligible) {
   $hash=substr(hash_file('sha256',$source),0,16);
   $im=null;
   try {
    foreach($widths as $target=>$quality){
     if($target >= $w) continue;
     $th=max(1,(int)round($h*$target/$w));$name="lbb-responsive-v1-$hash-w$target.webp";$dest=$dir.'/'.$name;
     if(is_link($dest)) throw new RuntimeException('Unexpected derivative symlink');
     if(!is_file($dest)) {
      if(!$im){$im=imagecreatefromstring(file_get_contents($source));if(!$im)throw new RuntimeException('Decode failed');}
      $resized=imagecreatetruecolor($target,$th);if(!$resized)throw new RuntimeException('Allocation failed');
      $tmp=null;
      try { imagealphablending($resized,false);imagesavealpha($resized,true);imagecopyresampled($resized,$im,0,0,0,0,$target,$th,$w,$h);
       $tmp=tempnam($dir,'.lbb-rsp-'); if(!$tmp || !imagewebp($resized,$tmp,$quality))throw new RuntimeException('Encode failed');
       $check=@getimagesize($tmp);if(!$check||$check[0]!==$target||$check[1]!==$th||$check['mime']!=='image/webp')throw new RuntimeException('Invalid derivative');
       if(filesize($tmp)>=$stat['size'])continue;
       if(!chmod($tmp,0644)||!rename($tmp,$dest))throw new RuntimeException('Publish derivative failed');
      } finally {imagedestroy($resized);if($tmp&&is_file($tmp))unlink($tmp);}
     }
     $check=@getimagesize($dest);if(!$check||$check[0]!==$target||$check[1]!==$th||$check['mime']!=='image/webp')throw new RuntimeException('Invalid existing derivative');
     if(filesize($dest)<$stat['size']){$out[]=['width'=>$target,'file'=>$name];}
    }
   } finally {if($im)imagedestroy($im);}
  }
  clearstatcache(true,$source);$after=stat($source);if([$after['ino'],$after['size'],$after['mtime'],$after['ctime']]!==$fingerprint)throw new RuntimeException('Source changed during processing');
  $tmp=tempnam($manifests,'.manifest-');if(!$tmp)throw new RuntimeException('Manifest temp failed');
  try { $data=['version'=>1,'url'=>$url,'fingerprint'=>$fingerprint,'width'=>$w,'variants'=>$out];
   if(file_put_contents($tmp,json_encode($data,JSON_THROW_ON_ERROR))===false||!chmod($tmp,0640)||!rename($tmp,$manifest))throw new RuntimeException('Manifest write failed');
  }finally{if(is_file($tmp))unlink($tmp);}
  $ok++;$variants+=count($out);
 } catch(Throwable $e){$errors++;fwrite(STDERR,'MEDIA_OPTIMIZATION_ERROR asset='.$asset->id.' '.$e->getMessage().PHP_EOL);}
}
echo "R7_MEDIA_PROCESSED=$ok REUSED=$reused VARIANTS=$variants ERRORS=$errors\n";
exit($errors?1:0);
