(function (w, d) {
    'use strict';

    const root = d.documentElement;
    const classList = root.classList;

    // Announce JavaScript support
    classList.remove('no-js');
    classList.add('has-js');

    // Test srcset support
    w.Tollwerk.has.srcset = ('srcset' in d.createElement('img'));
    classList.add(`${w.Tollwerk.has.srcset ? 'has' : 'no'}-srcset`);

    w.Tollwerk.has.lazyload = ('loading' in HTMLImageElement.prototype);
    classList.add(`${w.Tollwerk.has.lazyload ? 'has' : 'no'}-lazyload`);

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
