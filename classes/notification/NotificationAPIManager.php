<?php

require_once __DIR__ . "/NotificationDto.php";
require_once __DIR__ . "/../../classes.php";
require_once __DIR__ . "/../ReportManager.php";

class NotificationAPIManager {
    private System $system;
    private User $player;

    public function __construct(System $system, User $player ) {
        $this->system = $system;
        $this->player = $player;
    }

    /**
     * @return NotificationDto[]
     */
    public function getUserNotifications(): array
    {
        // Staff check
        if ($this->player->staff_manager->isModerator()) {
            $reportManager = new ReportManager($this->system, $this->player, true);
        }
        // Used for PM checks
        $playerInbox = new InboxManager($this->system, $this->player);

        // Return array
        $notifications = [];
        $notification_ids_to_delete = [];
        $notification_table_result = $this->system->db->query(
            "SELECT * FROM `notifications` WHERE `user_id` = {$this->player->user_id}"
        );
        while ($row = $this->system->db->fetch($notification_table_result)) {
            // If notification not valid mark for deletion and go to next loop, otherwise add to list
            if ($this->checkExpiration($row['expires'])) {
                $notification_ids_to_delete[] = $row['notification_id'];
                continue;
            }
            switch ($row['type']) {
                case NotificationManager::NOTIFICATION_TRAINING:
                    if ($this->player->train_time <= 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    }
                    else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("training"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_TRAINING_COMPLETE:
                    if ($this->player->train_time > 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    }
                    else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("training"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_STAT_TRANSFER:
                    if ($this->player->stat_transfer_completion_time <= 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    }
                    else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("profile"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_SPECIALMISSION:
                    if ($this->player->special_mission == 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("specialmissions"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_SPECIALMISSION_COMPLETE:
                    if ($this->player->special_mission != 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("specialmissions"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_SPECIALMISSION_FAILED:
                    if ($this->player->special_mission != 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("specialmissions"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_MISSION:
                    if ($this->player->mission_id == 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = MissionNotificationDto::fromDb($row, $this->system->router->getUrl("mission"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_MISSION_TEAM:
                    if ($this->player->mission_id == 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = MissionNotificationDto::fromDb($row, $this->system->router->getUrl("team"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_MISSION_CLAN:
                    if ($this->player->mission_id == 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = MissionNotificationDto::fromDb($row, $this->system->router->getUrl("clan"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_RANK:
                    if (!($this->player->level >= $this->player->rank->max_level && $this->player->exp >= $this->player->expForNextLevel() && $this->player->rank_num < System::SC_MAX_RANK && $this->player->rank_up)) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("profile"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_SYSTEM:
                    if (false) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl(""));
                    }
                    break;
                case NotificationManager::NOTIFICATION_WARNING:
                    if (!($this->player->getOfficialWarnings(true))) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl('account_record'));
                    }
                    break;
                case NotificationManager::NOTIFICATION_REPORT:
                    if (!($this->player->staff_manager->isModerator() && $reportManager->getActiveReports(true))) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl('report', ['page' => 'view_all_reports']));
                    }
                    break;
                case NotificationManager::NOTIFICATION_BATTLE:
                    if (json_decode($row['attributes'], true)['battle_id'] != $this->player->battle_id) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("battle"));
                        // to-do switch for URL based on battle type
                    }
                    break;
                case NotificationManager::NOTIFICATION_CHALLENGE:
                    if (!($this->player->challenge)) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("spar"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_TEAM:
                    if (!($this->player->team_invite)) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("team"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_MARRIAGE:
                    if (!($this->player->spouse < 0)) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("marriage"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_STUDENT:
                    if (!(SenseiManager::hasApplications($this->player->user_id, $this->system))) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl('academy'));
                    }
                    break;
                case NotificationManager::NOTIFICATION_INBOX:
                    if (!($playerInbox->checkIfUnreadMessages() || $playerInbox->checkIfUnreadAlerts())) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("inbox"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_CHAT:
                    if (false) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $chat_notification = ChatNotificationDto::fromDb($row, $this->system->router->getUrl("chat"));
                        if (isset($chat_notification->post_id)) {
                            $chat_notification->action_url = $this->system->router->getUrl("chat", ['post_id' => $chat_notification->post_id]);
                        }
                        $notifications[] = $chat_notification;
                    }
                    break;
                case NotificationManager::NOTIFICATION_EVENT:
                    if (false) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("event"));
                    }
                    break;
                case NotificationManager::NOTIFICATION_PROPOSAL_CREATED:
                case NotificationManager::NOTIFICATION_PROPOSAL_PASSED:
                case NotificationManager::NOTIFICATION_PROPOSAL_CANCELED:
                case NotificationManager::NOTIFICATION_PROPOSAL_EXPIRED:
                    if (false) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("villageHQ"));
                    }
                    break;
                default:
                    break;
            }
        }
        if (count($notification_ids_to_delete) > 0) {
            $this->system->db->query(
                "DELETE FROM `notifications` WHERE `notification_id` IN (" . implode(",", $notification_ids_to_delete) . ")"
            );
        }

        /* Check for general notifications */


        //Battle
        if ($this->player->battle_id > 0) {
            $result = $this->system->db->query(
                "SELECT `battle_type` FROM `battles` WHERE `battle_id`='{$this->player->battle_id}' LIMIT 1"
            );
            if ($this->system->db->last_num_rows == 0) {
                $this->player->battle_id = 0;
            } else {
                $result = $this->system->db->fetch($result);
                $link = null;
                switch ($result['battle_type']) {
                    case Battle::TYPE_AI_ARENA:
                        $link = $this->system->router->getUrl('arena');
                        break;
                    case Battle::TYPE_AI_MISSION:
                        $link = $this->system->router->getUrl('mission');
                        break;
                    case Battle::TYPE_AI_RANKUP:
                        $link = $this->system->router->getUrl('rankup');
                        break;
                    case Battle::TYPE_AI_WAR:
                        $link = $this->system->router->getUrl('war');
                        break;
                    case Battle::TYPE_SPAR:
                        $link = $this->system->router->getUrl('spar');
                        break;
                    case Battle::TYPE_FIGHT:
                        // battle notifications for PVP created on attack
                        break;
                }
                if ($link) {
                    $notifications[] = new NotificationDto(
                        action_url: $link,
                        type: NotificationManager::NOTIFICATION_BATTLE,
                        message: "In battle!",
                        user_id: $this->player->user_id,
                        created: time(),
                        alert: false,
                    );
                }
            }
        }
        //New PM
        if ($playerInbox->checkIfUnreadMessages() || $playerInbox->checkIfUnreadAlerts()) {
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('inbox'),
                type: NotificationManager::NOTIFICATION_INBOX,
                message: "You have unread PM(s)",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        //Official Warning
        if ($this->player->getOfficialWarnings(true)) {
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('account_record'),
                type: NotificationManager::NOTIFICATION_WARNING,
                message: "Official Warning(s)!",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        //New Report
        if ($this->player->staff_manager->isModerator() && $reportManager->getActiveReports(true)) {
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('report', ['page' => 'view_all_reports']),
                type: NotificationManager::NOTIFICATION_REPORT,
                message: "New Report(s)!",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        //New spar
        if ($this->player->challenge) {
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('spar'),
                type: NotificationManager::NOTIFICATION_CHALLENGE,
                message: "Challenged!",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        //Team invite
        if ($this->player->team_invite) {
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('team'),
                type: NotificationManager::NOTIFICATION_TEAM,
                message: "Invited to team!",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        //Proposal
        if ($this->player->spouse < 0) {
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('marriage'),
                type: NotificationManager::NOTIFICATION_MARRIAGE,
                message: "Proposal received!",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        //Student Applications
        if (SenseiManager::isActiveSensei($this->player->user_id, $this->system)) {
            if (SenseiManager::hasApplications($this->player->user_id, $this->system)) {
                $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('academy'),
                type: NotificationManager::NOTIFICATION_STUDENT,
                message: "Application received!",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
            }
        }
        //Event
        if (isset($this->system->event)) {
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('event'),
                type: NotificationManager::NOTIFICATION_EVENT,
                message: $this->system->event->name . " is active! " .
                    $this->system->time_remaining($this->system->event->end_time->getTimestamp() - time()),
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        //Caravan
        $time = time();
        $result = $this->system->db->query("SELECT `caravans`.*, `regions`.`name` as 'region_name' FROM `caravans` INNER JOIN `regions` on `caravans`.`region_id` = `regions`.`region_id` WHERE `start_time` < {$time}");
        $result = $this->system->db->fetch_all($result);
        foreach ($result as $row) {
            // if travel time is set then only display if active
            if (!empty($row['travel_time'])) {
                if ($row['travel_time'] + ($row['start_time'] * 1000) + Patrol::DESTINATION_BUFFER_MS > (time() * 1000)) {
                    $notifications[] = new NotificationDto(
                        action_url: $this->system->router->getUrl('travel'),
                        type: NotificationManager::NOTIFICATION_CARAVAN,
                        message: "{$row['name']} is active near {$row['region_name']}",
                        user_id: $this->player->user_id,
                        created: time(),
                        alert: false,
                    );
                }
            }
        }
        //Raid
        $time = time() - 60; // only get recently updated raids, prevent something like starting a raid and logging out = perpetual notif
        $result = $this->system->db->query("SELECT `operations`.*, `region_locations`.`x`, `region_locations`.`y`, `region_locations`.`map_id`, `region_locations`.`name` FROM `operations`
            INNER JOIN `region_locations` ON `region_locations`.`id` = `operations`.`target_id`
            WHERE (`user_village` = {$this->player->village->village_id} OR `target_village` = {$this->player->village->village_id})
            AND `last_update` > {$time}
            AND `status` = " . Operation::OPERATION_ACTIVE .
            " AND `operations`.`type` = " . Operation::OPERATION_RAID
            . " GROUP BY `region_locations`.`id`");
        $result = $this->system->db->fetch_all($result);
        foreach ($result as $row) {
            $location = new TravelCoords($row['x'], $row['y'], $row['map_id']);
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('travel'),
                type: $row['user_village'] == $this->player->village->village_id ? NotificationManager::NOTIFICATION_RAID_ALLY : NotificationManager::NOTIFICATION_RAID_ENEMY,
                message: $row['user_village'] == $this->player->village->village_id ? "An ally is attacking {$row['name']} at {$location->displayString()}!" : "{$row['name']} is under attack at {$location->displayString()}!",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        return $notifications;
    }

    public function closeNotification(int $notification_id): bool {
        $this->system->db->query("DELETE FROM `notifications` WHERE `notification_id` = {$notification_id}");
        return $this->system->db->last_affected_rows > 0 ? true : false;
    }

    public function clearNotificationAlert(int $notification_id): bool {
        $this->system->db->query("UPDATE `notifications` set `alert` = 0 WHERE `notification_id` = {$notification_id}");
        return $this->system->db->last_affected_rows > 0 ? true : false;
    }

    public function checkExpiration(?int $expires): bool {
        if (!isset($expires)) {
            return false;
        }
        if ($expires < time()) {
            return true;
        }
        return false;
    }
}