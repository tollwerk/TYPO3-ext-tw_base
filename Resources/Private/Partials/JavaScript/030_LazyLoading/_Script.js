(function (w, d) {
    // Enable / disable srcset
    const removeAttribute = w.Tollwerk.has.srcset ? 'src' : 'srcset';
    w.Tollwerk.Observer.register('img[srcset][src]', img => img.removeAttribute(removeAttribute));

    // Enable native lazyloading
    if (w.Tollwerk.has.lazyload) {
        console.debug('native lazy loading');
        w.Tollwerk.Observer.register(`img[loading=lazy][src][data-src]`, img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
        w.Tollwerk.Observer.register(`img[loading=lazy][data-srcset]`, img => {
            img.srcset = img.dataset.srcset;
            img.removeAttribute('src');
            img.removeAttribute('data-srcset');
        });
    } else {
        console.debug('non-native lazy loading');
        let lazysizes = null;
        const libDir = '/typo3conf/ext/tw_base/Resources/Public/JavaScript/Lib/';

        /**
         * Asynchronously require an external script
         *
         * @param {String} src Script source
         * @param {Element} Script node
         */
        function requireScript(src) {
            const script = d.createElement('script');
            script.defer = true;
            script.src = src;
            d.body.appendChild(script);
            return script;
        }

        w.Tollwerk.Observer.register(`img[loading=lazy]`, img => {
            img.classList.add('lazyload');
            if (!lazysizes) {
                lazysizes = requireScript(`${libDir}lazysizes.min.js`) &&
                    (w.Tollwerk.has.srcset || requireScript(`${libDir}ls.respimg.min.js`));
            }
        });
    }
})(window, document);
