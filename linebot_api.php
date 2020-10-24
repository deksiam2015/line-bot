<?php

$channelAccessToken = 'pjZ/EwJCOEIgxRniI/fVN5YMwLrZcFEZ3aP6Kqp6n8UiU+Ha4VYufqPMnevHbemJp2QdMzoeV36uwNhZnzX+lAhbVuePJr1vEHGiRtqO+ewLj96wVD2PxRLqO2MuT8dw6TdX1KdWEZ21cpcQnHh0gwdB04t89/1O/w1cDnyilFU='; // Access Token ค่าที่เราสร้างขึ้น

$request = file_get_contents('php://input');   // Get request content

$request_json = json_decode($request, true);   // Decode JSON request

foreach ($request_json['events'] as $event)
{
	if ($event['type'] == 'message') 
	{
		if($event['message']['type'] == 'text')
		{
			$text = $event['message']['text'];
			
			$reply_message = 'ฉันได้รับข้อความ "'. $text . '" ของคุณแล้ว!';
			
			$txts = explode(" ", $text);
			
			if($text == "สถานการณ์โควิดวันนี้" || $text == "covid19" || $text == "covid-19" || $text == "Covid-19"){
                $url = 'https://covid19.th-stat.com/api/open/today';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $post_header);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                $result = curl_exec($ch);
                curl_close($ch);   

                $obj = json_decode($result);

                // $reply_message = $result;

                $reply_message = 'รายงานยอดติดเชื้อสะสม Covid-19 ในประเทศไทย \r\n';
                $reply_message .= 'Update ล่าสุดเมื่อ : '.$obj->{'UpdateDate'} .'\r\n';
                $reply_message .= 'ติดเชื้อสะสม : '.$obj->{'Confirmed'} .' ราย \r\n';
                $reply_message .= 'ติดเชื้อเพิ่มขึ้น : '.$obj->{'NewConfirmed'} .' ราย \r\n';
                $reply_message .= 'เสียชีวิตสะสม : '. $obj->{'Deaths'} .' ราย \r\n';
                $reply_message .= 'เสียชีวิตเพิ่มขึ้น : '. $obj->{'NewDeaths'} .' ราย \r\n';
                $reply_message .= 'รักษาตัวในรพ.ตอนนี้ : '.$obj->{'Confirmed'} .' ราย \r\n';
                $reply_message .= 'รักษาตัวในรพ.เพิ่มขึ้น : '.$obj->{'NewHospitalized'} .' ราย \r\n';
                $reply_message .= 'หายแล้วตอนนี้ : '.$obj->{'Confirmed'} .' ราย \r\n';
                $reply_message .= 'หายแล้วเพิ่มขึ้น : '.$obj->{'Confirmed'} .' ราย \r\n';

                // $reply_message = 'ติดเชื้อสะสม '. $obj->{'Confirmed'};
            }
			
			if($text == "@covid"){
                $api_url = 'https://covid19.th-stat.com/api/open/today';
                $jsondatas = file_get_contents($api_url);
                
                $reply_message = json_decode($jsondatas);
            } else if($txts[0] == "@บอท"){
				
				$reply_message = "กรุณาใช้รูปแบบคำสั่งที่ถูกต้องงงงง!!\n";
				
				$reply_message .= "ฉันมีบริการให้คุณสั่งได้ ดังนี้...\n";
				
				$reply_message .= "พิมพ์ว่า \"@บอท ขอรายชื่อนิสิตทั้งหมด\"\n";
				$reply_message .= "พิมพ์ว่า \"@บอท ขอรายชื่อนิสิต รหัส 61160xxx\"\n";
				$reply_message .= "พิมพ์ว่า \"@บอท ขอรหัส FTP ของ s61160xxx\"\r\n";
				
				
				
				if($txts[1] == "ขอรายชื่อนิสิตทั้งหมด"){
					$reply_message = mySQL_selectAll('http://bot.kantit.com/json_select_users.php');
				}
			
				if($txts[1]." ".$txts[2] == "ขอรายชื่อนิสิต รหัส"){
					$reply_message = mySQL_selectAll('http://bot.kantit.com/json_select_users.php?sid='.$txts[3]);
				}
				
				if($txts[1]." ".$txts[2]." ".$txts[3] == "ขอรหัส FTP ของ"){
					$reply_message = mySQL_selectFTP('http://bot.kantit.com/json_select_ftp.php?sid='.$txts[4]);					
				}
				
			}
			
		} else {
			$reply_message = 'ฉันได้รับ ' . $event['message']['type'] . ' ของคุณแล้ว!';
		}
		
	} else {
		$reply_message = 'ฉันได้รับ Event ' . $event['type'] . ' ของคุณแล้ว!';
	}
	
	if($reply_message == null || $reply_message == ""){ $reply_message =  'ขออภัยฉันไม่สามารถตอบกลับข้อความ "'. $text . '" ของคุณ!'; }
		
	// reply message
	$post_header = array('Content-Type: application/json', 'Authorization: Bearer ' . $channelAccessToken);	
	$data = ['replyToken' => $event['replyToken'], 'messages' => [['type' => 'text', 'text' => $reply_message]]];	
	$post_body = json_encode($data);	
	//$send_result = replyMessage('https://api.line.me/v2/bot/message/reply', $post_header, $post_body);
	$send_result = send_reply_message('https://api.line.me/v2/bot/message/reply', $post_header, $post_body);	
}

function mySQL_selectAll($url)
{
	$result = file_get_contents($url);
	
	$result_json = json_decode($result, true); //var_dump($result_json);
	
	$data = "ผลลัพธ์:\r\n";
		
	foreach($result_json as $values) {
		$data .= $values["user_stuid"] . " " . $values["user_firstname"] . " " . $values["user_lastname"] . "\r\n";
	}
	
	return $data;
}

function mySQL_selectFTP($url)
{
	$result = file_get_contents($url);
	
	$result_json = json_decode($result, true); //var_dump($result_json);
	
	$data = "ผลลัพธ์:\r\n";
		
	foreach($result_json as $values) {
		$data .= $values["user_password"] . "\r\n";
	}
	
	return "รหัส FTP ของคุณคือ ".$data;
}


function replyMessage($url, $post_header, $post_body)
{
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $post_header,
                'content' => $post_body,
            ],
        ]);
	
	$result = file_get_contents($url, false, $context);
	
	return $result;
}

function send_reply_message($url, $post_header, $post_body)
{
	$ch = curl_init($url);	
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $post_header);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	$result = curl_exec($ch);
	
	curl_close($ch);
	
	return $result;
}

?>
