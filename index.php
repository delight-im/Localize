<?php

// CONFIGURATION (PLASE ADJUST THIS TO YOUR SETTINGS) BEGIN
define('PROJECT_TITLE', 'Example'); // short name of your translation platform
define('PAGE_TITLE', 'Collaborative Android Project Translation'); // HTML page title for your translation platform
define('PAGE_DESCRIPTION', 'Free web tool for collaborative translation of Android projects.'); // meta description for your translation platform
define('PAGE_KEYWORDS', 'translation,localization,crowdsourcing,software,android,apps,internationalization,language'); // meta keywords for your translation platform
define('BASE_PATH', 'http://example.org/'); // path that is used for absolute URLs (must end with a slash)
define('MAX_FILESIZE', 1024*1024*1.5); // maximum size for file uploads (XML files)
define('TMP_DIR', '/var/tmp');
define('OUTPUT_DIR', '/var/www/vhosts/example.org/httpdocs/_output');
define('HASH_SALT_1', '839453845'); // first arbitrary value that is used as a salt value for hashing (may not be changed later)
define('HASH_SALT_2', '123983589'); // second arbitrary value that is used as a salt value for hashing (may not be changed later)
define('LANDING_HTML_DEFAULT', "''Welcome to our translation pages!''\n\n\nWe would be glad if you could help us to translate this application to new languages and improve existing translations. Thank you so much!\n\n''Get started:''\n\n* [".BASE_PATH." ".PROJECT_TITLE."]\n* [".BASE_PATH."_demo Demo]\n\nIf you have any questions, feel free to ask us."); // default text for custom landing pages that is displayed to the user as an example
define('SUPPORT_EMAIL', 'info@example.org'); // email address that is displayed so that users may contact you for help
define('FOOTER_HTML', '<p><a href="'.BASE_PATH.'">'.PROJECT_TITLE.'</a> &middot; <a href="http://developer.android.com/guide/topics/resources/localization.html">Android&trade;</a></p>'); // HTML text that is displayed at the bottom of your translation platform page
define('DEMO_USER_ID', 1); // requests from this user ID will not result in any real actions and can be used to showcase the platform
date_default_timezone_set('Europe/Berlin'); // your default timezone
include '/var/www/vhosts/example.org/database.php'; // file that establishes MySQL database connection
// CONFIGURATION (PLASE ADJUST THIS TO YOUR SETTINGS) END

// DEFINITIONS (DO NOT CHANGE) BEGIN
header('Expires: Mon, 24 Mar 2008 00:00:00 GMT'); // to prevent caching
header('Cache-Control: no-cache'); // to prevent caching
mb_internal_encoding('utf-8');
define('LABEL_APPLY_INVITATION', 'Request invitation');
define('STATUS_VISIBILITY_SUCCESS', 1);
define('STATUS_VISIBILITY_SIGNIN', 2);
define('STATUS_VISIBILITY_APPLY', 3);
define('STATUS_VISIBILITY_APPLICATION_SENT', 4);
define('STATUS_VISIBILITY_REJECTED', 5);
// DEFINITIONS (DO NOT CHANGE) END

/**
 * Decodes a phrase from XML-encoded input to plain text output
 * @param string $text XML-encoded input
 * @return string plain text output
 */
function phraseDecode($text) {
	$text = str_replace('\n', "\n", $text);
	$text = str_replace('\\\'', '\'', $text);
	$text = str_replace('\\"', '"', $text);
	$text = str_replace('&#8230;', '...', $text);
	$text = str_replace('&#38;', '&', $text);
	return $text;
}

/**
 * Encodes plain text input into XML-encoded output
 * @param string $text plain text input
 * @return string XML-encoded output
 */
function phraseEncode($text) {
	$text = str_replace("\n", '\n', $text);
	$text = str_replace('\'', '\\\'', $text);
	$text = str_replace('"', '\\"', $text);
	$text = str_replace('&', '&#38;', $text);
	$text = str_replace('...', '&#8230;', $text);
	return $text;
}

/**
 * Compresses a given folder to a ZIP file
 * @param string $source the source folder that is to be zipped
 * @param string $destination the destination file that the ZIP is to be written to
 * @return boolean whether this process was successful or not
 */
function zipFolder($source, $destination) {
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }
    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }
    $source = str_replace('\\', '/', realpath($source));
    if (is_dir($source) === true) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file) {
            $file = str_replace('\\', '/', $file);
            if (in_array(substr($file, strrpos($file, '/')+1), array('.', '..'))) { // ignore '.' and '..' files
                continue;
            }
            $file = realpath($file);
            if (is_dir($file) === true) {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            }
            else if (is_file($file) === true) {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    }
    else if (is_file($source) === true) {
        $zip->addFromString(basename($source), file_get_contents($source));
    }
    return $zip->close();
}

/**
 * Gives the readable description for a given visibility level
 * @param string $visibility identifier that defines a visibility level
 * @param boolean $explanationOnly whether only an explanation is to be returned or abbreviation tags as well
 * @return string the readable description for this visibility level
 * @throws Exception
 */
function getVisibilityTag($visibility, $explanationOnly = FALSE) {
	switch ($visibility) {
		case 'public':
			$visKey = 'public';
			$visLabel = 'visible to the public';
			break;
		case 'protected':
			$visKey = 'signed-in users';
			$visLabel = 'visible to signed-in users only';
			break;
		case 'private':
			$visKey = 'invite-only';
			$visLabel = 'visible to invited users only';
			break;
		default:
			throw new Exception('Unknown visibility level: '.$visibility);
	}
	if ($explanationOnly) {
		return $visLabel;
	}
	else {
		return '<abbr title="'.$visLabel.'">'.$visKey.'</abbr>';
	}
}

/**
 * Parses markup that may be used for custom landing pages
 * @param string $text User-defined landing page text that may contain custom markup
 * @return string HTML source text for landing page
 */
function parseMarkup($text) {
    $patterns = array(
        "/\'\'(.+?)\'\'/s", // bold text
        "/\[((news|(ht|f)tp(s?)|irc):\/\/(.+?))( (.+))\]/i", // URLs with name
        "/\[((news|(ht|f)tp(s?)|irc):\/\/(.+?))\]/i", // URLs without name
        "/[\n\r]?#.+([\n|\r]#.+)+/", // ordered lists
        "/[\n\r]?\*.+([\n|\r]\*.+)+/", // unordered lists
        "/^[#\*]+ *(.+)$/m", // list items
        "/^[^><\n]+$/m" // newlines
    );
    $replacements = array(
        "<strong>$1</strong>",
        "<a href=\"$1\">$7</a>",
        "<a href=\"$1\">$1</a>",
        "</p><ol class=\"inline\">$0</ol><p>",
        "</p><ul class=\"inline\">$0</ul><p>",
        "<li>$1</li>",
        "$0<br/>"
    );
    return preg_replace($patterns, $replacements, $text);
}
/**
 * Encodes a given string to its SHA-256 hash using two salt values
 * @param string $text the input text
 * @return string its SHA-256 hash
 */
function getPasswordHash($text) {
	return hash('sha256', HASH_SALT_1.$text.HASH_SALT_2);
}

/**
 * Encodes a name for use in URLs (without becoming unreadable as with urlencode())
 * @param string $name the name to be encoded
 * @return string the encoded form of the name for use in URLs
 */
function cleanName($name) {
    $name = preg_replace('/[^a-z0-9_]+/i', '_', $name);
    $name = preg_replace('/(_)+$/i', '', $name);
    $name = preg_replace('/^(_)+/i', '', $name);
	return mb_strtolower($name);
}

/**
 * intval() equivalent for larger numbers
 * @param mixed $value input to be validated as a large integer
 * @return int valid large integer or 0
 */
function bigintval($value) {
    $value = trim($value);
    if (ctype_digit($value)) {
    	return $value;
    }
    $value = preg_replace("/[^0-9](.*)$/", '', $value);
    if (ctype_digit($value)) {
    	return $value;
    }
    return 0;
}

/**
 * Displays a login form for this translation platform
 * @param string $returnURL URL to return after user has been logged in
 * @param string $username optional username to prefill the form with
 * @param string $password optional password to prefill the form with
 * @return string HTML of the login form
 */
function showLoginForm($returnURL, $username = '', $password = '') {
	$out = '<form action="/" method="post" accept-charset="utf-8">';
	$out .= '<fieldset><label for="lUsername">Username</label><input type="text" id="lUsername" name="lUsername" value="'.htmlspecialchars($username).'" /></fieldset>';
	$out .= '<fieldset><label for="lPassword">Password</label><input type="password" id="lPassword" name="lPassword" value="'.htmlspecialchars($password).'" /></fieldset>';
	$out .= '<fieldset><input class="form_email" type="text" name="email" /><input type="hidden" name="returnURL" value="'.htmlspecialchars($returnURL).'" /><input type="submit" value="Sign in" /></fieldset>';
	$out .= '</form>';
	return $out;
}

/**
 * Returns a visibility response code that describes whether the given project is visible for the current user
 * @param string $visibility visibility level of the given project
 * @param int $owner user ID of the project owner
 * @param int $project_id project ID to check visibility for
 * @return int visibility response code
 */
function isProjectVisible($visibility, $owner, $project_id) {
	if ($visibility == 'public' || $owner == $_SESSION['userID']) {
		return STATUS_VISIBILITY_SUCCESS;
	}
	else {
		if ($visibility == 'protected') {
			if ($_SESSION['userID'] == -1) {
				return STATUS_VISIBILITY_SIGNIN;
			}
			else {
				return STATUS_VISIBILITY_SUCCESS;
			}
		}
		else {
			$sql1 = "SELECT approved FROM invitations WHERE projectID = ".intval($project_id)." AND userID = ".intval($_SESSION['userID']);
			$sql2 = mysql_query($sql1);
			if (mysql_num_rows($sql2) > 0) {
				$sql3 = mysql_result($sql2, 0);
				if ($sql3 == 1) {
					return STATUS_VISIBILITY_SUCCESS;
				}
				elseif ($sql3 == -1) {
					return STATUS_VISIBILITY_REJECTED;
				}
				else {
					return STATUS_VISIBILITY_APPLICATION_SENT;
				}
			}
			else {
				return STATUS_VISIBILITY_APPLY;
			}
		}
	}
}

/**
 * Displays a form that users may use to apply for projects
 * @param int $project_id the ID of the project the user wants to apply for
 * @param string $project_name the name of the project to apply for
 * @return string HTML of the application form
 */
function showInviteApplicationForm($project_id, $project_name) {
	$out = '<form action="/" method="post" accept-charset="utf-8">';
	$out .= '<fieldset><label for="apply_projectName">Project</label><input class="read_only" type="text" id="apply_project" name="apply_project" value="'.htmlspecialchars($project_name).'" readonly="readonly" /><input class="form_email" type="text" name="email" /></fieldset>';
	$out .= '<fieldset><input type="hidden" name="apply_projectID" value="'.id2short($project_id).'" /><input type="submit" name="apply_action" value="'.LABEL_APPLY_INVITATION.'" /><input type="submit" name="apply_action" value="Cancel" /></fieldset>';
	$out .= '</form>';
	return $out;
}

/**
 * Encodes a decimal integer ID to a shorter ID with another base
 * @param int $old_number the original decimal ID
 * @return string the short base-converted ID
 */
function id2short($old_number) {
	$alphabet = '23456789bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ';
	// no 0, 1, a, e, i, o, u in alphabet to avoid offensive words (which need vowels)
	$new_number = '';
	while ($old_number > 0) {
		$rest = $old_number%33;
		if ($rest >= 33) { return FALSE; }
		$new_number .= $alphabet[$rest];
		$old_number = floor($old_number/33);
	}
	$new_number = strrev($new_number);
	return $new_number;
}

/**
 * Decodes a short ID (with another base) back to the original decimal integer ID
 * @param string $new_number the short base-converted ID
 * @return int the original decimal ID
 */
function secure2id($new_number) {
	$alphabet = '23456789bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ';
	// no 0, 1, a, e, i, o, u in alphabet to avoid offensive words (which need vowels)
	$old_number = 0;
	$new_number = strrev($new_number);
	$len = strlen($new_number);
	$n = 0;
	$base = 1;
	while($n < $len) {
		$c = $new_number[$n];
		$index = strpos($alphabet, $c);
		if ($index === FALSE) { return FALSE; }
		$old_number += $base*$index;
		$base *= 33;
		$n++;
	}
	return $old_number;
}

function phraseContainsProblem($text) {
	$paragraphs = explode("\n", $text);
	foreach ($paragraphs as $paragraph) {
		if (mb_strlen($paragraph) > 400) {
			return TRUE;
		}
	}
	$words = preg_split('/(\s)+/i', $text);
	foreach ($words as $word) {
		if (mb_strlen($word) > 16) {
			return TRUE;
		}
	}
	return FALSE;
}

/**
	Paul's Simple Diff Algorithm v 0.1
	(C) Paul Butler 2007 <http://www.paulbutler.org/>
	May be used and distributed under the zlib/libpng license.
	
	This code is intended for learning purposes; it was written with short
	code taking priority over performance. It could be used in a practical
	application, but there are a few ways it could be optimized.
	
	Given two arrays, the function diff will return an array of the changes.
	I won't describe the format of the array, but it will be obvious
	if you use print_r() on the result of a diff on some test data.
	
	htmlDiff is a wrapper for the diff command, it takes two strings and
	returns the differences in HTML. The tags used are <ins> and <del>,
	which can easily be styled with CSS.
*/
// ALTERED SOURCE CODE BELOW
function diff($old, $new){
    $matrix = array();
	$maxlen = 0;
	foreach ($old as $oindex => $ovalue){
		$nkeys = array_keys($new, $ovalue);
		foreach($nkeys as $nindex){
			$matrix[$oindex][$nindex] = isset($matrix[$oindex-1][$nindex-1]) ? $matrix[$oindex-1][$nindex-1] + 1 : 1;
			if ($matrix[$oindex][$nindex] > $maxlen){
				$maxlen = $matrix[$oindex][$nindex];
				$omax = $oindex+1 - $maxlen;
				$nmax = $nindex+1 - $maxlen;
			}
		}	
	}
	if ($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
	return array_merge(
		diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
		array_slice($new, $nmax, $maxlen),
		diff(array_slice($old, $omax+$maxlen), array_slice($new, $nmax+$maxlen)));
}
// ALTERED SOURCE CODE BELOW
function htmlDiff($old, $new){
	if ($old == $new) {
		return '<span style="color:#999;">-- no changes --</span>';
	}
	$ret = '';
	$diff = diff(explode(' ', $old), explode(' ', $new));
	foreach($diff as $k){
		if(is_array($k)) {
			$ret .= (!empty($k['d']) ? '<span style="color:#666; text-decoration:line-through;">'.implode(' ',$k['d']).'</span> ' : '').(!empty($k['i']) ? '<span style="color:#f00; font-weight:bold;">'.implode(' ',$k['i']).'</span> ' : '');
        }
		else {
            $ret .= $k . ' ';
        }
	}
	return $ret;
}

/**
 * Logs the current user out
 */
function logout() {
	$_SESSION['userID'] = -1;
	session_destroy();
	session_unset();
}

/**
 * Returns whether the given language is a right-to-left language or left-to-right (otherwise)
 * @param string $language_key the identifier for the language to check
 * @return boolean whether the given language is RTL
 */
function isRTL($language_key) {
    return $language_key == 'values-ar' || $language_key == 'values-iw' || $language_key == 'values-fa';
}

$languages = array(
	'values' => 'English (English)',
	'values-af' => 'Afrikaans (Afrikaans)',
	'values-am' => 'Amharic (አማርኛ)', 
	'values-ar' => 'Arabic (العربية)',
	'values-az' => 'Azerbaijani (Azərbaycan)',
	'values-ba' => 'Bashkir (Башҡортса)',
	'values-be' => 'Belarusian (беларуская мова)',
	'values-bg' => 'Bulgarian (български)',
	'values-bn' => 'Bengali (বাংলা)',
	'values-br' => 'Breton (Brezhoneg)',
	'values-bs' => 'Bosnian (Bosanski)',
	'values-ca' => 'Catalan (Català)',
	'values-cs' => 'Czech (Česky)',
	'values-cv' => 'Chuvash (Чӑвашла)',
	'values-cy' => 'Welsh (Cymraeg)',
	'values-da' => 'Danish (Dansk)',
	'values-de' => 'German (Deutsch)',
	'values-el' => 'Greek (ελληνικά)',
	'values-es' => 'Spanish (Español)',
	'values-et' => 'Estonian (Eesti)',
	'values-eu' => 'Basque (Euskara)',
    'values-fa' => 'Persian (فارسی)',
	'values-fi' => 'Finnish (Suomi)',
	'values-fr' => 'French (Français)',
	'values-fy' => 'Western Frisian (Frysk)',
	'values-ga' => 'Irish (Gaeilge)',
	'values-gl' => 'Galician (Galego)',
	'values-gu' => 'Gujarati (ગુજરાતી)',
	'values-hi' => 'Hindi (हिन्दी)',
	'values-ht' => 'Haitian (Kreyòl Ayisyen)',
	'values-hr' => 'Croatian (Hrvatski)',
	'values-hu' => 'Hungarian (Magyar)',
	'values-hy' => 'Armenian (Հայերեն)',
	'values-id' => 'Indonesian (Bahasa Indonesia)',
	'values-is' => 'Icelandic (Íslenska)',
	'values-it' => 'Italian (Italiano)',
	'values-iw' => 'Hebrew (עברית)',
	'values-ja' => 'Japanese (日本語)',
	'values-jv' => 'Javanese (Basa Jawa)',
	'values-ka' => 'Georgian (ქართული)',
	'values-kn' => 'Kannada (ಕನ್ನಡ )',
	'values-kk' => 'Kazakh (Қазақ тілі)',
	'values-ko' => 'Korean (한국어)',
	'values-ku' => 'Kurdish (Kurdî)',
	'values-ky' => 'Kirghiz (Кыргызча)',
	'values-lb' => 'Luxembourgish (Lëtzebuergesch)',
	'values-lt' => 'Lithuanian (Lietuvių)',
	'values-lv' => 'Latvian (Latviešu)',
	'values-mg' => 'Malagasy (Malagasy)',
	'values-mk' => 'Macedonian (Македонски)',
	'values-ml' => 'Malayalam (മലയാളം)',
	'values-mr' => 'Marathi (मराठी)',
	'values-ms' => 'Malay (Bahasa Melayu)',
	'values-ne' => 'Nepali (नेपाली)',
	'values-nb' => 'Norwegian Bokmål (Norsk bokmål)',
	'values-nl' => 'Dutch (Nederlands)',
	'values-nn' => 'Norwegian Nynorsk (Norsk nynorsk)',
	'values-oc' => 'Occitan (Occitan)',
	'values-pl' => 'Polish (Polski)',
	'values-pt-rBR' => 'Portuguese for Brazil (Português)',
	'values-pt-rPT' => 'Portuguese for Portugal (Português)',
	'values-ro' => 'Romanian (Română)',
	'values-ru' => 'Russian (Русский)',
	'values-sk' => 'Slovak (Slovenčina)',
	'values-sl' => 'Slovene (Slovenščina)',
	'values-sq' => 'Albanian (Shqip)',
	'values-sr' => 'Serbian (Српски)',
	'values-su' => 'Sundanese (Basa Sunda)',
	'values-sv' => 'Swedish (Svenska)',
	'values-sw' => 'Swahili (Kiswahili)',
	'values-te' => 'Telugu (తెలుగు )',
	'values-tg' => 'Tajik (Тоҷикӣ)',
	'values-th' => 'Thai (ไทย)',
	'values-tl' => 'Tagalog (Tagalog)',
	'values-tr' => 'Turkish (Türkçe)',
	'values-tt' => 'Tatar (Татарча)',
	'values-uk' => 'Ukrainian (Українська)',
	'values-uz' => 'Uzbek (Oʻzbekcha)',
	'values-vi' => 'Vietnamese (Tiếng Việt)',
	'values-wa' => 'Walloon (Walon)',
	'values-yo' => 'Yoruba (Yorùbá)',
    'values-zh-rCN' => 'Chinese Simplified (中文)',
    'values-zh-rTW' => 'Chinese Traditional (中文)'
);
asort($languages);

class Translation {
	private $id = 0;
	private $ident_code = '';
	private $position = '';
	private $type = '';
	private $phrase = '';
	private $enabled = 1;
	public function __construct($id, $ic, $po, $ty, $ph, $en) {
		$this->id = intval($id);
		$this->ident_code = $ic;
		$this->position = $po;
		$this->type = $ty;
		$this->phrase = $ph;
		$this->enabled = intval($en);
	}
	public static function createKey($ident_code, $position, $type) {
		return hash('sha256', $ident_code.'#'.$position.'#'.$type);
	}
	public function getID() {
		return $this->id;
	}
	public function isEnabled() {
		return $this->enabled == 1;
	}
	public function getIdentCode() {
		return $this->ident_code;
	}
	public function getPosition() {
		return $this->position;
	}
	public function getType() {
		return $this->type;
	}
	public function getPhrase() {
		return $this->phrase;
	}
	public function getKey() {
		return self::createKey($this->ident_code, $this->position, $this->type);
	}
}
// DECLARATIONS END

// SESSION HANDLING AND SECURITY BEGIN
@session_start();
if (!isset($_SESSION['userID'])) {
	$_SESSION['userID'] = -1;
}
else {
	$_SESSION['userID'] = intval($_SESSION['userID']);
}
if (isset($_SESSION['lastActivity']) && (time()-$_SESSION['lastActivity'] > 1800)) { // last request was more than 30 minutes ago
	logout(); // log the user out by cleaning all session data
}
$_SESSION['lastActivity'] = time(); // update time stamp for the last activity
if (isset($_POST['lUsername']) || isset($_POST['rUsername']) || isset($_GET['logout'])) { // state changes
	session_regenerate_id(true); // re-generate session id for security reasons on state changes
}
// SESSION HANDLING AND SECURITY END

/**
 * Returns whether the current user is registered as a developer and thus may host projects
 * @return boolean whether the current user is a developer
 */
function isDeveloper() {
    return isset($_SESSION['userType']) && $_SESSION['userType'] == 'developer';
}

// AJAX DELETE PHRSASE BEGIN
if (isset($_GET['project']) && isset($_GET['deleteID'])) {
	$deleteID = intval(secure2id(trim($_GET['deleteID'])));
    $projectID = intval(secure2id(trim($_GET['project'])));
	$projectData1 = "SELECT name, user, default_language FROM projects WHERE id = ".$projectID;
	$projectData2 = mysql_query($projectData1);
	if (mysql_num_rows($projectData2) == 1) {
		$projectData3 = mysql_fetch_assoc($projectData2);
		$projectOwner = $projectData3['user'];
		$defaultLanguage = $projectData3['default_language'];
	}
	else {
		$projectOwner = -1;
		$defaultLanguage = '';
	}
    if ($_SESSION['userID'] == $projectOwner) {
        mysql_query("DELETE FROM translations WHERE id = ".$deleteID." AND project = ".$projectID); // delete this phrase for the default language
        mysql_query("DELETE FROM translations_pending WHERE project = ".$projectID." AND originalID = ".$deleteID." AND done = 0"); // delete pending contributions for this phrase
    }
    else { // requesting user is not the owner of the project
        header('HTTP/1.0 404 Not Found', true, 404); // ajax request failed
    }
    exit;
}
// AJAX DELETE PHRSASE END

// EXPORT BEGIN
if (isset($_GET['project']) && isset($_POST['exportDoStart'])) {
    $export_success = false;
	$projectID = intval(secure2id(trim($_GET['project'])));
	$projectData1 = "SELECT name, user, default_language FROM projects WHERE id = ".$projectID;
	$projectData2 = mysql_query($projectData1);
	if (mysql_num_rows($projectData2) == 1) {
		$projectData3 = mysql_fetch_assoc($projectData2);
		$projectOwner = $projectData3['user'];
		$defaultLanguage = $projectData3['default_language'];
	}
	else {
		$projectOwner = -1;
		$defaultLanguage = '';
	}
	if ($_SESSION['userID'] == $projectOwner) {
        $xmlFilename = isset($_POST['exportFilename']) ? trim($_POST['exportFilename']) : 'strings';
        if (preg_match('/^[a-z0-9_]+$/i', $xmlFilename)) {
            $xmlSavePath = OUTPUT_DIR.'/'.id2short($projectID).'/'.time().'/';
            if (mkdir($xmlSavePath, 0777, true)) { // if output folder has successfully been created
                foreach (array_keys($languages) as $language_key) {
                    $out = '<?xml version="1.0" encoding="utf-8"?>'."\n";
                    $out .= '<resources>'."\n";
                    $exportItems = array(); // array holding all entries to be exported
                    $getExportData1 = "SELECT language, ident_code, position, type, phrase, enabled FROM translations WHERE project = ".$projectID." AND language = '".mysql_real_escape_string($defaultLanguage)."' ORDER BY ident_code ASC, position ASC";
                    $getExportData2 = mysql_query($getExportData1);
                    while ($getExportData3 = mysql_fetch_assoc($getExportData2)) {
                        $currentKey = Translation::createKey($getExportData3['ident_code'], $getExportData3['position'], $getExportData3['type']);
                        $exportItems[$currentKey] = $getExportData3; // activate this key and provide a default value
                    }
                    $getExportData1 = "SELECT language, ident_code, position, type, phrase, enabled FROM translations WHERE project = ".$projectID." AND language = '".$language_key."' ORDER BY ident_code ASC, position ASC";
                    $getExportData2 = mysql_query($getExportData1);
                    while ($getExportData3 = mysql_fetch_assoc($getExportData2)) {
                        $currentKey = Translation::createKey($getExportData3['ident_code'], $getExportData3['position'], $getExportData3['type']);
                        if (isset($exportItems[$currentKey])) { // set entries for all keys that have been activated by the default language
                            if ($exportItems[$currentKey]['enabled'] == 1 && $getExportData3['phrase'] != '') { // only change the translation if this phrase is enabled in the default language
                                $exportItems[$currentKey] = $getExportData3;
                            }
                        }
                    }
                    $lastIdentCode = '';
                    $tagToClose = '';
                    foreach ($exportItems as $exportItem) {
                        if ($exportItem['type'] == 'string-array') {
                            if ($exportItem['ident_code'] != $lastIdentCode) {
                                if ($tagToClose != '') {
                                    $out .= "\t".'</'.$tagToClose.'>'."\n";
                                    $tagToClose = '';
                                }
                                $out .= "\t".'<string-array name="'.$exportItem['ident_code'].'">'."\n";
                            }
                            $out .= "\t\t".'<item>'.phraseEncode($exportItem['phrase']).'</item>'."\n";
                            $tagToClose = 'string-array';
                        }
                        elseif ($exportItem['type'] == 'plurals') {
                            if ($exportItem['ident_code'] != $lastIdentCode) {
                                if ($tagToClose != '') {
                                    $out .= "\t".'</'.$tagToClose.'>'."\n";
                                    $tagToClose = '';
                                }
                                $out .= "\t".'<plurals name="'.$exportItem['ident_code'].'">'."\n";
                            }
                            $out .= "\t\t".'<item quantity="'.$exportItem['position'].'">'.phraseEncode($exportItem['phrase']).'</item>'."\n";
                            $tagToClose = 'plurals';
                        }
                        else {
                            if ($exportItem['ident_code'] != $lastIdentCode) {
                                if ($tagToClose != '') {
                                    $out .= "\t".'</'.$tagToClose.'>'."\n";
                                    $tagToClose = '';
                                }
                            }
                            $out .= "\t".'<string name="'.$exportItem['ident_code'].'">'.phraseEncode($exportItem['phrase']).'</string>'."\n";
                        }
                        $lastIdentCode = $exportItem['ident_code'];
                    }
                    if ($tagToClose != '') {
                        $out .= "\t".'</'.$tagToClose.'>'."\n";
                        $tagToClose = '';
                    }
                    $out .= '</resources>';
                    if (mkdir($xmlSavePath.$language_key.'/', 0777, true)) {
                        if (file_put_contents($xmlSavePath.$language_key.'/'.$xmlFilename.'.xml', $out)) {
                            $export_success = true;
                        }
                    }
                }
            }
        }
        if ($export_success) {
            if (zipFolder($xmlSavePath, $xmlSavePath.'Export.zip')) {
                header('Location: '.str_replace(OUTPUT_DIR, '/_output', $xmlSavePath).'Export.zip');
                exit;
            }
        }
	}
}
// EXPORT END

// SIGNIN AND LOGOUT BEGIN
$login_message = '';
if (isset($_POST['lUsername']) && isset($_POST['lPassword']) && isset($_POST['email']) && isset($_POST['returnURL'])) {
	$lUsername = mysql_real_escape_string(trim(strip_tags($_POST['lUsername'])));
	$lPassword = getPasswordHash(trim($_POST['lPassword']));
	$returnURL = trim($_POST['returnURL']);
	if ($lUsername != '' && $lPassword != '' && $_POST['email'] == '') {
		$throttling1 = "SELECT COUNT(*) FROM login_attempts WHERE username = '".$lUsername."' AND attempt_time > ".(time()-7200);
		$throttling2 = mysql_query($throttling1);
		$login_attempts = mysql_result($throttling2, 0);
		if ($login_attempts <= 12) {
			$sql1 = "SELECT id, account_type FROM users WHERE username = '".$lUsername."' AND password_hash = '".$lPassword."'";
			$sql2 = mysql_query($sql1);
			if (mysql_num_rows($sql2) == 1) {
                $user_data = mysql_fetch_assoc($sql2);
				$_SESSION['userID'] = intval($user_data['id']);
                $_SESSION['userType'] = $user_data['account_type'];
				if ($returnURL != '/') {
					header('Location: '.$returnURL);
					exit;
				}
			}
			else {
				$throttling1 = "INSERT INTO login_attempts (username, attempt_time) VALUES ('".$lUsername."', ".time().")";
				$throttling2 = mysql_query($throttling1);
				$login_message = '<div class="alert_message"><p>Please check if your login credentials are correct.</p></div>';
			}
		}
		else {
			$login_message = '<div class="alert_message"><p>There have been more than 12 failed login attempts recently. Please try again later.</p></div>';
		}
	}
	else {
		$login_message = '<div class="alert_message"><p>Please enter your username and password.</p></div>';
	}
}
elseif (isset($_GET['logout'])) {
	if (intval(trim($_GET['logout'])) == $_SESSION['userID']) {
		logout(); // log the user out by cleaning all session data
	}
}
// SIGNIN AND LOGOUT END

header('content-type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="en" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="content-style-type" content="text/css" />
<meta name="robots" content="index,follow" />
<meta name="description" content="<?php echo PAGE_DESCRIPTION; ?>" />
<meta name="keywords" content="<?php echo PAGE_KEYWORDS; ?>" />
<link rel="stylesheet" type="text/css" media="all" href="/style.css" />
<link rel="icon" href="/_favicon.png" type="image/png" />
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
<title><?php echo PAGE_TITLE.' - '.PROJECT_TITLE; ?></title>
<script type="text/javascript" src="/jquery.js"></script>
<script type="text/javascript" src="/scripts.js"></script>
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-1037542-37']);
  _gaq.push(['_gat._anonymizeIp']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' === document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
</head>
<body>
<div id="navBar">
	<ul>
		<?php
        if ($_SESSION['userID'] == -1) { // guest user
            echo '<li><a href="/">'.PROJECT_TITLE.'</a></li>';
            echo '<li><a href="/_demo">Demo</a></li>';
        }
        else { // logged-in user
            echo '<li><a href="/">Home</a></li>';
            echo '<li><a href="mailto:'.SUPPORT_EMAIL.'">Feedback</a></li>';
            if (isDeveloper()) {
                echo '<li><a href="https://github.com/marcow/Localize">Open Source</a></li>';
            }
        }
        ?>
	</ul>
	<div class="clear"></div>
</div>
<div class="clear"></div>
<?php

// SIGNUP AND LOGIN-MESSAGE BEGIN
if (isset($_POST['rUsername']) && isset($_POST['rPassword']) && isset($_POST['email']) && isset($_POST['rAccountType'])) {
	$rUsername = mysql_real_escape_string(trim(strip_tags($_POST['rUsername'])));
	$rPassword = getPasswordHash(trim($_POST['rPassword']));
	$rPasswordVerify = getPasswordHash(trim($_POST['rPasswordVerify']));
    $rAccountType = $_POST['rAccountType'] == 'translator' ? 'translator' : ($_POST['rAccountType'] == 'developer' ? 'developer' : '');
	if ($rUsername != '' && $rPassword != '' && $rPassword != getPasswordHash('') && $_POST['email'] == '' && $rAccountType != '') {
		if ($rPassword == $rPasswordVerify) {
			$sql1 = "INSERT INTO users (username, password_hash, account_type, join_time) VALUES ('".$rUsername."', '".$rPassword."', '".$rAccountType."', ".time().")";
			$sql2 = mysql_query($sql1);
			if ($sql2 !== FALSE) {
				echo '<div class="alert_message"><p>Your account has successfully been created. You can now sign in.</p></div>';
			}
			else {
				echo '<div class="alert_message"><p>This username has already been taken, unfortunately.</p></div>';
			}
		}
		else {
			echo '<div class="alert_message"><p>The two passwords do not match.</p></div>';
		}
	}
	else {
		echo '<div class="alert_message"><p>Please choose your account type, username and password.</p></div>';
	}
}
elseif ($login_message != '') {
	echo '<div class="alert_message"><p>'.$login_message.'</p></div>';
}
// SIGNUP AND LOGIN-MESSAGE END

// APPLICATION FOR INVITE BEGIN
if (isset($_POST['apply_projectID']) && isset($_POST['apply_action']) && isset($_POST['email'])) {
	$applyProjectID = intval(secure2id($_POST['apply_projectID']));
	$applyAction = trim($_POST['apply_action']);
	if ($applyAction == LABEL_APPLY_INVITATION && $_SESSION['userID'] != DEMO_USER_ID) {
		if ($applyProjectID > 0 && $_POST['email'] == '' && $_SESSION['userID'] != -1) {
			$sql1 = "INSERT INTO invitations (projectID, userID) VALUES (".$applyProjectID.", ".intval($_SESSION['userID']).")";
			$sql2 = mysql_query($sql1);
			if ($sql2 !== FALSE) {
				echo '<div class="alert_message"><p>Your application has been sent and will be reviewed. Thank you!</p></div>';
			}
			else {
				echo '<div class="alert_message"><p>You have already applied for an invitation to this project.</p></div>';
			}
		}
		else {
			echo '<div class="alert_message"><p>The invitation could not be requested. Please try again.</p></div>';
		}
	}
}
elseif ($login_message != '') {
	echo '<div class="alert_message"><p>'.$login_message.'</p></div>';
}
// APPLICATION FOR INVITE END

if (isset($_GET['demo'])) {
	echo '<div class="contentBox">';
	echo '<h1>Demo</h1>';
	if ($_SESSION['userID'] == -1) {
		echo showLoginForm('/', 'Demo', 'demo');
	}
	else {
		echo '<p>You are already signed in. Please sign out to use the demo account.</p>';
	}
	echo '</div>';
}
elseif (isset($_GET['project'])) {
	$projectID = intval(secure2id(trim($_GET['project'])));
	if (isset($_POST['editorName']) && isset($_POST['uniqueEditorHash']) && $_SESSION['userID'] != DEMO_USER_ID) {
		$leaveName1 = "UPDATE translations_pending SET creation_user = '".mysql_real_escape_string(trim(strip_tags($_POST['editorName'])))."' WHERE creation_user = '".mysql_real_escape_string(trim(strip_tags($_POST['uniqueEditorHash'])))."'";
		mysql_query($leaveName1);
	}
	$getProject1 = "SELECT name, user, default_language, visibility, landing_html FROM projects WHERE id = ".$projectID;
	$getProject2 = mysql_query($getProject1);
	if (mysql_num_rows($getProject2) == 1) {
		$getProject3 = mysql_fetch_assoc($getProject2);
        $_SESSION['userVisited'][$projectID] = $getProject3['name'];
		if (isset($_GET['languageCode'])) {
			$languageCode = mysql_real_escape_string(trim(strip_tags($_GET['languageCode'])));
			if (isset($languages[$languageCode])) {
				if (isset($languages[$getProject3['default_language']])) {
					if (isset($_POST['edits']) && isset($_POST['previous']) && isset($_POST['doSave'])) {
						if (is_array($_POST['edits']) && is_array($_POST['previous'])) {
							$_SESSION['edits'][$projectID][$languageCode] = $_POST['edits'];
							$uniqueEditorHash = getPasswordHash(time().'_'.mt_rand(0, 100000));
							$up1 = "INSERT IGNORE INTO translations_pending (project, language, originalID, phrase, creation_time, creation_user) VALUES ";
							$up1_values = "";
							foreach ($_POST['edits'] as $editKey => $editPhrase) {
								$previousPhrase = isset($_POST['previous'][$editKey]) ? $_POST['previous'][$editKey] : '';
								if (trim($editPhrase) != '' && $editPhrase != $previousPhrase) {
									$up1_values .= "(".$projectID.", '".$languageCode."', ".intval(secure2id(trim($editKey))).", '".mysql_real_escape_string(trim($editPhrase))."', ".time().", '".$uniqueEditorHash."'),";
								}
							}
							if ($up1_values != "") {
								$up1 .= mb_substr($up1_values, 0, -1);
								if ($_SESSION['userID'] != DEMO_USER_ID) {
									$up2 = mysql_query($up1);
								}
								else {
									$up2 = TRUE;
								}
								if ($up2 !== FALSE) {
                                    if ($_SESSION['userID'] != -1 && $_SESSION['userID'] != DEMO_USER_ID) {
                                        mysql_query("INSERT INTO contributions (project, user, time_contributed, editHash) VALUES (".$projectID.", ".intval($_SESSION['userID']).", ".time().", '".$uniqueEditorHash."')");
                                    }
									echo '<div class="alert_message"><p>Thank you! Your changes have been saved and will be reviewed now!</p>';
									if ($_SESSION['userID'] != $getProject3['user'] && $_SESSION['userID'] != DEMO_USER_ID) {
										echo '<form action="/'.id2short($projectID).'/'.cleanName($getProject3['name']).'" method="post" accept-charset="utf-8"><fieldset><label for="editorName">Want to tell us who did this great piece of work? Leave your name here:</label><input type="text" id="editorName" name="editorName" /><input type="hidden" name="uniqueEditorHash" value="'.$uniqueEditorHash.'" /></fieldset><fieldset><input type="submit" value="Leave name" /></fieldset></form>';
									}
									echo '</div>';
								}
								else {
									echo '<div class="alert_message"><p>Server error.</p></div>';
								}
							}
							else {
								echo '<div class="alert_message"><p>Changes discarded.</p></div>';
							}
							if (isset($_POST['enablePhrase']) && $_SESSION['userID'] != DEMO_USER_ID) {
								if (is_array($_POST['enablePhrase'])) {
									$enableIDList = "";
									foreach ($_POST['enablePhrase'] as $enableIDEntry) {
										$enableIDList .= intval(secure2id($enableIDEntry)).",";
									}
									if ($enableIDList != "") {
										$enableIDList = mb_substr($enableIDList, 0, -1);
										$enableIDs1 = "UPDATE translations SET enabled = 1 WHERE project = ".$projectID." AND language = '".$languageCode."' AND id IN (".$enableIDList.")";
										$enableIDs2 = mysql_query($enableIDs1);
										$enableIDs1 = "UPDATE translations SET enabled = 0 WHERE project = ".$projectID." AND language = '".$languageCode."' AND id NOT IN (".$enableIDList.")";
										$enableIDs2 = mysql_query($enableIDs1);
									}
								}
							}
						}
						else {
							echo '<div class="alert_message"><p>Changes discarded.</p></div>';
						}
					}
					if (isset($_GET['reviewMode']) && $_SESSION['userID'] == $getProject3['user']) {
						if (isset($_POST['reviewID']) && isset($_POST['reviewAction']) && isset($_POST['reviewIdentCode']) && isset($_POST['reviewPosition']) && isset($_POST['reviewType']) && isset($_POST['reviewPhrase'])) {
							$reviewID = intval(secure2id(trim($_POST['reviewID'])));
							$reviewIdentCode = mysql_real_escape_string(trim(strip_tags($_POST['reviewIdentCode'])));
							$reviewPosition = mysql_real_escape_string(trim(strip_tags($_POST['reviewPosition'])));
							$reviewType = mysql_real_escape_string(trim(strip_tags($_POST['reviewType'])));
							$reviewPhrase = mysql_real_escape_string(trim($_POST['reviewPhrase']));
							$reviewAction = trim($_POST['reviewAction']);
							if ($_SESSION['userID'] != DEMO_USER_ID) {
								if ($reviewAction == 'Approve') {
									$reviewProcess1 = "INSERT INTO translations (project, language, ident_code, position, type, phrase) VALUES (".$projectID.", '".$languageCode."', '".$reviewIdentCode."', '".$reviewPosition."', '".$reviewType."', '".$reviewPhrase."') ON DUPLICATE KEY UPDATE phrase = VALUES(phrase)";
									$reviewProcess2 = mysql_query($reviewProcess1);
									$reviewProcess3 = "UPDATE translations_pending SET done = 1 WHERE id = ".$reviewID;
									$reviewProcess4 = mysql_query($reviewProcess3);
								}
								elseif ($reviewAction == 'Reject') {
									$reviewProcess3 = "DELETE FROM translations_pending WHERE id = ".$reviewID;
									$reviewProcess4 = mysql_query($reviewProcess3);
								}
								elseif ($reviewAction == 'Review later') {
									$reviewProcess1 = "INSERT INTO translations_pending (project, language, originalID, phrase, creation_time, creation_user) SELECT project, language, originalID, phrase, creation_time, creation_user FROM translations_pending WHERE id = ".$reviewID;
									$reviewProcess2 = mysql_query($reviewProcess1);
									$reviewProcess3 = "DELETE FROM translations_pending WHERE id = ".$reviewID;
									$reviewProcess4 = mysql_query($reviewProcess3);
								}
							}
						}
						echo '<div class="contentBox">';
						echo '<h1>'.htmlspecialchars($getProject3['name']).' &mdash; '.htmlspecialchars($languages[$languageCode]).'</h1>';
						echo '<h2>Review mode</h2>';
						$getPending1 = "SELECT a.id, a.originalID, a.phrase AS pendingPhrase, b.ident_code, b.position, b.type, b.phrase AS defaultPhrase FROM translations_pending AS a JOIN translations AS b ON a.originalID = b.id WHERE a.project = ".$projectID." AND a.done = 0 AND a.language = '".$languageCode."' AND b.enabled = 1 ORDER BY a.id ASC LIMIT 0, 1";
						$getPending2 = mysql_query($getPending1);
						if (mysql_num_rows($getPending2) == 1) {
							$getPending3 = mysql_fetch_assoc($getPending2);
							$sameLanguageOriginal = '';
							$sameLanguageOriginal1 = "SELECT phrase FROM translations WHERE project = ".$projectID." AND language = '".$languageCode."' AND ident_code = '".mysql_real_escape_string($getPending3['ident_code'])."' AND position = '".mysql_real_escape_string($getPending3['position'])."' LIMIT 0, 1";
							$sameLanguageOriginal2 = mysql_query($sameLanguageOriginal1);
							if (mysql_num_rows($sameLanguageOriginal2) == 1) {
								$sameLanguageOriginal = mysql_result($sameLanguageOriginal2, 0);
							}
							echo '<form action="/'.id2short($projectID).'/'.cleanName($getProject3['name']).'/'.$languageCode.'/review" method="post" accept-charset="utf-8">';
							echo '<fieldset>';
							echo '<input type="hidden" name="reviewID" value="'.id2short($getPending3['id']).'" />';
							echo '<input type="hidden" name="reviewIdentCode" value="'.htmlspecialchars($getPending3['ident_code']).'" />';
							echo '<input type="hidden" name="reviewPosition" value="'.htmlspecialchars($getPending3['position']).'" />';
							echo '<input type="hidden" name="reviewType" value="'.htmlspecialchars($getPending3['type']).'" />';
							echo '<input type="hidden" name="reviewPhrase" value="'.htmlspecialchars($getPending3['pendingPhrase']).'" />';
							echo '<input type="submit" name="reviewAction" value="Approve" style="display:inline-block; width:30%;" />';
							echo '<input type="submit" name="reviewAction" value="Review later" style="display:inline-block; width:30%;" />';
							echo '<input type="submit" name="reviewAction" value="Reject" style="display:inline-block; width:30%;" />';
							echo '</fieldset>';
							echo '</form>';
							echo '<table class="p50">';
                            $referenceIsRTL = isRTL($getProject3['default_language']);
                            $languageIsRTL = isRTL($languageCode);
							echo '<tr style="font-weight:bold;"><td>'.$languages[$getProject3['default_language']].'</td><td dir="'.($referenceIsRTL ? 'rtl' : 'ltr').'">'.htmlspecialchars($getPending3['defaultPhrase']).'</td></tr>';
							echo '<tr><td>'.$languages[$languageCode].' &mdash; Old</td><td dir="'.($languageIsRTL ? 'rtl' : 'ltr').'">'.htmlspecialchars($sameLanguageOriginal).'</td></tr>';
							echo '<tr><td>Applied changes</td><td dir="'.($languageIsRTL ? 'rtl' : 'ltr').'">'.htmlDiff(htmlspecialchars($sameLanguageOriginal), htmlspecialchars($getPending3['pendingPhrase'])).'</td></tr>';
							echo '<tr style="font-weight:bold;"><td dir="'.($languageIsRTL ? 'rtl' : 'ltr').'">'.$languages[$languageCode].' &mdash; New</td><td>'.htmlspecialchars($getPending3['pendingPhrase']).'</td></tr>';
							echo '</table>';
							echo '<p style="margin:0; padding:0;">&nbsp;</p>'; // bottom-margin of table does not work otherwise (unknown why)
							$getPendingCount1 = "SELECT COUNT(*) FROM translations_pending AS a JOIN translations AS b ON a.originalID = b.id WHERE a.project = ".$projectID." AND a.done = 0 AND a.language = '".$languageCode."' AND b.enabled = 1";
							$getPendingCount2 = mysql_query($getPendingCount1);
							$getPendingCount3 = mysql_result($getPendingCount2, 0);
							echo '<p style="text-align:center; color:#666; font-size:90%;">['.$getPendingCount3.' translation'.($getPendingCount3 == 1 ? '' : 's').' left to review]</p>';
						}
						else {
							$deleteBlockedPending1 = "DELETE FROM translations_pending WHERE project = ".$projectID." AND done = 0 AND language = '".$languageCode."' AND (SELECT enabled FROM translations WHERE id = translations_pending.originalID LIMIT 0, 1) = 0";
							mysql_query($deleteBlockedPending1);
							echo '<p>There are no pending edits.</p><p><a href="/'.id2short($projectID).'/'.cleanName($getProject3['name']).'/translate">Go back.</a></p>';
						}
						echo '</div>';
					}
					else {
						if ($_SESSION['userID'] == $getProject3['user']) {
							$topTranslators1 = "SELECT creation_user, COUNT(*) AS nEdits FROM translations_pending WHERE project = ".$projectID." AND done = 1 AND language = '".$languageCode."' GROUP BY creation_user ORDER BY nEdits DESC LIMIT 0, 5";
							$topTranslators2 = mysql_query($topTranslators1);
							if (mysql_num_rows($topTranslators2) > 0) {
								$topTranslatorsOut = '';
								while ($topTranslators3 = mysql_fetch_assoc($topTranslators2)) {
									if (mb_strlen($topTranslators3['creation_user']) != 64) {
										$topTranslatorsOut .= '<li>'.htmlspecialchars($topTranslators3['creation_user']).' <span style="color:#666;">('.$topTranslators3['nEdits'].' improvements)</span></li>';
									}
								}
								if ($topTranslatorsOut != '') {
									echo '<div class="contentBox"><h1 id="head_language_translators" onclick="toggleDisplay(this);" class="clickable">Translators for '.htmlspecialchars($languages[$languageCode]).'</h1><ul id="body_language_translators" style="display:none;">'.$topTranslatorsOut.'</ul></h1></div>';
								}
							}
                            if (isset($_POST['addPhraseType']) && isset($_POST['addPhraseIdent']) && isset($_POST['addPhrasePhrase'])) {
                                $addPhraseType = mysql_real_escape_string(trim($_POST['addPhraseType']));
                                $addPhraseIdent = mysql_real_escape_string(trim($_POST['addPhraseIdent']));
                                $addPhrasePhrase = mysql_real_escape_string(trim($_POST['addPhrasePhrase']));
                                if ($addPhraseType == 'string') {
                                    $add_success = false;
                                    if (preg_match('/^[a-z0-9_]+$/i', $addPhraseIdent)) {
                                        $addCheck1 = "SELECT COUNT(*) FROM translations WHERE project = ".$projectID." AND language = '".$getProject3['default_language']."' AND ident_code = '".$addPhraseIdent."'";
                                        $addCheck2 = mysql_query($addCheck1);
                                        if ($addCheck2 !== FALSE) {
                                            $addCheck3 = mysql_result($addCheck2, 0);
                                            if ($addCheck3 == 0) {
                                                $addPhrase1 = "INSERT INTO translations (project, language, ident_code, position, type, phrase) VALUES (".$projectID.", '".$getProject3['default_language']."', '".$addPhraseIdent."', '', '".$addPhraseType."', '".$addPhrasePhrase."')";
                                                $addPhrase2 = mysql_query($addPhrase1);
                                                if ($addPhrase2 != FALSE) {
                                                    $add_success = true;
                                                }
                                            }
                                        }
                                    }
                                    if ($add_success) {
                                        echo '<div class="alert_message"><p>The new phrase has been added to all languages of this project. You can now enter individual translations for each language.</p></div>';
                                    }
                                    else {
                                        echo '<div class="alert_message"><p>The new phrase could not be added.</p></div>';
                                    }
                                }
                            }
                            echo '<div class="contentBox">';
                            echo '<h1 id="head_translation_add_phrase" onclick="toggleDisplay(this);" class="clickable">Add new phrases</h1>';
                            echo '<form id="body_translation_add_phrase" style="display:none;" action="/'.id2short($projectID).'/'.cleanName($getProject3['name']).'/'.$languageCode.'" method="post" accept-charset="utf-8" onsubmit="return checkIdentName(document.getElementById(\'addPhraseIdent\'), \'unique name\');">';
                            echo '<fieldset><p>Please be aware that new phrases will be added to all languages of this project. You can enter the default translation below.</p></fieldset>';
                            echo '<fieldset><label for="addPhraseType">Type of the new entry</label><select id="addPhraseType" name="addPhraseType" size="1">';
                            echo '<option value="string">String</option>';
                            echo '<option value="string-array">String Array</option>';
                            echo '<option value="plurals">Quantity Strings (Plurals)</option>';
                            echo '</select></fieldset>';
                            echo '<fieldset><label for="addPhraseIdent">Unique name (identification code)</label><input id="addPhraseIdent" name="addPhraseIdent" value="" /></fieldset>';
                            $phraseIsRTL = isRTL($getProject3['default_language']);
                            echo '<fieldset><label for="addPhrasePhrase">Phrase ('.$languages[$getProject3['default_language']].')</label></fieldset><textarea id="addPhrasePhrase" name="addPhrasePhrase" dir="'.($phraseIsRTL ? 'rtl' : 'ltr').'"></textarea></fieldset>';
                            echo '<fieldset><input type="submit" value="Add phrase(s)" onclick="return checkIdentName(document.getElementById(\'addPhraseIdent\'), \'unique name\');" /></fieldset>';
                            echo '</form>';
                            echo '</div>';
						}
						$isProjectVisible = isProjectVisible($getProject3['visibility'], $getProject3['user'], $projectID);
						if ($isProjectVisible == STATUS_VISIBILITY_SUCCESS) {
							echo '<div class="contentBox">';
							$isDefaultLanguage = $languageCode == $getProject3['default_language'] && $_SESSION['userID'] == $getProject3['user'];
							echo '<h1>'.htmlspecialchars($getProject3['name']).' &mdash; '.htmlspecialchars($languages[$languageCode]).'</h1>';
							if ($isDefaultLanguage) {
								echo '<p><strong>Notice:</strong> This is the default language. Check all phrases that are to be translated, and uncheck all that you want to use the default language\'s phrases for. <a href="#" onclick="markDisabledPhrases(); return false;">Click here</a> to mark all disabled phrases.</p>';
							}
							$referenceLanguage = array();
							$editingLanguage = array();
							$lang1 = "SELECT id, ident_code, position, type, phrase, enabled FROM translations WHERE project = '".$projectID."' AND language = '".mysql_real_escape_string($getProject3['default_language'])."'";
							$lang2 = mysql_query($lang1);
							while ($lang3 = mysql_fetch_assoc($lang2)) {
								$newTrans = new Translation($lang3['id'], $lang3['ident_code'], $lang3['position'], $lang3['type'], $lang3['phrase'], $lang3['enabled']);
								$referenceLanguage[$newTrans->getKey()] = $newTrans;
							}
							$lang1 = "SELECT id, ident_code, position, type, phrase FROM translations WHERE project = '".$projectID."' AND language = '".$languageCode."'";
							$lang2 = mysql_query($lang1);
							while ($lang3 = mysql_fetch_assoc($lang2)) {
								$newTrans = new Translation($lang3['id'], $lang3['ident_code'], $lang3['position'], $lang3['type'], $lang3['phrase'], 1);
								$editingLanguage[$newTrans->getKey()] = $newTrans;
							}
							if (count($referenceLanguage) > 0) {
								echo '<form action="/'.id2short($projectID).'/'.cleanName($getProject3['name']).'/'.$languageCode.'" method="post" accept-charset="utf-8">';
								echo '<fieldset><input type="submit" name="doSave" value="Save changes" style="margin-left:0.8em;" onclick="return confirm(\'Are you sure you want to submit all changes on this page?\');" /> <input type="submit" name="doMarkProblems" value="Mark possible problems" style="margin-left:0.8em;" onclick="markPossibleProblems(); alert(\'Long paragraphs without line breaks and extremely long words are now marked in orange.\'); return false;" /></fieldset>';
								echo '<table class="p50"><thead>';
								echo '<tr>';
								echo '<th style="width:0.1em">&nbsp;</th><th>'.($isDefaultLanguage ? 'Unique name' : $languages[$getProject3['default_language']]).'</th><th>'.$languages[$languageCode].'</th>';
                                if ($isDefaultLanguage) {
                                    echo '<th style="width:0.1em">&nbsp;</th>'; // one extra column for delete button
                                }
								echo '</tr>';
								echo '</thead><tbody>';
								foreach ($referenceLanguage as $referencePhrase) {
									if (!$referencePhrase->isEnabled() && !$isDefaultLanguage) { continue; }
									$currentPhraseKey = id2short($referencePhrase->getID());
									$textFieldContent = isset($_SESSION['edits'][$projectID][$languageCode][$currentPhraseKey]) ? $_SESSION['edits'][$projectID][$languageCode][$currentPhraseKey] : (isset($editingLanguage[$referencePhrase->getKey()]) ? $editingLanguage[$referencePhrase->getKey()]->getPhrase() : '');
									echo '<tr id="phrase_'.$currentPhraseKey.'"'.(($isDefaultLanguage && !$referencePhrase->isEnabled()) ? ' class="disabledPhrase"' : (phraseContainsProblem($textFieldContent) ? ' class="problemPhrase"' : '')).'>';
									if ($isDefaultLanguage) {
										echo '<td style="width:0.1em"><input type="checkbox" '.($referencePhrase->isEnabled() ? ' checked="checked"' : '').'name="enablePhrase[]" value="'.$currentPhraseKey.'" /></td>';
                                        echo '<td>'.htmlspecialchars($referencePhrase->getIdentCode());
                                        if ($referencePhrase->getType() == 'plurals' && $referencePhrase->getPosition() != '') {
                                            echo '<span class="inline_comment">'.htmlspecialchars($referencePhrase->getPosition()).'</span>';
                                        }
                                        echo '</td>';
									}
									else {
										echo '<td style="width:0.1em"><a href="#phrase_'.$currentPhraseKey.'"><img src="/_images/link.png" alt="[L]" title="'.htmlspecialchars($referencePhrase->getIdentCode()).'" width="16" /></a></td>';
                                        $referenceIsRTL = isRTL($getProject3['default_language']);
                                        echo '<td dir="'.($referenceIsRTL ? 'rtl' : 'ltr').'">'.nl2br(htmlspecialchars($referencePhrase->getPhrase()));
                                        if ($referencePhrase->getType() == 'plurals' && $referencePhrase->getPosition() != '') {
                                            echo '<span class="inline_comment">'.htmlspecialchars($referencePhrase->getPosition()).'</span>';
                                        }
                                        echo '</td>';
									}
                                    $languageIsRTL = isRTL($languageCode);
									if (stripos($referencePhrase->getPhrase(), "\n") !== FALSE || mb_strlen($referencePhrase->getPhrase()) >= 100) {
										echo '<td><textarea name="edits['.$currentPhraseKey.']" style="height:'.round(mb_strlen($referencePhrase->getPhrase())/100*5.25).'em;" dir="'.($languageIsRTL ? 'rtl' : 'ltr').'">'.htmlspecialchars($textFieldContent).'</textarea>';
									}
									else {
										echo '<td><input type="text" name="edits['.$currentPhraseKey.']" value="'.htmlspecialchars($textFieldContent).'" dir="'.($languageIsRTL ? 'rtl' : 'ltr').'" />';
									}
									echo '<input type="hidden" name="previous['.$currentPhraseKey.']" value="'.htmlspecialchars($textFieldContent).'" /></td>';
                                    if ($isDefaultLanguage) {
                                        echo '<td style="width:0.1em"><a href="#" onclick="deletePhrase(\''.BASE_PATH.'\', \''.id2short($projectID).'\', \''.$currentPhraseKey.'\', this.parentNode.parentNode); return false;"><img src="/_images/delete.png" alt="[D]" title="Delete this phrase" width="16" /></a></td>';
                                    }
									echo '</tr>';
								}
								echo '</tbody></table>';
								echo '<fieldset><input type="submit" name="doSave" value="Save changes" style="margin-left:0.8em;" onclick="return confirm(\'Are you sure you want to submit all changes on this page?\');" /> <input type="submit" name="doMarkProblems" value="Mark possible problems" style="margin-left:0.8em;" onclick="markPossibleProblems(); alert(\'Long paragraphs without line breaks and extremely long words are now marked in orange.\'); return false;" /></fieldset>';
								echo '</form>';
							}
							else {
								echo '<p class="info_message">There aren\'t any translations in the default language ('.$languages[$getProject3['default_language']].') yet.</p>';
							}
							echo '</div>';
						}
						else { // project not visible to this user
							echo '<div class="contentBox"><h1>Protected project</h1>';
							if ($isProjectVisible == STATUS_VISIBILITY_SIGNIN || ($isProjectVisible == STATUS_VISIBILITY_APPLY && $_SESSION['userID'] == -1)) {
								echo '<p class="info_message">Please sign in. This is not a public project.</p>';
								echo showLoginForm('/'.id2short($projectID).'/'.cleanName($getProject3['name']).'/'.$languageCode);
							}
							elseif ($isProjectVisible == STATUS_VISIBILITY_APPLY) {
								echo '<p class="info_message">Please apply for an invitation. This project requires translators to be invited.</p>';
								echo showInviteApplicationForm($projectID, $getProject3['name']);
							}
							elseif ($isProjectVisible == STATUS_VISIBILITY_APPLICATION_SENT) {
								echo '<p>Your application is under review.</p>';
							}
							elseif ($isProjectVisible == STATUS_VISIBILITY_REJECTED) {
								echo '<p>Your application has been rejected by the project owner.</p>';
							}
							else {
								echo '<p>Error 29</p>';
							}
							echo '</div>';
						}
					}
				}
				else {
					echo '<div class="alert_message"><p>Please set the default language in the project\'s settings first.</p></div>';
				}
			}
			else {
				echo '<div class="alert_message"><p>Unknown language.</p></div>';
			}
		}
		else {
			if (isset($_FILES['newFile']) && isset($_POST['newLanguage']) && $_SESSION['userID'] == $getProject3['user'] && $_SESSION['userID'] != DEMO_USER_ID) {
				$language_code = trim($_POST['newLanguage']);
				if (isset($languages[$language_code])) { 
					if ($_FILES['newFile']['size'] < MAX_FILESIZE) {
						$newFileName = TMP_DIR.'/'.$_SESSION['userID'].'_'.mt_rand(0, 10).'.xml';
						if (move_uploaded_file($_FILES['newFile']['tmp_name'], $newFileName)) {
                            $fileContent = file_get_contents($newFileName);
							if ($fileContent !== false) {
								$fileContent = preg_replace('/<string-array([^>]*)>/i', '<entryList\1>', $fileContent);
								$fileContent = str_replace('</string-array>', '</entryList>', $fileContent);
								$fileContent = preg_replace('/<string([^>]*)>/i', '<entrySingle\1><![CDATA[', $fileContent);
								$fileContent = str_replace('</string>', ']]></entrySingle>', $fileContent);
								$fileContent = preg_replace('/<item([^>]*)>/i', '<item\1><![CDATA[', $fileContent);
								$fileContent = str_replace('</item>', ']]></item>', $fileContent);
								$inserted_phrases = 0;
                                $xml = simplexml_load_string($fileContent, 'SimpleXMLElement', LIBXML_NOCDATA);
								if ($xml != false) {
									$sql1 = "INSERT INTO translations (project, language, ident_code, position, type, phrase) VALUES ";
									$sql1_values = "";
									foreach ($xml->entrySingle as $entrySingle) {
										$entryAttributes = $entrySingle->attributes();
										$sql1_values .= "(".$projectID.", '".$language_code."', '".mysql_real_escape_string(trim($entryAttributes['name']))."', '', 'string', '".mysql_real_escape_string(trim(phraseDecode($entrySingle[0])))."'),";
										$inserted_phrases++;
									}
									foreach ($xml->entryList as $entryList) {
										$entryAttributes = $entryList->attributes();
										$counter = 0;
										foreach ($entryList->item as $entryItem) {
											$sql1_values .= "(".$projectID.", '".$language_code."', '".mysql_real_escape_string(trim($entryAttributes['name']))."', '".$counter."', 'string-array', '".mysql_real_escape_string(trim(phraseDecode($entryItem)))."'),";
											$counter++;
										}
										$inserted_phrases += $counter;
									}
									foreach ($xml->plurals as $plural) {
										$pluralAttributes = $plural->attributes();
										foreach ($plural->item as $pluralItem) {
											$itemAttributes = $pluralItem->attributes();
											$sql1_values .= "(".$projectID.", '".$language_code."', '".mysql_real_escape_string(trim($pluralAttributes['name']))."', '".mysql_real_escape_string(trim($itemAttributes['quantity']))."', 'plurals', '".mysql_real_escape_string(trim(phraseDecode($pluralItem)))."'),";
											$inserted_phrases++;
										}
									}
									if ($sql1_values != "") {
										$sql1 .= mb_substr($sql1_values, 0, -1)." ON DUPLICATE KEY UPDATE phrase = VALUES(phrase)";
										$sql2 = mysql_query($sql1);
										if ($sql2 !== FALSE) {
											echo '<div class="alert_message"><p>The selected language has successfully been updated with '.$inserted_phrases.' new translations.</p></div>';
										}
										else {
											echo '<div class="alert_message"><p>Server error.</p></div>';
										}
									}
									else {
										echo '<div class="alert_message"><p>The file did not contain any translations. Please try again.</p></div>';
									}
								}
								else {
									echo '<div class="alert_message"><p>The file doesn\'t seem to be valid XML. Please try again.</p></div>';
								}
							}
							else {
								echo '<div class="alert_message"><p>The file could not be opened. Please try again.</p></div>';
							}
						}
						else {
							echo '<div class="alert_message"><p>The file could not be processed. Please try again.</p></div>';
						}
					}
					else {
						echo '<div class="alert_message"><p>The file that you have uploaded exceeds the limit of 1.5MB.</p></div>';
					}
				}
				else {
					echo '<div class="alert_message"><p>Please select one of the languages in the list.</p></div>';
				}
			}
			if (isset($_POST['searchPhrase'])) {
				$searchPhrase = trim($_POST['searchPhrase']);
                $searchResults = array();
                echo '<div class="contentBox">';
                echo '<h1>Search for &quot;'.htmlspecialchars($searchPhrase).'&quot;</h1>';
                $search1 = "SELECT a.id, a.language, a.phrase, b.id AS referenceID FROM translations AS a JOIN translations AS b ON a.project = b.project AND b.language = '".mysql_real_escape_string($getProject3['default_language'])."' AND a.ident_code = b.ident_code WHERE a.project = ".$projectID." AND a.phrase LIKE '%".mysql_real_escape_string($searchPhrase)."%'";
                $search2 = mysql_query($search1);
                if (mysql_num_rows($search2) == 0) {
                    echo '<p>No results. Please try a different search.</p>';
                }
                else {
                    echo '<ul>';
                    while ($search3 = mysql_fetch_assoc($search2)) {
                        if (!isset($searchResults[$search3['language']])) {
                            $searchResults[$search3['language']] = array();
                        }
                        $search3['phrase'] = htmlspecialchars($search3['phrase']);
                        $phraseItem = '<li><a href="/'.id2short($projectID).'/'.cleanName($getProject3['name']).'/'.$search3['language'].'#phrase_'.id2short($search3['referenceID']).'">[View]</a> ';
                        if (mb_strlen($search3['phrase']) > 250) {
                            $phraseItem .= mb_substr($search3['phrase'], 0, 250).'&#8230;';
                        }
                        else {
                            $phraseItem .= $search3['phrase'];
                        }
                        $phraseItem .= '</li>';
                        $searchResults[$search3['language']][] = $phraseItem;
                    }
                    foreach ($searchResults as $searchLanguage => $searchPhrases) {
                        echo '<li><a href="/'.id2short($projectID).'/'.cleanName($getProject3['name']).'/'.$searchLanguage.'">'.$languages[$searchLanguage].'</a>';
                        echo '<ul>';
                        foreach ($searchPhrases as $searchPhrase) {
                            echo $searchPhrase;
                        }
                        echo '</ul>';
                        echo '</li>';
                    }
                    echo '</ul>';
                }
                echo '</div>';
			}
            if (isset($_POST['landing_mode']) && isset($_POST['landing_html']) && $_SESSION['userID'] == $getProject3['user'] && $_SESSION['userID'] != DEMO_USER_ID) {
                $new_landing_html = '';
                if ($_POST['landing_mode'] == 'static' && $_POST['landing_html'] != '') {
                    $getProject3['landing_html'] = trim($_POST['landing_html']);
                    $new_landing_html = mysql_real_escape_string($getProject3['landing_html']);
                }
                $updateHTML = "UPDATE projects SET landing_html = '".$new_landing_html."' WHERE id = ".$projectID;
                mysql_query($updateHTML) or die(mysql_error());
            }
			if ($getProject3['user'] == $_SESSION['userID']) {
				if (isset($_GET['inviteUser']) && isset($_GET['inviteMode']) && isset($_GET['inviteHash']) && $_SESSION['userID'] != DEMO_USER_ID) {
					$inviteUser = intval(secure2id(trim($_GET['inviteUser'])));
					$inviteMode = intval(trim($_GET['inviteMode']));
					$inviteHash = trim($_GET['inviteHash']);
					$verifyHash = getPasswordHash($_SESSION['userID'].'_'.$projectID.'_'.$inviteUser);
					if ($inviteHash == $verifyHash) {
						$inviteReview1 = "UPDATE invitations SET approved = ".($inviteMode == 1 ? 1 : -1)." WHERE projectID = ".$projectID." AND userID = ".$inviteUser." AND approved = 0";
						mysql_query($inviteReview1);
						if (mysql_affected_rows() == 1) {
							echo '<div class="alert_message"><p>The selected user has been '.($inviteMode == 1 ? 'invited' : 'rejected').'.</p></div>';
						}
						else {
							echo '<div class="alert_message"><p>The invitation could not be processed. Please try again.</p></div>';
						}
					}
				}
				// REVIEW PENDING EDITS BEGIN
				$pendingEdits1 = "SELECT language FROM translations_pending WHERE project = ".$projectID." AND done = 0 GROUP BY language";
				$pendingEdits2 = mysql_query($pendingEdits1);
				if (mysql_num_rows($pendingEdits2) > 0) {
					echo '<div class="contentBox">';
					echo '<h1>Review contributions</h1>';
					echo '<p>You have pending edits for the following languages. Start reviewing them now.</p>';
					echo '<ul>';
					while ($pendingEdits3 = mysql_fetch_assoc($pendingEdits2)) {
						echo '<li><a href="/'.id2short($projectID).'/'.cleanName($getProject3['name']).'/'.$pendingEdits3['language'].'/review">'.(isset($languages[$pendingEdits3['language']]) ? $languages[$pendingEdits3['language']] : $pendingEdits3['language']).'</a></li>';
					}
					echo '</ul>';
					echo '</div>';
				}
				// REVIEW PENDING EDITS END
				// REVIEW APPLICATIONS BEGIN
				$pendingApplications1 = "SELECT a.userID, b.username, b.join_time FROM invitations AS a JOIN users AS b ON a.userID = b.id WHERE a.projectID = ".$projectID." AND a.userID > 0 AND a.approved = 0";
				$pendingApplications2 = mysql_query($pendingApplications1);
				if (mysql_num_rows($pendingApplications2) > 0) {
					echo '<div class="contentBox">';
					echo '<h1>Review applications</h1>';
					echo '<p>The following users have applied for this project. Invite or reject them.</p>';
					echo '<table><thead><tr><th>User</th><th>Joined</th><th>&nbsp;</th></tr></thead><tbody>';
					while ($pendingApplications3 = mysql_fetch_assoc($pendingApplications2)) {
						echo '<tr>';
						echo '<td>'.htmlspecialchars($pendingApplications3['username']).'</td>';
						echo '<td>'.date('d/m/Y', $pendingApplications3['join_time']).'</td>';
						$verifyHash = getPasswordHash($_SESSION['userID'].'_'.$projectID.'_'.$pendingApplications3['userID']);
						echo '<td><a href="/'.id2short($projectID).'/_invite/'.id2short($pendingApplications3['userID']).'/'.$verifyHash.'" onclick="return confirm(\'Are you sure?\');">Invite</a> or <a href="/'.id2short($projectID).'/_reject/'.id2short($pendingApplications3['userID']).'/'.$verifyHash.'" onclick="return confirm(\'Are you sure?\');">Reject</a></td>';
						echo '</tr>';
					}
					echo '</tbody></table>';
					echo '<p style="margin:0; padding:0;">&nbsp;</p>'; // bottom-margin of table does not work otherwise (unknown why)
					echo '</div>';
				}
				// REVIEW APPLICATIONS END
				echo '<div class="contentBox">';
				echo '<h1 id="head_project_setup_landing" onclick="toggleDisplay(this);" class="clickable">Configure this project\'s landing page</h1>';
				echo '<form id="body_project_setup_landing" style="display:none;" action="/'.id2short($projectID).'/'.cleanName($getProject3['name']).'" method="post" accept-charset="utf-8">';
				echo '<fieldset><label for="landing_link">Share this page and let users contribute</label><input type="text" name="landing_link" value="'.BASE_PATH.id2short($projectID).'/'.cleanName($getProject3['name']).'" readonly="readonly" /></fieldset>';
                echo '<fieldset><p>What will visitors see if they visit your project\'s translation page? You can either set up a static landing page to welcome users or keep the default landing page, which means that visitors immediately see the list of languages and translations.</p></fieldset>';
                echo '<fieldset><label for="landing_mode">Landing page</label><select id="landing_mode" name="landing_mode" size="1">';
                echo '<option value="static"'.($getProject3['landing_html'] == '' ? '' : ' selected="selected"').'>Static page</option>';
                echo '<option value="overview"'.($getProject3['landing_html'] == '' ? ' selected="selected"' : '').'>List of languages</option>';
                echo '</select></fieldset>';
                echo '<fieldset id="landing_html_container"'.($getProject3['landing_html'] == '' ? ' style="display:none;"' : '').'><label for="landing_html">Static page content</label><textarea id="landing_html" name="landing_html">'.($getProject3['landing_html'] == '' ? LANDING_HTML_DEFAULT : htmlspecialchars($getProject3['landing_html'])).'</textarea></fieldset>';
				echo '<fieldset><input type="submit" value="Save settings" /></fieldset>';
                echo '<fieldset>';
                echo '<p>If you choose the static landing page, you must link to your project\'s list of languages - otherwise, visitors will not be able to navigate to the language folders:</p>';
                echo '<ul class="small"><li>['.BASE_PATH.id2short($projectID).'/'.cleanName($getProject3['name']).'/translate Start translating]</li></ul>';
                $otherProjects1 = "SELECT id, name FROM projects WHERE user = ".$getProject3['user'];
                $otherProjects2 = mysql_query($otherProjects1) or die(mysql_error());
                $otherProjectsList = '';
                while ($otherProjects3 = mysql_fetch_assoc($otherProjects2)) {
                    if ($otherProjects3['id'] != $projectID) {
                        $otherProjectsList .= '<li>['.BASE_PATH.id2short($otherProjects3['id']).'/'.cleanName($otherProjects3['name']).'/translate '.htmlspecialchars($otherProjects3['name']).']</li>';
                    }
                }
                if ($otherProjectsList != '') {
                    echo '<p>You can also link to your other projects:</p>';
                    echo '<ul class="small">'.$otherProjectsList.'</ul>';
                }
                echo '</fieldset>';
				echo '</form>';
				echo '</div>';
				echo '<div class="contentBox">';
				echo '<h1 id="head_project_import_xml" onclick="toggleDisplay(this);" class="clickable">Import an XML translation file</h1>';
				echo '<form id="body_project_import_xml" style="display:none;" action="/'.id2short($projectID).'/'.cleanName($getProject3['name']).'/translate" method="post" enctype="multipart/form-data" accept-charset="utf-8">';
				echo '<fieldset><label for="newFile">XML translations file</label><input type="hidden" name="MAX_FILE_SIZE" value="'.MAX_FILESIZE.'" /><input type="file" id="newFile" name="newFile" /></fieldset>';
				echo '<fieldset><label for="newLanguage">Language</label><select id="newLanguage" name="newLanguage" size="1">';
				echo '<option value="">-- Select here --</option>';
				foreach ($languages as $languageKey => $languageName) {
					echo '<option value="'.$languageKey.'">'.$languageName.'</option>';
				}
				echo '</select></fieldset>';
				echo '<fieldset><input type="submit" value="Import" onclick="return confirm(\'Are you sure you want to import this XML file and replace all translations that may already exist for this language?\');" /></fieldset>';
				echo '</form>';
				echo '</div>';
				echo '<div class="contentBox">';
				echo '<h1 id="head_project_export_xml" onclick="toggleDisplay(this);" class="clickable">Export all XML translation files</h1>';
				echo '<form id="body_project_export_xml" style="display:none;" action="/'.id2short($projectID).'/'.cleanName($getProject3['name']).'/translate" method="post" accept-charset="utf-8" onsubmit="return checkIdentName(document.getElementById(\'exportFilename\'), \'export filename\');">';
				echo '<fieldset><p>If you click &quot;Download&quot; below, a ZIP file will be generated for you that contains the translation files for all languages.</p></fieldset>';
                echo '<fieldset><label for="exportFilename">Filename of XML file (inside each language folder)</label><input id="exportFilename" name="exportFilename" value="strings" /></fieldset>';
				echo '<fieldset><input type="submit" name="exportDoStart" value="Download" onclick="return checkIdentName(document.getElementById(\'exportFilename\'), \'export filename\');" /></fieldset>';
				echo '</form>';
				echo '</div>';
				echo '<div class="contentBox">';
				echo '<h1 id="head_project_search" onclick="toggleDisplay(this);" class="clickable">Search project for phrases</h1>';
				echo '<form id="body_project_search" style="display:none;" action="/'.id2short($projectID).'/'.cleanName($getProject3['name']).'/translate" method="post" accept-charset="utf-8">';
                echo '<fieldset><label for="searchPhrase">Find phrase(s):</label><input id="searchPhrase" name="searchPhrase" value="" /></fieldset>';
				echo '<fieldset><input type="submit" name="searchDoStart" value="Search" /></fieldset>';
				echo '</form>';
				echo '</div>';
			}
			$isProjectVisible = isProjectVisible($getProject3['visibility'], $getProject3['user'], $projectID);
			if ($isProjectVisible == STATUS_VISIBILITY_SUCCESS) {
                if ($getProject3['landing_html'] == '' || isset($_GET['doTranslate'])) {
                    echo '<div class="contentBox">';
                    echo '<h1>'.htmlspecialchars($getProject3['name']).' &mdash; Translations</h1>';
                    echo '<table class="p50"><thead><tr>';
                    echo '<th>Language</th><th>Progress</th>';
                    echo '</tr></thead><tbody>';
                    $getProgress1 = "SELECT language, COUNT(*) AS translatedPhrases FROM translations WHERE project = ".$projectID." AND enabled = 1 GROUP BY language";
                    $getProgress2 = mysql_query($getProgress1);
                    $progressPerLanguage = array();
                    $maxTranslatedPhrases = 0;
                    while ($getProgress3 = mysql_fetch_assoc($getProgress2)) {
                        $progressPerLanguage[$getProgress3['language']] = $getProgress3['translatedPhrases'];
                        if ($getProgress3['language'] == $getProject3['default_language']) {
                            $maxTranslatedPhrases = $getProgress3['translatedPhrases'];
                        }
                    }
                    $languageList = '';
                    foreach ($languages as $languageKey => $languageName) {
                        $currentProgress = isset($progressPerLanguage[$languageKey]) ? round($progressPerLanguage[$languageKey]/$maxTranslatedPhrases*100) : 0;
                        if ($currentProgress < 2) { $currentProgress = 2; }
                        $languageItem = '<td><a href="/'.id2short($projectID).'/'.cleanName($getProject3['name']).'/'.$languageKey.'">'.$languageName.'</a></td><td><div class="progressBar" style="width:'.$currentProgress.'%;"></div></td>';
                        if ($languageKey == $getProject3['default_language']) {
                            echo '<tr style="background-color:#dfdfdf;">'.$languageItem.'</tr>';
                        }
                        else {
                            $languageList .= '<tr>'.$languageItem.'</tr>';
                        }
                    }
                    echo $languageList;
                    echo '</tbody></table>';
                    echo '<p style="margin:0; padding:0;">&nbsp;</p>'; // bottom-margin of table does not work otherwise (unknown why)
                    echo '</div>';
                }
                else {
                    echo '<div class="contentBox">';
                    echo '<h1>'.htmlspecialchars($getProject3['name']).' &mdash; Translations</h1>';
                    echo '<p>'.parseMarkup(htmlspecialchars($getProject3['landing_html'])).'</p>';
                    echo '</div>';
                }
			}
			else { // project not visible to this user
				echo '<div class="contentBox"><h1>Protected project</h1>';
				if ($isProjectVisible == STATUS_VISIBILITY_SIGNIN || ($isProjectVisible == STATUS_VISIBILITY_APPLY && $_SESSION['userID'] == -1)) {
					echo '<p class="info_message">Please sign in. This is not a public project.</p>';
					echo showLoginForm('/'.id2short($projectID).'/'.cleanName($getProject3['name']));
				}
				elseif ($isProjectVisible == STATUS_VISIBILITY_APPLY) {
					echo '<p class="info_message">Please apply for an invitation. This project requires translators to be invited.</p>';
					echo showInviteApplicationForm($projectID, $getProject3['name']);
				}
				elseif ($isProjectVisible == STATUS_VISIBILITY_APPLICATION_SENT) {
					echo '<p>Your application is under review.</p>';
				}
				elseif ($isProjectVisible == STATUS_VISIBILITY_REJECTED) {
					echo '<p>Your application has been rejected by the project owner.</p>';
				}
				else {
					throw new Exception('Unknown visibility response: '.$isProjectVisible);
				}
				echo '</div>';
			}
		}
	}
	else {
		echo '<div class="alert_message"><p>The selected project could not be found.</p></div>';
	}
}
else if ($_SESSION['userID'] != -1) {
	if (isset($_POST['projectName']) && isset($_POST['projectDefaultLanguage']) && isset($_POST['projectVisibility'])) {
		$projectName = mysql_real_escape_string(trim(strip_tags($_POST['projectName'])));
		$projectDefaultLanguage = mysql_real_escape_string(trim(strip_tags($_POST['projectDefaultLanguage'])));
		$projectVisibility = intval($_POST['projectVisibility']);
		switch ($projectVisibility) {
			case 1: $projectVisibilityKey = 'protected'; break;
			case 2: $projectVisibilityKey = 'private'; break;
			default: $projectVisibilityKey = 'public'; break;
		}
		if ($projectName != '' && $projectDefaultLanguage != '') {
			if ($_SESSION['userID'] != DEMO_USER_ID) {
				$sql1 = "INSERT INTO projects (name, user, default_language, visibility) VALUES ('".$projectName."', ".$_SESSION['userID'].", '".$projectDefaultLanguage."', '".$projectVisibilityKey."')";
				$sql2 = mysql_query($sql1);
				echo '<div class="alert_message"><p>Your new project has successfully been created.</p></div>';
			}
		}
		else {
			echo '<div class="alert_message"><p>Please enter a project name and choose the default language.</p></div>';
		}
	}
    if (isDeveloper()) {

        echo '<div class="contentBox">';
        echo '<h1>Your projects</h1>';
        $sql1 = "SELECT id, name, visibility FROM projects WHERE user = ".$_SESSION['userID'];
        $sql2 = mysql_query($sql1);
        if (mysql_num_rows($sql2) > 0) {
            echo '<ul>';
            while ($sql3 = mysql_fetch_assoc($sql2)) {
                echo '<li><a href="/'.id2short($sql3['id']).'/'.cleanName($sql3['name']).'">'.$sql3['name'].'</a> ('.getVisibilityTag($sql3['visibility']).')</li>';
            }
            echo '</ul>';
        }
        else {
            echo '<p class="info_message">You do not have any projects yet.<p>';
        }
        echo '</div>';

        echo '<div class="contentBox">';
        echo '<h1 id="head_project_add" onclick="toggleDisplay(this);" class="clickable">Add a new project</h1>';
        echo '<form id="body_project_add" style="display:none;" action="/" method="post" accept-charset="utf-8">';
        echo '<fieldset><label for="projectName">Project name</label><input type="text" id="projectName" name="projectName" /></fieldset>';
        echo '<fieldset><label for="projectDefaultLanguage">Default language</label><select id="projectDefaultLanguage" name="projectDefaultLanguage" size="1">';
        foreach ($languages as $languageKey => $languageName) {
            echo '<option value="'.$languageKey.'"'.($languageKey == 'values' ? ' selected="selected"' : '').'>'.$languageName.'</option>';
        }
        echo '</select></fieldset>';
        echo '<fieldset><label for="projectVisibility">Visibility</label><select id="projectVisibility" name="projectVisibility" size="1">';
        echo '<option value="0">'.getVisibilityTag('public', TRUE).'</option>';
        echo '<option value="1">'.getVisibilityTag('protected', TRUE).'</option>';
        echo '<option value="2">'.getVisibilityTag('private', TRUE).'</option>';
        echo '</select></fieldset>';
        echo '<fieldset><input type="submit" value="Create project" /></fieldset>';
        echo '</form>';
        echo '</div>';

    }
    else {

        echo '<div class="contentBox">';
        echo '<h1>My projects</h1>';
        $getMembership1 = "SELECT a.project, b.name FROM contributions AS a JOIN projects AS b ON a.project = b.id WHERE a.user = ".intval($_SESSION['userID'])." GROUP BY project";
        $getMembership2 = mysql_query($getMembership1);
        if (mysql_num_rows($getMembership2) > 0) {
            echo '<ul>';
            while ($getMembership3 = mysql_fetch_assoc($getMembership2)) {
                echo '<li><a href="/'.id2short($getMembership3['project']).'/'.cleanName($getMembership3['name']).'">'.htmlspecialchars($getMembership3['name']).'</a></li>';
            }
            echo '</ul>';
        }
        else {
            echo '<p class="info_message">You have not contributed to any projects yet.<p>';
        }
        echo '</div>';

        if (isset($_SESSION['userVisited']) && is_array($_SESSION['userVisited']) && count($_SESSION['userVisited']) > 0) {
            echo '<div class="contentBox">';
            echo '<h1>Recently visited</h1>';
            echo '<ul>';
            foreach ($_SESSION['userVisited'] as $visitedID => $visitedName) {
                echo '<li><a href="/'.id2short(intval($visitedID)).'/'.cleanName($visitedName).'">'.htmlspecialchars($visitedName).'</a></li>';
            }
            echo '</ul>';
            echo '</div>';
        }

    }
	$getAccountData1 = "SELECT username, join_time FROM users WHERE id = ".$_SESSION['userID'];
	$getAccountData2 = mysql_query($getAccountData1);
	if (mysql_num_rows($getAccountData2) == 1) {
		$getAccountData3 = mysql_fetch_assoc($getAccountData2);
		echo '<div class="contentBox">';
		echo '<h1>Your account</h1>';
		echo '<ul>';
		echo '<li>Username: '.htmlspecialchars($getAccountData3['username']).'</li>';
		echo '<li>Joined on: '.date('d.m.Y', $getAccountData3['join_time']).'</li>';
		echo '<li><a href="/_logout/'.$_SESSION['userID'].'">Sign out</a></li>';
		echo '</ul>';	
		echo '</div>';
	}
}
else {
	echo '<div class="contentBox">';
	echo '<h1>'.PAGE_TITLE.'</h1>';
	echo '<ul>';
	echo '<li>Easily set up project folders and manage your translations online</li>';
	echo '<li>Import existing translation XML files from your Android projects</li>';
	echo '<li>Export your collaboratively edited translations as Android-ready XML files</li>';
	echo '<li>Support for <span class="tagWord">string</span>, <span class="tagWord">string-array</span> and <span class="tagWord">plurals</span> elements</li>';
	echo '<li>Keep all translations for your projects in sync</li>';
	echo '<li>Three levels of visibility: <span class="tagWord">public</span>, <span class="tagWord">signed-in users</span> and <span class="tagWord">invite-only</span></li>';
	echo '<li>Convenient review system for suggested translations and applying translators</li>';
    echo '<li>Support for LTR languages (English, Spanish, French, etc.)</li>';
    echo '<li>Support for RTL languages (Arabian, Hebrew, Persian, etc.)</li>';
	echo '<li>'.count($languages).' supported languages</li>';
	echo '<li>Unlimited projects, phrases and contributors</li>';
	echo '<li>100% free and without restrictions</li>';
    echo '<li>Open Source</li>';
	echo '</ul>';
	echo '</div>';
	echo '<div class="contentBox">';
	echo '<h1>Sign in</h1>';
	echo showLoginForm('/');
	echo '</div>';
	echo '<div class="contentBox">';
	echo '<h1>Create a free account</h1>';
	echo '<form action="/" method="post" accept-charset="utf-8">';
    echo '<fieldset><label for="rAccountType">Account type</label><select id="rAccountType" name="rAccountType" size="1"><option value="">-- Please choose --</option><option value="translator">Translator (Supporter)</option><option value="developer">Developer (Project Host)</option></select></fieldset>';
	echo '<fieldset><label for="rUsername">Username</label><input type="text" id="rUsername" name="rUsername" /></fieldset>';
	echo '<fieldset><label for="rPassword">Password</label><input type="password" id="rPassword" name="rPassword" /></fieldset>';
	echo '<fieldset><label for="rPasswordVerify">Password (verification)</label><input type="password" id="rPasswordVerify" name="rPasswordVerify" /></fieldset>';
	echo '<fieldset><input class="form_email" type="text" name="email" /><input type="submit" value="Sign up" /></fieldset>';
	echo '</form>';
	echo '</div>';
}

?>
<div class="footBox" id="footBoxArea">
	<?php echo FOOTER_HTML; ?>
	<div class="clearBoth"></div>
</div>
</body>
</html>