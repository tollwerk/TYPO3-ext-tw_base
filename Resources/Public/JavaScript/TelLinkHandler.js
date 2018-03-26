/**
 * Module: TYPO3/CMS/TwBase/TelLinkHandler
 * Tel link interaction
 */
define(['jquery', 'TYPO3/CMS/Recordlist/LinkBrowser'], function ($, LinkBrowser) {
    'use strict';

    /**
     *
     * @type {{}}
     * @exports TYPO3/CMS/TwBase/TelLinkHandler
     */
    var TelLinkHandler = {};

    $(function () {
        $('#ltelform').on('submit', function (event) {
            event.preventDefault();

            var value = $(this).find('[name="lnumber"]').val();
            if (value === 'tel:') {
                return;
            }

            while (value.substr(0, 4) === 'tel:') {
                value = value.substr(4);
            }

            value = $.trim(value);
            value = ((value.substr(0, 1) === '+') ? '+' : '') + value.replace(/[^0-9]/ig, '');
            LinkBrowser.finalizeFunction('tel:' + value);
        });
    });

    return TelLinkHandler;
});
