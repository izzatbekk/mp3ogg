<?php

$HTTP_API = '1449565555:AAFeeFHTdJN5n00av_bLDpSGsDFbRRi3EBw';

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
'text' => "Hi!
The bot serves to cut music and send in the form of voice message.

Please send me an audio file (.mp3).",
]);
}

if ($message->audio) {
bot('sendMessage',[
'chat_id' => $chat_id,
'text' => "Sending, please wait..",
]);
$path = bot('getFile',['file_id' =>$message->audio->file_id])->result->file_path;
$file = "https://api.telegram.org/file/bot$HTTP_API/$path";
file_put_contents("data/$chat_id.mp3", file_get_contents($file));
exec("ffmpeg -ss 30 -t 30 -i data/".$chat_id.".mp3 -acodec libopus -b:a 16k data/".$chat_id.".ogg -y");
bot('sendVoice',[
'chat_id' => $chat_id,
'voice' => new CURLFile("data/".$chat_id.".ogg"),
'duration' => 30
]);
unlink("data/$chat_id.mp3");
unlink("data/$chat_id.ogg");
}