/**
 * (Revealing) Prototype Pattern
 * E.g. public/js/frontend/module/urlHandler.js
 */

(function () {
    "use strict";

    function UrlHandler() {
        this.options = {};
        // ...
    }

    UrlHandler.prototype = function() {

        // Public:
        var push = function () {
            var options = this.options;     // access properties
            this.notify();                  // access methods

            // ...
        };

        // Private:
        var notify = function () {
            // ...
        };

        return {
            push: push
        };
    }();

    return UrlHandler;
})();
