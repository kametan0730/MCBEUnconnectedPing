<?php

/*
 *
 * @author kametan0730mc
 * @link https://twitter.com/kametan0730
 *
*/


const ID_UNCONNECTED_PING = 0x01;
const ID_UNCONNECTED_PONG = 0x1c;

function unconnectedPing(string $host, int $port, &$result) : bool{
	$sock = @fsockopen("udp://" . $host, $port);
	if (!$sock) false;
	stream_set_timeout($sock,  1, 0);

	/* Encode UnconnectedPing */
	$sendPacketBuffer = chr(ID_UNCONNECTED_PING); // UnconnectedPing packet id
	$sendPacketBuffer .= pack("J", mt_rand(0,100000)); // Ping Id
	$sendPacketBuffer .= "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78"; // Magic

	if(!@fwrite($sock, $sendPacketBuffer)) return false;
	$receivePacketBuffer = fread($sock, 1024);
	if(strlen($receivePacketBuffer) === 0) return false;

	/* Decode UnconnectedPong */
	$pid = ord(substr($receivePacketBuffer, 0, 1));
	if($pid !== ID_UNCONNECTED_PONG) return false;
	$sendPingTime = unpack("J", substr($receivePacketBuffer, 1, 8))[1];
	$serverId = unpack("J", substr($receivePacketBuffer, 9, 8))[1];
	$magic = substr($receivePacketBuffer, 17, 16);
	$serverData = substr($receivePacketBuffer, 35, unpack("n", substr($receivePacketBuffer, 33, 2))[1]);
	$info = explode(';', $serverData);
	$result = $info;
	return true;
}

if(!unconnectedPing("sg.lbsg.net", 19132, $result)) {
	print("Failed to get information");
}else{

	$mcpe = $result[0]; // MCPE
	// ↓ pmmp and vanilla
	$serverName = $result[1];
	$unknown1 = $result[2]; // ?
	$version = $result[3];
	$loggedInPlayer = $result[4];
	$maxPlayer = $result[5];
	$unknown2 = $result[6]; // ?
	/*
	// ↓ vanilla only
	$worldName = $result[7];
	$gameMode = $result[8];
	$unknown3 = $result[9]; // ?
	$unknown4 = $result[10]; // ?
	$unknown5 = $result[11]; // ?
	$unknown6 = $result[12]; // ?
	*/
	print($serverName . " (" . $loggedInPlayer . "/" . $maxPlayer . ")");
}