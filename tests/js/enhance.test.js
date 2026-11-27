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
