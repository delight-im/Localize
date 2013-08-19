function markDisabledPhrases() {
    var elements = document.getElementsByClassName('disabledPhrase');
    for (var i = 0; i < elements.length; i++) {
        elements[i].style.backgroundColor = '#f00';
        elements[i].style.color = '#fff';
    }
}
function deletePhrase(baseURL, project, originalID, sourceRow) {
    if (typeof(baseURL) !== 'undefined' && typeof(project) !== 'undefined' && typeof(originalID) !== 'undefined' && typeof(sourceRow) !== 'undefined') {
        if (confirm('Are you sure to delete this phrase for all languages? This cannot be undone!')) {
            $.ajax({
                url: baseURL+'?project='+encodeURIComponent(project)+'&deleteID='+encodeURIComponent(originalID),
                cache: false,
                dataType: 'text'
            }).done(function() {
                $(sourceRow).hide(400);
            }).fail(function(){
                alert('Phrase could not be deleted!');
            });
        }
    }
}
function markPossibleProblems() {
    var elements = document.getElementsByClassName('problemPhrase');
    for (var i = 0; i < elements.length; i++) {
        elements[i].style.backgroundColor = '#ffa500';
        elements[i].style.color = '#000';
    }
}
function toggleDisplay(element) {
    var toToggle = $('#'+element.id.replace('head_', 'body_'));
    if (typeof(toToggle) !== 'undefined') {
        toToggle.toggle(400);
    }
}
function checkIdentName(element, elementName) {
    if (typeof(element) !== 'undefined' && typeof(elementName) !== 'undefined') {
        if (typeof(element.value) !== 'undefined') {
            if (/^[a-zA-Z0-9_]+$/.test(element.value)) {
                return true;
            }
            else {
                alert('The '+elementName+' may only contain the following characters:\na-z, A-Z, 0-9, _');
                return false;
            }
        }
    }
    return false;
}
$(document).ready(function() {
    var landing_mode = $('#landing_mode');
    if (typeof(landing_mode) !== 'undefined') {
        landing_mode.change(function() {
            var landing_html_container = $('#landing_html_container');
            if (typeof(landing_html_container) !== 'undefined') {
                if (landing_mode.val() === 'static') {
                    landing_html_container.show(400);
                }
                else {
                    landing_html_container.hide(400);
                }
            }
        });
    }
});
function getFormatStrings(text) {
    if (typeof(text) !== 'undefined') {
        return text.match(/%(([0-9]+\$)?)([,+ (\-#0]*)([0-9]*)(.[0-9]+)?((hh|h|l|ll|L|z|j|t)*)(d|i|u|f|F|e|E|g|G|x|X|o|s|S|c|C|a|A|b|B|h|H|p|n|%)/g);
    }
    else {
        return null;
    }
}
function markFormatStrings(htmlContainer, formatStrings) {
    if (typeof(htmlContainer) !== 'undefined' && typeof(formatStrings) !== 'undefined') {
        for (var w = 0; w < formatStrings.length; w++) { // loop through all format strings in original phrase
            htmlContainer.innerHTML = htmlContainer.innerHTML.replace(formatStrings[w], "<span style=\"color:#f00;\">"+formatStrings[w]+"</span>"); // mark all format strings in original phrase
        }
    }
}
function checkAndSubmit(form) {
    if (confirm('Are you sure you want to submit all changes on this page?')) { // user is sure to submit
        if (typeof(form) !== 'undefined') { // if form can be found in DOM
            var rows = form.getElementsByTagName('tr'); // get all rows of single translations
            var originalItem;
            var translationItem;
            var placeholdersInOriginal;
            var placeholdersInTranslation;
            var errorInCurrentRow;
            var errorInForm = false;
            for (var r = 0; r < rows.length; r++) { // loop through all rows
                if (typeof(rows[r]) !== 'undefined' && rows[r] !== null) {
                    errorInCurrentRow = false;
                    originalItem = rows[r].getElementsByClassName('originalItem');
                    if (typeof(originalItem) !== 'undefined' && originalItem !== null && originalItem.length === 1) {
                        translationItem = rows[r].getElementsByClassName('translationItem');
                        if (typeof(translationItem) !== 'undefined' && translationItem !== null && translationItem.length === 1) {
                            if (typeof(originalItem[0].innerHTML) !== 'undefined' && originalItem[0].innerHTML !== null && typeof(translationItem[0].value) !== 'undefined' && translationItem[0].value !== null) {
                                if (translationItem[0].value.trim() !== '') { // translation is not empty (which would always be valid because the default language is used then)
                                    placeholdersInOriginal = getFormatStrings(originalItem[0].innerHTML);
                                    placeholdersInTranslation = getFormatStrings(translationItem[0].value);
                                    if (!areFormatStringsMatching(placeholdersInOriginal, placeholdersInTranslation)) { // if format strings do not match
                                        markFormatStrings(originalItem[0], placeholdersInOriginal); // mark all format strings for the user
                                        errorInCurrentRow = true; // mark this row as invalid
                                    }
                                }
                            }
                        }
                    }
                    if (errorInCurrentRow) { // if row did contain an error
                        errorInForm = true; // form has errors
                    }
                    else { // if row did not contain any errors
                        rows[r].style.display = 'none'; // hide row as it does not need to be corrected
                    }
                }
            }
            if (errorInForm) { // if form did contain an error
                alert('Please check the given translations again: Some placeholders from the left side are missing in each of them!'); // tell the user about the errors
                return false; // do not submit form (yet)
            }
            else { // if form did not contain any errors
                return true; // submit form
            }
        }
        else { // if form cannot be found in DOM
            alert('Error'); // this should never happen
            return false; // do not submit the form
        }
    }
    else { // user cancelled submitting
        return false; // do not submit the form
    }
}
function checkAndApprove(original, translation) {
    if (typeof(original) !== 'undefined' && original !== null && typeof(translation) !== 'undefined' && translation !== null) { // if original and translation could be found in the DOM
        if (translation.value.trim() === '') { // translation is empty (which is always valid because the default language is used then)
            return true; // submit the form
        }
        var placeholdersOriginal = getFormatStrings(original.innerHTML); // get format strings from original phrase
        var placeholdersTranslation = getFormatStrings(translation.value); // get format strings from translation phrase
        if (areFormatStringsMatching(placeholdersOriginal, placeholdersTranslation)) { // if format strings are matching
            return true; // submit the form
        }
        else { // if format strings do not match
            markFormatStrings(original, placeholdersOriginal); // mark the format strings
            alert('Please check the translation again: Some placeholders from the original are missing in the new translation!'); // tell the user about the error
            return false; // do not submit the form (yet)
        }
        return false; // submit the form
    }
    else { // if either original or translation could not be found in DOM
        alert('Error'); // this should never happen
        return false; // do not submit the form
    }
}
function areFormatStringsMatching(placeholdersOriginal, placeholdersTranslation) {
    if (typeof(placeholdersOriginal) !== 'undefined' && placeholdersOriginal !== null) { // if original phrase did contain format strings
        if (typeof(placeholdersTranslation) !== 'undefined' && placeholdersTranslation !== null) { // if translation phrase did contain format strings as well
            placeholdersOriginal.sort(); // sort both placeholder arrays
            placeholdersTranslation.sort(); // so that they have the same placeholders at the same positions if they match
            for (var p = 0; p < placeholdersOriginal.length; p++) { // loop through all format strings from the original phrase
                if (placeholdersOriginal[p] !== placeholdersTranslation[p]) { // some format string is missing in translation phrase
                    return false; // some format string from the original phrase is missing in the translation phrase so they do not match
                }
            }
        }
        else { // if translation phrase did not contain any format strings
            return false; // the translation phrase had no format strings while the original did so they do not match
        }
    }
    return true; // no format string has been found to be missing so they do match
}