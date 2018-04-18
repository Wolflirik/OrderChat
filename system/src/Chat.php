<?php

namespace OrderChat;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        $msg = json_decode($msg);
        require_once('../../config.php');
        $db = new MySQL(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        switch($msg->type){
            case 'add': 
                $db->query("INSERT INTO " . DB_PREFIX . "chat_for_order SET name = '" . $db->escape($msg->author) . "', important = '" . (int)$msg->status . "', date_time = NOW(), order_id = '" . $db->escape($msg->orderId) . "', message = '" . $db->escape($msg->text) . "'");
                $result = $db->query("SELECT MAX(id_message) as id, MAX(date_time) as date_time FROM " . DB_PREFIX . "chat_for_order")->row;
                $msg->date = $result['date_time'];
                $msg->id = $result['id'];
            break; 
            case 'update': 
                $db->query("UPDATE  `" . DB_PREFIX . "chat_for_order` SET  `important` =  '".$db->escape((int)$msg->status)."' WHERE  `id_message` ='" . $db->escape($msg->id)."'");
            break; 
            case 'delete': 
                $db->query("UPDATE  `" . DB_PREFIX . "chat_for_order` SET  `visibility` =  '0' WHERE  `id_message` =" . $db->escape($msg->id));
            break; 
        }
        $msg = json_encode($msg);
        $msg = substr($msg, 0, -1);
        $msg .= ',"from":"';
        foreach ($this->clients as $client) {
            if ($from->resourceId == $client->resourceId) {
                $client->send($msg.'0"}');
            }
            if($from->resourceId != $client->resourceId){
                $client->send($msg.'1"}');
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}