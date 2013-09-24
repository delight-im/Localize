function addPhraseTypeSelect(selectedClass) {
    if (typeof(selectedClass) !== 'undefined' && selectedClass !== null) {
        var availableClasses = [ "addPhraseGroup_String", "addPhraseGroup_StringArray", "addPhraseGroup_Plurals" ];
        for (var i = 0; i < availableClasses.length; i++) {
            var newDisplayCSS;
            if (availableClasses[i] == selectedClass) {
                newDisplayCSS = 'block';
            }
            else {
                newDisplayCSS = 'none';
            }
            var elements = document.getElementsByClassName(availableClasses[i]);
            for (var k = 0; k < elements.length; k++) {
                if (newDisplayCSS == 'block' && elements[k].tagName == 'A') {
                    newDisplayCSS = 'inline-block';
                }
                elements[k].style.display = newDisplayCSS;
            }
        }
    }
}
function addPhraseAddItem(nameToCopy) {
    if (typeof(nameToCopy) !== 'undefined' && nameToCopy !== null) {
        var innerElementToCopy = document.getElementsByName(nameToCopy);
        if (typeof(innerElementToCopy) !== 'undefined' && innerElementToCopy !== null && innerElementToCopy.length >= 1) {
            var sameNameElements = innerElementToCopy.length;
            var outerElementToCopy = innerElementToCopy[sameNameElements-1].parentNode.parentNode;
            if (typeof(outerElementToCopy) !== 'undefined' && outerElementToCopy !== null) {
                var oldValue = innerElementToCopy[sameNameElements-1].value;
                innerElementToCopy[sameNameElements-1].value = '';
                var outerCopiedElement = outerElementToCopy.cloneNode(true);
                innerElementToCopy[sameNameElements-1].value = oldValue;
                appendNodeAfter(outerCopiedElement, outerElementToCopy);
            }
        }
    }
}
function appendNodeAfter(appendNode, afterNode) {
    if (typeof(appendNode) !== 'undefined' && appendNode !== null) {
        if (typeof(afterNode) !== 'undefined' && afterNode !== null) {
            if (afterNode.nextSibling) {
                afterNode.parentNode.insertBefore(appendNode, afterNode.nextSibling);
            }
            else {
                afterNode.parentNode.appendChild(appendNode);
            }
        }
    }
}
function toggleMoreRow(toggleButton, rowToToggle) {
    if (typeof(toggleButton) !== 'undefined' && toggleButton !== null) {
        if (typeof(rowToToggle) !== 'undefined' && rowToToggle !== null) {
            if (rowToToggle.style.display == 'none') {
                rowToToggle.style.display = 'table-row';
                toggleButton.innerHTML = 'Less';
            }
            else {
                rowToToggle.style.display = 'none';
                toggleButton.innerHTML = 'More';
            }
        }
    }
}
function chooseTimezoneByCountry(countryCode) {
    if (typeof(countryCode) !== 'undefined' && countryCode !== null) {
        var timezoneSelects = document.getElementsByClassName('timezone-select');
        for (var i = 0; i < timezoneSelects.length; i++) {
            if (countryCode != '' && timezoneSelects[i].className.indexOf('timezone-select-'+countryCode) > -1) {
                timezoneSelects[i].style.display = 'block';
            }
            else {
                timezoneSelects[i].style.display = 'none';
            }
        }
    }
}
function openTablePage(tableID, pageToOpen) {
    if (typeof(tableID) !== 'undefined' && tableID !== null) {
        if (typeof(pageToOpen) !== 'undefined' && pageToOpen !== null) {
            var table = document.getElementById(tableID); // get the actual table DOM element
            var pagination = document.getElementById('pagination-'+tableID); // get the pagination bar DOM element
            if (typeof(table) !== 'undefined' && table !== null) { // make sure the actual table exists
                if (typeof(table) !== 'undefined' && table !== null) { // make sure the pagination bar exists
                    // show the requested page of the table
                    var pages = table.getElementsByClassName('table-page');
                    var pageToOpenClass = 'table-page table-page-'+pageToOpen;
                    for (var i = 0; i < pages.length; i++) {
                        if (pages[i].className == pageToOpenClass) {
                            pages[i].style.display = 'table-row-group';
                        }
                        else {
                            pages[i].style.display = 'none';
                        }
                    }

                    // mark the correct page in the pagination bar
                    var counter = 0;
                    for (var c = 0; c < pagination.childNodes.length; c++) {
                        if (pagination.childNodes[c].tagName == 'LI') {
                            if (counter == pageToOpen) {
                                pagination.childNodes[c].className = 'active';
                            }
                            else {
                                pagination.childNodes[c].className = '';
                            }
                            counter++;
                        }
                    }

                    // scroll back to the top of the page
                    try {
                        $('html, body').animate({ scrollTop: 0 }, 'slow');
                    }
                    catch (e) {
                        window.scrollTo(0, 0);
                    }
                }
            }
        }
    }
}