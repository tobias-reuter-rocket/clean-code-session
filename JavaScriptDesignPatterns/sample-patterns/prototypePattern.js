/**
 * Prototype Pattern
 * E.g. public/js/frontend/module/urlHandler.js
 */

(function () {
    "use strict";

    function UrlHandler() {
        this.options = {};
        // ...
    }

    UrlHandler.prototype.push = function () {
        var options = this.options;     // access properties
        this.notify();                  // access methods

        // ...
    };

    UrlHandler.prototype.notify = function () {
        // ...
    };

    return UrlHandler;
})();
