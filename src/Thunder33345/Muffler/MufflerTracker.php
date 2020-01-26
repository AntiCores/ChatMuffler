<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\Muffler;

use pocketmine\Player;

class MufflerTracker
{
	private $muffled = [];
	private $chatMuffled = -1;

	public function __construct(array $muffled, int $chatMuffled)
	{

		$this->muffled = $muffled;
		foreach($this->muffled as $player => $till){
			if($till === -1) continue;
			if(time() > $till){
				unset ($this->muffled[$player]);
			}
		}
		if($chatMuffled !== -1 AND time() > $chatMuffled) $chatMuffled = 0;
		$this->chatMuffled = $chatMuffled;
	}

	/**
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
	 *
	 * @param bool $asDuration
	 * Makes it treat $till as how many seconds to mute for
	 */

	public function muffle($player, int $till, bool $asDuration = false):void
	{
		$player = $this->convertPlayer($player);

		if($till == -1){
			$this->muffled[$player] = $till;
			return;
		}
		if($till == 0){
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
		if(!isset($this->muffled[$player])) return 0;

		$time = $this->muffled[$player];
		if($time === -1) return -1;

		if(time() > $time){
			unset($this->muffled[$player]);
			return 0;
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
	public function isMuffled($player){ return ($this->getMuffledExpiry($player, true) > 1); }

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
	 * @param bool $asSeconds
	 * Makes it treat $till as time()+seconds to mute for
	 */
	public function muffleChat(int $till, bool $asSeconds = false):void
	{
		if($till == -1){
			$this->chatMuffled = $till;
			return;
		}
		if($till == 0){
			unset($this->chatMuffled);
			return;
		}

		if($asSeconds)
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
		if($expiry <= 0) return $expiry;
		if(time() > $expiry){
			$this->chatMuffled = 0;
			return 0;
		}
		if($asRemaining){
			$expiry = $expiry - time();
		}
		return $expiry;
	}

	public function isChatMuffled():bool{ return ($this->getChatMuffle(true) > 1); }

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
			if($till === -1) continue;
			if(time() > $till){
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
		throw new \InvalidArgumentException(__CLASS__ . "::" . __FUNCTION__ . "() Expects Player OR String but got " . gettype($player) . $class);
	}
}