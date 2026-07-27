import AdminLayout from '@/layouts/admin-layout';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect } from 'react';

interface QueueJob {
    id: number;
    type: string;
    queue: string;
    attempts: number;
    created_at: string;
}

interface ExecutingJob extends QueueJob {
    reserved_at: string;
}

interface FailedJob {
    id: number;
    uuid: string;
    type: string;
    queue: string;
    failed_at: string;
    exception: string;
}

function shortenClassName(fqn: string): string {
    const parts = fqn.split('\\');

    return parts[parts.length - 1];
}

function truncate(text: string, max = 200): string {
    return text.length > max ? `${text.slice(0, max)}...` : text;
}

export default function QueueIndex({
    pending,
    executing,
    failed,
}: {
    pending: QueueJob[];
    executing: ExecutingJob[];
    failed: FailedJob[];
}) {
    useEffect(() => {
        const interval = setInterval(() => {
            router.reload({ only: ['pending', 'executing', 'failed'] });
        }, 10000);

        return () => clearInterval(interval);
    }, []);

    return (
        <AdminLayout>
            <Head title="Queue Jobs" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-3xl font-bold">Queue Jobs</h1>

                <section className="mb-6">
                    <h2 className="mb-2 text-base font-medium">Pending ({pending.length})</h2>
                    {pending.length === 0 ? (
                        <p className="text-sm text-muted-foreground">No pending jobs.</p>
                    ) : (
                        // ponytail: pending/executing rows differ by one field; inline dup beats a 1-file component for 2 callers
                        <div className="divide-y rounded-lg border">
                            {pending.map((job) => (
                                <div key={job.id} className="flex items-center gap-3 px-4 py-3">
                                    <span className="text-sm font-medium">{shortenClassName(job.type)}</span>
                                    <span className="text-xs text-muted-foreground">{job.queue}</span>
                                    <span className="text-xs text-muted-foreground">attempts: {job.attempts}</span>
                                    <Link
                                        href={route('admin.queue.cancel', job.id)}
                                        method="post"
                                        as="button"
                                        className="ml-auto text-xs text-destructive hover:underline"
                                    >
                                        Cancel
                                    </Link>
                                </div>
                            ))}
                        </div>
                    )}
                </section>

                <section className="mb-6">
                    <h2 className="mb-2 text-base font-medium">Executing ({executing.length})</h2>
                    {executing.length === 0 ? (
                        <p className="text-sm text-muted-foreground">No executing jobs.</p>
                    ) : (
                        <div className="divide-y rounded-lg border">
                            {executing.map((job) => (
                                <div key={job.id} className="flex items-center gap-3 px-4 py-3">
                                    <span className="text-sm font-medium">{shortenClassName(job.type)}</span>
                                    <span className="text-xs text-muted-foreground">{job.queue}</span>
                                    <span className="text-xs text-muted-foreground">attempts: {job.attempts}</span>
                                    <span className="text-xs text-muted-foreground">reserved: {job.reserved_at}</span>
                                    <Link
                                        href={route('admin.queue.release', job.id)}
                                        method="post"
                                        as="button"
                                        className="ml-auto text-xs text-muted-foreground hover:text-foreground hover:underline"
                                    >
                                        Release
                                    </Link>
                                </div>
                            ))}
                        </div>
                    )}
                </section>

                <section className="mb-6">
                    <h2 className="mb-2 text-base font-medium">Failed ({failed.length})</h2>
                    {failed.length === 0 ? (
                        <p className="text-sm text-muted-foreground">No failed jobs.</p>
                    ) : (
                        <div className="divide-y rounded-lg border">
                            {failed.map((job) => (
                                <div key={job.id} className="flex flex-col gap-1 px-4 py-3">
                                    <div className="flex items-center gap-3">
                                        <span className="text-sm font-medium">{shortenClassName(job.type)}</span>
                                        <span className="text-xs text-muted-foreground">{job.queue}</span>
                                        <span className="text-xs text-muted-foreground">failed: {job.failed_at}</span>
                                        <div className="ml-auto flex items-center gap-3">
                                            <Link
                                                href={route('admin.queue.retry', job.uuid)}
                                                method="post"
                                                as="button"
                                                className="text-xs text-muted-foreground hover:text-foreground hover:underline"
                                            >
                                                Retry
                                            </Link>
                                            <Link
                                                href={route('admin.queue.delete', job.uuid)}
                                                method="post"
                                                as="button"
                                                className="text-xs text-destructive hover:underline"
                                            >
                                                Delete
                                            </Link>
                                        </div>
                                    </div>
                                    <p className="text-xs text-muted-foreground">{truncate(job.exception)}</p>
                                </div>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </AdminLayout>
    );
}
