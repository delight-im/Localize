<?php

require_once(__DIR__.'/classes/Authentication.php');
require_once(__DIR__.'/classes/File_IO.php');
require_once(__DIR__.'/classes/Helper.php');
require_once(__DIR__.'/classes/Language_Android.php');
require_once(__DIR__.'/classes/Phrase_Android_Plurals.php');
require_once(__DIR__.'/classes/Phrase_Android_String.php');
require_once(__DIR__.'/classes/Phrase_Android_StringArray.php');
require_once(__DIR__.'/classes/Repository.php');
require_once(__DIR__.'/classes/UI.php');
require_once(__DIR__.'/classes/UI_Alert.php');
require_once(__DIR__.'/classes/User.php');
require_once(__DIR__.'/classes/Database.php');
require_once(__DIR__.'/classes/Edit.php');
require_once(__DIR__.'/libs/password_compat.php');

Authentication::init();
UI::init();
Database::init();

if (UI::isPage('sign_up')) {
    if (UI::isAction('sign_up')) {
        $data = UI::getDataPOST('sign_up');

        $data_type = isset($data['type']) ? intval($data['type']) : 0;
        $data_username = isset($data['username']) ? trim($data['username']) : '';
        $data_password1 = isset($data['password1']) ? trim($data['password1']) : '';
        $data_password2 = isset($data['password2']) ? trim($data['password2']) : '';

        if ($data_password1 == $data_password2) {
            if (mb_strlen($data_password1) >= 6) {
                if (mb_strlen($data_username) >= 3) {
                    if ($data_type == User::TYPE_TRANSLATOR || $data_type == User::TYPE_DEVELOPER) {
                        $data_password = password_hash($data_password1, PASSWORD_BCRYPT);
                        try {
                            Database::insert("INSERT INTO users (username, password, type, join_date) VALUES (".Database::escape($data_username).", ".Database::escape($data_password).", ".intval($data_type).", ".time().")");
                            $alert = new UI_Alert('<p>Your free account has been created!</p><p>Please sign in by entering your username and password in the top-right corner.</p>', UI_Alert::TYPE_SUCCESS);
                        }
                        catch (Exception $e) {
                            $alert = new UI_Alert('<p>It seems this username has already been taken. Please try another one.</p>', UI_Alert::TYPE_WARNING);
                        }
                    }
                    else {
                        $alert = new UI_Alert('<p>Please choose one of the account types!</p>', UI_Alert::TYPE_WARNING);
                    }
                }
                else {
                    $alert = new UI_Alert('<p>Your username must be at least 3 characters long!</p>', UI_Alert::TYPE_WARNING);
                }
            }
            else {
                $alert = new UI_Alert('<p>Your password must be at least 6 characters long!</p>', UI_Alert::TYPE_WARNING);
            }
        }
        else {
            $alert = new UI_Alert('<p>The two passwords did not match!</p>', UI_Alert::TYPE_WARNING);
        }

        echo UI::getPage(UI::PAGE_SIGN_UP, array($alert));
    }
    else {
        echo UI::getPage(UI::PAGE_SIGN_UP);
    }
}
elseif (UI::isPage('contact')) {
    echo UI::getPage(UI::PAGE_CONTACT);
}
elseif (UI::isPage('sign_out')) {
    Authentication::signOut();
    $alert = new UI_Alert('You have successfully been signed out!', UI_Alert::TYPE_SUCCESS);
    echo UI::getPage(UI::PAGE_INDEX, array($alert));
}
elseif (UI::isPage('project')) {
    echo UI::getPage(UI::PAGE_PROJECT);
}
elseif (UI::isPage('language')) {
    $alert = NULL;
    if (UI::isAction('updatePhrases')) {
        $languageID = UI::validateID(UI::getDataGET('language'), true);
        $repositoryID = UI::validateID(UI::getDataGET('project'), true);
        $repositoryData = Database::getRepositoryData($repositoryID);
        if (!empty($repositoryData)) {
            $repository = new Repository($repositoryID, $repositoryData['name'], $repositoryData['visibility'], $repositoryData['defaultLanguage']);
            $role = Database::getRepositoryRole(Authentication::getUserID(), $repositoryID);
            $permissions = $repository->getPermissions(Authentication::getUserID(), $role);

            if (!$permissions->isLoginMissing() && !$permissions->isInvitationMissing()) {
                $data = UI::getDataPOST('updatePhrases');
                if (isset($data['edits']) && is_array($data['edits']) && isset($data['previous']) && is_array($data['previous'])) {
                    $editData = array();
                    $counter = 0;
                    foreach ($data['edits'] as $phraseID => $phraseSubKeys) {
                        foreach ($phraseSubKeys as $phraseSubKey => $phraseSuggestedValue) {
                            $previousValue = isset($data['previous'][$phraseID][$phraseSubKey]) ? trim($data['previous'][$phraseID][$phraseSubKey]) : '';
                            $phraseSuggestedValue = trim($phraseSuggestedValue);
                            if ($phraseSuggestedValue != '' && $phraseSuggestedValue != $previousValue) {
                                $editData[] = new Edit(Helper::decodeID($phraseID), $phraseSubKey, $phraseSuggestedValue);
                                $counter++;
                            }
                        }
                    }
                    if (!empty($editData)) {
                        Database::submitEdits($repositoryID, $languageID, Authentication::getUserID(), $editData);
                        $alert = new UI_Alert('<p>Thank you very much!</p><p>Your modifications to '.$counter.' phrases have been submitted to this project.</p><p>They will now be reviewed by the project owners.</p>', UI_Alert::TYPE_SUCCESS);
                    }
                    else {
                        $alert = new UI_Alert('<p>You did change any phrase. Please try again!</p>', UI_Alert::TYPE_WARNING);
                    }
                }
                else {
                    $alert = new UI_Alert('<p>You did not send any modifications. Please try again!</p>', UI_Alert::TYPE_WARNING);
                }
            }
            else {
                $alert = new UI_Alert('<p>You are not allowed to submit changes to this project.</p>', UI_Alert::TYPE_WARNING);
            }
        }
        else {
            $alert = new UI_Alert('<p>The project could not be found.</p>', UI_Alert::TYPE_WARNING);
        }
    }
    if (empty($alert)) {
        echo UI::getPage(UI::PAGE_PROJECT);
    }
    else {
        echo UI::getPage(UI::PAGE_PROJECT, array($alert));
    }
}
elseif (UI::isPage('export')) {
    $alert = NULL;
    if (UI::isAction('export')) {
        $repositoryID = UI::validateID(UI::getDataGET('project'), true);
        $repositoryData = Database::getRepositoryData($repositoryID);
        if (!empty($repositoryData)) {
            $repositoryDefaultLanguage = $repositoryData['defaultLanguage'];
            $isAllowed = Repository::hasUserPermissions(Authentication::getUserID(), $repositoryID, $repositoryData, Repository::ROLE_DEVELOPER);
            if ($isAllowed) {
                $data = UI::getDataPOST('export');
                $filename = isset($data['filename']) ? trim($data['filename']) : '';
                if (File_IO::isFilenameValid($filename)) {
                    $repository = new Repository($repositoryID, $repositoryData['name'], $repositoryData['visibility'], $repositoryData['defaultLanguage']);
                    $repository->loadLanguages(true);
                    File_IO::exportRepository($repository, $filename);
                    exit;
                }
                else {
                    $alert = new UI_Alert('<p>Please enter a valid filename.</p>', UI_Alert::TYPE_WARNING);
                }

            }
            else {
                $alert = new UI_Alert('<p>You are not allowed to export files from this project.</p>', UI_Alert::TYPE_WARNING);
            }
        }
        else {
            $alert = new UI_Alert('<p>The project could not be found.</p>', UI_Alert::TYPE_WARNING);
        }
    }
    if (empty($alert)) {
        echo UI::getPage(UI::PAGE_PROJECT);
    }
    else {
        echo UI::getPage(UI::PAGE_PROJECT, array($alert));
    }
}
elseif (UI::isPage('import')) {
    $alert = NULL;
    if (UI::isAction('import')) {
        $repositoryID = UI::validateID(UI::getDataGET('project'), true);
        $repositoryData = Database::getRepositoryData($repositoryID);
        if (!empty($repositoryData)) {
            $repositoryDefaultLanguage = $repositoryData['defaultLanguage'];
            $isAllowed = Repository::hasUserPermissions(Authentication::getUserID(), $repositoryID, $repositoryData, Repository::ROLE_DEVELOPER);
            if ($isAllowed) {
                $data = UI::getDataPOST('import');
                $overwriteMode = isset($data['overwrite']) ? intval(trim($data['overwrite'])) : 0;
                $languageID = isset($data['languageID']) ? intval(trim($data['languageID'])) : 0;
                if ($overwriteMode == 0 || $overwriteMode == 1) {
                    if ($languageID > 0) {
                        try {
                            $languageNameFull = Language::getLanguageNameFull($languageID);
                            $importResult = File_IO::importXML($repositoryID, $_FILES['importFileXML']);
                            if (isset($importResult) && is_array($importResult)) {
                                Database::addPhrases($repositoryID, $languageID, $importResult, $overwriteMode == 1);
                                $alert = new UI_Alert('<p>You have imported '.count($importResult).' phrases to '.$languageNameFull.'.</p>', UI_Alert::TYPE_SUCCESS);
                            }
                            else {
                                switch ($importResult) {
                                    case File_IO::UPLOAD_ERROR_NO_TRANSLATIONS_FOUND:
                                        $alert = new UI_Alert('<p>No translations were found in the uploaded XML file.</p>', UI_Alert::TYPE_WARNING);
                                        break;
                                    case File_IO::UPLOAD_ERROR_COULD_NOT_OPEN:
                                        $alert = new UI_Alert('<p>Could not open the XML file. Please try again!</p>', UI_Alert::TYPE_WARNING);
                                        break;
                                    case File_IO::UPLOAD_ERROR_COULD_NOT_PROCESS:
                                        $alert = new UI_Alert('<p>Could not process the XML file. Please try again!</p>', UI_Alert::TYPE_WARNING);
                                        break;
                                    case File_IO::UPLOAD_ERROR_TOO_LARGE:
                                        $alert = new UI_Alert('<p>The XML file that you tried to upload was too large.</p>', UI_Alert::TYPE_WARNING);
                                        break;
                                    case File_IO::UPLOAD_ERROR_XML_INVALID:
                                        $alert = new UI_Alert('<p>Invalid XML in the uploaded file.</p>', UI_Alert::TYPE_WARNING);
                                        break;
                                    default:
                                        $alert = new UI_Alert('<p>Unknown error. Please try again!</p>', UI_Alert::TYPE_WARNING);
                                }
                            }
                        }
                        catch (Exception $e) {
                            $alert = new UI_Alert('<p>Could not find the language that you have selected.</p>', UI_Alert::TYPE_WARNING);
                        }
                    }
                    else {
                        $alert = new UI_Alert('<p>Please choose one of the languages from the list.</p>', UI_Alert::TYPE_WARNING);
                    }
                }
                else {
                    $alert = new UI_Alert('<p>Please choose if you want to overwrite existing phrases or not.</p>', UI_Alert::TYPE_WARNING);
                }
            }
            else {
                $alert = new UI_Alert('<p>You are not allowed to import phrases to this project.</p>', UI_Alert::TYPE_WARNING);
            }
        }
        else {
            $alert = new UI_Alert('<p>The project could not be found.</p>', UI_Alert::TYPE_WARNING);
        }
    }
    if (empty($alert)) {
        echo UI::getPage(UI::PAGE_PROJECT);
    }
    else {
        echo UI::getPage(UI::PAGE_PROJECT, array($alert));
    }
}
elseif (UI::isPage('add_phrase')) {
    $alert = NULL;
    if (UI::isAction('add_phrase')) {
        $repositoryID = UI::validateID(UI::getDataGET('project'), true);
        $repositoryData = Database::getRepositoryData($repositoryID);
        if (!empty($repositoryData)) {
            $repositoryDefaultLanguage = $repositoryData['defaultLanguage'];
            $isAllowed = Repository::hasUserPermissions(Authentication::getUserID(), $repositoryID, $repositoryData, Repository::ROLE_DEVELOPER);
            if ($isAllowed) {
                $data = UI::getDataPOST('add_phrase');
                $phraseType = isset($data['type']) ? intval(trim($data['type'])) : 0;
                $phraseKey = isset($data['key']) ? trim($data['key']) : '';
                if ($phraseType == 1) { // string
                    $phraseValue = isset($data['string']) ? trim($data['string']) : '';
                    if (!empty($phraseValue)) {
                        $phrasePayload = Phrase_Android_String::getPayloadFromValue($phraseValue);
                        try {
                            Database::addPhrase($repositoryID, $repositoryDefaultLanguage, $phraseKey, $phrasePayload);
                            $alert = new UI_Alert('<p>The new phrase has successfully been added.</p>', UI_Alert::TYPE_SUCCESS);
                        }
                        catch (Exception $e) {
                            $alert = new UI_Alert('<p>The new phrase could not be added.</p><p>It seems there is already a phrase with the same key.</p>', UI_Alert::TYPE_WARNING);
                        }
                    }
                    else {
                        $alert = new UI_Alert('<p>The phrase must not be empty.</p>', UI_Alert::TYPE_WARNING);
                    }
                }
                elseif ($phraseType == 2) { // string-array
                    if (isset($data['string_array']) && is_array($data['string_array']) && count($data['string_array']) > 0) {
                        $phraseValues = array();
                        $phraseValuesCandidates = $data['string_array'];
                        $phraseValuesComplete = true;
                        foreach ($phraseValuesCandidates as $phraseValuesCandidate) {
                            $phraseValuesCandidate = trim($phraseValuesCandidate);
                            if (!empty($phraseValuesCandidate)) {
                                $phraseValues[] = $phraseValuesCandidate;
                            }
                            else {
                                $phraseValuesComplete = false;
                            }
                        }
                        if ($phraseValuesComplete) {
                            $phrasePayload = Phrase_Android_StringArray::getPayloadFromValue($phraseValues);
                            try {
                                Database::addPhrase($repositoryID, $repositoryDefaultLanguage, $phraseKey, $phrasePayload);
                                $alert = new UI_Alert('<p>The new phrase has successfully been added.</p>', UI_Alert::TYPE_SUCCESS);
                            }
                            catch (Exception $e) {
                                $alert = new UI_Alert('<p>The new phrase could not be added.</p><p>It seems there is already a phrase with the same key.</p>', UI_Alert::TYPE_WARNING);
                            }
                        }
                        else {
                            $alert = new UI_Alert('<p>New phrases must not be empty.</p>', UI_Alert::TYPE_WARNING);
                        }
                    }
                }
                elseif ($phraseType == 3) { // plurals
                    if (isset($data['plurals']) && is_array($data['plurals']) && count($data['plurals']) > 0) {
                        $phraseValues = array();
                        $phraseValuesCandidates = $data['plurals'];
                        $phraseValuesComplete = true;
                        foreach ($phraseValuesCandidates as $phraseValuesKey => $phraseValuesCandidate) {
                            $phraseValuesCandidate = trim($phraseValuesCandidate);
                            if (!empty($phraseValuesCandidate)) {
                                $phraseValues[$phraseValuesKey] = $phraseValuesCandidate;
                            }
                            else {
                                $phraseValuesComplete = false;
                            }
                        }
                        if ($phraseValuesComplete) {
                            $phrasePayload = Phrase_Android_Plurals::getPayloadFromValue($phraseValues);
                            try {
                                Database::addPhrase($repositoryID, $repositoryDefaultLanguage, $phraseKey, $phrasePayload);
                                $alert = new UI_Alert('<p>The new phrase has successfully been added.</p>', UI_Alert::TYPE_SUCCESS);
                            }
                            catch (Exception $e) {
                                $alert = new UI_Alert('<p>The new phrase could not be added.</p><p>It seems there is already a phrase with the same key.</p>', UI_Alert::TYPE_WARNING);
                            }
                        }
                        else {
                            $alert = new UI_Alert('<p>New phrases must not be empty.</p>', UI_Alert::TYPE_WARNING);
                        }
                    }
                }
            }
            else {
                $alert = new UI_Alert('<p>You are not allowed to add new phrases to this project.</p>', UI_Alert::TYPE_WARNING);
            }
        }
        else {
            $alert = new UI_Alert('<p>The project could not be found.</p>', UI_Alert::TYPE_WARNING);
        }
    }
    if (empty($alert)) {
        echo UI::getPage(UI::PAGE_PROJECT);
    }
    else {
        echo UI::getPage(UI::PAGE_PROJECT, array($alert));
    }
}
elseif (UI::isPage('create_project') && Authentication::isSignedIn()) {
    if (UI::isAction('create_project')) {
        $data = UI::getDataPOST('create_project');

        $data_visibility = isset($data['visibility']) ? intval($data['visibility']) : 0;
        $data_name = isset($data['name']) ? trim($data['name']) : '';
        $data_defaultLanguage = isset($data['defaultLanguage']) ? intval($data['defaultLanguage']) : 0;
        $data_editRepositoryID = isset($data['editRepositoryID']) ? UI::validateID($data['editRepositoryID'], true) : 0;
        if ($data_editRepositoryID > 0) {
            $repositoryData = Database::getRepositoryData($data_editRepositoryID);
            $isAllowed = Repository::hasUserPermissions(Authentication::getUserID(), $data_editRepositoryID, $repositoryData, Repository::ROLE_ADMINISTRATOR);
        }
        else {
            $isAllowed = true;
        }

        if ($data_visibility == Repository::VISIBILITY_PUBLIC || $data_visibility == Repository::VISIBILITY_PROTECTED || $data_visibility == Repository::VISIBILITY_PRIVATE) {
            $allLanguages = Language::getList();
            if (in_array($data_defaultLanguage, $allLanguages)) {
                if (mb_strlen($data_name) >= 3) {
                    if ($data_editRepositoryID <= 0 || $isAllowed) {
                        if ($data_editRepositoryID > 0) { // edit project
                            Database::update("UPDATE repositories SET name = ".Database::escape($data_name).", visibility = ".intval($data_visibility).", defaultLanguage = ".intval($data_defaultLanguage)." WHERE id = ".intval($data_editRepositoryID));
                            header('Location: index.php?p=project&project='.Helper::encodeID($data_editRepositoryID));
                        }
                        else { // create project
                            Database::insert("INSERT INTO repositories (name, visibility, defaultLanguage, creation_date) VALUES (".Database::escape($data_name).", ".intval($data_visibility).", ".intval($data_defaultLanguage).", ".time().")");
                            $newProjectID = Database::getLastInsertID(Database::TABLE_REPOSITORIES_SEQUENCE);
                            if ($newProjectID > 0) {
                                Database::insert("INSERT INTO roles (userID, repositoryID, role) VALUES (".intval(Authentication::getUser()->getID()).", ".intval($newProjectID).", ".Repository::ROLE_ADMINISTRATOR.")");
                                header('Location: index.php?p=project&project='.Helper::encodeID($newProjectID));
                            }
                            else {
                                throw new Exception('Project could not be created');
                            }
                        }
                    }
                    else {
                        $alert = new UI_Alert('<p>You are not allowed to edit this project!</p>', UI_Alert::TYPE_WARNING);
                    }
                }
                else {
                    $alert = new UI_Alert('<p>Your project\'s name must be at least 3 characters long!</p>', UI_Alert::TYPE_WARNING);
                }
            }
            else {
                $alert = new UI_Alert('<p>Please choose one of the languages from the list!</p>', UI_Alert::TYPE_WARNING);
            }
        }
        else {
            $alert = new UI_Alert('<p>Please choose one of the visibility levels from the list!</p>', UI_Alert::TYPE_WARNING);
        }

        echo UI::getPage(UI::PAGE_CREATE_PROJECT, array($alert));
    }
    else {
        echo UI::getPage(UI::PAGE_CREATE_PROJECT);
    }
}
else {
    if (UI::isAction('sign_in')) {
        $data = UI::getDataPOST('sign_in');

        $data_username = isset($data['username']) ? trim($data['username']) : '';
        $data_password = isset($data['password']) ? trim($data['password']) : '';

        $userData = Database::selectFirst("SELECT id, username, password, real_name, type, join_date FROM users WHERE username = ".Database::escape($data_username));
        if (!empty($userData)) {
            if (isset($userData['password']) && password_verify($data_password, $userData['password'])) {
                $userObject = new User($userData['id'], $userData['type'], $userData['username'], $userData['real_name'], $userData['join_date']);
                Authentication::signIn($userObject);
                Database::update("UPDATE users SET last_login = ".time()." WHERE id = ".intval($userData['id']));
                $alert = new UI_Alert('<p>You have successfully been signed in!</p>', UI_Alert::TYPE_SUCCESS);
            }
            else {
                $alert = new UI_Alert('<p>Please check your password again!</p>', UI_Alert::TYPE_WARNING);
            }
        }
        else {
            $alert = new UI_Alert('<p>We could not find any user with this name!</p>', UI_Alert::TYPE_WARNING);
        }

        if (Authentication::isSignedIn()) {
            echo UI::getPage(UI::PAGE_DASHBOARD, array($alert));
        }
        else {
            echo UI::getPage(UI::PAGE_INDEX, array($alert));
        }
    }
    else {
        if (Authentication::isSignedIn()) {
            $alert = NULL;
            if (UI::isAction('myAccount')) {
                $data = UI::getDataPOST('myAccount');
                $realName = isset($data['realName']) ? trim($data['realName']) : '';
                $nativeLanguages = isset($data['nativeLanguage']) ? $data['nativeLanguage'] : array();
                Database::updateAccountInfo(Authentication::getUserID(), $realName, $nativeLanguages);
                $userObject = Authentication::getUser();
                if (!empty($userObject)) {
                    $userObject->setRealName($realName);
                    Authentication::updateUserInfo($userObject);
                }
                $alert = new UI_Alert('<p>Your account has been updated.</p>', UI_Alert::TYPE_SUCCESS);
            }
            if (isset($alert)) {
                echo UI::getPage(UI::PAGE_DASHBOARD, array($alert));
            }
            else {
                echo UI::getPage(UI::PAGE_DASHBOARD);
            }
        }
        else {
            echo UI::getPage(UI::PAGE_INDEX);
        }
    }
}

?>