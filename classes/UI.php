<?php

require_once('File_IO.php');
require_once('UI_Group.php');
require_once('UI_Container.php');
require_once('UI_Row.php');
require_once('UI_Cell.php');
require_once('UI_Heading.php');
require_once('UI_Paragraph.php');
require_once('UI_List.php');
require_once('UI_Form.php');
require_once('UI_Form_Radio.php');
require_once('UI_Form_Text.php');
require_once('UI_Form_Textarea.php');
require_once('UI_Form_Select.php');
require_once('UI_Form_Hidden.php');
require_once('UI_Form_File.php');
require_once('UI_Form_StaticText.php');
require_once('UI_Form_Button.php');
require_once('UI_Link.php');
require_once('UI_Table.php');
require_once('UI_Progress.php');
require_once('Time.php');
require_once('URL.php');
require_once(__DIR__.'/../libs/SimpleDiff.php');
require_once(__DIR__.'/../config.php');

abstract class UI {

    const ERROR_REPORTING_ON = CONFIG_ERROR_REPORTING_ON;
    const PAGE_INDEX = 1;
    const PAGE_DASHBOARD = 2;
    const PAGE_SIGN_UP = 3;
    const PAGE_CONTACT = 4;
    const PAGE_CREATE_PROJECT = 5;
    const PAGE_PROJECT = 6;
    const PAGE_REVIEW = 7;
    const PAGE_SETTINGS = 8;
    const PAGE_INVITATIONS = 9;
	const PAGE_HELP = 10;
    const PAGE_PHRASE = 11;

    private static $page;
    private static $actionPOST;
    private static $actionGET;
    private static $breadcrumbPath;
    private static $breadcrumbDisabled;

    abstract public function getHTML();

    public static function init($timezone) {
        mb_internal_encoding('utf-8');
        if (!empty($timezone)) {
            @date_default_timezone_set($timezone);
        }
        header('Content-type: text/html; charset=utf-8');
        header('Expires: Mon, 24 Mar 2008 00:00:00 GMT'); // prevent caching
        header('Cache-Control: no-cache'); // prevent caching

        if (self::ERROR_REPORTING_ON) {
            error_reporting(E_ALL);
            ini_set('display_errors', 'stdout');
        }
        else {
            error_reporting(0);
            ini_set('display_errors', 'stderr');
        }

        self::$page = isset($_GET['p']) ? trim($_GET['p']) : '';
        self::$actionPOST = isset($_POST) ? $_POST : array();
        self::$actionGET = isset($_GET) ? $_GET : array();
        self::$breadcrumbPath = array();
        self::$breadcrumbDisabled = false;
    }

    public static function isErrorReportingOn() {
        return self::ERROR_REPORTING_ON;
    }

    public static function getBreadcrumbPath() {
        return self::$breadcrumbPath;
    }

    public static function addBreadcrumbItem($href, $label) {
        self::$breadcrumbPath[] = array($href, $label);
    }

    public static function setBreadcrumbDisabled($disabled) {
        self::$breadcrumbDisabled = $disabled;
    }

    public static function isBreadcrumbDisabled() {
        return self::$breadcrumbDisabled;
    }

    public static function isPage($page) {
        return self::$page == $page;
    }

    public static function isAction($action) {
        return isset(self::$actionPOST[$action]);
    }

    public static function getDataPOST($action) {
        return isset(self::$actionPOST[$action]) ? self::$actionPOST[$action] : NULL;
    }

    public static function getDataGET($action) {
        return isset(self::$actionGET[$action]) ? self::$actionGET[$action] : NULL;
    }

    protected static function getHeader($isSignedIn) {
        $headerHTML = file_get_contents($isSignedIn ? 'templates/header_signed_in.html' : 'templates/header_signed_out.html');
        return sprintf(
            $headerHTML,
            URL::toResource('css/'),
            URL::toResource('js/'),
            URL::toResource('img/'),
            URL::toDashboard(),
            URL::toPage('settings'),
            URL::toPage('sign_out')
        );
    }

    protected static function getFooter() {
        $footerHTML = file_get_contents('templates/footer.html');
        return sprintf(
            $footerHTML,
            URL::toPage('help'),
			URL::toPage('contact'),
            URL::toResource('js/')
        );
    }

    protected static function showBreadcrumb() {
        $out = '';
        if (!self::isBreadcrumbDisabled()) {
            $out .= '<ol class="breadcrumb">';
            $breadcrumbItems = self::getBreadcrumbPath();
            $breadcrumbCount = count($breadcrumbItems);
            if ($breadcrumbCount == 0) {
                $out .= '<li class="active">Dashboard</li>';
            }
            else {
                $out .= '<li><a href="'.URL::toDashboard().'">Dashboard</a></li>';
                for ($b = 0; $b < $breadcrumbCount; $b++) {
                    $out .= ' <li';
                    if (($b+1) == $breadcrumbCount) {
                        $out .= ' class="active">'.$breadcrumbItems[$b][1];
                    }
                    else {
                        $out .= '><a href="'.$breadcrumbItems[$b][0].'">'.$breadcrumbItems[$b][1].'</a>';
                    }
                    $out .= '</li>';
                }
            }
            $out .= '</ol>';
        }
        return $out;
    }

    public static function getPage($pageID, $contents = array(), $containers = array()) {
        $out = self::getHeader(Authentication::isSignedIn());
        $content = self::findPage($pageID, $contents, $containers);
        $out .= self::showBreadcrumb(); // place after invocation of findPage() so that the breadcrumb navigation can be built there
        if ($content instanceof UI) {
            $out .= $content->getHTML();
        }
        else {
            throw new Exception('Result of getPageContent() must be an instance of class UI');
        }
        $out .= self::getFooter();
        return $out;
    }

    public static function findPage($pageID, $contents = array(), $containers = array()) {
        switch ($pageID) {
            case self::PAGE_INDEX:
                return self::getPage_Index($contents, $containers);
            case self::PAGE_DASHBOARD:
                return self::getPage_Dashboard($contents, $containers);
            case self::PAGE_SIGN_UP:
                return self::getPage_SignUp($contents, $containers);
            case self::PAGE_CONTACT:
                return self::getPage_Contact($contents, $containers);
            case self::PAGE_CREATE_PROJECT:
                return self::getPage_CreateProject($contents, $containers);
            case self::PAGE_PROJECT:
                return self::getPage_Project($contents, $containers);
            case self::PAGE_REVIEW:
                return self::getPage_Review($contents, $containers);
            case self::PAGE_SETTINGS:
                return self::getPage_Settings($contents, $containers);
            case self::PAGE_INVITATIONS:
                return self::getPage_Invitations($contents, $containers);
			case self::PAGE_HELP:
				return self::getPage_Help($contents, $containers);
            case self::PAGE_PHRASE:
                return self::getPage_Phrase($contents, $containers);
            default:
                throw new Exception('Unknown page ID '.$pageID);
        }
    }

    public static function getPage_Index($contents, $containers) {
        self::setBreadcrumbDisabled(true);
        $contents[] = new UI_Heading('Android&trade; Localization for Everyone');
        $contents[] = new UI_Paragraph('The free platform for the translation of Android apps.');
        $contents[] = new UI_Paragraph('Optimized for collaborative work &mdash; invite users to help you and manage contributions conveniently.');
        $contents[] = new UI_Paragraph('While Localize takes care of all the background work, you can concentrate on great apps and perfect translations.');
        $contents[] = new UI_Paragraph('Completely free, simple and open-source.');
        $contents[] = new UI_Paragraph('<a class="btn btn-success btn-lg" href="'.URL::toPage('sign_up').'">Create free account &raquo;</a>');
        $landingView = new UI_Container($contents, true);

        $featureColumn1 = new UI_Cell(array(
            new UI_Heading('Geared to Android', false, 2),
            new UI_Paragraph('Import XML files from your Android app and export a single ZIP file at the end &mdash; ready for deployment, containing all translations for every single language. Support for 83 languages, including LTR and RTL. Manage <abbr title="Resource type for single phrases">string</abbr>, <abbr title="Resource type for arrays of phrases">string-array</abbr> and <abbr title="Resource type for quantity strings">plurals</abbr> elements.')
        ), 4);
        $featureColumn2 = new UI_Cell(array(
            new UI_Heading('Free of charge', false, 2),
            new UI_Paragraph('Smaller teams and single developers don\'t always want to pay for their app translation. Create any number of projects with unlimited number of words and contributors. Control access to your project with two levels of visibility: '.Repository::getRepositoryVisibilityTag(Repository::VISIBILITY_PUBLIC).' and '.Repository::getRepositoryVisibilityTag(Repository::VISIBILITY_PRIVATE))
        ), 4);
        $featureColumn3 = new UI_Cell(array(
            new UI_Heading('Simple and convenient', false, 2),
            new UI_Paragraph('Fast translation of XML resources with our web-based editor. Collaborative platform that makes inviting contributors fast and efficient. Easily set up projects, manage all translations in one place and keep your localization files in sync.')
        ), 4);
        $featureRow = new UI_Row(array(
            $featureColumn1,
            $featureColumn2,
            $featureColumn3
        ));
        $featureList = new UI_Container(array($featureRow));

        $containers[] = $landingView;
        $containers[] = $featureList;

        $group = new UI_Group($containers);
        return $group;
    }

    public static function getPage_Dashboard($contents, $containers) {
        $createProjectButton = new UI_Link('Create new project', URL::toPage('create_project'), UI_Link::TYPE_SUCCESS);

        $projectList = Database::select("SELECT a.repositoryID, a.role, b.name, b.visibility, b.defaultLanguage FROM roles AS a JOIN repositories AS b ON a.repositoryID = b.id WHERE a.userID = ".intval(Authentication::getUserID())." ORDER BY b.name ASC");

        $contents[] = new UI_Heading('Dashboard', true);
        if (Authentication::isUserDeveloper()) {
            if (count($projectList) > 0) {
                $projectTable = new UI_Table(array('Project', 'Review', 'Default language', 'Visibility'));
                $projectTable->setColumnPriorities(4, 3, 3, 2);
                foreach ($projectList as $projectData) {
                    $linkedName = new UI_Link(htmlspecialchars($projectData['name']), URL::toProject($projectData['repositoryID']), UI_Link::TYPE_UNIMPORTANT);
                    $languageName = Language::getLanguageNameFull($projectData['defaultLanguage']);

                    $pendingEditsURL = URL::toReview($projectData['repositoryID']);
                    $pendingEdits = Database::getPendingEditsByRepositoryCount($projectData['repositoryID']);
                    $pendingEditsButton = $pendingEdits > 0 ? (new UI_Link($pendingEdits, $pendingEditsURL, UI_Link::TYPE_INFO)) : (new UI_Link($pendingEdits, $pendingEditsURL, UI_Link::TYPE_UNIMPORTANT, '', '', 'return false;'));

                    $pendingInvitationsURL = URL::toInvitations($projectData['repositoryID']);
                    $pendingInvitations = Database::getInvitationsByRepositoryCount($projectData['repositoryID']);
                    $pendingInvitationsButton = $pendingInvitations > 0 ? (new UI_Link($pendingInvitations, $pendingInvitationsURL, UI_Link::TYPE_INFO)) : (new UI_Link($pendingInvitations, $pendingInvitationsURL, UI_Link::TYPE_UNIMPORTANT, '', '', 'return false;'));

                    $projectTable->addRow(array($linkedName->getHTML(), $pendingEditsButton->getHTML().' '.$pendingInvitationsButton->getHTML(), $languageName, Repository::getRepositoryVisibilityTag($projectData['visibility'])));
                }
                $contents[] = new UI_Paragraph($createProjectButton);
                $contents[] = $projectTable;
            }
            else {
                $contents[] = new UI_Paragraph($createProjectButton);
                $contents[] = new UI_Paragraph('You have no projects yet. You may either host your own projects or contribute to other projects that have been shared with you.');
            }

            $cell = new UI_Cell($contents);
            $row = new UI_Row(array($cell));

            $containers[] = new UI_Container(array($row));
            return new UI_Group($containers);
        }
        else {
            $contents[] = new UI_Paragraph('Visit any project that you want to contribute to. Having signed in, you may now contribute to all public projects. For private projects, you must first apply for an invitation.');
            $contents[] = new UI_Paragraph('You should have received a project\'s link from its owners already. Just visit this link to start translating.');

            $cellIntroduction = new UI_Cell($contents);
            $rowIntroduction = new UI_Row(array($cellIntroduction));
            $containerIntroduction = new UI_Container(array($rowIntroduction));

            $contents = array();
            $contents[] = new UI_Heading('Recently visited', true, 3);
            $recentRepositories = Authentication::getCachedRepositories();
            if (empty($recentRepositories)) {
                $contents[] = new UI_Paragraph('You have not visited any projects recently.');
            }
            else {
                $listRecentlyVisited = new UI_List();
                foreach ($recentRepositories as $recentRepositoryID => $recentRepositoryName) {
                    $listRecentlyVisited->addItem(new UI_Link($recentRepositoryName, URL::toProject($recentRepositoryID)));
                }
                $contents[] = $listRecentlyVisited;
            }

            $cellVisited = new UI_Cell($contents);
            $rowVisited = new UI_Row(array($cellVisited));
            $containerVisited = new UI_Container(array($rowVisited));

            $contents = array();
            $contents[] = new UI_Heading('Contributed to', true, 3);
            $contributedRepositories = Database::getRepositoriesByContribution(Authentication::getUserID());
            if (empty($contributedRepositories)) {
                $contents[] = new UI_Paragraph('You have not contributed to any projects lately.');
            }
            else {
                $listRecentlyVisited = new UI_List();
                foreach ($contributedRepositories as $contributedRepository) {
                    $listRecentlyVisited->addItem(new UI_Link($contributedRepository['name'], URL::toProject($contributedRepository['repositoryID'])));
                }
                $contents[] = $listRecentlyVisited;
            }

            $cellContributed = new UI_Cell($contents);
            $rowContributed = new UI_Row(array($cellContributed));
            $containerContributed = new UI_Container(array($rowContributed));

            $contents = array();
            $contents[] = new UI_Heading('Latest invitation requests', true, 3);
            $invitationRequests = Database::getInvitationsByUser(Authentication::getUserID());
            if (empty($invitationRequests)) {
                $contents[] = new UI_Paragraph('You have not requested any invitations yet.');
            }
            else {
                $listInvitationRequests = new UI_List();
                foreach ($invitationRequests as $invitationRequest) {
                    $projectLink = new UI_Link($invitationRequest['name'], URL::toProject($invitationRequest['repositoryID']));
                    $listInvitationRequests->addItem($projectLink->getHTML().' ('.date('d.m.Y H:i', $invitationRequest['request_time']).') &raquo; <strong>'.Repository::getInvitationStatus($invitationRequest['accepted']).'</strong>');
                }
                $contents[] = $listInvitationRequests;
            }

            $cellInvitations = new UI_Cell($contents);
            $rowInvitations = new UI_Row(array($cellInvitations));
            $containerInvitations = new UI_Container(array($rowInvitations));

            $containers[] = $containerIntroduction;
            $containers[] = $containerVisited;
            $containers[] = $containerContributed;
            $containers[] = $containerInvitations;
            return new UI_Group($containers);
        }
    }

    public static function getPage_CreateProject($contents, $containers) {
        $currentPageURL = URL::toPage('create_project');
        $form = new UI_Form($currentPageURL, false);

        $repositoryID = self::validateID(self::getDataGET('project'), true);
        $repositoryData = Database::getRepositoryData($repositoryID);
        if (!empty($repositoryData)) {
            self::addBreadcrumbItem(URL::toProject($repositoryID), htmlspecialchars($repositoryData['name']));
            $isAllowed = Repository::hasUserPermissions(Authentication::getUserID(), $repositoryID, $repositoryData, Repository::ROLE_ADMINISTRATOR);
            if (!$isAllowed) {
                $repositoryData = NULL;
            }
        }
        self::addBreadcrumbItem($currentPageURL, 'Create project');

        if (!empty($repositoryData)) {
            $form->addContent(new UI_Form_StaticText('Public URL', Repository::getRepositoryShareURL($repositoryID), 'Share this URL with other people to let them contribute to your project.'));
        }

        $radioVisibility = new UI_Form_Radio('Visibility', 'create_project[visibility]');
        $radioVisibility->addOption(Repository::getRepositoryVisibilityTag(Repository::VISIBILITY_PUBLIC), Repository::VISIBILITY_PUBLIC);
        $radioVisibility->addOption(Repository::getRepositoryVisibilityTag(Repository::VISIBILITY_PRIVATE), Repository::VISIBILITY_PRIVATE);
        if (!empty($repositoryData)) {
            $radioVisibility->setDefaultOption($repositoryData['visibility']);
        }
        $form->addContent($radioVisibility);

        $textProjectName = new UI_Form_Text('Project name', 'create_project[name]', 'Enter your project\'s name', false, 'The public name of your project, shown in your dashboard and also shown to contributors');
        if (!empty($repositoryData)) {
            $textProjectName->setDefaultValue($repositoryData['name']);
        }
        $form->addContent($textProjectName);

        $defaultLanguageHelpText = 'The base language that you and your collaborators will translate from. This cannot be changed later and should be &quot;English&quot; in most cases.';
        if (empty($repositoryData)) { // default language can only be set when creating the project
            $selectDefaultLanguage = new UI_Form_Select('Default language', 'create_project[defaultLanguage]', $defaultLanguageHelpText);
            $languages = Language::getList();
            foreach ($languages as $language) {
                $languageLabel = Language::getLanguageNameFull($language);
                $selectDefaultLanguage->addOption($languageLabel, $language);
            }
            if (!empty($repositoryData)) {
                $selectDefaultLanguage->addDefaultOption($repositoryData['defaultLanguage']);
            }
            $form->addContent($selectDefaultLanguage);
        }
        else { // when editing the project just show the current setting
            $form->addContent(new UI_Form_StaticText('Default language', Language::getLanguageNameFull($repositoryData['defaultLanguage']), $defaultLanguageHelpText));
            $form->addContent(new UI_Form_Hidden('create_project[defaultLanguage]', $repositoryData['defaultLanguage']));
        }

        $buttonSubmit = new UI_Form_Button((empty($repositoryData) ? 'Create project' : 'Edit project'), UI_Form_Button::TYPE_SUCCESS);
        $buttonCancel = new UI_Link('Cancel', (empty($repositoryData) ? URL::toDashboard() : URL::toProject($repositoryID)), UI_Form_Button::TYPE_UNIMPORTANT);
        if (!empty($repositoryData)) {
            $form->addContent(new UI_Form_Hidden('create_project[editRepositoryID]', URL::encodeID($repositoryID)));
        }
        $form->addContent(new UI_Form_ButtonGroup(array($buttonSubmit, $buttonCancel)));

        $contents[] = new UI_Heading((empty($repositoryData) ? 'Create project' : 'Edit project'), true);
        $contents[] = $form;
        $cell = new UI_Cell($contents);
        $row = new UI_Row(array($cell));

        $containers[] = new UI_Container(array($row));
        return new UI_Group($containers);
    }

    public static function getPage_SignUp($contents, $containers) {
        $currentPageURL = URL::toPage('sign_up');
        self::addBreadcrumbItem($currentPageURL, 'Create free account');
        $form = new UI_Form($currentPageURL, false);

        if (Authentication::isAllowSignUpDevelopers()) {
            $radioType = new UI_Form_Radio('Account type', 'sign_up[type]');
            $radioType->addOption('Translator (Supporter)', User::TYPE_TRANSLATOR);
            $radioType->addOption('Developer (Project host)', User::TYPE_DEVELOPER);
            $form->addContent($radioType);
        }
        else {
            $form->addContent(new UI_Form_Hidden('sign_up[type]', User::TYPE_TRANSLATOR));
        }


        $textUsername = new UI_Form_Text('Username', 'sign_up[username]', 'Choose your username', false, 'You will need to enter your username when you want to sign in.');
        $form->addContent($textUsername);

        $textPassword1 = new UI_Form_Text('Password', 'sign_up[password1]', 'Type a strong password', true, 'You will need this password for signing in, along with your username.');
        $form->addContent($textPassword1);

        $textPassword2 = new UI_Form_Text('Password', 'sign_up[password2]', 'Repeat your password', true, 'Just to make sure you did not make any typing errors.');
        $form->addContent($textPassword2);

        $buttonSubmit = new UI_Form_Button('Sign up', UI_Form_Button::TYPE_SUCCESS);
        $buttonCancel = new UI_Link('Cancel', URL::toDashboard(), UI_Link::TYPE_UNIMPORTANT);
        $form->addContent(new UI_Form_ButtonGroup(array(
            $buttonSubmit,
            $buttonCancel
        )));

        $contents[] = new UI_Heading('Create free account', true);
        $contents[] = $form;
        $cell = new UI_Cell($contents);
        $row = new UI_Row(array($cell));

        $containers[] = new UI_Container(array($row));
        return new UI_Group($containers);
    }

    public static function getPage_Contact($contents, $containers) {
        self::addBreadcrumbItem(URL::toPage('contact'), 'Contact');
        $contents[] = new UI_Heading('Contact', true);
        $contents[] = new UI_Paragraph('<img src="'.URL::toResource('img/contact.php').'" alt="Contact" width="300">');
        $cell = new UI_Cell($contents);
        $row = new UI_Row(array($cell));

        $containers[] = new UI_Container(array($row));
        return new UI_Group($containers);
    }

    public static function getPage_Help($contents, $containers) {
        self::addBreadcrumbItem(URL::toPage('help'), 'Help');
        $contents[] = new UI_Heading('Help with Localization for Android', true);

		$contents[] = new UI_Heading('Embedding HTML in String resources and using it from Java', true, 3);
		$contents[] = new UI_Paragraph('<strong>How does escaping HTML for XML work?</strong><br />There are five special characters in XML which you have to escape if you do not want their special function. These are:<br /><code>\'</code> single quote<br /><code>&quot;</code> double quote<br /><code>&lt;</code> less-than<br /><code>&gt;</code> greater-than<br /><code>&amp;</code> ampersand<br />You can escape them either by wrapping them inside <code>&lt;![CDATA[...]]&gt;</code> or by replacing them with their respective XML entities.');
		$contents[] = new UI_Paragraph('<strong>Do I always have to escape my HTML inside XML?</strong><br />If you want to preserve the HTML, for example if you want to show the HTML code on screen, yes.<br />If you want to use HTML in order to style your text for use in TextViews, it depends, as there are two ways to do this:');
		$contents[] = new UI_Paragraph('<strong>Approach A: Leave HTML unescaped and call getText(...)</strong><br />You can embed your HTML code in XML without escaping it, but then you must call <code>getText(...)</code> instead of <code>getString(...)</code> from Java. The advantage is that this approach is very easy, you do not need any escaping and you can just as well reference the HTML strings from XML layout files without losing the text styling.');
		$contents[] = new UI_Paragraph('<strong>Approach B: Escape HTML and call Html.fromHtml(getText(...))</strong><br />If you escape your HTML by using CDATA sections or by escaping the single characters, you have to call <code>Html.fromHtml(getText(...))</code> from Java if you want to get the styled text.');
		$contents[] = new UI_Paragraph('<strong>Which HTML tags can I use?</strong><br />You can only be sure about <code>&lt;b&gt;</code>, <code>&lt;i&gt;</code> and <code>&lt;u&gt;</code>. These will (almost) always work. Others may work on some devices, but usually they do not.');
		$contents[] = new UI_Paragraph('<strong>What does that mean for me?</strong><br />Localize takes care of all the text processing and escaping for you. When exporting your translations to XML files, you can choose between approaches A (<code>getText(...)</code>) and B (<code>Html.fromHtml(getString(...))</code>) for your project.');
		$contents[] = new UI_Paragraph('<img src="'.URL::toResource('img/android/html_in_resources.png').'" alt="Embedding HTML in Android String resources and using it from Java" title="Embedding HTML in Android String resources and using it from Java" width="666">');

        $cell = new UI_Cell($contents);
        $row = new UI_Row(array($cell));

        $containers[] = new UI_Container(array($row));
        return new UI_Group($containers);
    }

    public static function getPage_Review($contents, $containers) {
        $repositoryID = self::validateID(self::getDataGET('project'), true);
        $languageID = self::validateID(self::getDataGET('language'), true);

        $repositoryData = Database::getRepositoryData($repositoryID);
        $languageData = Database::getLanguageData($languageID);

        if (empty($repositoryData)) {
            self::addBreadcrumbItem(URL::toProject($repositoryID), 'Project not found');
            $contents[] = new UI_Heading('Project not found', true);
            $contents[] = new UI_Paragraph('We\'re sorry, but we could not find the project that you requested.');
            $contents[] = new UI_Paragraph('Please check if you have made any typing errors.');
        }
        else {
            if (Authentication::getUserID() <= 0) {
                $contents[] = new UI_Heading(htmlspecialchars($repositoryData['name']), true);
                $contents[] = self::getLoginForm();
            }
            elseif (!Repository::hasUserPermissions(Authentication::getUserID(), $repositoryID, $repositoryData, Repository::ROLE_MODERATOR)) {
                $contents[] = new UI_Heading(htmlspecialchars($repositoryData['name']), true);
                $contents[] = new UI_Paragraph('Only administrators, developers and moderators of this project are allowed to review contributions.');
            }
            else {
                self::addBreadcrumbItem(URL::toReview($repositoryID), 'Review');
                if (empty($languageData)) { // review index page for this repository
                    $contents[] = new UI_Heading('Review contributions', true);

                    $table = new UI_Table(array('Language', 'Review'));
                    $table->setColumnPriorities(9, 3);
                    $pendingLanguages = Database::getPendingEditsByRepository($repositoryID);

                    if (count($pendingLanguages) > 0) {
                        foreach ($pendingLanguages as $pendingLanguage) {
                            $reviewURL = URL::toReviewLanguage($repositoryID, $pendingLanguage['languageID']);
                            $linkedName = new UI_Link(Language::getLanguageNameFull($pendingLanguage['languageID']), $reviewURL, UI_Link::TYPE_UNIMPORTANT);
                            $pendingCount = new UI_Link($pendingLanguage['COUNT(*)'], $reviewURL, UI_Link::TYPE_INFO);
                            $table->addRow(array(
                                $linkedName->getHTML(),
                                $pendingCount->getHTML()
                            ));
                        }
                    }
                    else {
                        $table->addRow(array('No pending contributions', 'No pending contributions'));
                    }

                    $contents[] = $table;
                }
                else { // single-language review details page for this repository
                    $currentPageURL = URL::toReviewLanguage($repositoryID, $languageID);
                    self::addBreadcrumbItem(htmlspecialchars($currentPageURL), Language::getLanguageNameFull($languageID));
                    $contents[] = new UI_Heading($languageData->getNameFull(), true);

                    $editData = Database::getPendingEdit($repositoryID, $languageID);
                    if (empty($editData)) { // no edits available for review (anymore)
                        UI::redirectToURL(URL::toReview($repositoryID));
                    }
                    else { // edits available to review
                        $form = new UI_Form(htmlspecialchars($currentPageURL), false);
                        $table = new UI_Table(array('', ''));
                        $table->setColumnPriorities(3, 9);
                        $contributorName = empty($editData[0]['real_name']) ? '<span class="text-muted">'.$editData[0]['username'].'</span>' : $editData[0]['real_name'].'<span class="text-muted"> ('.$editData[0]['username'].')</span>';

                        $buttonApprove = new UI_Form_Button('Approve', UI_Form_Button::TYPE_SUCCESS, UI_Form_Button::ACTION_SUBMIT, 'review[action]', 'approve');
                        $buttonReviewLater = new UI_Form_Button('Review later', UI_Form_Button::TYPE_UNIMPORTANT, UI_Form_Button::ACTION_SUBMIT, 'review[action]', 'reviewLater');
                        $buttonReject = new UI_Form_Button('Reject', UI_Form_Button::TYPE_WARNING, UI_Form_Button::ACTION_SUBMIT, 'review[action]', 'reject');
                        $buttonApproveAllByContributor = new UI_Form_Button('Approve all from this contributor', UI_Form_Button::TYPE_SUCCESS, UI_Form_Button::ACTION_SUBMIT, 'review[action]', 'approveAllFromThisContributor', 'return confirm(\'Are you sure you want to execute this batch operation?\');');
						$buttonRejectAllByContributor = new UI_Form_Button('Reject all from this contributor', UI_Form_Button::TYPE_DANGER, UI_Form_Button::ACTION_SUBMIT, 'review[action]', 'rejectAllFromThisContributor', 'return confirm(\'Are you sure you want to execute this batch operation?\');');

                        $actionButtons = new UI_Form_ButtonGroup(array(
                            $buttonApprove,
                            $buttonReviewLater,
                            $buttonReject
                        ), true);
                        $newValueEdit = new UI_Form_Textarea('', 'review[newValue]', $editData[0]['suggestedValue'], '', true, htmlspecialchars($editData[0]['suggestedValue']), UI_Form_Textarea::getOptimalRowCount($editData[0]['suggestedValue'], 2), Language::isLanguageRTL($languageID));

                        $referencedPhrase = Phrase::create(0, $editData[0]['phraseKey'], $editData[0]['payload']);

                        $previousPhraseData = Database::getPhrase($repositoryID, $languageID, $editData[0]['phraseKey']);
                        if (empty($previousPhraseData)) {
                            $previousPhrase = Phrase::create(0, $editData[0]['phraseKey'], $editData[0]['payload'], 0, true, true);
                        }
                        else {
                            $previousPhrase = Phrase::create(0, $editData[0]['phraseKey'], $previousPhraseData['payload']);
                        }

                        $valuesReference = $referencedPhrase->getPhraseValues();
                        $valueReference = isset($valuesReference[$editData[0]['phraseSubKey']]) ? trim($valuesReference[$editData[0]['phraseSubKey']]) : '';
                        $valuesPrevious = $previousPhrase->getPhraseValues();
                        $valuePrevious = isset($valuesPrevious[$editData[0]['phraseSubKey']]) ? trim($valuesPrevious[$editData[0]['phraseSubKey']]) : '';

                        $placeholdersReference = Phrase_Android::getPlaceholders($valueReference);

                        $pendingEditsLeftCount = Database::getPendingEditsByRepositoryAndLanguageCount($repositoryID, $languageID) - 1;
                        $pendingEditsLeft = $pendingEditsLeftCount == 0 ? 'only this one' : ($pendingEditsLeftCount == 1 ? '1 other' : $pendingEditsLeftCount.' others');

                        $phraseWithMarkedPlaceholders = Phrase::markPlaceholders(htmlspecialchars($valueReference), $placeholdersReference);

                        $table->addRow(array('<strong>'.Language::getLanguageNameFull($repositoryData['defaultLanguage']).'</strong>', '<span dir="'.(Language::isLanguageRTL($repositoryData['defaultLanguage']) ? 'rtl' : 'ltr').'">'.nl2br($phraseWithMarkedPlaceholders).'</span>'));
                        $table->addRow(array('<strong>Old value</strong>', '<span dir="'.(Language::isLanguageRTL($languageID) ? 'rtl' : 'ltr').'">'.nl2br(htmlspecialchars($valuePrevious)).'</span>'));
                        $table->addRow(array('<strong>Applied changes</strong>', '<span dir="'.(Language::isLanguageRTL($languageID) ? 'rtl' : 'ltr').'">'.nl2br(htmlDiff(htmlspecialchars($valuePrevious), htmlspecialchars($editData[0]['suggestedValue']))).'</span>'));
                        $table->addRow(array('<strong>New value</strong>', $newValueEdit->getHTML()));
                        $table->addRow(array('<strong>Submit time</strong>', date('d.m.Y H:i', $editData[0]['submit_time'])));
                        $table->addRow(array('<strong>Contributor</strong>', $contributorName));
                        $table->addRow(array('<strong>Edits left</strong>', $pendingEditsLeft));

                        $form->addContent(new UI_Form_Hidden('review[editID]', URL::encodeID($editData[0]['id'])));
                        $form->addContent(new UI_Form_Hidden('review[referenceValue]', $valueReference));
                        $form->addContent(new UI_Form_Hidden('review[phraseObject]', base64_encode(serialize($previousPhrase))));
                        $form->addContent(new UI_Form_Hidden('review[phraseKey]', htmlspecialchars($editData[0]['phraseKey'])));
                        $form->addContent(new UI_Form_Hidden('review[phraseSubKey]', htmlspecialchars($editData[0]['phraseSubKey'])));
                        $form->addContent(new UI_Form_Hidden('review[contributorID]', URL::encodeID($editData[0]['userID'])));

                        $form->addContent($actionButtons);
                        $form->addContent($table);
                        $form->addContent($actionButtons);
                        $form->addContent(new UI_Form_ButtonGroup(array($buttonApproveAllByContributor, $buttonRejectAllByContributor), true));

                        $contents[] = $form;
                    }
                }
            }
        }

        $cell = new UI_Cell($contents);
        $row = new UI_Row(array($cell));

        $containers[] = new UI_Container(array($row));
        return new UI_Group($containers);
    }

    public static function getPage_Invitations($contents, $containers) {
        $repositoryID = self::validateID(self::getDataGET('project'), true);
        $repositoryData = Database::getRepositoryData($repositoryID);

        if (empty($repositoryData)) {
            self::addBreadcrumbItem(URL::toProject($repositoryID), 'Project not found');
            $contents[] = new UI_Heading('Project not found', true);
            $contents[] = new UI_Paragraph('We\'re sorry, but we could not find the project that you requested.');
            $contents[] = new UI_Paragraph('Please check if you have made any typing errors.');
        }
        else {
            if (Authentication::getUserID() <= 0) {
                $contents[] = new UI_Heading(htmlspecialchars($repositoryData['name']), true);
                $contents[] = self::getLoginForm();
            }
            elseif (!Repository::hasUserPermissions(Authentication::getUserID(), $repositoryID, $repositoryData, Repository::ROLE_ADMINISTRATOR)) {
                $contents[] = new UI_Heading(htmlspecialchars($repositoryData['name']), true);
                $contents[] = new UI_Paragraph('Only administrators of this project are allowed to review invitation requests.');
            }
            else {
                $currentPageURL = URL::toInvitations($repositoryID);
                self::addBreadcrumbItem($currentPageURL, 'Invitations');
                $contents[] = new UI_Heading('Invitations', true);

                $invitationData = Database::getInvitationByRepository($repositoryID);
                if (empty($invitationData)) { // no invitations available for review (anymore)
                    UI::redirectToURL(URL::toDashboard());
                }
                else { // edits available to review
                    Time::init();

                    $form = new UI_Form(htmlspecialchars($currentPageURL), false);
                    $table = new UI_Table(array('', ''));
                    $table->setColumnPriorities(6, 6);

                    $buttonAccept = new UI_Form_Button('Accept', UI_Form_Button::TYPE_SUCCESS, UI_Form_Button::ACTION_SUBMIT, 'invitations[accept]', Repository::INVITATION_ACCEPTED);
                    $buttonDecline = new UI_Form_Button('Decline', UI_Form_Button::TYPE_WARNING, UI_Form_Button::ACTION_SUBMIT, 'invitations[accept]', Repository::INVITATION_DECLINED);
                    $actionButtons = new UI_Form_ButtonGroup(array(
                        $buttonAccept,
                        $buttonDecline
                    ), true);

                    $invitationRoleSelect = new UI_Form_Select('', 'invitations[role]', '', false, '', '', '', true);
                    $invitationRoleSelect->addOption(Repository::getRoleName(Repository::ROLE_CONTRIBUTOR), Repository::ROLE_CONTRIBUTOR);
                    $invitationRoleSelect->addOption(Repository::getRoleName(Repository::ROLE_MODERATOR), Repository::ROLE_MODERATOR);
                    $invitationRoleSelect->addOption(Repository::getRoleName(Repository::ROLE_DEVELOPER), Repository::ROLE_DEVELOPER);
                    $invitationRoleSelect->addOption(Repository::getRoleName(Repository::ROLE_ADMINISTRATOR), Repository::ROLE_ADMINISTRATOR);

                    $table->addRow(array('<strong>Assigned role</strong>', $invitationRoleSelect->getHTML()));
                    $table->addRow(array('<strong>Username</strong>', $invitationData[0]['username']));
                    $table->addRow(array('<strong>Real name</strong>', (empty($invitationData[0]['real_name']) ? '&mdash;' : $invitationData[0]['real_name'])));
                    $table->addRow(array('<strong>Country</strong>', (empty($invitationData[0]['localeCountry']) ? '&mdash;' : Time::getCountryName($invitationData[0]['localeCountry'], '&mdash;'))));
                    $table->addRow(array('<strong>Request date</strong>', date('d.m.Y H:i', $invitationData[0]['request_time'])));
                    $table->addRow(array('<strong>Sign-up date</strong>', date('d.m.Y H:i', $invitationData[0]['join_date'])));
                    $table->addRow(array('<strong>Last sign-in</strong>', date('d.m.Y H:i', $invitationData[0]['last_login'])));

                    $form->addContent(new UI_Form_Hidden('invitations[userID]', URL::encodeID($invitationData[0]['userID'])));

                    $form->addContent($actionButtons);
                    $form->addContent($table);
                    $form->addContent($actionButtons);

                    $contents[] = $form;
                }
            }
        }

        $cell = new UI_Cell($contents);
        $row = new UI_Row(array($cell));

        $containers[] = new UI_Container(array($row));
        return new UI_Group($containers);
    }

    public static function getPage_Settings($contents, $containers) {
        $currentPageURL = URL::toPage('settings');
        UI::addBreadcrumbItem($currentPageURL, 'Settings');
        $contents[] = new UI_Heading('Settings', true);
        $form = new UI_Form($currentPageURL, false);

        $form->addContent(new UI_Form_StaticText('Username', Authentication::getUserName()));
        $textRealName = new UI_Form_Text('Real name', 'settings[realName]', 'Enter your name here', false, 'Let others know who you are, so that they know who is contributing to their projects.');
        $textRealName->setDefaultValue(Authentication::getUserRealName());
        $form->addContent($textRealName);
        $selectNativeLanguage = new UI_Form_Select('Native language', 'settings[nativeLanguage][]', 'Which language is your native language? You may select multiple entries here.', true);
        $userNativeLanguages = Database::getNativeLanguages(Authentication::getUserID());
        $languages = Language::getList();
        foreach ($languages as $languageID) {
            $selectNativeLanguage->addOption(Language::getLanguageNameFull($languageID), $languageID);
        }
        foreach ($userNativeLanguages as $userNativeLanguage) {
            $selectNativeLanguage->addDefaultOption($userNativeLanguage);
        }
        Time::init();
        /** @var array|UI_Form_Select[] $selectTimezones */
        $selectTimezones = array();
        $selectCountry = new UI_Form_Select('Country', 'settings[country]', 'Choose your country of residence to control the timezone selection below.', false, '', '', 'chooseTimezoneByCountry(this.value);');
        $selectCountry->addOption('- Please choose -', '');
        $countries = Time::getCountries();
        $defaultCountry = Authentication::getUserCountry();
        $defaultTimezone = Authentication::getUserTimezone();
        foreach ($countries as $countryCode => $countryName) {
            $selectCountry->addOption($countryName, $countryCode);
            $timezones = Time::getTimezones($countryCode);
            $selectTimezones[$countryCode] = new UI_Form_Select('Timezone', 'settings[timezone]['.$countryCode.']', 'Set your timezone here to determine how dates are displayed for you.', false, 'timezone-select timezone-select-'.$countryCode, ($countryCode == $defaultCountry ? '' : 'display:none;'));
            foreach ($timezones as $timezoneName) {
                $selectTimezones[$countryCode]->addOption($timezoneName, $timezoneName);
            }
            if ($countryCode == $defaultCountry) {
                $selectTimezones[$countryCode]->addDefaultOption($defaultTimezone);
            }
        }
        $selectCountry->addDefaultOption($defaultCountry);

        $form->addContent($selectNativeLanguage);
        $form->addContent($selectCountry);
        foreach ($selectTimezones as $selectTimezone) {
            $form->addContent($selectTimezone);
        }
        $form->addContent(new UI_Form_ButtonGroup(array(
            new UI_Form_Button('Save'),
            new UI_Link('Cancel', URL::toDashboard(), UI_Form_Button::TYPE_UNIMPORTANT)
        )));
        $contents[] = $form;

        $cell = new UI_Cell($contents);
        $row = new UI_Row(array($cell));

        $containers[] = new UI_Container(array($row));
        return new UI_Group($containers);
    }

    public static function getPage_Phrase($contents, $containers) {
        $repositoryID = self::validateID(self::getDataGET('project'), true);
        $languageID = self::validateID(self::getDataGET('language'), true);
        $phraseID = self::validateID(self::getDataGET('phrase'), true);

        $repositoryData = Database::getRepositoryData($repositoryID);
        $phraseData = Database::getPhraseData($repositoryID, $phraseID);

        if (empty($repositoryData) || empty($phraseData)) {
            self::addBreadcrumbItem(URL::toProject($repositoryID), 'Phrase not found');
            $contents[] = new UI_Heading('Phrase not found', true);
            $contents[] = new UI_Paragraph('We\'re sorry, but we could not find the phrase that you requested.');
            $contents[] = new UI_Paragraph('Please check if you have made any typing errors.');
        }
        else {
            self::addBreadcrumbItem(URL::toProject($repositoryID), htmlspecialchars($repositoryData['name']));
            self::addBreadcrumbItem(URL::toLanguage($repositoryID, $languageID), Language::getLanguageNameFull($languageID));
            Authentication::saveCachedRepository($repositoryID, $repositoryData['name']);

            $repository = new Repository($repositoryID, $repositoryData['name'], $repositoryData['visibility'], $repositoryData['defaultLanguage']);
            $role = Database::getRepositoryRole(Authentication::getUserID(), $repositoryID);
            $permissions = $repository->getPermissions(Authentication::getUserID(), $role);

            if (Authentication::getUserID() <= 0) {
                $contents[] = new UI_Heading(htmlspecialchars($repositoryData['name']), true);
                $contents[] = self::getLoginForm();
            }
            elseif ($permissions->isInvitationMissing()) {
                $contents[] = new UI_Heading(htmlspecialchars($repositoryData['name']), true);
                $contents[] = self::getInvitationForm($repositoryID);
            }
            else {
                $mayMovePhrases = Repository::isRoleAllowedToMovePhrases($role);
                $currentPageURL = URL::toPhraseDetails($repositoryID, $languageID, $phraseID);
                self::addBreadcrumbItem($currentPageURL, $phraseData['phraseKey']);

                $heading = new UI_Heading('Phrase: '.$phraseData['phraseKey'], true);

                $phraseObject = Phrase::create($phraseID, $phraseData['phraseKey'], $phraseData['payload']);
                $phraseObjectEntries = $phraseObject->getPhraseValues();
                $phraseEntries = new UI_List();
                foreach ($phraseObjectEntries as $phraseObjectEntry) {
                    $phraseEntries->addItem(htmlspecialchars($phraseObjectEntry));
                }

                if ($mayMovePhrases) {
                    $formButtonList = array(
                        new UI_Form_Button('Remove translations', UI_Form_Button::TYPE_WARNING, UI_Form_Button::ACTION_SUBMIT, 'phraseChange[action]', 'untranslate', 'return confirm(\'Are you sure you want to remove all translations for this phrase and keep the default language entry?\');'),
                        new UI_Form_Button('Delete phrase', UI_Form_Button::TYPE_DANGER, UI_Form_Button::ACTION_SUBMIT, 'phraseChange[action]', 'delete', 'return confirm(\'Are you sure you want to delete the phrase from your project completely?\');'),
                        new UI_Link('Cancel', URL::toLanguage($repositoryID, $languageID), UI_Link::TYPE_UNIMPORTANT)
                    );
                }
                else {
                    $formButtonList = NULL;
                }

                $form = new UI_Form($currentPageURL, false);
                $form->addContent(new UI_Form_Hidden('phraseChange[phraseKey]', $phraseData['phraseKey']));
                if (isset($formButtonList)) {
                    $form->addContent(new UI_Form_ButtonGroup($formButtonList, true));
                }

                $contents[] = $heading;
                $contents[] = $phraseEntries;
                $contents[] = $form;
            }
        }

        $cell = new UI_Cell($contents);
        $row = new UI_Row(array($cell));

        $containers[] = new UI_Container(array($row));
        return new UI_Group($containers);
    }

    public static function getPage_Project($contents, $containers) {
        $page = self::getDataGET('p');
        $repositoryID = self::validateID(self::getDataGET('project'), true);
        $languageID = self::validateID(self::getDataGET('language'), true);
        $isAddingMode = isset($page) && $page == 'add_phrase';
        $isExportMode = isset($page) && $page == 'export';
        $isImportMode = isset($page) && $page == 'import';

        $repositoryData = Database::getRepositoryData($repositoryID);
        $languageData = Database::getLanguageData($languageID);

        if (empty($repositoryData)) {
            self::addBreadcrumbItem(URL::toProject($repositoryID), 'Project not found');
            $contents[] = new UI_Heading('Project not found', true);
            $contents[] = new UI_Paragraph('We\'re sorry, but we could not find the project that you requested.');
            $contents[] = new UI_Paragraph('Please check if you have made any typing errors.');
        }
        else {
            self::addBreadcrumbItem(URL::toProject($repositoryID), htmlspecialchars($repositoryData['name']));
            Authentication::saveCachedRepository($repositoryID, $repositoryData['name']);

            $repository = new Repository($repositoryID, $repositoryData['name'], $repositoryData['visibility'], $repositoryData['defaultLanguage']);
            $role = Database::getRepositoryRole(Authentication::getUserID(), $repositoryID);
            $permissions = $repository->getPermissions(Authentication::getUserID(), $role);

            if (Authentication::getUserID() <= 0) {
                $contents[] = new UI_Heading(htmlspecialchars($repositoryData['name']), true);
                $contents[] = self::getLoginForm();
            }
            elseif ($permissions->isInvitationMissing()) {
                $contents[] = new UI_Heading(htmlspecialchars($repositoryData['name']), true);
                $contents[] = self::getInvitationForm($repositoryID);
            }
            else {
                $defaultLanguage = new Language_Android($repository->getDefaultLanguage());
                if ($isAddingMode) {
                    $formTargetURL = URL::toAddPhrase($repositoryID, $languageID);
                    self::addBreadcrumbItem($formTargetURL, 'Add phrase');

                    $form = new UI_Form($formTargetURL, false);

                    $radioType = new UI_Form_Radio('Phrase type', 'add_phrase[type]');
                    $radioType->addOption('<abbr title="Resource type for single phrases">string</abbr>', 1, 'addPhraseTypeSelect(\'addPhraseGroup_String\');');
                    $radioType->addOption('<abbr title="Resource type for arrays of phrases">string-array</abbr>', 2, 'addPhraseTypeSelect(\'addPhraseGroup_StringArray\');');
                    $radioType->addOption('<abbr title="Resource type for quantity strings">plurals</abbr>', 3, 'addPhraseTypeSelect(\'addPhraseGroup_Plurals\');');
                    $form->addContent($radioType);

                    $textUsername = new UI_Form_Text('Key', 'add_phrase[key]', 'Unique identifier', false, 'This is the short string that you\'ll identify the phrase(s) with later.');
                    $form->addContent($textUsername);

                    $textUsername = new UI_Form_Textarea('String', 'add_phrase[string]', 'String for '.$defaultLanguage->getNameFull(), 'You can later translate this string to other languages.', false, '', 2, $defaultLanguage->isRTL(), '', 'addPhraseGroup_String');
                    $form->addContent($textUsername);

                    $textPassword1 = new UI_Form_Textarea('Item', 'add_phrase[string_array][]', 'Item for '.$defaultLanguage->getNameFull(), 'You can later translate this item to other languages.', false, '', 2, $defaultLanguage->isRTL(), '', 'addPhraseGroup_StringArray', 'display:none;', false);
                    $form->addContent($textPassword1);

                    $quantities = Phrase_Android_Plurals::getList();
                    foreach ($quantities as $quantity) {
                        $textPassword2 = new UI_Form_Textarea($quantity, 'add_phrase[plurals]['.$quantity.']', 'Quantity for '.$defaultLanguage->getNameFull(), 'You can later translate this quantity to other languages.', false, '', 2, $defaultLanguage->isRTL(), '', 'addPhraseGroup_Plurals', 'display:none;');
                        $form->addContent($textPassword2);
                    }

                    $buttonSubmit = new UI_Form_Button('Save phrase(s)', UI_Form_Button::TYPE_SUCCESS);
                    $buttonAddItem = new UI_Link('Add item', '#', UI_Form_Button::TYPE_INFO, 'addPhraseGroup_StringArray', 'display:none;', 'addPhraseAddItem(\'add_phrase[string_array][]\'); return false;');
                    $buttonCancel = new UI_Link('Cancel', URL::toLanguage($repositoryID, $languageID), UI_Form_Button::TYPE_UNIMPORTANT);
                    $form->addContent(new UI_Form_ButtonGroup(array(
                        $buttonSubmit,
                        $buttonAddItem,
                        $buttonCancel
                    )));

                    $contents[] = new UI_Heading('Add phrase to default language', true);
                    $contents[] = $form;
                }
                elseif ($isExportMode) {
                    $formTargetURL = URL::toExport($repositoryID);
                    self::addBreadcrumbItem($formTargetURL, 'Export XML');

                    $form = new UI_Form($formTargetURL, false);

                    $textFilename = new UI_Form_Text('Filename', 'export[filename]', 'strings.xml', false, 'Please choose a name for the XML files that will be exported inside each language folder.');
                    $textFilename->setDefaultValue('strings.xml');
                    $form->addContent($textFilename);

                    $selectGroupID = new UI_Form_Select('Phrase groups', 'export[groupID]', 'Do you want to export <em>all</em> phrases or only a single group?');
                    $selectGroupID->addOption('- All groups -', Phrase::GROUP_ALL);
                    $selectGroupID->addOption('(Default group)', Phrase::GROUP_NONE);
                    $form->addContent($selectGroupID);

                    $selectHtmlEscaping = new UI_Form_Select('HTML Escaping', 'export[htmlEscaping]', 'Which Java method do you want to use to get HTML-styled strings from your translations? (<a href="'.URL::toPage('help').'">Help</a>)');
                    $selectHtmlEscaping->addOption('- Please choose -', File_IO::HTML_ESCAPING_NONE);
                    $selectHtmlEscaping->addOption('getText(...)', File_IO::HTML_ESCAPING_GETTEXT);
                    $selectHtmlEscaping->addOption('Html.fromHtml(getString(...))', File_IO::HTML_ESCAPING_HTML_FROMHTML);
                    $selectHtmlEscaping->addOption('I don\'t care', File_IO::HTML_ESCAPING_GETTEXT);
                    $form->addContent($selectHtmlEscaping);

                    $isAdmin = Repository::hasUserPermissions(Authentication::getUserID(), $repositoryID, $repositoryData, Repository::ROLE_ADMINISTRATOR);

                    $buttonSubmit = new UI_Form_Button('Export XML', UI_Form_Button::TYPE_SUCCESS);
                    $buttonManageGroups = new UI_Link('Manage groups', URL::toEditProject($repositoryID), UI_Form_Button::TYPE_UNIMPORTANT);
                    $buttonCancel = new UI_Link('Cancel', URL::toProject($repositoryID), UI_Form_Button::TYPE_UNIMPORTANT);
                    if ($isAdmin) {
                        $form->addContent(new UI_Form_ButtonGroup(array(
                            $buttonSubmit,
                            $buttonManageGroups,
                            $buttonCancel
                        )));
                    }
                    else {
                        $form->addContent(new UI_Form_ButtonGroup(array(
                            $buttonSubmit,
                            $buttonCancel
                        )));
                    }

                    $contents[] = new UI_Heading('Export XML', true);
                    $contents[] = $form;
                }
                elseif ($isImportMode) {
                    $formTargetURL = URL::toImport($repositoryID);
                    self::addBreadcrumbItem($formTargetURL, 'Import XML');

                    $form = new UI_Form($formTargetURL, false, true);

                    $radioOverwrite = new UI_Form_Radio('Overwrite', 'import[overwrite]');
                    $radioOverwrite->addOption('<strong>Import only new phrases</strong> and do not overwrite any existing phrases', 0);
                    $radioOverwrite->addOption('<strong>Import all phrases</strong> and overwrite any phrases that do already exist', 1);
                    $form->addContent($radioOverwrite);

                    $selectLanguage = new UI_Form_Select('Language', 'import[languageID]', 'Which language do you want to import the phrases for?');
                    $selectLanguage->addOption('- Please choose -', 0);
                    $languageIDs = Language::getList();
                    foreach ($languageIDs as $languageID) {
                        $selectLanguage->addOption(Language::getLanguageNameFull($languageID), $languageID);
                    }
                    $form->addContent($selectLanguage);

                    $selectGroupID = new UI_Form_Select('Phrase group', 'import[groupID]', 'Which group do you want to import the phrases to?');
                    $selectGroupID->addOption('(Default group)', Phrase::GROUP_NONE);
                    $form->addContent($selectGroupID);

                    $fileSizeHidden = new UI_Form_Hidden('MAX_FILE_SIZE', File_IO::getMaxFileSize());
                    $form->addContent($fileSizeHidden);

                    $fileXML = new UI_Form_File('XML file', 'importFileXML', 'The XML resources file that you want to extract the phrases from.');
                    $form->addContent($fileXML);

                    $isAdmin = Repository::hasUserPermissions(Authentication::getUserID(), $repositoryID, $repositoryData, Repository::ROLE_ADMINISTRATOR);

                    $buttonSubmit = new UI_Form_Button('Import XML', UI_Form_Button::TYPE_SUCCESS);
                    $buttonManageGroups = new UI_Link('Manage groups', URL::toEditProject($repositoryID), UI_Form_Button::TYPE_UNIMPORTANT);
                    $buttonCancel = new UI_Link('Cancel', URL::toProject($repositoryID), UI_Form_Button::TYPE_UNIMPORTANT);
                    if ($isAdmin) {
                        $form->addContent(new UI_Form_ButtonGroup(array(
                            $buttonSubmit,
                            $buttonManageGroups,
                            $buttonCancel
                        )));
                    }
                    else {
                        $form->addContent(new UI_Form_ButtonGroup(array(
                            $buttonSubmit,
                            $buttonCancel
                        )));
                    }

                    $contents[] = new UI_Heading('Import XML', true);
                    $contents[] = $form;
                }
                elseif (empty($languageData)) {
                    $heading = new UI_Heading(htmlspecialchars($repositoryData['name']), true, 1, $repository->getShareURL());

                    $languageTable = new UI_Table(array('Language', 'Completion'));
                    $languageTable->setColumnPriorities(8, 4);
                    $languages = Language::getList($defaultLanguage->getID());

                    $cachedLanguageProgress = Authentication::getCachedLanguageProgress($repositoryID);
                    $newCachedLanguageProgress = array();
                    if (empty($cachedLanguageProgress)) {
                        $repository->loadLanguages(false, Repository::SORT_NO_LANGUAGE, Repository::LOAD_ALL_LANGUAGES);
                    }

                    foreach ($languages as $languageID) {
                        $linkURL = URL::toLanguage($repositoryID, $languageID);
                        $nameLink = new UI_Link(Language::getLanguageNameFull($languageID), $linkURL, UI_Link::TYPE_UNIMPORTANT);
                        if (empty($cachedLanguageProgress)) {
                            $languageObject = $repository->getLanguage($languageID);
                            $completeness = intval($languageObject->getCompleteness()*100);
                        }
                        else {
                            $completeness = intval($cachedLanguageProgress[$languageID]);
                        }
                        $progressBar = new UI_Progress($completeness);
                        $rowClass = ($languageID == $defaultLanguage->getID() ? 'active' : '');
                        $languageTable->addRow(array(
                            $nameLink->getHTML(),
                            $progressBar->getHTML()
                        ), '', $rowClass);
                        $newCachedLanguageProgress[$languageID] = $completeness;
                    }

                    Authentication::setCachedLanguageProgress($repositoryID, $newCachedLanguageProgress);

                    $actionsForm = new UI_Form(URL::toProject($repositoryID), false);
                    $buttonExport = new UI_Link('Export XML', URL::toExport($repositoryID), UI_Form_Button::TYPE_SUCCESS);
                    $buttonImport = new UI_Link('Import XML', URL::toImport($repositoryID), UI_Form_Button::TYPE_UNIMPORTANT);
                    $buttonEdit = new UI_Link('Edit project', URL::toEditProject($repositoryID), UI_Form_Button::TYPE_UNIMPORTANT);
                    $actionsForm->addContent(new UI_Form_Hidden('exportXML', 1));

                    $isAdmin = Repository::hasUserPermissions(Authentication::getUserID(), $repositoryID, $repositoryData, Repository::ROLE_ADMINISTRATOR);
                    $isDev = Repository::hasUserPermissions(Authentication::getUserID(), $repositoryID, $repositoryData, Repository::ROLE_DEVELOPER);
                    if ($isAdmin) {
                        $actionButtons = array(
                            $buttonExport,
                            $buttonImport,
                            $buttonEdit
                        );
                    }
                    elseif ($isDev) {
                        $actionButtons = array(
                            $buttonExport,
                            $buttonImport
                        );
                    }
                    else {
                        $actionButtons = array();
                    }
                    if (!empty($actionButtons)) {
                        $actionsForm->addContent(new UI_Form_ButtonGroup($actionButtons, true));
                    }

                    $contents[] = $heading;
                    $contents[] = $actionsForm;
                    $contents[] = $languageTable;
                }
                else {
                    $mayMovePhrases = Repository::isRoleAllowedToMovePhrases($role);
                    $language = new Language_Android($languageID);
                    self::addBreadcrumbItem(URL::toLanguage($repositoryID, $languageID), $language->getNameFull());

                    $heading = new UI_Heading($languageData->getNameFull(), true);

                    $repository->loadLanguages(false, $languageID, $languageID);
                    $languageLeft = $repository->getLanguage($repository->getDefaultLanguage());
                    $languageRight = $repository->getLanguage($language->getID());

                    $languageLeftPhrases = $languageLeft->getPhrases();
                    $languageRightPhrases = $languageRight->getPhrases();
                    if (count($languageLeftPhrases) != count($languageRightPhrases)) {
                        throw new Exception('Count of left language\'s phrases does not match right language\'s count of phrases');
                    }
                    if ($language->getID() == $defaultLanguage->getID()) { // viewing the default language itself
                        $phrasesTable = new UI_Table(array('Unique key', $language->getNameFull()));
                        $phrasesTable->setColumnPriorities(6, 6);
                    }
                    else { // viewing another language that will be compared to default language
                        $phrasesTable = new UI_Table(array($defaultLanguage->getNameFull(), $language->getNameFull()));
                        $phrasesTable->setColumnPriorities(6, 6);
                    }
                    if (count($languageLeftPhrases) <= 0) {
                        $phrasesTable->addRow(array(
                            'No phrases yet',
                            'No phrases yet'
                        ));
                    }
                    else {
                        if ($language->getID() == $defaultLanguage->getID()) { // viewing the default language itself
                            /** @var Phrase $defaultPhrase */
                            foreach ($languageLeftPhrases as $defaultPhrase) {
                                $values = $defaultPhrase->getPhraseValues();
                                foreach ($values as $subKey => $value) {
                                    $phraseKey = $defaultPhrase->getPhraseKey();
                                    $phraseKeyName = $phraseKey;
                                    if ($defaultPhrase instanceof Phrase_Android_StringArray) {
                                        $phraseKeyName .= ' » ['.$subKey.']';
                                    }
                                    elseif ($defaultPhrase instanceof Phrase_Android_Plurals) {
                                        $phraseKeyName .= ' » '.$subKey;
                                    }

                                    $phraseFormKey = 'updatePhrases[edits]['.URL::encodeID($defaultPhrase->getID()).']['.$subKey.']';
                                    $value = Authentication::getCachedEdit($repositoryID, $languageID, URL::encodeID($defaultPhrase->getID()), $subKey, $value);

                                    $valuePrevious = new UI_Form_Hidden(str_replace('[edits]', '[previous]', $phraseFormKey), $value);
                                    $valueEdit = new UI_Form_Textarea('', $phraseFormKey, $value, '', true, htmlspecialchars($value), UI_Form_Textarea::getOptimalRowCount($value, 2), $defaultLanguage->isRTL());

                                    $phraseKeyLink = new UI_Link($phraseKeyName, URL::toPhraseDetails($repositoryID, $languageID, $defaultPhrase->getID()));

                                    $phrasesTable->addRow(array(
                                        $phraseKeyLink->getHTML(),
                                        $valuePrevious->getHTML().$valueEdit->getHTML()
                                    ));
                                }
                            }
                        }
                        else { // viewing another language that will be compared to default language
                            foreach ($languageRightPhrases as $rightPhrase) {
                                /** @var Phrase $rightPhrase */
                                $defaultPhrase = $languageLeft->getPhraseByKey($rightPhrase->getPhraseKey());
                                $valuesLeft = $defaultPhrase->getPhraseValues();
                                $valuesRight = $rightPhrase->getPhraseValues();
                                foreach ($valuesLeft as $subKey => $valueLeft) {
                                    $valueLeft = '<span dir="'.($defaultLanguage->isRTL() ? 'rtl' : 'ltr').'">'.nl2br(htmlspecialchars($valueLeft)).'</span>';
                                    $phraseKey = 'updatePhrases[edits]['.URL::encodeID($defaultPhrase->getID()).']['.$subKey.']';
                                    $valuesRight[$subKey] = Authentication::getCachedEdit($repositoryID, $languageID, URL::encodeID($defaultPhrase->getID()), $subKey, $valuesRight[$subKey]);

                                    $valuePrevious = new UI_Form_Hidden(str_replace('[edits]', '[previous]', $phraseKey), $valuesRight[$subKey]);
                                    $valueEdit = new UI_Form_Textarea('', $phraseKey, $valuesRight[$subKey], '', true, htmlspecialchars($valuesRight[$subKey]), UI_Form_Textarea::getOptimalRowCount($valueLeft), $language->isRTL());

                                    $phrasesTable->addRow(array(
                                        $valueLeft,
                                        $valuePrevious->getHTML().$valueEdit->getHTML()
                                    ));
                                }
                            }
                        }
                    }

                    $formTargetURL = URL::toLanguage($repositoryID, $languageID);
                    $addPhraseURL = URL::toAddPhrase($repositoryID, $languageID);
                    if ($mayMovePhrases) {
                        $formButtonList = array(
                            new UI_Form_Button('Save changes'),
                            new UI_Link('Add phrase', $addPhraseURL, UI_Link::TYPE_UNIMPORTANT)
                        );
                    }
                    else {
                        $formButtonList = array(
                            new UI_Form_Button('Save changes')
                        );
                    }
                    $formButtons = new UI_Form_ButtonGroup($formButtonList, true);

                    $form = new UI_Form($formTargetURL, false);
                    $form->addContent($formButtons);
                    $form->addContent($phrasesTable);
                    $form->addContent($formButtons);

                    $contents[] = $heading;
                    $contents[] = $form;
                }
            }
        }

        $cell = new UI_Cell($contents);
        $row = new UI_Row(array($cell));

        $containers[] = new UI_Container(array($row));
        return new UI_Group($containers);
    }

    public static function validateID($idString, $isEncoded = false) {
        if (empty($idString)) {
            return 0;
        }
        else {
            if ($isEncoded) {
                return intval(URL::decodeID(trim($idString)));
            }
            else {
                return intval(trim($idString));
            }
        }
    }

    public static function getLoginForm() {
        $form = new UI_Form(URL::toDashboard(), false);

        $form->addContent(new UI_Form_StaticText('', 'Please sign in below to access this project. Don\'t have an account yet? <a href="'.URL::toPage('sign_up').'">Sign up in 60 seconds!</a>'));
        $form->addContent(new UI_Form_Text('Username', 'sign_in[username]', 'Enter your username', false));
        $form->addContent(new UI_Form_Text('Password', 'sign_in[password]', 'Type your password', true));
        $form->addContent(new UI_Form_Hidden('sign_in[returnURL]', base64_encode($_SERVER['REQUEST_URI'])));

        $buttonSubmit = new UI_Form_Button('Sign in', UI_Form_Button::TYPE_SUCCESS);
        $buttons = new UI_Form_ButtonGroup(array($buttonSubmit));

        $form->addContent($buttons);

        return $form;
    }

    public static function getInvitationForm($repositoryID) {
        $form = new UI_Form(URL::toDashboard(), false);

        $form->addContent(new UI_Form_StaticText('', 'This project is private &mdash; only people who have been invited by the project owners are allowed contribute.'));
        $form->addContent(new UI_Form_StaticText('', 'Just click the button below to request an invitation for this project.'));
        $form->addContent(new UI_Form_StaticText('', 'You will be able to check the current state of your request on your dashboard anytime.'));
        $form->addContent(new UI_Form_StaticText('', 'As soon as the project owners accept your request, you will be able to submit translations to this project.'));

        $form->addContent(new UI_Form_Hidden('requestInvitation[repositoryID]', URL::encodeID($repositoryID)));

        $buttonSubmit = new UI_Form_Button('Request an invitation', UI_Form_Button::TYPE_SUCCESS);
        $buttonCancel = new UI_Link('Cancel', URL::toDashboard(), UI_Link::TYPE_UNIMPORTANT);
        $buttons = new UI_Form_ButtonGroup(array(
            $buttonSubmit,
            $buttonCancel
        ));

        $form->addContent($buttons);

        return $form;
    }

    public static function redirectToURL($url) {
        try {
            header('Location: '.$url);
            exit;
        }
        catch (Exception $e) { }
    }

}

?>