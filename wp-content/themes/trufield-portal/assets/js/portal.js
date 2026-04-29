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
        var showLabel = btn.getAttribute('data-show-label') || 'Show optional fields';
        var hideLabel = btn.getAttribute('data-hide-label') || 'Hide optional fields';
        btn.setAttribute('aria-expanded', String(!expanded));
        if (expanded) {
          content.hidden = true;
          btn.querySelector('.tf-show-more__toggle-text').textContent = showLabel;
        } else {
          content.hidden = false;
          btn.querySelector('.tf-show-more__toggle-text').textContent = hideLabel;
        }
      });
    });
  }

  function initDateInputs() {
    document.querySelectorAll('[data-tf-date-input]').forEach(function (input) {
      var placeholder = input.getAttribute('data-date-placeholder') || 'MM/DD/YYYY';

      function syncMode() {
        var value = String(input.value || '').trim();

        if (value === '') {
          if (input.type !== 'text') {
            input.type = 'text';
          }
          input.placeholder = placeholder;
          return;
        }

        if (input.type !== 'date') {
          input.type = 'date';
        }
      }

      input.addEventListener('focus', function () {
        if (String(input.value || '').trim() === '') {
          input.type = 'date';
        }
      });

      input.addEventListener('blur', syncMode);
      input.addEventListener('change', syncMode);

      syncMode();
    });
  }

  function initRetailerPickers() {
    document.querySelectorAll('[data-tf-retailer-picker]').forEach(function (wrapper) {
      var select = wrapper.querySelector('[data-tf-retailer-select]');
      var manualWrap = wrapper.querySelector('[data-tf-retailer-manual]');
      var manualInput = wrapper.querySelector('[data-tf-retailer-manual-input]');
      var directory = {};
      var assignmentControlId = wrapper.getAttribute('data-tf-assignment-control') || '';
      var assignmentControl = assignmentControlId ? document.getElementById(assignmentControlId) : null;
      var autoFillFields = {
        retailer_branch_location: document.getElementById('retailer_branch_location'),
        retailer_key_contact: document.getElementById('retailer_key_contact'),
        retailer_contact_phone: document.getElementById('retailer_contact_phone'),
        retailer_address: document.getElementById('retailer_address'),
        retailer_city: document.getElementById('retailer_city'),
        phase_1_state_region: document.getElementById('phase_1_state_region')
      };

      if (!select || !manualWrap || !manualInput) {
        return;
      }

    try {
      directory = JSON.parse(wrapper.getAttribute('data-tf-retailer-directory') || '{}');
    } catch (error) {
      directory = {};
    }

    function getAssignmentContext() {
      var fallbackId = String(wrapper.getAttribute('data-tf-assigned-rep-id') || '').trim();
      var fallbackLabel = String(wrapper.getAttribute('data-tf-assigned-rep-label') || '').trim();
      var value = '';
      var label = '';

      if (assignmentControl) {
        value = String(assignmentControl.value || '').trim();
        if (assignmentControl.tagName === 'SELECT' && assignmentControl.selectedIndex >= 0) {
          label = String(assignmentControl.options[assignmentControl.selectedIndex].text || '').trim();
        }

        return {
          id: value,
          label: value ? label : ''
        };
      }

      return {
        id: fallbackId,
        label: fallbackLabel
      };
    }

    function retailerMatchesAssignment(retailer) {
      var context = getAssignmentContext();
      var assignmentIds = Array.isArray(retailer.assignment_ids) ? retailer.assignment_ids.map(String) : [];
      var assignmentLabels = Array.isArray(retailer.assignment_labels) ? retailer.assignment_labels.map(function (item) {
        return String(item || '').trim().toLowerCase();
      }) : [];

      if (!context.id && !context.label) {
        return true;
      }

      if (context.id && assignmentIds.indexOf(String(context.id)) !== -1) {
        return true;
      }

      if (context.label && assignmentLabels.indexOf(String(context.label).toLowerCase()) !== -1) {
        return true;
      }

      return false;
    }

    function applyRetailerData(retailerName) {
      var retailer = directory && retailerName ? directory[retailerName] : null;

      if (!retailer) {
        return;
      }

      Object.keys(autoFillFields).forEach(function (fieldName) {
        var control = autoFillFields[fieldName];
        if (!control || control.disabled || control.readOnly) {
          return;
        }

        control.value = String(retailer[fieldName] || '');
      });
    }

    function syncRetailerOptions() {
      var hasVisibleRetailers = false;
      var selectedIsVisible = !select.value || String(select.value).toLowerCase() === 'other';

      Array.prototype.slice.call(select.options).forEach(function (option) {
        var optionValue = String(option.value || '');
        var retailer = directory[optionValue];

        if (!optionValue || optionValue.toLowerCase() === 'other' || !retailer) {
          option.hidden = false;
          option.disabled = false;
          return;
        }

        var isVisible = retailerMatchesAssignment(retailer);
        option.hidden = !isVisible;
        option.disabled = !isVisible;

        if (isVisible) {
          hasVisibleRetailers = true;
        }

        if (isVisible && optionValue === String(select.value || '')) {
          selectedIsVisible = true;
        }
      });

      if (!selectedIsVisible) {
        select.value = hasVisibleRetailers ? '' : 'other';
        if (!hasVisibleRetailers) {
          manualInput.value = '';
        }
      }
    }

      function syncRetailerMode() {
        var isOther = String(select.value || '').toLowerCase() === 'other';
        manualWrap.hidden = !isOther;

        if (!isOther) {
          manualInput.value = '';
      applyRetailerData(String(select.value || ''));
        }
      }

      select.addEventListener('change', syncRetailerMode);
    if (assignmentControl) {
      assignmentControl.addEventListener('change', function () {
        syncRetailerOptions();
        syncRetailerMode();
      });
    }
    syncRetailerOptions();
      syncRetailerMode();
    });
  }

  function initPhaseSubsteps() {
    document.querySelectorAll('[data-tf-phase-substeps]').forEach(function (wrapper) {
      var form = wrapper.closest('form');
      var phase = Number(wrapper.getAttribute('data-phase') || '1');
      var hiddenInput = form && form.querySelector('[data-tf-phase-step-input]');
      var tabs = Array.prototype.slice.call(wrapper.querySelectorAll('[data-tf-phase-step-tab]'));
      var panels = Array.prototype.slice.call(wrapper.querySelectorAll('[data-tf-phase-step-panel]'));
      var plantFieldInput = form && form.querySelector('input[name="plant_field_id"]');
      var storageKey = plantFieldInput ? 'tf-phase-' + String(phase || 1) + '-step-' + String(plantFieldInput.value || '') : '';
      var requestedInitialStep = Number(wrapper.getAttribute('data-initial-step') || '0');
      var storedStep = storageKey && window.sessionStorage ? Number(window.sessionStorage.getItem(storageKey) || '0') : 0;
      var currentStep = requestedInitialStep > 0 ? requestedInitialStep : (storedStep > 0 ? storedStep : 1);

      if (!form || !hiddenInput || !tabs.length || !panels.length) {
        return;
      }

      function getPanel(step) {
        return panels.find(function (panel) {
          return Number(panel.getAttribute('data-step') || '0') === step;
        }) || null;
      }

      function getControl(fieldName) {
        if (!form || !fieldName) {
          return null;
        }

        var control = form.elements[fieldName];
        if (!control) {
          return null;
        }

        if (typeof control.length === 'number' && !control.tagName) {
          var firstNonHidden = null;

          for (var index = 0; index < control.length; index += 1) {
            if ((control[index].type === 'checkbox' || control[index].type === 'radio') && control[index].checked) {
              return control[index];
            }

            if (!firstNonHidden && control[index].type !== 'hidden') {
              firstNonHidden = control[index];
            }
          }

          return firstNonHidden || control[0] || null;
        }

        return control;
      }

      function getFieldLabel(fieldName) {
        var label = form.querySelector('[for="' + fieldName + '"]');
        var text = label ? String(label.textContent || '') : fieldName;
        return text.replace('*', '').trim();
      }

    function getFieldValidationMessage(control, fieldName) {
      if (!control || control.disabled) {
        return '';
      }

      var valueMissing = !!(control.validity && control.validity.valueMissing);
      var isEmpty = String(control.value || '').trim() === '';

      if (valueMissing || isEmpty) {
        return '';
      }

      if (typeof control.checkValidity === 'function' && !control.checkValidity()) {
        return getFieldLabel(fieldName);
      }

      if (String(control.type || '').toLowerCase() === 'email') {
        var emailValue = String(control.value || '').trim();
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (emailValue !== '' && !emailPattern.test(emailValue)) {
          return getFieldLabel(fieldName);
        }
      }

      return '';
    }

      function focusControl(fieldName) {
        if (fieldName === 'retailer_name') {
          var retailerSelect = form.querySelector('[data-tf-retailer-select]');
          var retailerManual = form.querySelector('[data-tf-retailer-manual-input]');

          if (retailerSelect && String(retailerSelect.value || '').toLowerCase() === 'other' && retailerManual) {
            retailerManual.focus();
            return;
          }
        }

        var visibleControl = document.getElementById(fieldName) || getControl(fieldName);
        if (visibleControl && typeof visibleControl.focus === 'function' && !visibleControl.disabled) {
          visibleControl.focus();
        }
      }

      function validatePanel(panel) {
        var errorBox = panel.querySelector('[data-tf-step-error]');
        var fieldNames = String(panel.getAttribute('data-required-fields') || '')
          .split(',')
          .map(function (fieldName) { return fieldName.trim(); })
          .filter(Boolean);
        var invalidLabels = [];
        var firstInvalidField = '';
        var manualOverride = getControl('field_location_manual_override');
        var manualOverrideEnabled = !!(manualOverride && manualOverride.checked);

        fieldNames.forEach(function (fieldName) {
          var control;
          var value;

          if (fieldName === 'field_location_address' && manualOverrideEnabled) {
            return;
          }

          control = getControl(fieldName);
          if (!control) {
            return;
          }

          if (fieldName === 'retailer_name' && String(control.value || '').toLowerCase() === 'other') {
            var manualControl = getControl('retailer_name_manual');
            var manualValue = manualControl ? String(manualControl.value || '').trim() : '';

            if (manualValue === '') {
              invalidLabels.push(getFieldLabel(fieldName));
              if (!firstInvalidField) {
                firstInvalidField = fieldName;
              }
            }
            return;
          }

          value = String(control.value || '').trim();
          if (value === '') {
            invalidLabels.push(getFieldLabel(fieldName));
            if (!firstInvalidField) {
              firstInvalidField = fieldName;
            }
			return;
		  }

		  var invalidMessage = getFieldValidationMessage(control, fieldName);
		  if (invalidMessage !== '') {
			invalidLabels.push(invalidMessage);
			if (!firstInvalidField) {
			  firstInvalidField = fieldName;
			}
          }
        });

        if (errorBox) {
          if (invalidLabels.length) {
            errorBox.hidden = false;
			var summary = invalidLabels.length === fieldNames.length
			  ? 'Complete the required fields in this section before continuing.'
			  : 'Correct the highlighted fields in this section before continuing.';
			errorBox.innerHTML = '<strong>' + summary + '</strong><span>' + invalidLabels.join(', ') + '</span>';
          } else {
            errorBox.hidden = true;
            errorBox.innerHTML = '';
          }
        }

        if (firstInvalidField) {
          focusControl(firstInvalidField);
        }

        return invalidLabels.length === 0;
      }

      function syncStepQuery(step) {
        if (!window.history || typeof window.history.replaceState !== 'function' || !window.URL) {
          return;
        }

        var url = new window.URL(window.location.href);
        url.searchParams.set('phase_' + String(phase || 1) + '_step', String(step));
        window.history.replaceState({}, '', url.toString());
      }

      function syncStepState(step) {
        currentStep = step;
        hiddenInput.value = String(step);

        if (storageKey && window.sessionStorage) {
          window.sessionStorage.setItem(storageKey, String(step));
        }

        syncStepQuery(step);

        tabs.forEach(function (tab) {
          var tabStep = Number(tab.getAttribute('data-step') || '0');
          var isActive = tabStep === step;
          var isComplete = tabStep < step;
          var dot = tab.querySelector('.tf-phase-substeps__tab-dot');

          tab.classList.toggle('is-active', isActive);
          tab.classList.toggle('is-complete', isComplete);
          tab.setAttribute('aria-current', isActive ? 'step' : 'false');

          if (dot) {
	        dot.textContent = isComplete ? '✓' : String(tabStep);
	      }
        });

        panels.forEach(function (panel) {
          var panelStep = Number(panel.getAttribute('data-step') || '0');
          var isActive = panelStep === step;
          panel.hidden = !isActive;
          panel.classList.toggle('is-active', isActive);
        });
      }

      tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          var targetStep = Number(tab.getAttribute('data-step') || '0');
          var activePanel = getPanel(currentStep);

          if (!targetStep || targetStep === currentStep) {
            return;
          }

          if (targetStep > currentStep && activePanel && !validatePanel(activePanel)) {
            return;
          }

          syncStepState(targetStep);
        });
      });

      panels.forEach(function (panel) {
        var prevButton = panel.querySelector('[data-tf-phase-step-prev]');
        var nextButton = panel.querySelector('[data-tf-phase-step-next]');
		var submitButtons = Array.prototype.slice.call(panel.querySelectorAll('button[type="submit"]'));

        if (prevButton) {
          prevButton.addEventListener('click', function () {
            syncStepState(Math.max(1, currentStep - 1));
          });
        }

        if (nextButton) {
          nextButton.addEventListener('click', function () {
            if (!validatePanel(panel)) {
              return;
            }

            syncStepState(Math.min(panels.length, currentStep + 1));
          });
        }

    submitButtons.forEach(function (button) {
      button.addEventListener('click', function (event) {
        var actionValue = String(button.value || button.getAttribute('value') || '');
        if (actionValue === 'verify_address' || button.hasAttribute('formnovalidate')) {
          return;
        }

        if (!validatePanel(panel)) {
          event.preventDefault();
        }
      });
    });
      });

      syncStepState(Math.max(1, Math.min(panels.length, currentStep)));
    });
  }

  function initTrialSearch() {
    var searchWrapper = document.querySelector('[data-tf-trial-search]');
    var input = searchWrapper && searchWrapper.querySelector('[data-tf-trial-search-input]');
    var hint = searchWrapper && searchWrapper.querySelector('[data-tf-trial-search-hint]');
    var grid = document.querySelector('[data-tf-trial-grid]');
    var emptyState = document.querySelector('[data-tf-trial-empty]');
    var count = document.querySelector('[data-tf-trial-count]');

    if (!searchWrapper || !input || !grid) {
      return;
    }

    var cards = Array.prototype.slice.call(grid.querySelectorAll('[data-tf-trial-card]'));
    var totalCount = cards.length;

    function updateCount(visibleCount) {
      if (!count) {
        return;
      }

      var singularLabel = count.getAttribute('data-singular-label') || 'record';
      var pluralLabel = count.getAttribute('data-plural-label') || 'records';
      count.textContent = visibleCount + ' ' + (visibleCount === 1 ? singularLabel : pluralLabel);
    }

    function updateHint(query, visibleCount) {
      if (!hint) {
        return;
      }

      if (!query) {
        hint.textContent = 'Start typing to filter the visible field cards instantly.';
        return;
      }

      hint.textContent = visibleCount === 1
        ? '1 matching trial'
        : visibleCount + ' matching trials';
    }

    function applyFilter() {
      var query = String(input.value || '').trim().toLowerCase();
      var visibleCount = 0;

      cards.forEach(function (card) {
        var haystack = String(card.getAttribute('data-tf-search') || '').toLowerCase();
        var isMatch = !query || haystack.indexOf(query) !== -1;

        card.hidden = !isMatch;
        card.setAttribute('aria-hidden', isMatch ? 'false' : 'true');

        if (isMatch) {
          visibleCount += 1;
        }
      });

      updateCount(visibleCount);
      updateHint(query, visibleCount);

      if (emptyState) {
        emptyState.hidden = visibleCount !== 0;
      }
    }

    input.addEventListener('input', applyFilter);
    input.addEventListener('search', applyFilter);

    updateCount(totalCount);
    updateHint('', totalCount);
  }

  function initLeaderboardSearch() {
    var searchWrapper = document.querySelector('[data-tf-leaderboard-search]');
    var input = searchWrapper && searchWrapper.querySelector('[data-tf-leaderboard-search-input]');
    var emptyState = searchWrapper && searchWrapper.querySelector('[data-tf-leaderboard-empty]');

    if (!searchWrapper || !input) {
      return;
    }

    var rows = Array.prototype.slice.call(searchWrapper.querySelectorAll('[data-tf-leaderboard-row]'));

    function applyFilter() {
      var query = String(input.value || '').trim().toLowerCase();
      var visibleCount = 0;

      rows.forEach(function (row) {
        var haystack = String(row.getAttribute('data-tf-search') || '').toLowerCase();
        var isMatch = !query || haystack.indexOf(query) !== -1;

        row.hidden = !isMatch;

        if (isMatch) {
          visibleCount += 1;
        }
      });

      if (emptyState) {
        emptyState.hidden = visibleCount !== 0;
      }
    }

    input.addEventListener('input', applyFilter);
    input.addEventListener('search', applyFilter);
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

  function setLocationStatus(statusEl, message, state) {
    if (!statusEl) {
      return;
    }

    statusEl.textContent = message;
    statusEl.setAttribute('data-state', state || 'default');
  }

  function requestAddressGeocode(address, callback) {
    if (!TF.ajaxUrl || !TF.geocodeNonce) {
      callback(null);
      return;
    }

    var url = TF.ajaxUrl + '?action=trufield_geocode_address&nonce=' +
      encodeURIComponent(TF.geocodeNonce) + '&address=' + encodeURIComponent(address || '');

    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) {
        return;
      }

      if (xhr.status !== 200) {
        callback(null);
        return;
      }

      try {
        var data = JSON.parse(xhr.responseText);
        callback(data && data.success ? data.data : null);
      } catch (e) {
        callback(null);
      }
    };
    xhr.send();
  }

  function googlePlacesReady() {
    return !!(window.google && window.google.maps && window.google.maps.places);
  }

  function updateLocationMap(wrapper, lat, lng, address) {
    if (!wrapper) {
      return;
    }

    var mapEl = wrapper.querySelector('[data-tf-location-map]');
    var mapNoteEl = wrapper.querySelector('[data-tf-location-map-note]');

    if (!mapEl) {
      return;
    }

    if (lat === '' || lng === '' || lat === null || lng === null || lat === undefined || lng === undefined || isNaN(Number(lat)) || isNaN(Number(lng))) {
      mapEl.innerHTML = '';
      if (mapNoteEl) {
        mapNoteEl.hidden = false;
        mapNoteEl.textContent = 'Map preview will appear after the address is verified.';
      }
      return;
    }

    var query = '';
    if (address && String(address).trim() !== '') {
      query = encodeURIComponent(String(address).trim());
    } else {
      query = encodeURIComponent(String(lat) + ',' + String(lng));
    }

    mapEl.innerHTML = '<iframe class="tf-phase-location__map-frame" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://maps.google.com/maps?q=' + query + '&z=15&output=embed" title="Field location map preview"></iframe>';

    if (mapNoteEl) {
      mapNoteEl.hidden = true;
    }
  }

  function bindLocationField(wrapper) {
    if (!wrapper || wrapper.getAttribute('data-tf-location-bound') === 'true') {
      return;
    }

    var addressInput = wrapper.querySelector('[data-tf-location-address]');
    var latInput = wrapper.querySelector('[data-tf-location-lat]');
    var lngInput = wrapper.querySelector('[data-tf-location-lng]');
    var hiddenLatInput = wrapper.querySelector('[data-tf-location-lat-hidden]');
    var hiddenLngInput = wrapper.querySelector('[data-tf-location-lng-hidden]');
    var overrideInput = wrapper.querySelector('[data-tf-location-override]');
    var verifyButton = wrapper.querySelector('[data-tf-location-verify]');
    var statusEl = wrapper.querySelector('[data-tf-location-status]');
    var lockNoteEl = wrapper.querySelector('[data-tf-location-lock-note]');
    if (typeof wrapper._tfSuppressAddressReset !== 'boolean') {
      wrapper._tfSuppressAddressReset = false;
    }
    if (typeof wrapper._tfLocationRequestId !== 'number') {
      wrapper._tfLocationRequestId = 0;
    }
    if (typeof wrapper._tfVerifiedAddress !== 'string') {
      wrapper._tfVerifiedAddress = '';
    }

    if (!addressInput || !latInput || !lngInput || !hiddenLatInput || !hiddenLngInput || !overrideInput) {
      return;
    }

    function normalizeAddress(value) {
      return String(value || '').trim().replace(/\s+/g, ' ').toLowerCase();
    }

    function setVerifyButtonPending(isPending) {
      if (!verifyButton) {
        return;
      }

      verifyButton.disabled = isPending;
      verifyButton.textContent = isPending ? 'Verifying...' : 'Verify Address';
    }

    function cancelPendingLookup() {
      wrapper._tfLocationRequestId += 1;
      setVerifyButtonPending(false);
    }

    function beginLookup(address, pendingMessage) {
      var normalized = normalizeAddress(address);

      wrapper._tfLocationRequestId += 1;
      setVerifyButtonPending(true);
      if (pendingMessage) {
        setLocationStatus(statusEl, pendingMessage, 'pending');
      }

      return {
        id: wrapper._tfLocationRequestId,
        address: normalized
      };
    }

    function lookupIsCurrent(request) {
      if (!request) {
        return false;
      }

      return request.id === wrapper._tfLocationRequestId && normalizeAddress(addressInput.value) === request.address;
    }

    function finishLookup(request, data, warningMessage) {
      if (!lookupIsCurrent(request)) {
        return false;
      }

      setVerifyButtonPending(false);

      if (!data || data.lat === null || data.lng === null) {
        setLocationStatus(statusEl, warningMessage || 'We could not verify that address. Choose a Google suggestion or enable manual override.', 'warning');
        return true;
      }

      wrapper._tfSuppressAddressReset = true;
      if (typeof wrapper._tfSetCoordinates === 'function') {
        wrapper._tfSetCoordinates(data.lat, data.lng, data.address || addressInput.value);
      }

      if (typeof wrapper._tfSyncLocationMode === 'function') {
        wrapper._tfSyncLocationMode();
      }

      return true;
    }

    function syncHiddenCoordinates() {
      hiddenLatInput.value = latInput.value;
      hiddenLngInput.value = lngInput.value;
    }

    function hasCoordinates() {
      return String(hiddenLatInput.value || '').trim() !== '' && String(hiddenLngInput.value || '').trim() !== '';
    }

    function syncMode() {
      var isManual = overrideInput.checked;
      wrapper.classList.toggle('is-manual', isManual);
      wrapper.classList.toggle('is-locked', !isManual);
      latInput.disabled = !isManual;
      lngInput.disabled = !isManual;
      latInput.readOnly = !isManual;
      lngInput.readOnly = !isManual;
      latInput.setAttribute('aria-disabled', String(!isManual));
      lngInput.setAttribute('aria-disabled', String(!isManual));
      latInput.tabIndex = isManual ? 0 : -1;
      lngInput.tabIndex = isManual ? 0 : -1;

      if (!isManual) {
        latInput.blur();
        lngInput.blur();
      }

      if (isManual) {
        setLocationStatus(statusEl, 'Manual override enabled. Enter latitude and longitude directly if an address cannot be verified.', 'manual');
        if (lockNoteEl) {
          lockNoteEl.textContent = 'Manual override is on. Latitude and longitude are now editable.';
        }
        syncHiddenCoordinates();
        return;
      }

      if (lockNoteEl) {
        lockNoteEl.textContent = 'Latitude and longitude stay locked until manual override is enabled.';
      }

      if (hasCoordinates() && String(addressInput.value || '').trim() !== '') {
        setLocationStatus(statusEl, 'Verified address selected. Coordinates are synced from Google Places.', 'verified');
      } else if (TF.googlePlacesEnabled) {
        setLocationStatus(statusEl, 'Search and choose a suggested address to verify coordinates.', 'pending');
      } else {
        setLocationStatus(statusEl, 'Google Places search is unavailable here. Enable manual override to enter coordinates.', 'warning');
      }
    }

    function clearCoordinates() {
      cancelPendingLookup();
      latInput.value = '';
      lngInput.value = '';
      wrapper._tfVerifiedAddress = '';
      syncHiddenCoordinates();
      updateLocationMap(wrapper, null, null, '');
    }

    function setCoordinates(lat, lng, resolvedAddress) {
      latInput.value = Number(lat).toFixed(6);
      lngInput.value = Number(lng).toFixed(6);
      if (resolvedAddress) {
        addressInput.value = resolvedAddress;
      }
      wrapper._tfVerifiedAddress = normalizeAddress(resolvedAddress || addressInput.value);
      syncHiddenCoordinates();
      updateLocationMap(wrapper, latInput.value, lngInput.value, resolvedAddress || addressInput.value);
      setLocationStatus(statusEl, 'Verified address selected. Coordinates are synced from Google Places.', 'verified');
    }

    overrideInput.addEventListener('change', syncMode);

    addressInput.addEventListener('input', function () {
      if (overrideInput.checked) {
        return;
      }

      if (wrapper._tfSuppressAddressReset) {
        wrapper._tfSuppressAddressReset = false;
        return;
      }

      clearCoordinates();
      if (TF.googlePlacesEnabled) {
        setLocationStatus(statusEl, 'Choose a suggested address to verify coordinates, or enable manual override.', 'pending');
      }
    });

    latInput.addEventListener('input', function () {
      if (overrideInput.checked) {
        syncHiddenCoordinates();
        setLocationStatus(statusEl, 'Manual coordinates entered. Longitude is also required before completion.', 'manual');
      }
    });

    [latInput, lngInput].forEach(function (input) {
      input.addEventListener('focus', function () {
        if (!overrideInput.checked) {
          input.blur();
        }
      });

      input.addEventListener('mousedown', function (event) {
        if (!overrideInput.checked) {
          event.preventDefault();
          input.blur();
        }
      });
    });

    lngInput.addEventListener('input', function () {
      if (overrideInput.checked) {
        syncHiddenCoordinates();
        updateLocationMap(wrapper, latInput.value, lngInput.value, addressInput.value);
        setLocationStatus(statusEl, 'Manual coordinates entered. Address is optional while override is enabled.', 'manual');
      }
    });

    if (verifyButton) {
      verifyButton.addEventListener('click', function (event) {
        var address = String(addressInput.value || '').trim();

        event.preventDefault();

        if (overrideInput.checked) {
          setLocationStatus(statusEl, 'Manual override is enabled. Enter latitude and longitude directly or turn it off to verify an address.', 'manual');
          return;
        }

        if (!address) {
          setLocationStatus(statusEl, 'Enter an address before verifying it.', 'warning');
          return;
        }

        if (hasCoordinates() && normalizeAddress(address) === wrapper._tfVerifiedAddress) {
          setLocationStatus(statusEl, 'Verified address selected. Coordinates are synced from Google Places.', 'verified');
          return;
        }

        var request = beginLookup(address, 'Verifying address...');
        requestAddressGeocode(address, function (data) {
          finishLookup(request, data, 'We could not verify that address. Choose a Google suggestion or enable manual override.');
        });
      });
    }

    wrapper._tfSetCoordinates = setCoordinates;
    wrapper._tfSyncLocationMode = syncMode;
    wrapper._tfBeginAddressLookup = beginLookup;
    wrapper._tfFinishAddressLookup = finishLookup;
    wrapper._tfCancelAddressLookup = cancelPendingLookup;
    wrapper.setAttribute('data-tf-location-bound', 'true');
    syncHiddenCoordinates();
    syncMode();
    updateLocationMap(wrapper, hiddenLatInput.value, hiddenLngInput.value, addressInput.value);
  }

  function connectLocationAutocomplete(wrapper) {
    if (!wrapper || wrapper.getAttribute('data-tf-location-autocomplete') === 'true') {
      return;
    }

    if (!googlePlacesReady()) {
      return;
    }

    var addressInput = wrapper.querySelector('[data-tf-location-address]');
    var overrideInput = wrapper.querySelector('[data-tf-location-override]');
    if (!addressInput || !overrideInput) {
      return;
    }

    var geocoder = new window.google.maps.Geocoder();
    var geocodeTimer = null;

    function hasCoordinates() {
      var latValue = wrapper.querySelector('[data-tf-location-lat-hidden]');
      var lngValue = wrapper.querySelector('[data-tf-location-lng-hidden]');
      return !!latValue && !!lngValue && String(latValue.value || '').trim() !== '' && String(lngValue.value || '').trim() !== '';
    }

    function geocodeAddress() {
      var address = String(addressInput.value || '').trim();

      if (overrideInput.checked || !address || hasCoordinates()) {
        return;
      }

      var request = typeof wrapper._tfBeginAddressLookup === 'function'
        ? wrapper._tfBeginAddressLookup(address, 'Verifying address...')
        : null;

      geocoder.geocode({ address: address }, function (results, status) {
        if (status !== 'OK' || !results || !results.length || !results[0].geometry || !results[0].geometry.location) {
          requestAddressGeocode(address, function (data) {
            if (typeof wrapper._tfFinishAddressLookup === 'function') {
              wrapper._tfFinishAddressLookup(request, data, 'We could not verify that address. Choose a Google suggestion or enable manual override.');
              return;
            }
          });
          return;
        }

        if (typeof wrapper._tfFinishAddressLookup === 'function') {
          wrapper._tfFinishAddressLookup(request, {
            lat: results[0].geometry.location.lat(),
            lng: results[0].geometry.location.lng(),
            address: results[0].formatted_address || address
          });
        }
      });
    }

    function queueGeocode() {
      if (geocodeTimer) {
        window.clearTimeout(geocodeTimer);
      }

      geocodeTimer = window.setTimeout(geocodeAddress, 250);
    }

    wrapper._tfRunAddressVerification = geocodeAddress;
    wrapper._tfQueueAddressVerification = queueGeocode;

    var autocomplete = new window.google.maps.places.Autocomplete(addressInput, {
      fields: ['formatted_address', 'geometry', 'name']
    });

    autocomplete.addListener('place_changed', function () {
      var place = autocomplete.getPlace();
      if (!place || !place.geometry || !place.geometry.location) {
        setLocationStatus(wrapper.querySelector('[data-tf-location-status]'), 'We could not verify that address. Choose a suggestion or enable manual override.', 'warning');
        return;
      }

      if (typeof wrapper._tfCancelAddressLookup === 'function') {
        wrapper._tfCancelAddressLookup();
      }
      wrapper._tfSuppressAddressReset = true;
      if (typeof wrapper._tfSetCoordinates === 'function') {
        wrapper._tfSetCoordinates(
          place.geometry.location.lat(),
          place.geometry.location.lng(),
          place.formatted_address || place.name || addressInput.value
        );
      }

      overrideInput.checked = false;
      if (typeof wrapper._tfSyncLocationMode === 'function') {
        wrapper._tfSyncLocationMode();
      }
    });

    addressInput.addEventListener('blur', queueGeocode);
    addressInput.addEventListener('change', queueGeocode);
    addressInput.addEventListener('input', function () {
      var address = String(addressInput.value || '').trim();
      if (overrideInput.checked || wrapper._tfSuppressAddressReset || address.length < 8) {
        return;
      }

      queueGeocode();
    });

    if (String(addressInput.value || '').trim() !== '' && !hasCoordinates()) {
      queueGeocode();
    }

    wrapper.setAttribute('data-tf-location-autocomplete', 'true');
  }

  function initPhaseLocationFields() {
    document.querySelectorAll('[data-tf-location]').forEach(function (wrapper) {
      bindLocationField(wrapper);
      connectLocationAutocomplete(wrapper);
    });
  }

  function bindFallbackGeocode(wrapper) {
    if (!wrapper || wrapper.getAttribute('data-tf-location-fallback-bound') === 'true') {
      return;
    }

    var addressInput = wrapper.querySelector('[data-tf-location-address]');
    if (!addressInput) {
      return;
    }

    var fallbackTimer = null;

    var queueFallback = function () {
      if (fallbackTimer) {
        window.clearTimeout(fallbackTimer);
      }

      fallbackTimer = window.setTimeout(function () {
      var address = String(addressInput.value || '').trim();
      if (!address) {
        return;
      }

      var request = typeof wrapper._tfBeginAddressLookup === 'function'
        ? wrapper._tfBeginAddressLookup(address, 'Verifying address...')
        : null;

      requestAddressGeocode(address, function (data) {
        if (typeof wrapper._tfFinishAddressLookup === 'function') {
          wrapper._tfFinishAddressLookup(request, data, 'We could not verify that address. Enable manual override to enter coordinates.');
        }
      });
      }, 350);
    };

    wrapper._tfRunAddressVerification = queueFallback;
    wrapper._tfQueueAddressVerification = queueFallback;

    addressInput.addEventListener('blur', queueFallback);
    addressInput.addEventListener('change', queueFallback);
    addressInput.addEventListener('input', function () {
      var address = String(addressInput.value || '').trim();
      if (wrapper._tfSuppressAddressReset || address.length < 8) {
        return;
      }

      queueFallback();
    });
    wrapper.setAttribute('data-tf-location-fallback-bound', 'true');
  }

  function ensurePhaseLocationBindings(attempt) {
    var nextAttempt = typeof attempt === 'number' ? attempt : 0;

    initPhaseLocationFields();

    if (googlePlacesReady()) {
      return;
    }

    if (nextAttempt >= 20) {
      document.querySelectorAll('[data-tf-location]').forEach(function (wrapper) {
        bindFallbackGeocode(wrapper);
      });
      return;
    }

    window.setTimeout(function () {
      ensurePhaseLocationBindings(nextAttempt + 1);
    }, 250);
  }

  function initStandCountDelta() {
    document.querySelectorAll('form.tf-phase-form').forEach(function (form) {
      var treatedInput = form.querySelector('[data-tf-stand-count-treated]');
      var untreatedInput = form.querySelector('[data-tf-stand-count-untreated]');
      var deltaInput = form.querySelector('[data-tf-stand-count-delta]');

      if (!treatedInput || !untreatedInput || !deltaInput) {
        return;
      }

      function formatDelta(value) {
        var rounded = Math.round(value * 100) / 100;

        if (Math.abs(rounded - Math.round(rounded)) < 0.000001) {
          return String(Math.round(rounded));
        }

        return String(rounded).replace(/\.0+$/, '').replace(/(\.\d*?)0+$/, '$1');
      }

      function syncDelta() {
        var treated = String(treatedInput.value || '').trim();
        var untreated = String(untreatedInput.value || '').trim();

        if (treated === '' || untreated === '' || isNaN(Number(treated)) || isNaN(Number(untreated))) {
          deltaInput.value = '';
          return;
        }

        deltaInput.value = formatDelta(Number(treated) - Number(untreated));
      }

      treatedInput.addEventListener('input', syncDelta);
      untreatedInput.addEventListener('input', syncDelta);
      syncDelta();
    });
  }

  function initPortal() {
    initAlertDismiss();
    initNavToggle();
    initShowMore();
    initDateInputs();
    initRetailerPickers();
    initPhaseSubsteps();
    initStandCountDelta();
    initTrialSearch();
    initLeaderboardSearch();
    ensurePhaseLocationBindings(0);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPortal);
  } else {
    initPortal();
  }
}());
