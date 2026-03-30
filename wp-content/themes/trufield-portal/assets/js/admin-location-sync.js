/* TruField Portal - Admin Google location sync */
(function ($) {
  'use strict';

  function parseMapValue(raw) {
    if (!raw) {
      return null;
    }

    try {
      var parsed = JSON.parse(raw);
      return parsed && typeof parsed === 'object' ? parsed : null;
    } catch (e) {
      return null;
    }
  }

  function setAddress(value) {
    var $field = $('.acf-field[data-name="field_location_address"]');
    if (!$field.length || !value) {
      return;
    }

    var $input = $field.find('textarea, input[type="text"]').first();
    if ($input.length) {
      $input.val(value).trigger('change');
    }
  }

  function setLatLng(lat, lng) {
    var $latField = $('.acf-field[data-name="field_location_lat"]');
    var $lngField = $('.acf-field[data-name="field_location_lng"]');

    if ($latField.length && lat !== '' && lat !== null && lat !== undefined) {
      $latField.find('input[type="number"], input[type="text"]').first().val(lat).trigger('change');
    }

    if ($lngField.length && lng !== '' && lng !== null && lng !== undefined) {
      $lngField.find('input[type="number"], input[type="text"]').first().val(lng).trigger('change');
    }
  }

  function syncFromMapRaw(raw) {
    var value = parseMapValue(raw);
    if (!value) {
      return;
    }

    setAddress(value.address || '');
    setLatLng(value.lat, value.lng);
  }

  function initLocationSync() {
    var $mapField = $('.acf-field[data-name="field_location_google_place"]');
    if (!$mapField.length) {
      return;
    }

    var $hidden = $mapField.find('input[type="hidden"][name^="acf["]').first();
    if (!$hidden.length) {
      return;
    }

    var lastRaw = '';

    function syncNow() {
      var raw = $hidden.val() || '';
      if (raw === lastRaw) {
        return;
      }

      lastRaw = raw;
      syncFromMapRaw(raw);
    }

    // Initial pass and periodic checks because ACF updates this hidden value internally.
    syncNow();
    setInterval(syncNow, 300);

    // Additional hooks for manual typing/search events inside the map UI.
    $mapField.on('change blur keyup', 'input[type="text"]', function () {
      setTimeout(syncNow, 50);
    });
  }

  $(document).ready(function () {
    initLocationSync();
  });
}(jQuery));
