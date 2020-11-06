<?php

$HTTP_API = '982445371:AAGLsP22TafPHCVOZj2mBzSIfoxul1i5rVI';

function bot($method, $datas = []) {
global $HTTP_API;
$url = 'https://api.telegram.org/bot' . $HTTP_API . '/'. $method;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
$result = curl_exec($ch);
if (curl_error($ch)) {
var_dump(curl_error($ch));
} else {
return json_decode($result);
}
}

$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$text = $message->text;
$message_id = $message->message_id;
$chat_id = $message->chat->id;
$user_id = $message->from->id;

if (!is_dir('./data')) mkdir('./data');

if ($text == '/start') {
bot('sendMessage',[
'chat_id' => $chat_id,
'text' => "Salom!
Bot musiqalarni qirqish va ovozli xabar ko'rinishida yuborish uchun xizmat qiladi.

Iltimos biror audio fayl (.mp3) yuboring.",
]);
}

if ($message->audio) {
bot('sendMessage',[
'chat_id' => $chat_id,
'text' => "Yuklanmoqda iltimos kuting.",
]);
$path = bot('getFile',['file_id' =>$message->audio->file_id])->result->file_path;
$file = "https://api.telegram.org/file/bot$HTTP_API/$path";
file_put_contents("data/$chat_id.mp3", file_get_contents($file));
exec("ffmpeg -ss 30 -t 30 -i data/".$chat_id.".mp3 -c:a libopus -b:a 16k data/".$chat_id.".ogg -y");
$id3_tags = mp3_get_tags($chat_id.".mp3");
bot('sendVoice',[
'chat_id' => $chat_id,
'voice' => new CURLFile("data/".$chat_id.".ogg"),
'caption' => $id3_tags['title'].$id3_tags['album'].$id3_tags['artist']
]);
unlink("data/$chat_id.mp3");
unlink("data/$chat_id.ogg");
}