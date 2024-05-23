const _hiddenClassRotation = 'd-none';
const _activeClassRotation = 'active';

function openLowerModalRotation(id) {
  const modalOverlay = document.querySelector('.overlay');
  const modalLowerLevel = document.querySelector(`.modal-lower-level-rotation-${id}`);

  modalOverlay.classList.remove(_hiddenClassRotation);
  modalLowerLevel.classList.remove(_hiddenClassRotation);
}

function closeLowerModalRotation(id) {

  const modalOverlay = document.querySelector('.overlay');
  const modalLowerLevel = document.querySelector(`.modal-lower-level-rotation-${id}`);
  const lvName = document.querySelectorAll('.level-name');
  const trLv1 = document.querySelectorAll('.parent-lv1');
  const trLv2 = document.querySelectorAll('.child-lv2');

  lvName.length && lvName.forEach((item) => {
    item.innerHTML = 'Cấp 1';
  });

  trLv1.length && trLv1.forEach((item) => {
    item.classList.remove(_hiddenClassRotation);
  });

  trLv2.length && trLv2.forEach((item) => {
    item.classList.add(_hiddenClassRotation);
  });

  modalOverlay.classList.add(_hiddenClassRotation);
  modalLowerLevel.classList.add(_hiddenClassRotation);
}

function changeUrlRotation(tabId) {
  if (tabId == 1) {
    window.history.pushState('', '', '?page=rotation&paged=1&tab=setting1');
  }
  if (tabId == 2) {
    window.history.pushState('', '', '?page=rotation&paged=1&tab=setting2');
  }
  if (tabId == 3) {
    window.history.pushState('', '', '?page=rotation&paged=1&tab=setting3');
  }
  if (tabId == 4) {
    window.history.pushState('', '', '?page=rotation&paged=1&tab=setting4');
  }
  if (tabId == 5) {
    window.history.pushState('', '', '?page=rotation&paged=1&tab=setting5');
  }

}


function handleSubmitRotation() {
  const arraySelect = ['dateFrom', 'monthFrom', 'yearFrom', 'dateTo', 'monthTo', 'yearTo'];
  const arrayTest = [];
  arraySelect.forEach((item) => {
    arrayTest.push(document.querySelector(`select[name="${item}"]`).value);
  });
  const check = arrayTest.every((item) => item !== '');

  if (check) {
    document.querySelector('.submitFilter').removeAttribute('disabled');
  } else {
    document.querySelector('.submitFilter').setAttribute('disabled', '');
  }
}

function showLevel2(userId, childId) {
  const modalLv1 = document.querySelector(`.modal-lower-level-${userId}`);
  const lvName = modalLv1.querySelector('.level-name');
  const trLv1 = modalLv1.querySelectorAll('.parent-lv1');
  const trLv2 = document.querySelectorAll(`.child-lv2-${childId}`);

  lvName.innerHTML = 'Cấp 2';
  trLv1.forEach((item) => {
    item.classList.add(_hiddenClassRotation);
  });
  trLv2.forEach((item) => {
    item.classList.remove(_hiddenClassRotation);
  });
}

function hideLevel2(userId, childId) {
  const modalLv1 = document.querySelector(`.modal-lower-level-${userId}`);
  const lvName = modalLv1.querySelector('.level-name');
  const trLv1 = modalLv1.querySelectorAll('.parent-lv1');
  const trLv2 = document.querySelectorAll(`.child-lv2-${childId}`);

  lvName.innerHTML = 'Cấp 1';
  trLv1.forEach((item) => {
    item.classList.remove(_hiddenClassRotation);
  });
  trLv2.forEach((item) => {
    item.classList.add(_hiddenClassRotation);
  });
}

function showTopCommission(id) {
  const modalLv1 = document.querySelector(`.modal-lower-level-${id}`);
  const tdCommission = modalLv1.querySelectorAll('.tdCommission');
  const lvName = modalLv1.querySelector('.level-name');

  lvName.innerHTML = 'Cấp 1';

  let max = Number(tdCommission[0].innerHTML);
  let maxTd = tdCommission[0];
  tdCommission.forEach((item) => {
    if (Number(item.innerHTML) >= max) {
      max = Number(item.innerHTML);
      maxTd = item;
    }
  });

  const trLv1 = modalLv1.querySelectorAll('.parent-lv1');
  trLv1.forEach((item) => {
    item.classList.add(_hiddenClassRotation);
  });
  maxTd.parentElement.classList.remove(_hiddenClassRotation);

  const childLv2 = modalLv1.querySelectorAll('.child-lv2');
  childLv2.forEach((item) => {
    item.classList.add(_hiddenClassRotation);
  });
}

function showTopIntroduce(id) {
  const modalLv1 = document.querySelector(`.modal-lower-level-${id}`);
  const tdIntroduce = modalLv1.querySelectorAll('.parent-lv1');
  let max = Number(tdIntroduce[0].getAttribute('data-number-lv2'));
  let maxTd = tdIntroduce[0];
  const lvName = modalLv1.querySelector('.level-name');

  lvName.innerHTML = 'Cấp 1';

  tdIntroduce.forEach((item) => {
    if (Number(item.getAttribute('data-number-lv2')) >= max) {
      max = Number(item.getAttribute('data-number-lv2'));
      maxTd = item;
    }
  });

  tdIntroduce.forEach((item) => {
    item.classList.add(_hiddenClassRotation);
  });
  maxTd.classList.remove(_hiddenClassRotation);

  const childLv2 = modalLv1.querySelectorAll('.child-lv2');
  childLv2.forEach((item) => {
    item.classList.add(_hiddenClassRotation);
  });
}

window.addEventListener('load', function() {
  /* Common */
  const removeMessageBtn = document.getElementById('remove-message');
  removeMessageBtn && removeMessageBtn.addEventListener('click', function() {
    document.getElementById('message').classList.add(hiddenClass);
  });
  /* Tabs */
  const _sPageURL = window.location.search.substring(1);
  const _params = _sPageURL.split('&');
  
  const tabSettingRotation1 = document.getElementById('tabSettingRotation1');
  const tabSettingRotation2 = document.getElementById('tabSettingRotation2');
  const tabSettingRotation3 = document.getElementById('tabSettingRotation3');
  const tabSettingRotation4 = document.getElementById('tabSettingRotation4');
  const tabSettingRotation5 = document.getElementById('tabSettingRotation5');
  const tabSettingRotation1Content = document.getElementById('tab-setting-rotation-1-content');
  const tabSettingRotation2Content = document.getElementById('tab-setting-rotation-2-content');
  const tabSettingRotation3Content = document.getElementById('tab-setting-rotation-3-content');
  const tabSettingRotation4Content = document.getElementById('tab-setting-rotation-4-content');
  const tabSettingRotation5Content = document.getElementById('tab-setting-rotation-5-content');

  function clickChangeTabRotation(tabIndex) {
    const listTab = [tabSettingRotation1, tabSettingRotation2, tabSettingRotation3,tabSettingRotation4,tabSettingRotation5];
    const listContentTab = [tabSettingRotation1Content, tabSettingRotation2Content, tabSettingRotation3Content,tabSettingRotation4Content,tabSettingRotation5Content];
    listTab.forEach((tab, index) => {
      Number(tabIndex) === Number(index + 1) ? tab.classList.add(_activeClassRotation) : tab.classList.remove(_activeClassRotation);
    });
    listContentTab.forEach((tabContent, index) => {console.log(index);
      Number(tabIndex) === Number(index + 1) ? tabContent.classList.add(_activeClassRotation) : tabContent.classList.remove(_activeClassRotation);
    });
  }

  if (tabSettingRotation1) {
    console.log(_params);
    if (_params.length === 1 || _params[2].split('=')[1] === 'setting1') {
      clickChangeTabRotation(1);
    } else if (_params[2].split('=')[1] === 'setting2') {
      clickChangeTabRotation(2);
    } else if (_params[2].split('=')[1] === 'setting3') {
      clickChangeTabRotation(3);
    } 
    else if (_params[2].split('=')[1] === 'setting4') {
      clickChangeTabRotation(4);
    } 
    else if (_params[2].split('=')[1] === 'setting5') {
      clickChangeTabRotation(5);
    } 
  }
  
  const _tabs = document.querySelectorAll('ul.nav-tabs-rotation > li');
  for (i = 0; i < _tabs.length; i++) {
    _tabs[i].addEventListener('click', _switchTab);
  }

  function _switchTab(event) {
    event.preventDefault();
    document.querySelector('ul.nav-tabs-rotation li.active').classList.remove(_activeClassRotation);
    document.querySelector('.tab-pane-rotation.active').classList.remove(_activeClassRotation);
    const clickedTab = event.currentTarget;
    const anchor = event.target;
    const activePaneID = anchor.getAttribute('href');
    clickedTab.classList.add(_activeClassRotation);
    
    document.querySelector(activePaneID).classList.add(_activeClassRotation);
  }
});
