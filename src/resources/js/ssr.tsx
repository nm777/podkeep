import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { ComponentType } from 'react';
import ReactDOMServer from 'react-dom/server';
import type { Config } from 'ziggy-js';
import { route } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer((page) =>
    createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => (title ? `${title} - ${appName}` : appName),
        resolve: (name) =>
            resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')).then(
                (module) => (module as { default: ComponentType }).default,
            ),
        setup: ({ App, props }) => {
            const ziggy = page.props.ziggy as Config & { location: string };

            globalThis.route = ((name: string, params?: unknown, absolute?: boolean) =>
                route(name, params as never, absolute, {
                    ...ziggy,
                    location: new URL(ziggy.location),
                } as Parameters<typeof route>[3])) as typeof route;

            return <App {...props} />;
        },
    }),
);
