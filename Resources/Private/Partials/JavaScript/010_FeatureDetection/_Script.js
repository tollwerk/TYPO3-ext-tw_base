'use strict';

(function (w, d) {
    const root = d.documentElement;
    const classList = root.classList;

    // Announce JavaScript support
    classList.remove('no-js');
    classList.add('has-js');

    // :focus-within mini polyfill
    try {
        d.querySelector(':focus-within');
        classList.add('has-focus-within');
    } catch (ignoredError) {
        classList.add('no-focus-within');
    }

    // Debounced touch detection
    w.addEventListener('touchstart', function touched() {
        classList.add('has-touch');
        w.removeEventListener('touchstart', touched, false);
    }, false)
})(window, document);
