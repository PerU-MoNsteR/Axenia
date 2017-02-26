<?php


class SiteDao extends BotDao
{

    public function getUserRewardsFull($user_id)
    {
        $res = $this->select("
            SELECT t.img,t.title,t.level,r.description 
            FROM Reward_Type t, Rewards r
            WHERE r.type_id=t.id AND r.user_id=" . $user_id
        );

        return $res;
    }


    public function getUserKarmaList($user_id)
    {
        $res = $this->select("
            SELECT c.title, c.id, k.user_id, k.level, k.chat_id 
            FROM Karma k, Chats c 
            WHERE k.chat_id=c.id AND k.user_id=" . $user_id . " 
            ORDER BY level DESC", true
        );

        return $res;
    }

    public function getGroupKarmaList($chat_id)
    {
        $res = $this->select("
            SELECT u . username, u . firstname, u . lastname, k . level, u . id 
            FROM Karma k, Users u 
            WHERE k . user_id = u . id AND k . chat_id = " . $chat_id . " 
            ORDER BY level DESC", true
        );

        return $res;
    }


    public function isUserPhotoRemembered($user_id, $photo_id)
    {
        $res = $this->select("SELECT 1 FROM Users WHERE id=" . $user_id . " AND img=" . $photo_id);
        return !($res[0]) ? false : $res[0];
    }

    public function updateUserPhoto($user_id, $photo_id)
    {
        $query = "UPDATE Users SET img=" . $photo_id . " WHERE id=" . $user_id;
        return $this->update($query);
    }

    public function getCountAllKarma()
    {
        $res = $this->select("SELECT sum(level) FROM Karma");
        return !($res[0]) ? -1 : $res[0];
    }

    public function getCountUsers()
    {
        $res = $this->select("SELECT count(1) FROM Users");
        return !($res[0]) ? -1 : $res[0];
    }

    public function getCountUsernames()
    {
        $res = $this->select("SELECT count(1) FROM Users WHERE username<>''");
        return !($res[0]) ? -1 : $res[0];
    }

    public function getCountKarmaPositive()
    {
        $res = $this->select("SELECT count(1) FROM Karma WHERE level>0");
        return !($res[0]) ? -1 : $res[0];
    }

    public function getCountKarmaNegative()
    {
        $res = $this->select("SELECT count(1) FROM Karma WHERE level<0");
        return !($res[0]) ? -1 : $res[0];
    }

    public function getCountGroups()
    {
        $res = $this->select("
        SELECT count(1) 
        FROM 
          (
            SELECT c.title,k.chat_id,sum(k.level) 
            FROM Karma k,Chats c 
            WHERE c.id=k.chat_id 
            GROUP BY k.chat_id
          ) k2"
        );
        return !($res[0]) ? -1 : $res[0];
    }

}