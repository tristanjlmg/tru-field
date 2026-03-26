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

  document.addEventListener('DOMContentLoaded', function () {
    initAlertDismiss();
    initShowMore();
  });
}());
