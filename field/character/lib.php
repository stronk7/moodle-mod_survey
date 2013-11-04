<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * @package    surveyfield
 * @subpackage character
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/survey/locallib.php');

// patterns
define('SURVEYFIELD_CHARACTER_FREEPATTERN'  , 'PATTERN_FREE');
define('SURVEYFIELD_CHARACTER_CUSTOMPATTERN', 'PATTERN_CUSTOM');
define('SURVEYFIELD_CHARACTER_EMAILPATTERN' , 'PATTERN_EMAIL');
define('SURVEYFIELD_CHARACTER_URLPATTERN'   , 'PATTERN_URL');

/*
 * survey_character_text_match_pattern
 * @param $text, $pattern
 * @return
 */
function survey_character_text_match_pattern($text, $pattern) {
    // replace free characters
    $pos = -1;
    while ($pos = strpos($pattern, '*', $pos+1)) {
        $text = substr_replace($text, '*', $pos, 1);
    }

    $regex = array('~[A-Z]~', '~[a-z]~', '~[0-9]~');
    $replacement = array('A', 'a', '0');
    $text = preg_replace($regex, $replacement, $text);

    return ($text == $pattern);
}

/*
 * survey_character_is_valid_url
 * @param $url
 * @return
 */
function survey_character_is_valid_url($url) {
    return (filter_var($url, FILTER_VALIDATE_URL) !== false);
}
