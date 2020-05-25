<?php

/*
 *
 * @author kametan0730mc
 * @link https://twitter.com/kametan0730
 *
*/


const ID_UNCONNECTED_PING = 0x01;
const ID_UNCONNECTED_PONG = 0x1c;

const MAGIC = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";

function unconnectedPing(string $host, int $port, &$result) : bool{
	$sock = @fsockopen("udp://" . $host, $port);
	if (!$sock) return false;
	stream_set_timeout($sock,  1, 0);

	/* Encode UnconnectedPing */
	$sendPacketBuffer = chr(ID_UNCONNECTED_PING); // UnconnectedPing packet id
	$sendPacketBuffer .= pack("J", mt_rand(0,100000)); // Ping id
	$sendPacketBuffer .= MAGIC; // Magic

	if(!@fwrite($sock, $sendPacketBuffer)) return false;
	$receivePacketBuffer = fread($sock, 1024);
	if(strlen($receivePacketBuffer) === 0) return false;

	/* Decode UnconnectedPong */
	$pid = ord(substr($receivePacketBuffer, 0, 1));
	if($pid !== ID_UNCONNECTED_PONG) return false;
	$sendPingTime = unpack("J", substr($receivePacketBuffer, 1, 8))[1];
	$serverId = unpack("J", substr($receivePacketBuffer, 9, 8))[1];
	$magic = substr($receivePacketBuffer, 17, 16);
	if($magic !== MAGIC) return false;
	$length = unpack("n", substr($receivePacketBuffer, 33, 2))[1];
	if($length === null or $length === 0) return false;
	$serverData = substr($receivePacketBuffer, 35, $length);
	if($serverData === null) return false;
	$info = explode(';', $serverData);
	if(count($info) === 0 or $info[0] !== "MCPE") return false;
	$result = $info;
	return true;
}

if(!unconnectedPing("sg.lbsg.net", 19132, $result)) {
	print("Failed to get information");
}else{

	// ↓ pmmp and vanilla
	$mcpe = $result[0]; // MCPE
	$serverName = $result[1];
	$protocol = $result[2];
	$version = $result[3];
	$loggedInPlayer = $result[4];
	$maxPlayer = $result[5];
	$unknown2 = $result[6]; // server id?
	/*
	// ↓ vanilla only
	$worldName = $result[7];
	$gameMode = $result[8];
	$unknown3 = $result[9]; // ?
	$ipv4Port = $result[10];
	$ipv6Port = $result[11];
	*/
//var_dump($result);
	print($serverName . " (" . $loggedInPlayer . "/" . $maxPlayer . ")");
}
