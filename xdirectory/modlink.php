<?php
// $Id$
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
//	Hacks provided by: Adam Frick											 //
// 	e-mail: africk69@yahoo.com												 //
//	Purpose: Create a yellow-page like business directory for xoops using 	 //
//	the mylinks module as the foundation.									 //
// ------------------------------------------------------------------------- //

include "header.php";
$myts =& MyTextSanitizer::getInstance(); // MyTextSanitizer object
include_once XOOPS_ROOT_PATH."/class/xoopstree.php";
include_once XOOPS_ROOT_PATH."/class/module.errorhandler.php";
$mytree = new XoopsTree($xoopsDB->prefix("xdir_cat"),"cid","pid");

if (!empty($_POST['submit'])) {
	$eh = new ErrorHandler; //ErrorHandler object
	if (empty($xoopsUser)) {
		redirect_header(XOOPS_URL."/user.php",2,_MD_MUSTREGFIRST);
		exit();
	} else {
		$user = $xoopsUser->getVar('uid');
	}
   	$lid = intval($_POST["lid"]);

	// Check if Title exist
   	if ($_POST["title"]=="") {
       	$eh->show("1001");
   	}

	// Check if URL exist
   	//if ($_POST["url"]=="") {
       	//$eh->show("1016");
   	//}

	// Check if Description exist
   	if ($_POST['description']=="") {
       	$eh->show("1008");
   	}

	$url = $myts->makeTboxData4Save($_POST["url"]);
	$logourl = $myts->makeTboxData4Save($_POST["logourl"]);
	$cid = intval($_POST["cid"]);
	$title = $myts->makeTboxData4Save($_POST["title"]);
	$description = $myts->makeTareaData4Save($_POST["description"]);
	$newid = $xoopsDB->genId($xoopsDB->prefix("xdir_mod")."_requestid_seq");
	$sql = sprintf("INSERT INTO %s (requestid, lid, cid, title, address, address2, city, state, zip, country, phone, fax, email, url, logourl, description, modifysubmitter) VALUES (%u, %u, %u, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %u)", $xoopsDB->prefix("xdir_mod"), $newid, $lid, $cid, $title, $address, $address2, $city, $state, $zip, $country, $phone, $fax, $email, $url, $logourl, $description, $user);
	$xoopsDB->query($sql) or $eh->show("0013");
    $tags = array();
	$tags['MODIFYREPORTS_URL'] = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/admin/index.php?op=listModReq';
    $notification_handler =& xoops_gethandler('notification');
    $notification_handler->triggerEvent('global', 0, 'link_modify', $tags);
	redirect_header("index.php",2,_MD_THANKSFORINFO);
	exit();
} else {
	$lid = intval($_GET['lid']);
	if (empty($xoopsUser)) {
		redirect_header(XOOPS_URL."/user.php",2,_MD_MUSTREGFIRST);
		exit();
	}
	$xoopsOption['template_main'] = 'xdir_modlink.html';
	include XOOPS_ROOT_PATH."/header.php";
	$result = $xoopsDB->query("select cid, title, address, address2, city, state, zip, country, phone, fax, email, url, logourl from ".$xoopsDB->prefix("xdir_links")." where lid=$lid and status>0");
	$xoopsTpl->assign('lang_requestmod', _MD_REQUESTMOD);
	list($cid, $title, $address, $address2, $city, $state, $zip, $country, $phone, $fax, $email, $url, $logourl) = $xoopsDB->fetchRow($result);
	$result2 = $xoopsDB->query("SELECT description FROM ".$xoopsDB->prefix("xdir_text")." WHERE lid=$lid");
	list($description)=$xoopsDB->fetchRow($result2);
	$xoopsTpl->assign('link', array('id' => $lid, 'rating' => number_format($rating, 2), 'title' => $myts->htmlSpecialChars($title), 'address' => $myts->htmlSpecialChars($address), 'address2' => $myts->htmlSpecialChars($address2), 'city' => $myts->htmlSpecialChars($city), 'state' => $myts->htmlSpecialChars($state), 'zip' => $myts->htmlSpecialChars($zip), 'country' => $myts->htmlSpecialChars($country), 'phone' => $myts->htmlSpecialChars($phone), 'fax' => $myts->htmlSpecialChars($fax), 'email' => $myts->htmlSpecialChars($email), 'url' => $myts->htmlSpecialChars($url), '$logourl' => $myts->htmlSpecialChars($logourl), 'updated' => formatTimestamp($time,"m"), 'description' => $myts->htmlSpecialChars($description), 'adminlink' => $adminlink, 'hits' => $hits, 'votes' => $votestring));
	$xoopsTpl->assign('lang_linkid', _MD_LINKID);
	$xoopsTpl->assign('lang_sitetitle', _MD_SITETITLE);
	$xoopsTpl->assign('lang_siteaddress', _MD_BUSADDRESS);
	$xoopsTpl->assign('lang_siteaddress2', _MD_BUSADDRESS2);	
	$xoopsTpl->assign('lang_sitecity', _MD_BUSCITY);
	$xoopsTpl->assign('lang_sitestate', _MD_BUSSTATE);
	$xoopsTpl->assign('lang_sitezip', _MD_BUSZIP);
	$xoopsTpl->assign('lang_country', _MD_BUSCOUNTRY);
	$xoopsTpl->assign('lang_sitephone', _MD_BUSPHONE);
	$xoopsTpl->assign('lang_fax', _MD_BUSFAX);
	$xoopsTpl->assign('lang_email', _MD_BUSEMAIL);
	$xoopsTpl->assign('lang_siteurl', _MD_SITEURL);
	$xoopsTpl->assign('lang_category', _MD_CATEGORYC);
	ob_start();
	$mytree->makeMySelBox("title", "title", $cid);
	$selbox = ob_get_contents();
	ob_end_clean();
	$xoopsTpl->assign('category_selbox', $selbox);
	$xoopsTpl->assign('lang_description', _MD_DESCRIPTIONC);
	$xoopsTpl->assign('lang_sendrequest', _MD_SENDREQUEST);
	$xoopsTpl->assign('lang_cancel', _CANCEL);
	include XOOPS_ROOT_PATH.'/footer.php';
}
?>
