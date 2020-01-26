<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\Muffler;

use pocketmine\Player;

class MufflerTracker
{
	public const unmute = 0;
	public const mute_forever = -1;
	protected $muffled = [];
	protected $chatMuffled = -1;

	public function __construct(array $muffled, int $chatMuffled)
	{

		$this->muffled = $muffled;
		foreach($this->muffled as $player => $till){
			if($till === -1) continue;
			if(time() > $till){
				unset ($this->muffled[$player]);
			}
		}
		if($chatMuffled !== self::mute_forever AND time() > $chatMuffled) $chatMuffled = 0;
		$this->chatMuffled = $chatMuffled;
	}

	/**
	 * Mute/Unmute a player
	 *
	 * @param Player|String $player
	 * Allows for Player or player name, will be auto convert to lowercase
	 *
	 * @param int $till
	 * Till when do you want to mute them?
	 * Specify a EPOCH time in the future to specify when do you their mute to be released
	 * this please note will OVERWRITE previous mute
	 *
	 * Magic numbers:
	 * + -1 will mute them forever
	 * + 0 will release the mute, any number that's in the past will have the same effect, but 0 is preferred to release mute
	 * + < -1 will release the mute to negate undefined behaviour
	 *
	 * @param bool $asDuration
	 * Makes it treat $till as how many seconds to mute for
	 */

	public function muffle($player, int $till, bool $asDuration = false):void
	{
		$player = $this->convertPlayer($player);

		if($till == self::mute_forever){//handle muting forever
			$this->muffled[$player] = self::mute_forever;
			return;
		}

		if($till == self::unmute OR $till < self::mute_forever){//handles unmute and undefined negatives
			unset($this->muffled[$player]);
			return;
		}

		if($asDuration){
			$till = time() + $till;
		}
		$this->muffled[$player] = $till;
	}

	/**
	 * @param $player
	 * Allows for Player or player name, will be auto convert to lowercase
	 *
	 * @param bool $asRemaining
	 * True makes this return how long will the mute expire in seconds
	 *
	 * @return int
	 * When will the mute expires in EPOCH
	 *
	 * 0 means it has expired
	 * -1 means the mute will last forever
	 */
	public function getMuffledExpiry($player, bool $asRemaining = false):int
	{
		$player = $this->convertPlayer($player);
		if(!isset($this->muffled[$player])) return self::unmute;

		$time = $this->muffled[$player];
		if($time === self::mute_forever) return self::mute_forever;

		if($time === self::unmute OR $time < self::mute_forever or time() > $time){
			unset($this->muffled[$player]);
			return self::unmute;
		}
		if($asRemaining){
			$time = $time - time();
		}
		return $time;
	}

	/**
	 * @param $player
	 * Allows for Player or player name, will be auto convert to lowercase
	 *
	 * @return bool
	 * returns true if the player is muted, else false if they aren't muted
	 */
	public function isMuffled($player):bool
	{
		$remaining = $this->getMuffledExpiry($player, true);
		if($remaining == self::unmute OR $remaining < self::mute_forever) return false;
		if($remaining == self::mute_forever) return true;
		return ($remaining >= 1);
	}

	/**
	 * @param int $till
	 * Till when do you want to mute the chat?
	 * Specify a EPOCH time in the future to specify when do the chat mute is to be released
	 * this please note will OVERWRITE previous mute
	 *
	 * Magic numbers:
	 * + -1 will mute them forever
	 * + 0 will release the mute, any number that's in the past will have the same effect, but 0 is preferred to release mute
	 *
	 * @param bool $asDuration
	 * Makes it treat $till as duration of time()+seconds to mute for
	 */
	public function muffleChat(int $till, bool $asDuration = false):void
	{
		if($till == self::mute_forever){
			$this->chatMuffled = $till;
			return;
		}
		if($till == self::unmute OR $till < self::mute_forever){
			unset($this->chatMuffled);
			return;
		}

		if($asDuration)
			$till = time() + $till;

		$this->chatMuffled = $till;
	}

	/**
	 * @param bool $asRemaining
	 *
	 * @return int
	 * Until EPOCH time
	 *
	 * -1 for forever
	 * 0 for no mutes
	 */
	public function getChatMuffle(bool $asRemaining = false):int
	{
		$expiry = $this->chatMuffled;
		if($expiry == self::mute_forever) return self::mute_forever;

		if($expiry == self::unmute OR $expiry < self::mute_forever OR time() > $expiry){
			$this->chatMuffled = self::unmute;
			return self::unmute;
		}

		if($asRemaining){
			$expiry = $expiry - time();
		}
		return $expiry;
	}

	public function isChatMuffled():bool
	{
		$remaining = $this->getChatMuffle(true);
		if($remaining == self::unmute OR $remaining < self::mute_forever) return false;
		if($remaining == self::mute_forever) return true;
		return ($remaining >= 1);
	}

	/**
	 * @param bool $skipCleanup
	 * Skips the cleaning up, as this function was intended as exporting, any entries that are expired will be removed
	 *
	 * @return array
	 * Array of muffled players name lower caps as key, and until EPOCH time as value
	 */
	public function getAllMuffled($skipCleanup = false):array
	{
		if($skipCleanup) return $this->muffled;
		foreach($this->muffled as $player => $till){
			if($till === self::mute_forever) continue;
			if($till === self::unmute OR time() > $till){
				unset ($this->muffled[$player]);
			}
		}
		return $this->muffled;
	}


	/**
	 * @param Player|String $player
	 *
	 * @return string
	 */
	protected function convertPlayer($player):string
	{
		if($player instanceof Player){
			$player = $player->getLowerCaseName();
			return $player;
		}
		if(is_string($player)){
			$player = strtolower($player);
			return $player;
		}
		if(is_object($player))
			$class = " | " . get_class($player); else $class = '';
		throw new \InvalidArgumentException(__CLASS__ . "::" . __FUNCTION__ . "() Expects Player OR String but got " . gettype($player) . ' - ' . $class);
	}
}