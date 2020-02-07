<?php
/*
 *
 * @author kametan0730mc
 * @link https://twitter.com/kametan0730
 *
*/

class Packet{

	protected $offset;
	protected $buffer;
	
	public function setBuffer(string $buffer = "", int $offset = 0) : void{
		$this->buffer = $buffer;
		$this->offset = $offset;
	}
	public function getBuffer() : string{
		return $this->buffer;
	}
	
	public function get(int $len) : string{
		$remaining = strlen($this->buffer) - $this->offset;
		return $len === 1 ? $this->buffer[$this->offset++] : substr($this->buffer, ($this->offset += $len) - $len, $len);
	}
	public function put(string $str) : void{
		$this->buffer .= $str;
	}
}

function unconnectedPing($host, $port) : array{  
	$sock = @fsockopen( "udp://" . $host, $port );
	if (!$sock) return [0, 0, 0];
	$pingId = mt_rand(0,1000);
	$buffer = chr(0x01); // UnconnectedPing packet id
	$buffer .= pack("J", $pingId); // Ping Id
	$buffer .= "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78"; // Magic
	if(!@fwrite($sock, $buffer)) return [0, 0, 0];
	$result = fread($sock, 1024);
	if(strlen($result) === 0) return [0,0,0];
	$packet = new Packet();
	$packet->setBuffer($result);
	$pid = ord($packet->get(1));
	$sendPingTime = unpack("J", $packet->get(8))[1];
	$serverId = unpack("J", $packet->get(8))[1];
	$magic = $packet->get(16);
	$serverName = $packet->get(unpack("n", $packet->get(2))[1]);
	return [$sendPingTime, $serverId, $serverName];
}

$ping = unconnectedPing("sg.lbsg.net", 19132);
if($ping[2] === 0){
	print("error");
}else{
	print($ping[2]);
}




