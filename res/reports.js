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

function sortTable(n, table_id) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById(table_id);
  switching = true;
  dir = "asc";
  while (switching) {
    switching = false;
    rows = table.getElementsByTagName("TR");
    for (i = 1; i < (rows.length - 1); i++) {
      if(rows[i].getElementsByTagName("TD").length == 0) continue;
      shouldSwitch = false;
      x = rows[i].getElementsByTagName("TD")[n];
      y = rows[i + 1].getElementsByTagName("TD")[n];
      if (dir == "asc") {
        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
          shouldSwitch= true;
          break;
        }
      } else if (dir == "desc") {
        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
          shouldSwitch= true;
          break;
        }
      }
    }
    if (shouldSwitch) {
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      switchcount ++;
    } else {
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
  }
}

function sortTableNum(n, table_id) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById(table_id);
  switching = true;
  dir = "asc";
  while (switching) {
    switching = false;
    rows = table.getElementsByTagName("TR");
    for (i = 1; i < (rows.length - 1); i++) {
      if(rows[i].getElementsByTagName("TD").length == 0) continue;
      shouldSwitch = false;
      x = rows[i].getElementsByTagName("TD")[n];
      y = rows[i + 1].getElementsByTagName("TD")[n];
      if (x.innerHTML == "-") x = 0;
      else x = parseFloat(x.innerHTML.replace(/,/, ''));
      if (y.innerHTML == "-") y = 0;
      else y = parseFloat(y.innerHTML.replace(/,/, ''))
      if (dir == "asc") {
        if (x > y) {
          shouldSwitch= true;
          break;
        }
      } else if (dir == "desc") {
        if (x < y) {
          shouldSwitch= true;
          break;
        }
      }
    }
    if (shouldSwitch) {
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      switchcount ++;
    } else {
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
  }
}

function sortTableValue(n, table_id) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById(table_id);
  switching = true;
  dir = "asc";
  while (switching) {
    switching = false;
    rows = table.getElementsByTagName("TR");
    for (i = 1; i < (rows.length - 1); i++) {
      if(rows[i].getElementsByTagName("TD").length == 0) continue;
      shouldSwitch = false;
      x = rows[i].getElementsByTagName("TD")[n].getAttribute("value");
      y = rows[i + 1].getElementsByTagName("TD")[n].getAttribute("value");
      if (dir == "asc") {
        if (parseFloat(x) > parseFloat(y)) {
          shouldSwitch= true;
          break;
        }
      } else if (dir == "desc") {
        if (parseFloat(x) < parseFloat(y)) {
          shouldSwitch= true;
          break;
        }
      }
    }
    if (shouldSwitch) {
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      switchcount ++;
    } else {
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
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
