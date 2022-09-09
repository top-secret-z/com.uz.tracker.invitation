<?php
namespace wcf\system\event\listener;
use wcf\data\package\PackageCache;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\WCF;

/**
 * Listen to Invitation adding.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.invitation
 */
class TrackerInvitationListener implements IParameterizedEventListener {
	/**
	 * tracker and link
	 */
	protected $tracker = null;
	protected $link = '';
	
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_TRACKER) return;
		
		// only if user is to be tracked
		$user = WCF::getUser();
		if (!$user->userID || !$user->isTracked || WCF::getSession()->getPermission('mod.tracking.noTracking')) return;
		
		// only if trackers
		$trackers = TrackerCacheBuilder::getInstance()->getData();
		if (!isset($trackers[$user->userID])) return;
		
		$this->tracker = $trackers[$user->userID];
		if (!$this->tracker->usersInvitation) return;
		
		$this->link = $user->getLink();
		$this->store('wcf.uztracker.description.invitation', 'wcf.uztracker.type.users');
	}
	
	/**
	 * store log entry
	 */
	protected function store ($description, $type, $name = '', $content = '') {
		$packageID = PackageCache::getInstance()->getPackageID('com.uz.tracker.invitation');
		TrackerLogEditor::create(array(
				'description' => $description,
				'link' => $this->link,
				'name' => $name,
				'trackerID' => $this->tracker->trackerID,
				'type' => $type,
				'packageID' => $packageID,
				'content' => $content
		));
	}
}
