(function (w, d) {
    // Enable / disable srcset
    const removeAttribute = w.Tollwerk.has.srcset ? 'src' : 'srcset';
    w.Tollwerk.Observer.register('img[srcset][src]', img => img.removeAttribute(removeAttribute));

    // Enable native lazyloading
    if (w.Tollwerk.has.lazyload) {
        const replaceSrc = function(el) {
            el.src = el.dataset.src;
            el.removeAttribute('data-src');
        };
        w.Tollwerk.Observer.register('img[loading=lazy][src][data-src]', img => {
            replaceSrc(img);
            img.parentNode.querySelectorAll('source[data-src]').forEach(replaceSrc);
        });
        const replaceSrcset = function(el) {
            el.srcset = el.dataset.srcset;
            el.removeAttribute('src');
            el.removeAttribute('data-srcset');
        };
        w.Tollwerk.Observer.register('img[loading=lazy][data-srcset]', img => {
            replaceSrcset(img);
            img.parentNode.querySelectorAll('source[data-srcset]').forEach(replaceSrcset);
        });
    } else {
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

        w.Tollwerk.Observer.register('img[loading=lazy]', img => {
            img.classList.add('lazyload');
            if (!lazysizes) {
                lazysizes = requireScript(`${libDir}lazysizes.min.js`) &&
                    (w.Tollwerk.has.srcset || requireScript(`${libDir}ls.respimg.min.js`));
            }
        });
    }
})(window, document);
