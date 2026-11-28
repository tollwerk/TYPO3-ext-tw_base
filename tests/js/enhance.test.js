/**
 * Tests of the shared runtime of the components
 */
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

let enhance;

beforeEach(async () => {
    vi.resetModules();
    document.body.innerHTML = '<p class="Item">One</p><p class="Other">Other</p>';
    ({ enhance } = await import('../../Resources/Public/JavaScript/enhance.js'));
});

afterEach(() => {
    document.body.innerHTML = '';
});

/**
 * Wait until the observer has handled the mutations
 *
 * @returns {Promise<void>}
 */
const settle = () => new Promise((resolve) => {
    setTimeout(resolve, 0);
});

describe('enhance', () => {
    it('enhances the matching elements in the document', () => {
        const enhancer = vi.fn();
        enhance('.Item', enhancer);

        expect(enhancer).toHaveBeenCalledTimes(1);
        expect(enhancer).toHaveBeenCalledWith(document.querySelector('.Item'));
    });

    it('enhances elements that appear later, and their descendants', async () => {
        const enhancer = vi.fn();
        enhance('.Item', enhancer);
        const section = document.createElement('section');
        section.innerHTML = '<p class="Item">Two</p>';
        const item = document.createElement('p');
        item.className = 'Item';
        document.body.append(section, item, document.createTextNode('text'));
        await settle();

        expect(enhancer).toHaveBeenCalledTimes(3);
        expect(enhancer).toHaveBeenLastCalledWith(item);
    });

    it('enhances an element once, even when it moves', async () => {
        const enhancer = vi.fn();
        enhance('.Item', enhancer);
        document.body.append(document.querySelector('.Item'));
        await settle();

        expect(enhancer).toHaveBeenCalledTimes(1);
    });

    it('runs every registration for an element', async () => {
        const first = vi.fn();
        const second = vi.fn();
        enhance('.Item', first);
        enhance('p', second);
        document.body.insertAdjacentHTML('beforeend', '<p class="Item">Three</p>');
        await settle();

        expect(first).toHaveBeenCalledTimes(2);
        expect(second).toHaveBeenCalledTimes(3);
    });
});

describe('enhance with an enhancer that throws', () => {
    let reported;

    beforeEach(() => {
        reported = vi.fn();
        vi.stubGlobal('reportError', reported);
        document.body.innerHTML = '<p class="Item" id="first">One</p><p class="Item">Two</p>';
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    const failing = (element) => {
        if (element.id === 'first') {
            throw new Error('No button');
        }
    };

    it('still enhances the other elements and reports the error', () => {
        const enhancer = vi.fn(failing);
        enhance('.Item', enhancer);

        expect(enhancer).toHaveBeenCalledTimes(2);
        expect(reported).toHaveBeenCalledWith(new Error('No button'));
    });

    it('still runs the other registrations for added elements', async () => {
        const other = vi.fn();
        enhance('.Item', failing);
        enhance('.Other', other);
        document.body.insertAdjacentHTML('beforeend', '<div><p class="Item" id="first">Three</p></div>');
        document.querySelector('div').insertAdjacentHTML('beforeend', '<p class="Other">Four</p>');
        await settle();

        expect(other).toHaveBeenCalledTimes(1);
    });

    it('still observes the document after a first call that failed', async () => {
        const enhancer = vi.fn(failing);
        enhance('.Item', enhancer);
        document.body.insertAdjacentHTML('beforeend', '<p class="Item">Five</p>');
        await settle();

        expect(enhancer).toHaveBeenCalledTimes(3);
    });
});
