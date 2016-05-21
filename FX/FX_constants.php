<?php

/*********************************************************************
 * This file is part of the release of FX.php.  This PHP class is    *
 * freely available from http://www.iviking.org/                     *
 *                                                                   *
 * The comments herein are designed to be helpful to someone with    *
 * little or no programming experience.  To that end, many of the    *
 * comments may address things will appear obvious to many coders.   *
 * For the most part I'll place my comments at the end of each line. *
 * Feel free to e-mail any comments or questions to FX@iviking.org.  *
 * Please remember that this code is being released as open source   *
 * under The Artistic License of PERL fame:                          *
 * http://www.opensource.org/licenses/artistic-license.html          *
 *********************************************************************/

// these functions are designed to be used with DoFXAction():
// DoFXAction ($currentAction, $returnDataSet = true, $useInnerArray=false, $returnType = 'object')

// the following group of constants are designed to be used as the first parameter for DoFXAction()
define("FX_ACTION_OPEN", '-dbopen');
define("FX_ACTION_CLOSE", '-dbclose');
define("FX_ACTION_DELETE", '-delete');
define("FX_ACTION_DUPLICATE", '-dup');
define("FX_ACTION_EDIT", '-edit');
define("FX_ACTION_FIND", '-find');
define("FX_ACTION_FINDALL", '-findall');
define("FX_ACTION_FINDANY", '-findany');
define("FX_ACTION_NEW", '-new');
define("FX_ACTION_VIEW", '-view');
define("FX_ACTION_DATABASENAMES", '-dbnames');
define("FX_ACTION_LAYOUTNAMES", '-layoutnames');
define("FX_ACTION_SCRIPTNAMES", '-scriptnames');

// the following group of constants are designed to be used as the second parameter for DoFXAction()
define("FX_DATA_RETURNED", true);
define("FX_DATA_UNSENT", false);

// the following group of constants are designed to be used as the third parameter for DoFXAction()
define("FX_ARRAY_PORTALS", true);
define("FX_ARRAY_FIELDS", false);

// the following group of constants are designed to be used as the fourth parameter for DoFXAction()
define("FX_RETURN_OBJECT", 'object');
define("FX_RETURN_FULL", 'full');
define("FX_RETURN_BASIC", 'basic');

?>