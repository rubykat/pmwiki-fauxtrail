<?php if (!defined('PmWiki')) exit();
/*
 * Copyright 2007 Kathryn Andersen
 * 
 * This program is free software; you can redistribute it and/or modify it
 * under the Gnu Public Licence or the Artistic Licence.
 */ 

/** \file fauxtrail.php
 * \brief pagelist formats to create "fake" trails
 *
 * See Also: http://www.pmwiki.org/wiki/Cookbook/FauxTrail
 *
 * To activate this script, copy it into the cookbook/ directory, then add
 * the following line to your local/config.php:
 *
 *      include_once("$FarmD/cookbook/fauxtrail.php");
 * 
*/

$RecipeInfo['FauxTrail']['Version'] = '0.01';
$RecipeInfo['FauxTrail']['Date'] = '2007-06-14';

/*======================================================================
 * pagelist search patterns
 */
# like 'normal' but includes self
$SearchPatterns['content']['recent'] = '!\.(All)?Recent(Changes|Uploads)$!';
$SearchPatterns['content']['group'] = '!\.Group(Print)?(Header|Footer|Attributes)$!';
$SearchPatterns['content']['side'] = '!\.SideBar$!';

/*======================================================================
 * pagelist formats
 */
$FPLFormatOpt['fauxtrail']['fn'] = 'FPLFauxTrail';

$FPLFormatOpt['grouptrail']['fn'] = 'FPLFauxTrail';
$FPLFormatOpt['grouptrail']['trailpage'] = '{$Group}';
$FPLFormatOpt['grouptrail']['label'] = '{$GroupTitle}';

$FPLFormatOpt['trailtrail']['fn'] = 'FPLTrailTrail';

/*======================================================================
 * Functions
 */

// Take a pagelist, and make it look like trail markup
function FPLFauxTrail($pagename, &$matches, $opt) {
    $matches = MakePageList($pagename, $opt, 0);

    // make the matches array into a number-indexed array
    $matches = array_values($matches);

    // check for a minimum count
    if (@$opt['min']) {
    	if (count($matches) < $opt['min']) {
		return '';
	}
    }

    $trailpage = '{$Group}';
    if (@$opt['trailpage']) {
	$trailpage = $opt['trailpage'];
    }
    $label = '{$Title}';
    if (@$opt['label']) {
	$label = $opt['label'];
    }
    $index_itemfmt = "<a href='{\$PageUrl}'>$label</a>";

    $tp_val = FmtPageName($trailpage, $pagename);
    $tp_page = MakePageName($pagename, $tp_val);

    $itemfmt = "<a href='{\$PageUrl}'>{\$Title}</a>";
    $prev_link = '';
    $next_link = '';
    $out = '';
    for($i=0; $i < count($matches); $i++)
    {
	if ($matches[$i] == $pagename)
	{
	    if ($i > 0)
	    {
	    	$prev_page = $matches[$i-1];
		$prev_link = FmtPageName($itemfmt, $prev_page);
	    }
	    $trailindex = FmtPageName($index_itemfmt, $tp_page);
	    if ($i + 1 < count($matches))
	    {
	    	$next_page = $matches[$i+1];
		$next_link = FmtPageName($itemfmt, $next_page);
	    }
	    $out = "<p>&lt;&lt; $prev_link | $trailindex | $next_link &gt;&gt;</p>";
	    break;
	}
    }

    return $out;
}

// trailtrail
// Find all pages which link to this page;
// for each page which was found
// take it as a trail page, and create a FauxTrail for it.
function FPLTrailTrail($pagename, &$matches, $opt) {
    if (!@$opt['link']) {
    	$opt['link'] = $pagename;
    }
    $matches = MakePageList($pagename, $opt, 0);
    // deal with count
    if (@$opt['count']) {
	list($r0, $r1) = CalcRange($opt['count'], count($matches));
	if ($r1 < $r0)
	    $matches = array_reverse(array_slice($matches, $r1-1, $r0-$r1+1));
	else
	    $matches = array_slice($matches, $r0-1, $r1-$r0+1);
    }
    if (!@$opt['label']) {
    	$opt['label'] = '{$Title}';
    }

    $topt = array();
    $topt['fmt'] = 'fauxtrail';
    $topt['label'] = $opt['label'];

    $out = '';
    $tmatches = array();
    foreach($matches as $m) {
    	$topt['trail'] = $m;
	$topt['trailpage'] = $m;
	$out .= FPLFauxTrail($pagename, $tmatches, $topt);
    }

    return $out;
}

