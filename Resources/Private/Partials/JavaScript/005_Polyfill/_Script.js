(function (w, e, s, svg) {
    'use strict';

    // NodeList.forEach
    if (window.NodeList && !NodeList.prototype.forEach) {
        NodeList.prototype.forEach = function (callback, thisArg) {
            thisArg = thisArg || window;
            for (let i = 0; i < this.length; ++i) {
                callback.call(thisArg, this[i], i, this);
            }
        };
    }

    // Element.matches
    if (!e.matches) {
        e.matches =
            e.matchesSelector ||
            e.mozMatchesSelector ||
            e.msMatchesSelector ||
            e.oMatchesSelector ||
            e.webkitMatchesSelector ||
            (s => {
                const matches = (this.document || this.ownerDocument).querySelectorAll(s);
                let i = matches.length;
                while ((--i >= 0) && (matches.item(i) !== this)) {
                }
                return i > -1;
            });
    }

    // Element.closest
    if (!e.closest) {
        e.closest = function (s) {
            let el = this;
            do {
                if (el.matches(s)) return el;
                el = el.parentElement || el.parentNode;
            } while (el !== null && el.nodeType === 1);
            return null;
        };
    }

    // String.format
    if (!s.format) {
        s.format = function () {
            const args = arguments;
            return this.replace(/{(\d+)}/g, (match, number) => typeof args[number] !== 'undefined' ? args[number] : match);
        };
    }

    // classList support for IE11
    if (!('classList' in svg)) {
        Object.defineProperty(svg, 'classList', {
            get() {
                return {
                    contains: className => {
                        return this.className.baseVal.split(' ').indexOf(className) !== -1;
                    },
                    add: className => {
                        return this.setAttribute('class', this.getAttribute('class') + ' ' + className);
                    },
                    remove: className => {
                        var removedClass = this.getAttribute('class').replace(new RegExp('(\\s|^)' + className + '(\\s|$)', 'g'), '$2');
                        if (this.classList.contains(className)) {
                            this.setAttribute('class', removedClass);
                        }
                    }
                };
            }
        });
    }
})(window, Element.prototype, String.prototype, SVGElement.prototype);
