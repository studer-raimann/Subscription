<?php

$obj = new msTriage();
if (!$_REQUEST['cmd']) {
	$obj->start();
} else {
	$obj->performCommand($_REQUEST['cmd']);
}

/**
 * msTriage
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 1.0
 */
class msTriage {

	/**
	 * @var ilSubscriptionPlugin
	 */
	protected $pl;
	/**
	 * @var msSubscription
	 */
	protected $subscription;
	/**
	 * @var ilObjUser
	 */
	protected $usr;
	/**
	 * @var ilDB
	 */
	protected $db;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var string
	 */
	protected $token;


	public function __construct() {
		$this->initILIAS();
		global $DIC;
		$this->db = $DIC->database();
		$this->tpl = $DIC->ui()->mainTemplate();
		$this->usr = $DIC->user();
		$this->ctrl = $DIC->ctrl();
		$this->pl = ilSubscriptionPlugin::getInstance();

		$this->token = $_REQUEST['token'];
		$this->subscription = msSubscription::getInstanceByToken($this->token);
		$this->ctrl->setParameterByClass(ilTokenRegistrationGUI::class, 'token', $_REQUEST['token']);
	}


	public function initILIAS() {
		chdir(substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], '/Customizing')));

		if (!$this->ctrl instanceof ilCtrl) {
			//			echo "!!!";
			//			exit;
			require_once("Services/Init/classes/class.ilInitialisation.php");
			$_POST['username'] = 'anonymous';
			$_POST['password'] = 'anonymous';
			ilInitialisation::initILIAS();
		}

		//		require_once('include/inc.ilias_version.php');
		//		require_once('Services/Component/classes/class.ilComponent.php');
		//		if (ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '4.2.999')) {
		//			require_once('./Services/Context/classes/class.ilContext.php');
		//			ilContext::init(ilContext::CONTEXT_WEB);
		//			require_once('./Services/Authentication/classes/class.ilAuthFactory.php');
		//			ilAuthFactory::setContext(ilAuthFactory::CONTEXT_WEB);
		//			//$_COOKIE['ilClientId'] = $_SERVER['argv'][3];
		//			$_POST['username'] = 'anonymous';
		//			$_POST['password'] = 'anonymous';
		//			require_once('./include/inc.header.php');
		//		} else {
		//			$_POST['username'] = 'anonymous';
		//			$_POST['password'] = 'anonymous';
		//			require_once('./include/inc.header.php');
		//		}
		require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/Subscription/class.msSubscription.php');
		require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/AccountType/class.msAccountType.php');
		require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		require_once('./Services/Object/classes/class.ilObject2.php');
		require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/TokenRegistration/class.ilTokenRegistrationGUI.php');
		require_once 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/Triage/class.subscrTriageGUI.php';
	}


	/**
	 * @param $cmd
	 */
	public function performCommand($cmd) {

		if (is_array($cmd)) {
			$cmds = array_keys($cmd);
			$cmd = $cmds[0];
		}

		switch ($cmd) {
			case subscrTriageGUI::CMD_HAS_LOGIN:
			case subscrTriageGUI::CMD_HAS_NO_LOGIN:
				$this->{$cmd}();
				break;
			default:
				break;
		}
	}


	public function hasLogin() {
		$this->redirectToLogin();
	}


	public function hasNoLogin() {
		$this->determineLogin();
	}


	public function start() {
		if (msConfig::getValueByKey('ask_for_login')) {
			$this->showLoginDecision();
		} else {
			$this->determineLogin();
		}

		return;
	}


	protected function showLoginDecision() {
		$this->tpl->getStandardTemplate();
		$this->tpl->setVariable('BASE', msConfig::getPath());
		$this->tpl->setTitle($this->pl->txt('triage_title'));

		$de = new ilConfirmationGUI();
		$de->setFormAction($this->pl->getDirectory() . '/classes/triage.php');
		//$this->pl->txt('subscription_type_' . $this->subscription->getSubscriptionType()) . ': '
		//.
		$str = $this->subscription->getMatchingString() . ', Ziel: '
			. ilObject2::_lookupTitle(ilObject2::_lookupObjId($this->subscription->getObjRefId()));
		$de->addItem('token', $this->token, $str);

		$de->setHeaderText($this->pl->txt('qst_already_account'));
		$de->setConfirm($this->pl->txt('main_yes'), subscrTriageGUI::CMD_HAS_LOGIN);
		$de->setCancel($this->pl->txt('main_no'), subscrTriageGUI::CMD_HAS_NO_LOGIN);

		$this->tpl->setContent($de->getHTML());
		$this->tpl->show();
	}


	public function determineLogin() {
		if (msConfig::checkShibboleth() AND $this->subscription->getAccountType() == msAccountType::TYPE_SHIBBOLETH) {
			$this->redirectToLogin();
		} else {
			if (msConfig::getValueByKey('allow_registration')) {
				$this->redirectToTokenRegistrationGUI();
			} else {
				$this->redirectToLogin();
			}
		}

		return;
	}


	public function redirectToLogin() {
		$this->setSubscriptionToDeleted();
		$link = msConfig::getPath() . 'goto.php?target=crs_' . $this->subscription->getObjRefId() . '_rcode' . $this->getRegistrationCode();

		ilUtil::redirect($link);
	}


	/**
	 * @return object
	 */
	protected function getRegistrationCode() {
		/**
		 * @var $crs ilObjCourse
		 */
		$crs = ilObjectFactory::getInstanceByRefId($this->subscription->getObjRefId());
		if (!$crs->isRegistrationAccessCodeEnabled()) {
			$crs->enableRegistrationAccessCode(1);
			$crs->update();

			return $crs;
		}

		return $crs->getRegistrationAccessCode();
	}


	protected function setSubscriptionToDeleted() {
		$this->subscription->setDeleted(true);
		$this->subscription->update();
	}


	protected function redirectToTokenRegistrationGUI() {
		ilUtil::redirect('/goto.php?target=subscr_' . $_REQUEST['token']);
	}
}
