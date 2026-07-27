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

interface CompletedJob {
    id: number;
    job_type: string;
    queue: string;
    completed_at: string;
}

function shortenClassName(fqn: string): string {
    const parts = fqn.split('\\');

    return parts[parts.length - 1];
}

function formatDate(value: string | number): string {
    const date = typeof value === 'number' ? new Date(value * 1000) : new Date(value);

    return date.toLocaleString(undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

export default function QueueIndex({
    pending,
    executing,
    failed,
    recentlyCompleted,
}: {
    pending: QueueJob[];
    executing: ExecutingJob[];
    failed: FailedJob[];
    recentlyCompleted: CompletedJob[];
}) {
    useEffect(() => {
        const interval = setInterval(() => {
            router.reload({ only: ['pending', 'executing', 'failed', 'recentlyCompleted'] });
        }, 10000);

        return () => clearInterval(interval);
    }, []);

    return (
        <AdminLayout>
            <Head title="Queue Jobs" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-3xl font-bold">Queue Jobs</h1>

                <section className="mb-6">
                    <h2 className="mb-2 text-base font-medium">Pending ({pending.length})</h2>
                    {pending.length === 0 ? (
                        <p className="text-sm text-muted-foreground">No pending jobs.</p>
                    ) : (
                        // ponytail: pending/executing rows differ by one field; inline dup beats a 1-file component for 2 callers
                        <div className="divide-y rounded-lg border">
                            {pending.map((job) => (
                                <div key={job.id} className="flex flex-col gap-1 px-4 py-3">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">{shortenClassName(job.type)}</span>
                                        <Link href={route('admin.queue.cancel', job.id)} method="post" as="button" className="text-xs text-destructive hover:underline">
                                            Cancel
                                        </Link>
                                    </div>
                                    <div className="flex flex-wrap gap-x-3 text-xs text-muted-foreground">
                                        <span>{job.queue}</span>
                                    </div>
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
                                <div key={job.id} className="flex flex-col gap-1 px-4 py-3">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">{shortenClassName(job.type)}</span>
                                        <Link href={route('admin.queue.release', job.id)} method="post" as="button" className="text-xs text-muted-foreground hover:text-foreground hover:underline">
                                            Release
                                        </Link>
                                    </div>
                                    <div className="flex flex-wrap gap-x-3 text-xs text-muted-foreground">
                                        <span>{job.queue}</span>
                                        <span>attempts: {job.attempts}</span>
                                        <span>reserved: {formatDate(job.reserved_at)}</span>
                                    </div>
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
                                <details key={job.id} className="flex flex-col">
                                    <summary className="flex cursor-pointer list-none items-center justify-between px-4 py-3 [&::-webkit-details-marker]:hidden">
                                        <div className="flex flex-wrap items-center gap-x-3">
                                            <span className="text-sm font-medium">{shortenClassName(job.type)}</span>
                                            <span className="text-xs text-muted-foreground">{job.queue}</span>
                                            <span className="text-xs text-muted-foreground">failed: {formatDate(job.failed_at)}</span>
                                        </div>
                                        <div className="flex shrink-0 items-center gap-3" onClick={(e) => e.preventDefault()}>
                                            <Link href={route('admin.queue.retry', job.uuid)} method="post" as="button" className="text-xs text-muted-foreground hover:text-foreground hover:underline">
                                                Retry
                                            </Link>
                                            <Link href={route('admin.queue.delete', job.uuid)} method="post" as="button" className="text-xs text-destructive hover:underline">
                                                Delete
                                            </Link>
                                        </div>
                                    </summary>
                                    <pre className="whitespace-pre-wrap break-all px-4 pb-3 text-xs text-muted-foreground">{job.exception}</pre>
                                </details>
                            ))}
                        </div>
                    )}
                </section>

                <section className="mb-6">
                    <h2 className="mb-2 text-base font-medium">Recently Completed ({recentlyCompleted.length})</h2>
                    {recentlyCompleted.length === 0 ? (
                        <p className="text-sm text-muted-foreground">No recently completed jobs.</p>
                    ) : (
                        <div className="divide-y rounded-lg border">
                            {recentlyCompleted.map((job) => (
                                <div key={job.id} className="flex flex-col gap-1 px-4 py-3">
                                    <span className="text-sm font-medium">{shortenClassName(job.job_type)}</span>
                                    <div className="flex flex-wrap gap-x-3 text-xs text-muted-foreground">
                                        <span>{job.queue}</span>
                                        <span>{formatDate(job.completed_at)}</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </AdminLayout>
    );
}
