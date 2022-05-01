<?php
/** Buraya yöncü üye işlemlerinden aldıgınız api bilgilerini yazmalısınız */
$Parametreler	= array(
	'id'		=> 'API ID',
	'key'		=> 'API Key',
);

/** burada sabit api özelliği başlıyor */
function YoncuSunucuApi($Islem,$Veri){
	YoncuSunucuApi:
	$DeneSay=1;
	$Curl = curl_init();
	curl_setopt($Curl, CURLOPT_URL, "https://www.yoncu.com/apiler/sunucu/".$Islem.".php");
	curl_setopt($Curl, CURLOPT_HEADER, false);
	curl_setopt($Curl, CURLOPT_ENCODING, false);
	curl_setopt($Curl, CURLOPT_COOKIESESSION, false);
	curl_setopt($Curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($Curl, CURLOPT_HTTPHEADER, array(
		'Connection: keep-alive',
		'User-Agent: '.$_SERVER['SERVER_NAME'],
		'Referer: http://www.yoncu.com/',
		'Cookie: YoncuKoruma='.$_SERVER['SERVER_ADDR'].';YoncuKorumaRisk=0',
	));
	curl_setopt($Curl, CURLOPT_POSTFIELDS,http_build_query($Veri));
	if(curl_errno($Curl) == 0){
		$Json	= trim(curl_exec($Curl));
		$HTTP = curl_getinfo($Curl);
		if($HTTP['http_code'] == 200){
			if($Json != ""){
				list($Durum,$Bilgi)	= json_decode($Json);
				if(json_last_error() == 0){
					if($Durum == true){
						return [true,$Bilgi];
					}else{
						return [false,'Hata: '.$Bilgi];
					}
				}else{
					return [false,'Data Hata, Veri Json Değil. Gelen Veri: '.$Json];
				}
			}else{
				return [false,'Data Hata: Veri Boş Çekildi'];
			}
		}else{
			if($DeneSay > 9){
				return [false,'HTTP Erişimi Sağlanamadı ('.$Bilgi['http_code'].')'];
			}else{
				sleep(2);
				$DeneSay++;
				Goto YoncuSunucuApi;
			}
		}
	}else{
		return [false,"Curl Hata: ".curl_errno($Curl)." - ".curl_error($Curl)];
	}
	curl_close($Curl);
}

// IP adreslerini alt alta yazın yanlarında bir boşluk bırakaran rdns adresini yazınız.
ini_set('max_execution_time', 86400);
set_time_limit(86400);
ignore_user_abort(true);
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
while(@ob_end_flush());
ini_set('implicit_flush', true);
ob_implicit_flush(true);
$Islem	= 'rdns';
$IpRdns	= '
1.1.1.1 rdns1.yoncu.com
2.2.2.2 rdns2.yoncu.com
3.3.3.3 rdns3.yoncu.com';
foreach(explode("\n",trim($IpRdns)) as $IPRDNS){
	list($IP,$RDNS)	= explode(' ',trim($IPRDNS));
	$IP		= trim($IP);
	$RDNS	= trim($RDNS);
	if(filter_var($IP,FILTER_VALIDATE_IP)){
		list($Durum,$Bilgi)	= YoncuSunucuApi($Islem,array_merge_recursive($Parametreler,['ip'=>$IP,'rdns'=>$RDNS]));
		echo $IP." - ";
		if($Durum){
			echo 'OK';
		}else{
			echo 'Hata';
		}
		echo $Bilgi."<br/>";
	}else{
		echo " IP Hatalı: ".$IP."<br/>";
	}
	flush();
}
