/**
 * TruField Portal — Frontend JS
 */

(function () {
  'use strict';

  var TF = window.TruField || {};

  function requestGrowerSuggestions(q, callback) {
    if (!TF.ajaxUrl || !TF.nonce) {
      callback([]);
      return;
    }

    var url = TF.ajaxUrl + '?action=trufield_grower_search&nonce=' +
      encodeURIComponent(TF.nonce) + '&q=' + encodeURIComponent(q || '');

    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) {
        return;
      }

      if (xhr.status !== 200) {
        callback([]);
        return;
      }

      try {
        var data = JSON.parse(xhr.responseText);
        callback(data && data.success ? data.data : []);
      } catch (e) {
        callback([]);
      }
    };
    xhr.send();
  }

  function initAlertDismiss() {
    document.querySelectorAll('.tf-alert--success').forEach(function (el) {
      setTimeout(function () {
        el.style.transition = 'opacity .5s';
        el.style.opacity = '0';
        setTimeout(function () {
          if (el.parentNode) {
            el.parentNode.removeChild(el);
          }
        }, 500);
      }, 5000);
    });
  }

  function initShowMore() {
    document.querySelectorAll('.tf-show-more__toggle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var content = btn.parentNode.querySelector('.tf-show-more__content');
        var expanded = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', String(!expanded));
        if (expanded) {
          content.hidden = true;
          btn.querySelector('.tf-show-more__toggle-text').textContent = 'Show optional fields';
        } else {
          content.hidden = false;
          btn.querySelector('.tf-show-more__toggle-text').textContent = 'Hide optional fields';
        }
      });
    });
  }

  function initNavToggle() {
    var button = document.querySelector('.tf-nav-toggle');
    var navId = button && button.getAttribute('aria-controls');
    var nav = navId ? document.getElementById(navId) : null;

    if (!button || !nav) {
      return;
    }

    function setOpen(isOpen, shouldRestoreFocus) {
      button.setAttribute('aria-expanded', String(isOpen));
      nav.classList.toggle('is-open', isOpen);
      nav.setAttribute('data-nav-open', String(isOpen));

      if (!isOpen && shouldRestoreFocus) {
        button.focus();
      }
    }

    setOpen(false, false);

    button.addEventListener('click', function () {
      setOpen(button.getAttribute('aria-expanded') !== 'true', false);
    });

    nav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        setOpen(false, false);
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && button.getAttribute('aria-expanded') === 'true') {
        setOpen(false, true);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initAlertDismiss();
    initNavToggle();
    initShowMore();
  });
}());
