import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

export default function Appearance() {
    return (
        <AppLayout>
            <Head title="Appearance" />
            <div className="py-16 text-center">
                <p className="text-muted-foreground">Theme settings are now in the top bar. Use the sun/moon icon to toggle.</p>
            </div>
        </AppLayout>
    );
}
