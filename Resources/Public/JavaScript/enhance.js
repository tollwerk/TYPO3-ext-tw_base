/**
 * Shared runtime of the components: enhance elements once, also those that appear later
 *
 * A component module registers an enhancer for a selector. The enhancer runs once for each matching element that is
 * in the document or that comes into it later, such as markup loaded after the page.
 *
 * @example
 * import { enhance } from '@tollwerk/tw-base/JavaScript/enhance.js';
 *
 * enhance('.FormField__input', (input) => { … });
 */

/**
 * Registered enhancers, each with its selector
 *
 * @type {Array<{selector: string, enhancer: function(Element): void}>}
 */
const registrations = [];

/**
 * Registrations that already ran for an element
 *
 * @type {WeakMap<Element, Set<object>>}
 */
const done = new WeakMap();

/**
 * Observer of the document, started with the first registration
 *
 * @type {MutationObserver|null}
 */
let observer = null;

/**
 * Run a registration for an element unless it already ran for it
 *
 * @param {object} registration Registration
 * @param {Element} element Element that matches the selector
 */
function run(registration, element) {
    const ran = done.get(element) ?? new Set();
    if (ran.has(registration)) {
        return;
    }

    ran.add(registration);
    done.set(element, ran);
    registration.enhancer(element);
}

/**
 * Run a registration for a subtree, including its root
 *
 * @param {object} registration Registration
 * @param {Element} root Root of the subtree
 */
function scan(registration, root) {
    if (root.matches(registration.selector)) {
        run(registration, root);
    }

    root.querySelectorAll(registration.selector).forEach((element) => run(registration, element));
}

/**
 * Run all registrations for the elements a mutation added
 *
 * @param {MutationRecord[]} mutations Mutations
 */
function added(mutations) {
    mutations.forEach((mutation) => mutation.addedNodes.forEach((node) => {
        if (node.nodeType === Node.ELEMENT_NODE) {
            registrations.forEach((registration) => scan(registration, node));
        }
    }));
}

/**
 * Enhance every element that matches a selector, now and when it appears later
 *
 * @param {string} selector CSS selector
 * @param {function(Element): void} enhancer Function that enhances one element; it runs once per element
 */
export function enhance(selector, enhancer) {
    const registration = { selector, enhancer };
    registrations.push(registration);
    scan(registration, document.documentElement);
    if (observer === null) {
        observer = new MutationObserver(added);
        observer.observe(document.documentElement, { childList: true, subtree: true });
    }
}
