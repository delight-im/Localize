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
function openTablePage(tableID, paginationClass, pageToOpen, doScrollUp) {
    if (typeof(tableID) !== 'undefined' && tableID !== null) {
        if (typeof(pageToOpen) !== 'undefined' && pageToOpen !== null) {
            var table = document.getElementById(tableID); // get the actual table DOM element
            var paginations = document.getElementsByClassName(paginationClass); // get the pagination bar DOM element
            if (typeof(table) !== 'undefined' && table !== null) { // make sure the actual table exists
                var counter;
                var c;
                // iterate backwards over the pagination bars because we may delete them
                for (var p = paginations.length-1; p >= 0; p--) {
                    if (typeof(paginations[p]) !== 'undefined' && paginations[p] !== null) { // make sure the pagination bar exists
                        if (pageToOpen >= 0) {
                            // mark the correct page in the pagination bar
                            counter = 0;
                            for (c = 0; c < paginations[p].childNodes.length; c++) {
                                if (paginations[p].childNodes[c].tagName == 'LI') {
                                    if (counter == pageToOpen) {
                                        paginations[p].childNodes[c].className = 'active';
                                    }
                                    else {
                                        paginations[p].childNodes[c].className = '';
                                    }
                                    counter++;
                                }
                            }
                        }
                        else {
                            // remove the pagination bar
                            paginations[p].parentNode.removeChild(paginations[p]);
                        }
                    }
                }

                // show the requested page of the table
                var pages = table.getElementsByClassName('table-page');
                var pageToOpenClass = pageToOpen >= 0 ? 'table-page table-page-'+pageToOpen : null;
                for (var i = 0; i < pages.length; i++) {
                    if (pages[i].className == pageToOpenClass || pageToOpenClass === null) {
                        pages[i].style.display = 'table-row-group';
                    }
                    else {
                        pages[i].style.display = 'none';
                    }
                }

                if (typeof doScrollUp !== 'undefined' && doScrollUp !== null && doScrollUp) {
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
function filterTable(tableID, paginationClass, filterPhrase) {
    if (typeof(tableID) !== 'undefined' && tableID !== null) {
        if (typeof(paginationClass) !== 'undefined' && paginationClass !== null) {
            if (typeof(filterPhrase) !== 'undefined' && filterPhrase !== null) {
                var table = document.getElementById(tableID); // get the actual table DOM element
                var paginations = document.getElementsByClassName(paginationClass); // get the pagination bar DOM element
                if (typeof(table) !== 'undefined' && table !== null) { // make sure the actual table exists
                    // iterate backwards over the pagination bars because we may delete them
                    for (var p = paginations.length-1; p >= 0; p--) {
                        if (typeof(paginations[p]) !== 'undefined' && paginations[p] !== null) { // make sure the pagination bar exists
                            // remove the pagination bar
                            paginations[p].parentNode.removeChild(paginations[p]);
                        }
                    }

                    // show all pages of the table
                    var pages = table.getElementsByClassName('table-page');
                    for (var i = 0; i < pages.length; i++) {
                        pages[i].style.display = 'table-row-group';
                    }

                    // filter the table rows
                    filterPhrase = filterPhrase.toLocaleLowerCase();
                    var tableBodies = table.getElementsByTagName('tbody');
                    var r;
                    for (var b = 0; b < tableBodies.length; b++) {
                        var rows = tableBodies[b].getElementsByTagName('tr');
                        for (r = 0; r < rows.length; r++) {
                            if (rows[r].innerHTML.toLocaleLowerCase().indexOf(filterPhrase) == -1) {
                                rows[r].style.display = 'none';
                            }
                            else {
                                rows[r].style.display = '';
                            }
                        }
                    }
                }
            }
        }
    }
}