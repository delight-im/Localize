<?php

require_once(__DIR__.'/../config.php');

class File_IO {

    const MAX_FILE_SIZE = CONFIG_MAX_FILE_SIZE; // int from config.php in root directory
    const UPLOAD_ERROR_COULD_NOT_OPEN = 1;
    const UPLOAD_ERROR_COULD_NOT_PROCESS = 2;
    const UPLOAD_ERROR_TOO_LARGE = 3;
    const UPLOAD_ERROR_XML_INVALID = 4;
    const UPLOAD_ERROR_NO_TRANSLATIONS_FOUND = 5;
    const FILENAME_REGEX = '/^[a-z]+[a-z0-9_.]*$/';
	const FORMAT_ANDROID_XML = 1;
	const FORMAT_ANDROID_XML_ESCAPED_HTML = 2;
    const FORMAT_JSON = 3;
    const FORMAT_PLAINTEXT = 4;

    public static function getMaxFileSize() {
        return self::MAX_FILE_SIZE;
    }

    /**
     * Compresses a given folder to a ZIP file
     *
     * @param string $inputFolder the source folder that is to be zipped
     * @param string $zipOutputFile the destination file that the ZIP is to be written to
     * @return boolean whether this process was successful or not
     */
	function zipFolder($inputFolder, $zipOutputFile) {
		if (!extension_loaded('zip') || !file_exists($inputFolder)) {
			return false;
		}

		$zip = new ZipArchive();
		if (!$zip->open($zipOutputFile, ZIPARCHIVE::CREATE)) {
			return false;
		}

		$inputFolder = str_replace('\\', '/', realpath($inputFolder));

		if (is_dir($inputFolder) === true) {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
					$inputFolder,
					FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
				),
				RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ($files as $file) {
				$file = str_replace('\\', '/', $file);

				if (is_dir($file) === true) {
					$dirName = str_replace($inputFolder.'/', '', $file.'/');
					$zip->addEmptyDir($dirName);
				}
				else if (is_file($file) === true) {
					$fileName = str_replace($inputFolder.'/', '', $file);
					$zip->addFromString($fileName, file_get_contents($file));
				}
			}
		}
		else if (is_file($inputFolder) === true) {
			$zip->addFromString(basename($inputFolder), file_get_contents($inputFolder));
		}

		return $zip->close();
	}

    /**
     * Recursively deletes a directory and its content
     *
     * @param string $dir directory to delete recursively
     */
    public static function deleteDirectoryRecursively($dir) {
        $files = glob($dir.'/*');
        if (!empty($files)) {
            foreach ($files as $file) { // loop through child elements
                if (is_dir($file)) {
                    self::deleteDirectoryRecursively($file); // delete directories in this directory
                }
                else {
                    unlink($file); // delete files in this directory
                }
            }
        }
        rmdir($dir); // delete this directory itself
    }

    public static function isFilenameValid($filename) {
        return isset($filename) && preg_match(self::FILENAME_REGEX, $filename);
    }

    /**
     * Exports the given repository and creates a ZIP file containing XML output files
     *
     * @param Repository $repository the Repository instance to export
     * @param string $filename the output file name inside each language folder
     * @param int $groupID the group to get the output for (or Phrase::GROUP_ALL)
     * @param int $format the format (constant) to use for this export
     * @param int $minCompletion the minimum percentage of completion for languages to be eligible for exporting
     * @throws Exception if the repository could not be exported
     */
    public static function exportRepository($repository, $filename, $groupID, $format, $minCompletion = 0) {
        if (self::isFilenameValid($filename)) {
            if ($repository instanceof Repository) {
                $exportSuccess = true;
                $randomDir = mt_rand(1000000, 9999999);
                $savePath = URL::getTempPath(false).URL::encodeID($repository->getID());
                self::deleteDirectoryRecursively($savePath); // delete all old output files from output directory first
                $savePath .= '/'.$randomDir; // navigate to random directory inside output folder
                if (mkdir($savePath, 0755, true)) { // if output folder could be created
                    $languages = Language::getList();
                    foreach ($languages as $language) {
                        $languageObject = $repository->getLanguage($language);
                        $languageKeys = $languageObject->getKeys();

                        if ($format == self::FORMAT_ANDROID_XML_ESCAPED_HTML) {
                            $languageOutput = $languageObject->outputAndroidXMLEscapedHTML($groupID);
                            $fileExtension = '.xml';
                        }
                        elseif ($format == self::FORMAT_JSON) {
                            $languageOutput = $languageObject->outputJSON($groupID);
                            $fileExtension = '.json';
                        }
                        elseif ($format == self::FORMAT_PLAINTEXT) {
                            $languageOutput = $languageObject->outputPlaintext($groupID);
                            $fileExtension = '.txt';
                        }
                        else {
                            $languageOutput = $languageObject->outputAndroidXML($groupID);
                            $fileExtension = '.xml';
                        }

                        if ($languageOutput->getCompleteness() >= $minCompletion) {
                            foreach ($languageKeys as $languageKey) {
                                if (mkdir($savePath.'/'.$languageKey.'/', 0755, true)) {
                                    if (file_put_contents($savePath.'/'.$languageKey.'/'.$filename.$fileExtension, $languageOutput->getContent()) !== false) {
                                        $exportSuccess = true;
                                    }
                                    else { // output file could not be written
                                        $exportSuccess = false;
                                    }
                                }
                                else { // sub-directory for language could not be created
                                    $exportSuccess = false;
                                }
                            }
                        }
                    }
                }
                else { // output folder could not be created
                    $exportSuccess = false;
                }
                if ($exportSuccess) {
                    $outputPath = URL::getTempPath(true).URL::encodeID($repository->getID()).'/'.$randomDir;
                    if (self::zipFolder($savePath, $savePath.'/Export.zip')) {
                        UI::redirectToURL($outputPath.'/Export.zip');
                    }
                }
            }
            else {
                throw new Exception('The repository must be an instance of class Repository');
            }
        }
        else {
            throw new Exception('Invalid filename: '.$filename);
        }
    }

    public static function importXML($repositoryID, $fileArrayValue) {
        if (!isset($fileArrayValue['error'])) {
            return self::UPLOAD_ERROR_COULD_NOT_PROCESS;
        }
        switch ($fileArrayValue['error']) {
            case UPLOAD_ERR_FORM_SIZE:
            case UPLOAD_ERR_INI_SIZE: return self::UPLOAD_ERROR_TOO_LARGE;
            case UPLOAD_ERR_PARTIAL:
            case UPLOAD_ERR_NO_FILE: return self::UPLOAD_ERROR_COULD_NOT_PROCESS;
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION: return self::UPLOAD_ERROR_COULD_NOT_OPEN;
        }
        if ($fileArrayValue['size'] < self::getMaxFileSize()) {
            $newFileName = URL::getUploadPath(false).$repositoryID.'_'.mt_rand(1000000, 9999999).'.xml';
            if (move_uploaded_file($fileArrayValue['tmp_name'], $newFileName)) {
                $fileContent = file_get_contents($newFileName);
                if ($fileContent !== false) {
                    $fileContent = str_replace('<![CDATA[', '', $fileContent);
                    $fileContent = str_replace(']]>', '', $fileContent);
                    $fileContent = preg_replace('/<string-array([^>]*)>/i', '<entryList\1>', $fileContent);
                    $fileContent = str_replace('</string-array>', '</entryList>', $fileContent);
                    $fileContent = preg_replace('/<string([^>]*)>/i', '<entrySingle\1><![CDATA[', $fileContent);
                    $fileContent = str_replace('</string>', ']]></entrySingle>', $fileContent);
                    $fileContent = preg_replace('/<item([^>]*)>/i', '<item\1><![CDATA[', $fileContent);
                    $fileContent = str_replace('</item>', ']]></item>', $fileContent);
                    $xml = @simplexml_load_string($fileContent, 'SimpleXMLElement', LIBXML_NOCDATA);
                    if ($xml != false) {
                        $importedPhrases = array();
                        foreach ($xml->{'entrySingle'} as $entrySingle) {
                            $entryAttributes = $entrySingle->attributes();
                            $importedPhrase = new Phrase_Android_String(0, trim($entryAttributes['name']), true);
                            $importedPhrase->addValue(trim(Phrase_Android::readFromRaw($entrySingle[0])));
                            $importedPhrases[] = $importedPhrase;
                        }
                        foreach ($xml->{'entryList'} as $entryList) {
                            $entryAttributes = $entryList->attributes();
                            $importedPhrase = new Phrase_Android_StringArray(0, trim($entryAttributes['name']), true);
                            foreach ($entryList->{'item'} as $entryItem) {
                                $importedPhrase->addValue(trim(Phrase_Android::readFromRaw($entryItem)));
                            }
                            $importedPhrases[] = $importedPhrase;
                        }
                        foreach ($xml->{'plurals'} as $plural) {
                            $pluralAttributes = $plural->attributes();
                            $importedPhrase = new Phrase_Android_Plurals(0, trim($pluralAttributes['name']), true);
                            $defaultPluralValue = NULL;
                            foreach ($plural->{'item'} as $pluralItem) {
                                $itemAttributes = $pluralItem->attributes();
                                $pluralQuantity = trim($itemAttributes['quantity']);
                                $pluralValue = trim(Phrase_Android::readFromRaw($pluralItem));

                                if (!isset($defaultPluralValue) || $pluralQuantity === 'other') {
                                    $defaultPluralValue = $pluralValue;
                                }

                                $importedPhrase->addValue($pluralValue, $pluralQuantity);
                            }

                            if (isset($defaultPluralValue)) {
                                $importedPhrase->fillWithDefault($defaultPluralValue);
                            }

                            $importedPhrases[] = $importedPhrase;
                        }
                        if (count($importedPhrases) > 0) {
                            return $importedPhrases;
                        }
                        else {
                            return self::UPLOAD_ERROR_NO_TRANSLATIONS_FOUND;
                        }
                    }
                    else {
                        return self::UPLOAD_ERROR_XML_INVALID;
                    }
                }
                else {
                    return self::UPLOAD_ERROR_COULD_NOT_OPEN;
                }
            }
            else {
                return self::UPLOAD_ERROR_COULD_NOT_PROCESS;
            }
        }
        else {
            return self::UPLOAD_ERROR_TOO_LARGE;
        }
    }

}

?>
