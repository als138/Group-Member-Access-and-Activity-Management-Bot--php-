<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');


$content = file_get_contents("php://input");
$update = json_decode($content, true);

include('class/bot.php');
include('class/db.php');

$db = new db();

$bot = new bot("7114142815:AAE540lPoM3qOdqe59GxvnTMPoMnpl51YgI", $content, $db);

$cBackData = $bot->datamytext;

///////////////////////////////PAY////////////////////////////////////
$transaction_id = explode(":", $bot->datamytext)[1]; // دریافت transaction_id

if (strpos($bot->datamytext, "confirm_payment") !== false) {
  // تأیید تراکنش

  $db->confirm($transaction_id);
  // پیام تأیید به مالک و کاربر
  //$bot->answerCallbackQuery($bot->chatid, "تراکنش تأیید شد.");
  $user_id = $db->getUserIdFromTransaction($transaction_id);
  ////
  file_put_contents("ida.log", print_r($user_id . PHP_EOL, true), FILE_APPEND);

  ////
  $bot->sendmessage("جواب 'تایید' ثبت شد", $bot->datachatid, '');

  $bot->sendMessage("پرداخت شما تأیید شد.", $user_id, '');
  $db->levelUp($user_id);
  $re = $db->checkLevel($user_id);
  $bot->sendmessage("سطح شما به" . " " . $re . " " . "ارتقا یافت", $user_id, '');
} elseif (strpos($bot->datamytext, "reject_payment") !== false) {
  // رد تراکنش
  $db->reject($transaction_id);

  $bot->sendmessage("جواب 'رد' ثبت شد", $bot->datachatid, '');
  // پیام رد به مالک و کاربر
  //$bot->answerCallbackQuery($bot->datachatid, "تراکنش رد شد.");
  $user_id = $db->getUserIdFromTransaction($transaction_id);
  $bot->sendMessage("پرداخت شما رد شد. لطفاً دوباره امتحان کنید.", $user_id, '');
}

//////////////////////////////////PAY//////////////////////////////////

if ($cBackData == "sing_up") {
  $db->setStep($bot->datachatid, 'awaiting_age');
  $bot->sendMessage("سن خود را وارد کنید ", $bot->chatid, '');
}

if ($cBackData == "manual_up") {
  $db->setStepOwner($bot->datachatid, 'manual');
  $bot->sendMessage("آیدی کاربر مورد نظر را با @ وارد کنید ", $bot->chatid, '');
}

if ($cBackData == "open_level_up") {
  $db->setStep($bot->datachatid, 'pay');
  $bot->sendmessage("لطفا مبلغ مورد نظر را انتقال دهید و هش تراکنش را ارسال کنید.بعد از تایید پرداخت توسط ادمین.سطح شما ارتقا می یابد", $bot->chatid, '');
}

if ($cBackData == "open_groups") {
  // $groups = $db->titleGp($bot->datachatid);
  // $idd = $db->getGpId($groups);
  // $bot->sendGpLink($bot->datachatid, -1002327474085);
  $invite_link = $bot->createInviteLink(-1002320774167, null, 1);
  $bot->sendInviteLinkToUser($bot->datachatid, $invite_link);
}

if (isset($bot->recmsg)) {
  ####################################### GROUP #########################################################
  if ($bot->chattype === "group" || $bot->chattype === "supergroup") {
    // $unique_code = $bot->generateUniqueCode();
    $owner = $bot->getOwner();
    if ($db->checkUserExistence($bot->userid) == false) {

      switch ($db->checkLevel($bot->userid)) {
        case 1:
          //level 1
          $check = $db->checkMessageLimit($bot->userid, $bot->msgtype, 5, 1, 1, 1, 1, 1);
          break;

        case 2:
          //level 2
          $check = $db->checkMessageLimit($bot->userid, $bot->msgtype, 20, 5, 5, 5, 5, 5);
          break;

        case 3:
          //level 3
          $check = $db->checkMessageLimit($bot->userid, $bot->msgtype, 20, 0, 0, 0, 0, 0);
          break;

        case 4:
          //level 4
          $check = $db->checkMessageLimit($bot->userid, $bot->msgtype, 20, 0, 0, 0, 0, 0);
          break;

        default:
          //level 5
          $check = $db->checkMessageLimit($bot->userid, $bot->msgtype, 20, 0, 0, 0, 0, 0);
          break;
      }
      if ($check == false) {
        $bot->deleteNonOwnerMessages($owner);
      }
    } else {
      $owner = $bot->getOwner();
      if($bot->userid != $owner)
      $bot->sendUniqueBotLink($bot->chatid, $bot->userid);
      $bot->deleteNonOwnerMessages($owner);
       $db->setGroupInfo($bot->chatid, $owner, $bot->title);


      // $gp = $bot->chatid;
    }


    // if ($bot->chattype === "group" || $bot->chattype === "supergroup") {
    // file_put_contents("groupId.log", print_r($bot->chatid .PHP_EOL ,true), FILE_APPEND); 


    // }
    #################################################### /group############################################################

  } elseif ($bot->chattype === "private") {
    if ($db->checkOwner($bot->userid) == false) {
      #user 
      if ($db->isRowComplete($bot->userid)) {
        #Registered
        if ($bot->recmsg == '/start') {
          $bot->inlineUserPetty($bot->chatid); //inline user who Registered
          $db->userGroup($bot->userid, -1002320774167); ///add to group_users table
        }
        if ($db->getStep($bot->userid) == 'pay') {

          $ownerChatId = $db->getOwnerChatId($bot->userid);
          $uN = $db->getUserName($bot->userid);
          // $bot->sendHash($ownerChatId, $uN, $bot->recmsg);

          // $groups = $db->titleGp(1234); 
          // $idd = $db->getGpId($groups);
          //$db->insertPay($bot->recmsg,$bot->userid,$idd,"pending");

          // $db->userGroup($bot->userid,$db->groupId($ownerChatId));
          $db->setTran($bot->userid, $bot->recmsg);
          $bot->sendMessage("هش تراکنش شما دریافت شد. منتظر تأیید باشید.", $bot->userid, '');
          $bot->inlineHash($ownerChatId, $uN, $bot->recmsg, $bot->userid);
          $db->setStep($bot->userid, null);
        }
      } else {
        #Not Registered
        if ($bot->recmsg != '/start') {
          $bot->saveUserDetails($bot->userid, $bot->recmsg);
        }
        if ($bot->recmsg == '/start') {
          $db->newUserInsert($bot->userid);

          $bot->inlineSingUp($bot->chatid);
        }
        if ($bot->recmsg == "/start" . ' ' . $bot->userid) {

          //$db->gpToUser($db->getGpId($db->titleGp($bot->chatid)),$bot->userid);

          $db->newUserInsert($bot->userid);
          $bot->inlineSingUp($bot->chatid);
          //   $db->userGroup($bot->userid,$db->getGpId($db->titleGp($bot->chatid)));

        }
      }
    } else {
      #owner
      /// owner menu
      if ($bot->recmsg == '/start') {
        $bot->inlineManualUp($bot->chatid);
      }
      //// manual level up
      if ($db->getStepOwner($bot->userid) == 'manual') {
        $groupss = $db->titleGp($bot->chatid);
        $iddd = $db->getGpId($groups);
        if (strpos($bot->recmsg, '@') === 0) {
          $ui = $db->getUserIdByUserName($bot->recmsg);
          $db->levelUp($ui);
          $chh = $db->checkLevel($ui);
          $bot->sendmessage("لول این کاربر به " . " " . $chh . " " . "ارتقا یافت.", $bot->chatid, '');
        } else {
          $bot->sendmessage("کاربری با این آیدی در گروه شما عضو نیست", $bot->chatid, '');
        }
        $db->setStepOwner($bot->chatid, null);
      }
    }
  }
}



###################################################################################################################################
// file_put_contents("ow.log", print_r($db->getGpId($db->titleGp($bot->chatid)) . PHP_EOL, true), FILE_APPEND);
file_put_contents("ownerId.log", print_r($bot->userid . PHP_EOL, true), FILE_APPEND);

file_put_contents("ownerchatId.log", print_r($bot->chatid . PHP_EOL, true), FILE_APPEND);
file_put_contents("Row.log", print_r($db->isRowComplete($bot->userid) . PHP_EOL, true), FILE_APPEND);
