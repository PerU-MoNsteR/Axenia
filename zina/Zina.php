<?php

use BotDao;
use Util;
use Request;

class Zina
{

    private $db;

    /**
     * Axenia constructor.
     * @param $db BotDao
     */
    public function __construct($db)
    {
        $this->db = $db;
    }


    public function processMessage($message)
    {
        $message_id = $message['message_id'];
        $chat_id = $message['chat']['id'];
        $from_id = $message['from']['id'];

        $this->db->AddUser($from_id, $message['from']['username'], $message['from']['first_name'], $message['from']['last_name']);

        if (isset($message['text'])) {
            $text = str_replace("@" . BOT_NAME, "", $message['text']);
            switch (true) {
                case (preg_match('/^\/start/ui', $text, $matches) and $message['chat']['type'] == "private"):
                    Request::sendTyping($chat_id);
                    $out = "Привет! Меня зовут Зинаида, и я умею считать карму! Но надо <a href='telegram.me/" . BOT_NAME . "?startgroup=0'>выбрать чат</a>, в котором я буду это делать. ✌😊 ";
                    Request::sendHtmlMessage($chat_id, $out);
                    break;
                case preg_match('/^\/top/ui', $text, $matches):
                case preg_match('/^\/Stats/ui', $text, $matches):
                    Request::sendTyping($chat_id);

                    $out = "<b>Самые почётные люди чата «" . $this->db->GetGroupName($chat_id) . "»:</b>\r\n";
                    $top = $this->db->getTop($chat_id, 5);
                    $a = array_chunk($top, 4);
                    foreach ($a as $value) {
                        $out .= ($value[0] == "") ? $value[1] . " " . $value[2] : $value[0];
                        $out .= " (" . $value[3] . ")\r\n";
                    }
                    $out .= "<a href='" . PATH_TO_SITE . "?group_id=" . $chat_id . "'>Подробнее</a>";
                    Request::sendHtmlMessage($chat_id, $out);

                    break;
                case preg_match('/^(\+|\-|👍|👎) ?([\s\S]+)?/ui', $text, $matches):
                    $dist = Util::isInEnum("+,👍", $matches[1]) ? "+" : "-";

                    if (isset($message['reply_to_message'])) {
                        $replyUser = $message['reply_to_message']['from'];
                        $this->db->AddUser($replyUser['id'], $replyUser['username'], $replyUser['first_name'], $replyUser['last_name']);

                        if ($replyUser['username'] != BOT_NAME) {
                            Request::sendTyping($chat_id);
                            $output = $this->db->HandleKarma($dist, $from_id, $replyUser['id'], $chat_id);
                            Request::sendHtmlMessage($chat_id, $output);
                        }
                    } else {
                        if (preg_match('/@([\w]+)/ui', $matches[2], $user)) {
                            $to = $this->db->GetUserID($user[1]);
                            if ($to) {
                                Request::sendHtmlMessage($chat_id, $this->db->HandleKarma($dist, $from_id, $to, $chat_id));
                            } else {
                                Request::sendHtmlMessage($chat_id, "Я его не знаю, считать карму не буду", array('reply_to_message_id' => $message_id));
                            }
                        }

                    }
                    break;
            }
        }
        if (isset($message['new_chat_member'])) {
            $newMember = $message['new_chat_member'];
            if (BOT_NAME == $newMember['username']) {
                $chat = $message['chat'];
                $output = $this->db->AddChat($chat_id, $chat['title'], $chat['type']);
                if ($output !== false) {
                    Request::sendTyping($chat_id);
                    Request::exec("sendMessage", array('chat_id' => $chat_id, "text" => $output, "parse_mode" => "Markdown"));
                }
            } else {
                $this->db->AddUser($newMember['id'], $newMember['username'], $newMember['first_name'], $newMember['last_name']);
            }

        }

        if (isset($message['new_chat_title'])) {
            $newtitle = $message['new_chat_title'];
            $this->db->AddChat($chat_id, $newtitle, $chat = $message['chat']['type']);
        }

        if (isset($message['sticker'])) {
            //обработка получения стикеров
        }
    }

}


?>