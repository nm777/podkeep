import { type Appearance, useAppearance } from '@/hooks/use-appearance';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { Monitor, Moon, Sun } from 'lucide-react';
import { ComponentType } from 'react';

type ThemeOption = {
    value: Appearance;
    label: string;
    description: string;
    icon: ComponentType<{ className?: string }>;
};

const options: ThemeOption[] = [
    {
        value: 'light',
        label: 'Light',
        description: 'Always use light theme',
        icon: Sun,
    },
    {
        value: 'dark',
        label: 'Dark',
        description: 'Always use dark theme',
        icon: Moon,
    },
    {
        value: 'system',
        label: 'System',
        description: 'Follows your system preference automatically',
        icon: Monitor,
    },
];

export default function Appearance() {
    const { appearance, updateAppearance } = useAppearance();

    return (
        <AppLayout>
            <Head title="Appearance" />

            <div className="max-w-xl space-y-6">
                <h1 className="text-xl font-semibold">Appearance</h1>

                <Card>
                    <CardHeader>
                        <CardTitle>Theme</CardTitle>
                        <CardDescription>Choose how PodKeep looks to you</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-2">
                        {options.map((option) => {
                            const Icon = option.icon;
                            const isActive = appearance === option.value;

                            return (
                                <button
                                    key={option.value}
                                    type="button"
                                    onClick={() => updateAppearance(option.value)}
                                    aria-pressed={isActive}
                                    className={cn(
                                        'flex w-full items-center gap-3 rounded-lg border p-3 text-left transition-colors hover:bg-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                                        isActive ? 'border-primary bg-accent' : 'border-border',
                                    )}
                                >
                                    <Icon className="h-5 w-5 shrink-0" />
                                    <div className="flex flex-col">
                                        <span className="font-medium">{option.label}</span>
                                        <span className="text-sm text-muted-foreground">{option.description}</span>
                                    </div>
                                </button>
                            );
                        })}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
