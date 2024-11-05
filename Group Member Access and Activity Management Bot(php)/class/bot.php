<?php
ini_set('display_errors', '0');


class bot
{


    public $token;
    ///
    public $db;
    ////
    public $step;
    public $title;
    public $chatid;
    public $recmsg;
    public $message_id;
    public $userid;
    public $username;
    public $firstname;
    public $lastname;
    public $datamytext;
    public $datachatid;
    public $phone;
    public $exRecmsg;
    public $exRecGlass;
    public $chattype;
    public $dInline;
    public $msgid;
    public $msgtype;
    public $r;

    function __construct($token, $content, $dbInstance)
    {
        $this->token = $token;
        ///
        $this->db = $dbInstance;
        ////

        if ($content != '') {
            $update = json_decode($content, true);
            $message = $update["message"];
            $this->recmsg = $message['text'];
            $this->recmsg = str_replace('@teletools_member_counter_bot', '', $this->recmsg);
            $this->message_id = $message['message_id'];
            $this->chatid = $message['chat']['id'];
            $this->userid = $message['from']['id'];
            $this->chattype = $message["chat"]["type"];

            $this->username = $message['from']['username'];
            $this->firstname = $message['from']['first_name'];
            $this->lastname = $message['from']['last_name'];
            $this->dInline = $update["callback_query"];
            $this->msgid   = $update["callback_query"]["message"]["message_id"];
            $this->datamytext = $update["callback_query"]["data"];
            $this->datachatid = $update["callback_query"]["from"]["id"];

            $this->r;
            if ($this->chatid == '') {
                $this->chatid = $this->datachatid;
                $this->recmsg = $this->datamytext;
            }
            $this->title = $message['chat']['title'];
            //////////////////////////////
            if (isset($message['text'])) {
                $message_type = 'text';
            } elseif (isset($message['photo'])) {
                $message_type = 'photo';
            } elseif (isset($message['gif'])) {
                $message_type = 'gif';
            } elseif (isset($message['video'])) {
                $message_type = 'video';
            } elseif (isset($message['voice'])) {
                $message_type = 'voice';
            } elseif (isset($message['video_note'])) {
                $message_type = 'video_note';
            } else {
                // پیام از نوع مورد قبول نیست
                return;
            }
            $this->msgtype = $message_type;
            /////////////////////////////

            $this->phone = $message["contact"]["phone_number"];
            $erecmsg = str_replace("/start", "", $this->recmsg);
            $erecmsg = str_replace(" ", "", $erecmsg);
            $exrec = explode("_", $erecmsg);
            $this->exRecmsg = $exrec;
            $this->exRecGlass = explode("a", $this->datamytext);
        }
    }

    ############################################################### USER ########################################################

    public function answerCallbackQuery($callback_query_id, $text)
{
    // ساختن URL برای فراخوانی API تلگرام
    $url = "https://api.telegram.org/bot" . $this->token . "/answerCallbackQuery";
    
    // پارامترهای لازم برای درخواست
    $params = array(
        'callback_query_id' => $callback_query_id,
        'text' => $text,
        'show_alert' => false 
    );

    // شروع یک درخواست cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // نتیجه به صورت رشته بازگردانده شود
    curl_setopt($ch, CURLOPT_POST, true); // درخواست به صورت POST باشد
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params)); // ارسال پارامترها

    // اجرای درخواست
    $response = curl_exec($ch);
    curl_close($ch); // بستن درخواست

    // برگرداندن نتیجه به صورت آرایه
    return json_decode($response, true);
}

    function inlineSingUp($chat_id)
    {




        $keyboard = [
            "inline_keyboard" => [
                [

                    ["text" => "احراز هویت", "callback_data" => "sing_up"]

                ]

            ]
        ];

        $data = [
            'chat_id' => $chat_id,
            'text' => "ابتدا احراز هویت کنید",

            'reply_markup' => json_encode($keyboard)
        ];

        $api_url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        file_get_contents($api_url, false, $context);
    }

    function inlineHash($ownerChat_id, $username, $hash, $user_id)
    {

        $transaction_id = $this->db->getLastTransactionIdForUser($user_id);


        $keyboard = [
            "inline_keyboard" => [
                [
                    ["text" => "تأیید", "callback_data" => "confirm_payment:".$transaction_id],
                    ["text" => "رد", "callback_data" => "reject_payment:".$transaction_id]
                ]

            ]
        ];

        $data = [
            'chat_id' => $ownerChat_id,
            'text' => "کاربد بانام کاربری:" . $username .
                "هش تراکنش :\n" . $hash,

            'reply_markup' => json_encode($keyboard)
        ];

        $api_url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        file_get_contents($api_url, false, $context);
    }

    function sendHash($ownerChat_id, $username, $hash)
    {



        $keyboard = [
            "inline_keyboard" => [
                [
                    ["text" => "تایید پرداخت", "callback_data" => "accept"]

                ]

            ]
        ];

        $data = [
            'chat_id' => $ownerChat_id,
            'text' => "کاربد بانام کاربری:" . $username .
                "هش تراکنش :" . $hash,

            'reply_markup' => json_encode($keyboard)
        ];

        $api_url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        file_get_contents($api_url, false, $context);
    }

    function inlineUserPetty($chat_id)
    {



        $keyboard = [
            "inline_keyboard" => [
                [
                    ["text" => "ارتقا سطح", "callback_data" => "open_level_up"],
                    ["text" => "لینک گروه", "callback_data" => "open_groups"]

                ]

            ]
        ];

        $data = [
            'chat_id' => $chat_id,
            'text' => "شما قبلا ثبت نام کردید:",
            'reply_markup' => json_encode($keyboard)
        ];

        $api_url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        file_get_contents($api_url, false, $context);
    }
    function inlineUserGroup($chat_id)
    {
        $sql = "SELECT title FROM gp";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $groups = $stmt->fetchAll();



        $inlineKeyboard = [];

        foreach ($groups as $group) {
            $inlineKeyboard[] = [
                [
                    'text' => $group['title'], //  
                    'callback_data' => 'select_' . $group['title'] //  
                ]
            ];
        }

        //file_put_contents("ppp.log", print_r($inlineKeyboard, true), FILE_APPEND);
        $data = [
            'chat_id' => $chat_id,
            'text' => "شما قبلا ثبت نام کردید:",
            'replyMarkup' => json_encode(['inline_keyboard' => $inlineKeyboard])

        ];

        $api_url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        $r = file_get_contents($api_url, false, $context);
        file_put_contents("ppp.log", print_r($r, true), FILE_APPEND);
    }
    function sendGpLink($chatid, $gp_id)
    {
        $api_url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";

        // $bot_username = $this->getBotUsername($this->token); //    
        $invite_link = "https://api.telegram.org/bot" . $this->token . "/exportChatInviteLink?chat_id=" . $gp_id;

        $data = [
            'chat_id' => $chatid,
            'text' => "برای ورود به گروه از لینک زیر استفاده کنید:\n$invite_link"
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        $response = file_get_contents($api_url, false, $context);
        $res = json_decode($response, true);
        $date = $res['result'];
        return $data;
        if ($response === FALSE) {
            error_log("Error sending message: " . json_last_error_msg());
        }
        // $this->param = true;

    }

    ################################################################ /USER #########################################################

    ############################################################### OWNER ########################################################
    function inlineManualUp($chat_id)

    {



        $keyboard = [
            "inline_keyboard" => [
                [
                    ["text" => "ارتقا سطح", "callback_data" => "manual_up"]


                ]

            ]
        ];

        $data = [
            'chat_id' => $chat_id,
            'text' => "شما مالک گروه " . " " . $this->db->titleGp($chat_id) . " " . " هستید",
            'reply_markup' => json_encode($keyboard)
        ];

        $api_url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        file_get_contents($api_url, false, $context);
    }
    ############################################################## /OWNER ########################################################
    function getOwner()
    {
        $api_url = "https://api.telegram.org/bot" . $this->token . "/getChatAdministrators?chat_id=" . $this->chatid;
        $response = file_get_contents($api_url);
        $admins = json_decode($response, true);

        $owner_id = null;
        foreach ($admins['result'] as $admin) {
            if ($admin['status'] == 'creator') {
                $ownerid = $admin['user']['id'];
                break;
            }
        }
        return $ownerid;
    }

    function deleteNonOwnerMessages($ownerid)
    {
        if ($this->userid != $ownerid) {
            $api_url = "https://api.telegram.org/bot" . $this->token . "/deleteMessage?chat_id=" . $this->chatid . "&message_id=" . $this->message_id;
            file_get_contents($api_url); //   ‌
        }
    }
    function sendmessage($text, $chatid, $keys)
    {
        $ch = curl_init("https://api.telegram.org/bot" . $this->token . "/sendmessage?chat_id=$chatid&text=" . urlencode($text) . "&parse_mode=HTML&reply_markup=$keys");
        curl_exec($ch);
    }

    function sendAddBotToGroupInlineKeyboard($chatid)
    {
        $bot_url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";

        $addBotLink = "https://t.me/test_1237bot?startgroup=new";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'اضافه کردن ربات به گروه', 'url' => $addBotLink]
                ],
                [
                    ['text' => 'ارتقای سطح اعضای گروه هایی که شما مالک آن هستید ', 'callback_data' => 'show_group']
                ]

            ]
        ];

        $replyMarkup = json_encode($keyboard);

        $text = "برای اضافه کردن ربات به گروه، روی دکمه زیر کلیک کنید:";

        $postData = [
            'chat_id' => $chatid,
            'text' => $text,
            'reply_markup' => $replyMarkup
        ];

        $ch = curl_init($bot_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    function getBotUsername($token)
    {
        // URL API
        $url = "https://api.telegram.org/bot" . $token . "/getMe";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($result, true);

        if ($data['ok']) {
            return $data['result']['username'];
        } else {
            return "Error fetching bot username!";
        }
    }

    function sendUniqueBotLink($chatid, $unique_code)
    {
        $api_url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";

        $bot_username = $this->getBotUsername($this->token); //    
        $invite_link = "https://t.me/$bot_username?start=$unique_code";

        $data = [
            'chat_id' => $chatid,
            'text' => "برای ورود به ربات از لینک زیر استفاده کنید:\n$invite_link",
            'parse_mode' => 'HTML',
            'member_limit' => 1,  //   یک 
            'expire_date' => time() + 180
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        $response = file_get_contents($api_url, false, $context);

        if ($response === FALSE) {
            error_log("Error sending message: " . json_last_error_msg());
        }
        // $this->param = true;

    }

    function generateUniqueCode($length = 16)
    {
        //    
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    function firstSignUpInline($chat_id)
    {

        $keyboard = [
            "inline_keyboard" => [
                [
                    ["text" => "ثبت یا تغییر مشخصات", "callback_data" => "open_second_keyboard"]
                ]
            ]
        ];

        $data = [
            'chat_id' => $chat_id,
            'text' => "برای تغییر یا ثبت مشخصات لطفا کلیک کنید:",
            'reply_markup' => json_encode($keyboard)
        ];

        $api_url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        file_get_contents($api_url, false, $context);
    }
    function SecondSingUpInline($chat_id, $message_id)
    {
        $keyboard = [
            "inline_keyboard" => [
                [
                    ["text" => "شهر", "callback_data" => "city"],
                    ["text" => "جنسیت", "callback_data" => "gender"],
                    ["text" => "سن", "callback_data" => "age"]
                ],
                [
                    ["text" => "هدف از عضویت در گروه", "callback_data" => "goal"]
                ],
                [
                    ["text" => "آیدی تلگرام", "callback_data" => "tel_id"],
                    ["text" => "آیدی توییتر", "callback_data" => "tw_id"]
                ]
            ]
        ];

        $data = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "This is the second keyboard:",
            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ];

        $api_url = "https://api.telegram.org/bot" . $this->token . "/editMessageText";

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        return file_get_contents($api_url, false, $context);
    }

    function saveUserDetails($user_id, $message)
    {
        $step = $this->db->getStep($user_id);

        if ($step == 'awaiting_age') {
            // ذخیره سن کاربر
            //   if(is_numeric($message)){
            $this->db->saveUserAge($user_id, $message);

            // رفتن به مرحله بعد
            $this->askUserForDetails($user_id);
            //   }else{
            //  $this->sendMessage("سن را به عدد وارد کنید",$this->chatid,'')
            //  }
        } elseif ($step == 'awaiting_city') {
            // ذخیره شهر کاربر
            $this->db->saveUserCity($user_id, $message);

            // رفتن به مرحله بعد
            $this->askUserForDetails($user_id);
        } elseif ($step == 'awaiting_goal') {
            // ذخیره هدف کاربر

            $this->db->saveUserGoal($user_id, $message);

            $this->askUserForDetails($user_id);
        } elseif ($step == 'awaiting_gender') {
            // ذخیره جنسیت
            // if ($message == 'gender_female' || $message == 'gender_male') {
            //     $gender = ($message == 'gender_female') ? 'female' : 'male';

            $this->db->saveUserGender($user_id, $message);
            //     // رفتن به مرحله بعد
            $this->askUserForDetails($user_id);
            // }else {
            //     $this->sendMessage("لطفا جنسیت خود را از گزینه‌های داده شده انتخاب کنید.",$user_id,'');
            // }
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } elseif ($step == 'awaiting_twitter') {
            // چک کردن لینک توییتر
            if ($this->isValidTwitterLink($message)) {
                // ذخیره لینک توییتر
                $this->db->saveUserTw($user_id, $message);
                // رفتن به مرحله بعد
                $this->askUserForDetails($user_id);
            } else {
                // لینک نامعتبر است، دوباره از کاربر درخواست می‌کنیم
                $this->sendMessage("لینک توییتر معتبر نیست. لطفا لینک توییتر را به فرمت https://x.com/your_id وارد کنید.", $this->chatid, '');
            }
            //  $this->askUserForDetails($user_id);
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } elseif ($step == 'awaiting_tel') {
            // ذخیره هدف کاربر
            $this->db->saveUserTel($user_id, $message);
            // اتمام پروسه ثبت‌نام
            $this->db->setStep($user_id, null); // ثبت‌نام به پایان رسید
            // $this->sendMessage("ثبت‌نام شما با موفقیت انجام شد.",$user_id,'');
            $this->db->saveUserLevel($user_id, 1);
            $this->sendMessage("ثبت نام تکمیل شد لطفا برای ورود به گروه  /start بزنید \n سطح ۱ به شما تعلق گرفت", $this->chatid, '');
        }
    }

    function askUserForDetails($user_id)
    {
        // دریافت مرحله فعلی کاربر
        $step = $this->db->getStep($user_id);

        if ($step == null) {
            // شروع پروسه ثبت‌نام و پرسیدن سن

            $this->db->setStep($user_id, 'awaiting_age');
            $this->sendMessage("سن خود را وارد کنید ", $this->chatid, '');
        } elseif ($step == 'awaiting_age') {
            // کاربر باید سن را وارد کند
            $this->db->setStep($user_id, 'awaiting_city');
            $this->sendMessage("شهر خود را وارد کنید", $this->chatid, '');
        } elseif ($step == 'awaiting_city') {
            // کاربر باید شهر را وارد کند
            $this->db->setStep($user_id, 'awaiting_goal');
            $this->sendMessage("لطفا هدف از عضویت در گروه را وارد کنید:", $this->chatid, '');
        } elseif ($step == 'awaiting_goal') {
            // کاربر باید هدف از عضویت را وارد کند
            $this->db->setStep($user_id, 'awaiting_gender'); // مرحله ثبت‌نام به پایان رسیده
            $this->sendMessage("جنسیت خود را مشخص کنید", $this->chatid, '');

            // $this->db->setStep($user_id, null); // مرحله ثبت‌نام به پایان رسیده
            // $this->sendMessage("جنسیت خود را مشخص کنید",$user_id,'');
            // $keyboard = [
            //     "inline_keyboard" => [
            //         [
            //             ["text" => "دختر", "callback_data" => "gender_female"],
            //             ["text" => "پسر", "callback_data" => "gender_male"]
            //         ]
            //     ]
            // ];

            // $this->sendInlineKeyboard($user_id, "لطفا جنسیت خود را انتخاب کنید:", $keyboard);

        } elseif ($step == 'awaiting_gender') {
            $this->db->setStep($user_id, 'awaiting_twitter');
            $this->sendMessage("آیدی توییتر خودتان را وارد کنید", $this->chatid, '');
        } elseif ($step == 'awaiting_twitter') {
            $this->db->setStep($user_id, 'awaiting_tel'); //  ‌   
            $this->sendMessage("آیدی تلگرام خودتان رابا @ وارد کنید", $this->chatid, '');
        } elseif ($step == 'awaiting_tel') {
            if (strpos($this->recmsg, '@')[1] === 0) {
            $this->db->setStep($user_id, null); //   
            $this->db->saveUserLevel($user_id, 1);
            $this->sendMessage("ثبت نام انجام شد و سطح ۱ به شما تعلق گرفت", $this->chatid, '');
            }else{
            $this->sendMessage("آیدی خودتان را با @ وارد کنید", $this->chatid, '');
                
            }
        }
    }

    function sendInlineKeyboard($chat_id, $text, $keyboard)
    {
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => json_encode($keyboard)
        ];

        $api_url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        file_get_contents($api_url, false, $context);
    }
    function isValidTwitterLink($link)
    {
        return preg_match("/^https:\/\/x\.com\/[a-zA-Z0-9_]+$/", $link);
    }
    //////////////////////////////invite/////////////////////////
    function createInviteLink( $group_id, $expire_seconds = null, $member_limit = null) {
        // تنظیمات مربوط به محدودیت‌ها و زمان انقضا (در صورت وجود)
        $invite_link_data = [
            'chat_id' => $group_id,
        ];
    
        if ($expire_seconds) {
            // محاسبه زمان انقضا از زمان فعلی
            $invite_link_data['expire_date'] = time() + $expire_seconds;
        }
    
        if ($member_limit) {
            // محدودیت تعداد اعضا
            $invite_link_data['member_limit'] = $member_limit;
        }
    
        // درخواست به API تلگرام
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot".$this->token."/createChatInviteLink");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($invite_link_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    
        // پردازش پاسخ API
        $result = json_decode($response, true);
    
        if ($result['ok']) {
            return $result['result']['invite_link'];  // لینک دعوت برمی‌گردد
        } else {
            return "Error: " . $result['description'];  // خطا در صورت وجود
        }
    }

    function sendInviteLinkToUser($chat_id, $invite_link) {
        // داده‌های ارسال پیام
        $message_data = [
            'chat_id' => $chat_id,
            'text' => "لینک دعوت به گروه:\n$invite_link",
            'parse_mode' => 'HTML'
        ];
    
        // درخواست به API تلگرام برای ارسال پیام
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot".$this->token."/sendMessage");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($message_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    
        // پردازش پاسخ API
        $result = json_decode($response, true);
        if ($result['ok']) {
            return "Message sent successfully!";
        } else {
            return "Error: " . $result['description'];
        }
    }
    
}
