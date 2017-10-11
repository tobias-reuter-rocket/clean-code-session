/**
 * Publish/Subscribe pattern
 *
 * E.g. public/js/frontend/module/search.js
 */
/* global jQuery, app, console */
(function ($) {
    "use strict";

    var emitter = $(app);   // we can publish/subscribe to events on pure JavaScript objects (when wrapped by jQuery)

    /**
     * Publish / Emit
     */
    var publishMapUpdate = function () {
        var data = {
            some: 'data'
        };
        emitter.trigger('search.map.update', data);
    };

    var timeout = setTimeout(publishMapUpdate, 500);

})(jQuery);

(function ($) {
    "use strict";

    var emitter = $(app);

    /**
     * Subscribe / On
     */
    var subscribeMapUpdate = function () {
        emitter.on('search.map.update', function(event, data) {
            console.log('search.map.update', data);
        });
    };

    subscribeMapUpdate();

}(jQuery));