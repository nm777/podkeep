import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface Token {
    id: number;
    name: string;
    last_used_at: string | null;
    created_at: string;
    expires_at: string | null;
}

interface Flash {
    new_token?: string;
    success?: string;
}

export default function ApiKeys({ tokens, flash }: { tokens: Token[]; flash?: Flash }) {
    const { data, setData, post, errors, processing, reset } = useForm<Required<{ name: string }>>({ name: '' });
    const [copied, setCopied] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('api-keys.store'), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    const copyToken = async (token: string) => {
        await navigator.clipboard.writeText(token);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <AppLayout>
            <Head title="API Keys" />

            <div className="max-w-xl space-y-6">
                <h1 className="text-xl font-semibold">API Keys</h1>

                <Card>
                    <CardHeader>
                        <CardTitle>Create API Key</CardTitle>
                        <CardDescription>Generate a new API key for programmatic access.</CardDescription>
                    </CardHeader>
                    <form onSubmit={submit}>
                        <CardContent>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    className="mt-1 block w-full"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                    placeholder="e.g. CI deploy script"
                                />
                                <InputError className="mt-2" message={errors.name} />
                            </div>
                        </CardContent>
                        <CardFooter>
                            <Button disabled={processing}>Create Key</Button>
                        </CardFooter>
                    </form>
                </Card>

                {flash?.new_token && (
                    <Card className="border-amber-200 bg-amber-50 dark:border-amber-200/10 dark:bg-amber-700/10">
                        <CardHeader>
                            <CardTitle className="text-amber-700 dark:text-amber-100">Your new API key</CardTitle>
                            <CardDescription className="text-amber-700/80 dark:text-amber-100/80">
                                Copy your API key now. You won't be able to see it again.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center gap-2">
                                <Input readOnly value={flash.new_token} className="font-mono text-sm" />
                                <Button type="button" variant="secondary" onClick={() => copyToken(flash.new_token!)}>
                                    {copied ? 'Copied!' : 'Copy'}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle>Your API Keys</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {tokens.length === 0 ? (
                            <p className="text-sm text-muted-foreground">You haven't created any API keys yet.</p>
                        ) : (
                            tokens.map((token) => (
                                <div key={token.id} className="flex items-center justify-between gap-4">
                                    <div className="space-y-0.5">
                                        <p className="text-sm font-medium">{token.name}</p>
                                        <p className="text-xs text-muted-foreground">
                                            Created {new Date(token.created_at).toLocaleDateString()}
                                            {token.last_used_at
                                                ? ` · Last used ${new Date(token.last_used_at).toLocaleDateString()}`
                                                : ' · Never used'}
                                        </p>
                                    </div>
                                    <Button variant="destructive" size="sm" asChild>
                                        <Link href={route('api-keys.destroy', token.id)} method="delete" as="button">
                                            Revoke
                                        </Link>
                                    </Button>
                                </div>
                            ))
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
