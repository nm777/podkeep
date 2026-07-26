import AppLayout from '@/layouts/app-layout';
import { Link } from '@inertiajs/react';
import { type ReactNode } from 'react';

interface AdminLayoutProps {
    children: ReactNode;
}

const tabs = [
    { label: 'User Management', href: route('admin.users.index'), path: '/admin/users' },
    { label: 'Queue Jobs', href: '/admin/queue', path: '/admin/queue' },
];

export default ({ children }: AdminLayoutProps) => {
    const pathname = window.location.pathname;

    return (
        <AppLayout>
            <div className="flex items-center gap-1 border-b">
                {tabs.map((tab) => {
                    const active = pathname.startsWith(tab.path);
                    return (
                        <Link
                            key={tab.path}
                            href={tab.href}
                            className={`px-4 py-2 text-sm font-medium transition-colors ${
                                active ? 'border-b-2 border-foreground text-foreground' : 'text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            {tab.label}
                        </Link>
                    );
                })}
            </div>

            <div className="mt-4">{children}</div>
        </AppLayout>
    );
};
