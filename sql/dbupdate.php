<#1>
<?php
/**
 * @var $ilDB ilDB
 */
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ),
    'course_ref' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => false
    ),
    'email' => array(
        'type' => 'text',
        'length' => 50,
        'notnull' => false
    ),
    'token' => array(
        'type' => 'text',
        'length' => 100,
        'notnull' => false
    ),
    'deleted' => array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false
    )
);
if (!$ilDB->tableExists('rep_robj_xmsb_token')) {
	$ilDB->createTable("rep_robj_xmsb_token", $fields);
	$ilDB->addPrimaryKey("rep_robj_xmsb_token", array("id"));
	$ilDB->createSequence("rep_robj_xmsb_token");
}

?>
<#2>
<?php
$ilDB->addTableColumn("rep_robj_xmsb_token", "local_role",
    array (
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 2
    ));
?>
<#3>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'course_ref' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	),
	'user_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	),
	'invitation_type' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	)
);
if (!$ilDB->tableExists('rep_robj_xmsb_invt')) {
	$ilDB->createTable("rep_robj_xmsb_invt", $fields);
	$ilDB->addPrimaryKey("rep_robj_xmsb_invt", array("id"));
	$ilDB->createSequence("rep_robj_xmsb_invt");
}

?>
<#4>
<?php
/* DELETED */
?>
<#5>
<?php
/* DELETED */
?>
<#6>
<?php
/**
 * @var $ilDB ilDB
 */

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/class.ilSubscriptionPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/Config/class.msConfig.php');

msConfig::installDB();
msConfig::set('use_email', true);
msConfig::set('system_user', 6);

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/AccountType/class.msAccountType.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/Subscription/class.msSubscription.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/UserStatus/class.msUserStatus.php');
msSubscription::installDB();
if ($ilDB->tableExists('rep_robj_xmsb_token')) {
	$set = $ilDB->query('SELECT * FROM rep_robj_xmsb_token');
	while ($rec = $ilDB->fetchObject($set)) {
		$msSubscription = new msSubscription();
		$msSubscription->setCrsRefId($rec->course_ref);
		$msSubscription->setMatchingString($rec->email);
		$msSubscription->setToken($rec->token);
		$msSubscription->setDeleted($rec->deleted);
		$msSubscription->setRole(msUserStatus::ROLE_MEMBER);
		$msSubscription->setInvitationsSent(1);
		$msSubscription->create();
	}
	$ilDB->renameTable('rep_robj_xmsb_token', 'rep_robj_xmsb_tk_bak');
}
?>
<#7>
<?php
if ($ilDB->tableExists('xunibas_subs_type')) {
	$ilDB->dropTable("xunibas_subs_type");
}
?>
<#8>
<?php
//require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/class.ilSubscriptionPlugin.php');
//$pl = new ilSubscriptionPlugin();
//$pl->updateLanguageFiles();
?>
<#9>
<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/Subscription/class.msSubscription.php');
msSubscription::renameDBField('email', 'matching_string');
msSubscription::removeDBField('matriculation');
msSubscription::updateDB();
?>
<#10>
<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/Config/class.msConfig.php');
msConfig::set(msConfig::ENBL_INV, true);
?>
<#11>
<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/Config/class.msConfig.php');
msConfig::set(msConfig::F_SEND_MAILS, false);
?>
