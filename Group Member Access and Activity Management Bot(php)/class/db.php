<?php


// class db {}
// include("asl.php");

// try {
//     $conn = new PDO("mysql:host=$servername;dbname=$mydb", $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));
//     file_put_contents("telegram_tut.log", print_r("ok", true), FILE_APPEND);
// } catch (PDOException $e) {
//     file_put_contents("telegram_tut.log", print_r($e, true), FILE_APPEND);
// }

class db
{
    public $conn;

    function __construct()
    {
        include("asl.php");
        $conn = new PDO("mysql:host=$servername;dbname=$mydb", $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn = $conn;
    }


    function newUserInsert($user_id)
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO `user`(`user_id`) VALUES (:user_id)");
            $stmt->execute([':user_id' => $user_id]);
            // echo "User inserted successfully!";
            file_put_contents("telegram_tu.log", print_r("lol", true), FILE_APPEND);
        } catch (PDOException $e) {
            // echo "Error: " . $e->getMessage();
            file_put_contents("telegram_tu.log", print_r($e->getMessage(), true), FILE_APPEND);
        }
    }

    function checkUserExistence($user_id)
    {
        $sql = "select * from user where user_id='" . $user_id . "'  ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $numrow = $stmt->rowCount();
        if ($numrow == 0) {
            return true;
        }
        return false;
    }
    function ageInsert($user_id, $age)
    {
        $stmt = $this->conn->prepare("UPDATE user SET age = :age WHERE user_id = :id;");
        $stmt->execute([':age' => $age, ':user_id' => $user_id]);
    }


    public function getStep($user_id)
    {
        $stmt = $this->conn->prepare("SELECT step FROM user_steps WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }

    public function setStep($user_id, $step)
    {
        //INSERT INTO user_steps (user_id, step) VAUES (:user_id, :step) ON DUPLICATE KEY UPDATE step = :step
        $stmt = $this->conn->prepare("INSERT INTO user_steps (user_id, step) VALUES (:user_id, :step) ON DUPLICATE KEY UPDATE step = :step");
        $stmt->execute([':user_id' => $user_id, ':step' => $step]);
    }

    public function saveUserAge($user_id, $age)
    {
        $stmt = $this->conn->prepare("UPDATE user SET age = :age WHERE user_id = :user_id");
        $stmt->execute([':age' => (int)$age, ':user_id' => $user_id]);
    }

    public function saveUserCity($user_id, $city)
    {
        $stmt = $this->conn->prepare("UPDATE user SET city = :city WHERE user_id = :user_id");
        $stmt->execute([':city' => $city, ':user_id' => $user_id]);
    }

    public function saveUserGoal($user_id, $goals)
    {
        $stmt = $this->conn->prepare("UPDATE user SET goals = :goals WHERE user_id = :user_id");
        $stmt->execute([':goals' => $goals, ':user_id' => $user_id]);
    }
    public function saveUserGender($user_id, $gender)
    {
        $stmt = $this->conn->prepare("UPDATE user SET gender = :gender WHERE user_id = :user_id");
        $stmt->execute([':gender' => $gender, ':user_id' => $user_id]);
    }
    public function saveUserTw($user_id, $tw)
    {
        $stmt = $this->conn->prepare("UPDATE user SET tw_id = :tw_id WHERE user_id = :user_id");
        $stmt->execute([':tw_id' => $tw, ':user_id' => $user_id]);
    }
    public function saveUserTel($user_id, $tel)
    {
        $stmt = $this->conn->prepare("UPDATE user SET tel_id = :tel_id WHERE user_id = :user_id");
        $stmt->execute([':tel_id' => $tel, ':user_id' => $user_id]);
    }
    public function saveUserLevel($user_id, $lev)
    {
        $stmt = $this->conn->prepare("INSERT INTO level (user_id,lev) VALUES (:user_id,:lev)");
        $stmt->execute([':lev' => $lev, ':user_id' => $user_id]);
    }
    ///////////////////////
    /////////////////////
    function checkMessageLimit($user_id, $message_type, $count_text, $count_photo, $count_gif, $count_video, $count_voice, $count_videomsg)
    {

        $stmt = $this->conn->prepare("SELECT count, last_reset FROM user_messages WHERE user_id = :user_id AND message_type = :message_type");
        $stmt->execute([':user_id' => $user_id, ':message_type' => $message_type]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // محدودیت‌ها
        $limits = [
            'text' => $count_text,
            'photo' => $count_photo,
            'gif' => $count_gif,
            'video' => $count_video,
            'voice' => $count_voice,
            'video_note' => $count_videomsg
        ];

        $current_time = time();
        $one_hour_ago = $current_time +12480;//+ 9000; // convert to local time (12600 - 3600 = 9000)

        //  file_put_contents("tre.log", print_r(date('Y-m-d H-i-s',time()).PHP_EOL.$row['last_reset'].PHP_EOL, true), FILE_APPEND);


        if ($row) {
            if (strtotime($row['last_reset']) < $one_hour_ago) {

                $this->resetMessageCount($user_id, $message_type);
                return true;
            } else {
                if ($row['count'] < $limits[$message_type]) {
                    $this->incrementMessageCount($user_id, $message_type);
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            $this->createMessageCount($user_id, $message_type);
            return true;
        }
    }
    function resetMessageCount($user_id, $message_type)
    {
        $stmt = $this->conn->prepare("UPDATE user_messages SET count = 1, last_reset = NOW() WHERE user_id = :user_id AND message_type = :message_type");
        $stmt->execute(['user_id' => $user_id, 'message_type' => $message_type]);
    }
    function createMessageCount($user_id, $message_type)
    {
        $stmt = $this->conn->prepare("INSERT INTO user_messages (user_id, message_type, count) VALUES (:user_id, :message_type, 1)");
        $stmt->execute(['user_id' => $user_id, 'message_type' => $message_type]);
    }
    function incrementMessageCount($user_id, $message_type)
    {
        $stmt = $this->conn->prepare("UPDATE user_messages SET count = count + 1 WHERE user_id = :user_id AND message_type = :message_type");
        $stmt->execute(['user_id' => $user_id, 'message_type' => $message_type]);
    }
    function checkLevel($user_id)
    {
        $stmt = $this->conn->prepare("SELECT lev FROM level WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    function levelUp($user_id)
    {
        $stmt = $this->conn->prepare("UPDATE level SET lev = lev + 1 WHERE user_id = :user_id ");
        $stmt->execute(['user_id' => $user_id]);
    }
    function setGroupInfo($gp_id, $owner_id, $title)
    {
        $stmt = $this->conn->prepare("INSERT IGNORE INTO gp (gp_id ,owner_id, title ) VALUES (:gp_id, :owner_id,:title )");
        $stmt->execute(['gp_id' => $gp_id, 'owner_id' => $owner_id, 'title' => $title]);
    }
    // function gpToUser($gp_id, $user_id)
    // {
    //     $stmt = $this->conn->prepare("UPDATE user SET gp_id = :gp_id WHERE user_id = :user_id");
    //     $stmt->execute(['gp_id' => $gp_id, 'user_id' => $user_id]);
    // }
    function userJoined($owner_id)
    {
        $sql = "SELECT u.user_id
                FROM user u
                JOIN group_users gu ON u.user_id = gu.user_id
                JOIN gp g ON gu.group_id = g.gp_id
                WHERE g.owner_id = :owner_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['owner_id' => $owner_id]);
        return $stmt->fetchColumn();
    }
    function groupList($owner_id)
    {
        $stmt = $this->conn->prepare("SELECT title FROM gp WHERE owner_id = :owner_id");
        $stmt->execute(['owner_id' => $owner_id]);
        return $stmt->fetchColumn();
    }
    function addGroupUser($user_id, $group_id)
    {
        $stmt = $this->conn->prepare("INSERT IGNORE INTO group_users (user_id,group_id) VALUES (:user_id, :gp_id)");
        $stmt->execute(['user_id' => $user_id, 'group_id' => $group_id]);
    }
    function userNameToId($tel_id)
    {
        $stmt = $this->conn->prepare("SELECT user_id FROM user WHERE tel_id = :tel_id ");
        $stmt->execute(['tel_id' => $tel_id]);
        return $stmt->fetchColumn();
    }
    function getLevel($user_id)
    {

        $stmt = $this->conn->prepare("SELECT level FROM user WHERE user_id = :user_id ");
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchColumn();
    }

    ########################################### GROUP #############################################################

    ########################################### /GROUP #############################################################

    ########################################### PRIVATE #############################################################
    function checkOwner($owner_id)
    {

        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM gp WHERE owner_id = :owner_id ");
        $stmt->execute(['owner_id' => $owner_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];  // د

        if ($count > 0) {
            return true;
        } else {
            return false; //
        }
    }
    function titleGp($user_id)
    {
        $sql = "SELECT gp.title
                    FROM gp
                    JOIN group_users ON gp.gp_id = group_users.gp_id
                    WHERE group_users.user_id = :user_id;";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    function titleGp1($owner_id)
    {
        $stmt = $this->conn->prepare("SELECT title FROM gp WHERE owner_id=:owner_id");
        $stmt->execute(['user_id' => $owner_id]);
        return $stmt->fetchColumn();
    }

    function getGpId($title)
    {
        $sql = "SELECT gp_id FROM gp WHERE title = :title";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['title' => $title]);
        return $stmt->fetchColumn();
    }
    function getUserName($user_id)
    {
        $stmt = $this->conn->prepare("SELECT tel_id FROM user WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    function getOwnerChatId($user_id)
    {
        $sql = "SELECT gp.owner_chat_id
        FROM gp
        JOIN group_users ON gp.gp_id = group_users.gp_id
        WHERE group_users.user_id = :user_id;";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchcolumn();
    }
    function getChatUser($user_id)
    {
        $stmt = $this->conn->prepare("SELECT chat_id FROM user WHERE ");
    }
    function insertPay($hash, $gp_id, $user_id, $status)
    {
        $stmt = $this->conn->prepare("INSERT INTO `payments`(`tran_hash`, `gp_id`, `user_id`,vaz) VALUES (:hash , :gp_id, :user_id,:vaz)");
        $stmt->execute(['hash' => $hash, 'gp_id' => $gp_id, 'user_id' => $user_id, 'vaz' => $status]);
    }
    public function getLastTransactionIdForUser($user_id)
    {
        $query = "SELECT transaction_id FROM transactions WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['transaction_id'];
        } else {
            return null; // در صورتی که تراکنشی یافت نشد
        }
    }
    function reject($transaction_id)
    {
        $stmt = $this->conn->prepare("UPDATE transactions SET status = 'rejected' WHERE transaction_id = :transaction_id");
        $stmt->execute(['transaction_id' => $transaction_id]);
    }
    function confirm($transaction_id)
    {
        $stmt = $this->conn->prepare("UPDATE transactions SET status = 'confirmed' WHERE transaction_id = :transaction_id");
        $stmt->execute(['transaction_id' => $transaction_id]);
    }
    function setTran($user_id, $transaction_hash)
    {
        //INSERT INTO transactions (user_id, transaction_hash, status) VALUES ($user_id, '$transaction_hash', 'pending')
        $stmt = $this->conn->prepare("INSERT INTO transactions (user_id, transaction_hash, status) VALUES (:user_id, :transaction_hash, 'pending')");
        $stmt->execute(['user_id' => $user_id, 'transaction_hash' => $transaction_hash]);
    }
    function userGroup($user_id, $gp_id)
    {
        $stmt = $this->conn->prepare("INSERT IGNORE  group_users (user_id , gp_id ) VALUES (:user_id,:gp_id)");
        $stmt->execute(['gp_id' => $gp_id, 'user_id' => $user_id]);
    }

    function isRowComplete($userId)
    {
        // کوئری SQL که ستون های tw_id و tel_id را بررسی می کند
        $sql = "SELECT COUNT(*) FROM user WHERE user_id = :userId AND tw_id IS NOT NULL AND tel_id IS NOT NULL";

        // اجرای کوئری با PDO
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // دریافت نتیجه شمارش
        $rowCount = $stmt->fetchColumn();

        // اگر یک سطر وجود داشته باشد که هر دو ستون tw_id و tel_id مقدار داشته باشند، true برگردانده شود
        return $rowCount > 0;
    }
    function groupId($ownerchatId)
    {
        $stmt = $this->conn->prepare("SELECT gp_id FROM gp WHERE owner_id= :owner_id");
        $stmt->execute(['owner_id' => $ownerchatId]);
        return $stmt->fetchColumn();
    }

    // 
    function getUserIdFromTransaction($transaction_id){
        $stmt=$this->conn->prepare("SELECT user_id FROM transactions WHERE transaction_id = :transaction_id");
        $stmt->execute(['transaction_id'=>$transaction_id]);
        return $stmt->fetchColumn();
    }
    function getUserIdByUserName($tel_id){
        $stmt=$this->conn->prepare("SELECT user_id FROM user WHERE tel_id = :tel_id");
        $stmt->execute(['tel_id'=>$tel_id]);
        return $stmt->fetchColumn();
    }
    public function getStepOwner($owner_id)
    {
        $stmt = $this->conn->prepare("SELECT step FROM gp WHERE owner_id = :owner_id");
        $stmt->execute([':owner_id' => $owner_id]);
        return $stmt->fetchColumn();
    }

    public function setStepOwner($owner_id, $step)
    {
        //INSERT INTO user_steps (user_id, step) VAUES (:user_id, :step) ON DUPLICATE KEY UPDATE step = :step
        $stmt = $this->conn->prepare("UPDATE gp SET step = :step WHERE owner_id = :owner_id");
        $stmt->execute([':owner_id' => $owner_id, ':step' => $step]);
    }
    ########################################### /PRIVATE #############################################################

}
