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