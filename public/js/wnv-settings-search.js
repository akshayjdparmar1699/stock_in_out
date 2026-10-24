/* Filters the mobile Settings home list as the admin types. Progressive
 * enhancement only — the rows are all already in the DOM either way. */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var input = document.querySelector('[data-wnv-search]');
        if (!input) return;

        var groups = document.querySelectorAll('[data-wnv-group]');
        var empty = document.querySelector('[data-wnv-empty]');

        input.addEventListener('input', function () {
            var term = input.value.trim().toLowerCase();
            var anyVisible = false;

            groups.forEach(function (group) {
                var visibleInGroup = 0;
                group.querySelectorAll('[data-wnv-row]').forEach(function (row) {
                    var match = term === '' || row.getAttribute('data-wnv-row').indexOf(term) !== -1;
                    row.hidden = !match;
                    if (match) visibleInGroup++;
                });
                group.hidden = visibleInGroup === 0;
                if (visibleInGroup > 0) anyVisible = true;
            });

            if (empty) empty.hidden = anyVisible;
        });
    });
})();
