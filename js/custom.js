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