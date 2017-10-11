/**
 * (Revealing) Module
 *
 * E.g. public/js/frontend/module/tracking.js
 */
(function () {
    "use strict";

    // Private:

    var config = {
        debug: false
    };

    var decodeEntities = function (encodedString) {
        // ...
    };

    // Public:

    var init = function () {
        // ...
    };

    var track = function (data) {
        if (config.debug) {             // access class methods
            // ...
        }
    };

    // Reveal public methods
    return {
        init: init,
        track: track,
    };
}());
