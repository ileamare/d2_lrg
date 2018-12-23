$(document).ready(function() {
  $('.list tbody tr').on('click', function() {
    $(this).toggleClass('highlighted');
  });

  // FIXME
  //$('thead.th').removeAttr('onclick');
  $('table.sortable').tablesorter({
    sortInitialOrder: 'desc',
    stringTo: 'min',
    sortReset: true,
  });
  //$('#heroes-pickban').tablesorter();
});


// code from example on w3c

function showModal(text, cap) {
    document.getElementById('modal-text').innerHTML = text;
    document.getElementsByClassName('modal-header')[0].innerHTML = cap;
    document.getElementById('modal-box').style.display = "block";
}
window.onclick = function(event) {
    if (event.target == document.getElementById('modal-box')) {
        document.getElementById('modal-box').style.display = "none";
        document.getElementById('modal-sublevel').style.display = "none";
    }
}

function expandSubModal(text) {
    document.getElementById('modal-sublevel').innerHTML = text;
    document.getElementById('modal-sublevel').style.display = "block";
}
document.getElementById('modal-text').onclick = function(event) {
    if (event.target == document.getElementById('modal-text')) {
        document.getElementById('modal-sublevel').style.display = "none";
    }
}

function switchTab(evt, moduleID, className) {
    var i, tabcontent, tablinks;
    parent = document.getElementById(moduleID).parentNode;

    tabcontent = parent.getElementsByClassName(className);
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].className = tabcontent[i].className.replace(" active", "");
    }

    tablinks = parent.getElementsByClassName(className+"-selector");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(moduleID).className += " active";
    evt.currentTarget.className += " active";
}


function select_modules_link(a) {
  if(a.value)
    window.location=a.value;
}
