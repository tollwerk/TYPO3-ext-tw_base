/**
 * Configuration of the JavaScript tests, in a DOM of jsdom
 */
import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        include: ['tests/js/**/*.test.js'],
        environment: 'jsdom',
        coverage: { include: ['Resources/Public/**/*.js'] },
    },
});
