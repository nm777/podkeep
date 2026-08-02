import AdminLayout from '@/layouts/admin-layout';
import { Head, Link, usePoll } from '@inertiajs/react';
import { Copy } from 'lucide-react';
import { useMemo } from 'react';

interface JobMedia {
    id: number;
    title: string | null;
    url: string;
}

interface QueueJob {
    id: number;
    type: string;
    queue: string;
    attempts: number;
    created_at: string;
    media: JobMedia | null;
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
    media: JobMedia | null;
}

interface CompletedJob {
    id: number;
    job_type: string;
    queue: string;
    completed_at: string;
}

type JobStatus = 'pending' | 'executing' | 'failed' | 'completed';

interface UnifiedJob {
    key: string;
    type: string;
    queue: string;
    status: JobStatus;
    timestamp: number;
    timestampLabel: string;
    attempts?: number;
    exception?: string;
    uuid?: string;
    jobId?: number;
    media?: JobMedia | null;
}

const STATUS_STYLE: Record<JobStatus, string> = {
    pending: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    executing: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    failed: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    completed: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
};

const STATUS_ORDER: Record<JobStatus, number> = {
    pending: 0,
    executing: 1,
    failed: 2,
    completed: 2,
};

function shortenClassName(fqn: string): string {
    const parts = fqn.split('\\');

    return parts[parts.length - 1];
}

function formatDate(value: string | number): string {
    return new Date(toMs(value)).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function toMs(value: string | number): number {
    const str = String(value).trim();

    if (/^-?\d+$/.test(str)) {
        const num = Number(str);

        return num > 1e12 ? num : num * 1000;
    }

    return new Date(str).getTime();
}

function JobMediaLink({ media }: { media?: JobMedia | null }) {
    if (!media) return null;

    return (
        <a href={media.url} target="_blank" rel="noreferrer" className="text-xs text-muted-foreground underline hover:text-foreground">
            {media.title ?? `Media file #${media.id}`} (media #{media.id})
        </a>
    );
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
    usePoll(10000, { only: ['pending', 'executing', 'failed', 'recentlyCompleted'] });

    const jobs: UnifiedJob[] = useMemo(() => {
        const unified: UnifiedJob[] = [
            ...pending.map((j) => ({
                key: `p-${j.id}`,
                type: j.type,
                queue: j.queue,
                status: 'pending' as const,
                timestamp: toMs(j.created_at),
                timestampLabel: formatDate(j.created_at),
                jobId: j.id,
                media: j.media,
            })),
            ...executing.map((j) => ({
                key: `e-${j.id}`,
                type: j.type,
                queue: j.queue,
                status: 'executing' as const,
                timestamp: toMs(j.reserved_at),
                timestampLabel: formatDate(j.reserved_at),
                attempts: j.attempts,
                jobId: j.id,
                media: j.media,
            })),
            ...failed.map((j) => ({
                key: `f-${j.id}`,
                type: j.type,
                queue: j.queue,
                status: 'failed' as const,
                timestamp: toMs(j.failed_at),
                timestampLabel: formatDate(j.failed_at),
                exception: j.exception,
                uuid: j.uuid,
                media: j.media,
            })),
            ...recentlyCompleted.map((j) => ({
                key: `c-${j.id}`,
                type: j.job_type,
                queue: j.queue,
                status: 'completed' as const,
                timestamp: toMs(j.completed_at),
                timestampLabel: formatDate(j.completed_at),
            })),
        ];

        return unified.sort((a, b) => {
            const orderDiff = STATUS_ORDER[a.status] - STATUS_ORDER[b.status];

            if (orderDiff !== 0) return orderDiff;
            // Within failed/completed: most recent first. Within pending/executing: oldest first.
            if (a.status === 'pending' || a.status === 'executing') {
                return a.timestamp - b.timestamp;
            }

            return b.timestamp - a.timestamp;
        });
    }, [pending, executing, failed, recentlyCompleted]);

    return (
        <AdminLayout>
            <Head title="Queue Jobs" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-3xl font-bold">Queue Jobs ({jobs.length})</h1>

                {jobs.length === 0 ? (
                    <p className="text-sm text-muted-foreground">No jobs.</p>
                ) : (
                    <div className="divide-y rounded-lg border">
                        {jobs.map((job) => (
                            <div key={job.key} className="flex flex-col gap-1 px-4 py-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex flex-wrap items-center gap-x-3 gap-y-1">
                                        <span className="text-sm font-medium">{shortenClassName(job.type)}</span>
                                        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${STATUS_STYLE[job.status]}`}>
                                            {job.status}
                                        </span>
                                    </div>
                                    <div className="flex shrink-0 items-center gap-3">
                                        {job.status === 'pending' && job.jobId && (
                                            <Link href={route('admin.queue.cancel', job.jobId)} method="post" as="button" preserveScroll className="text-xs text-destructive hover:underline">
                                                Cancel
                                            </Link>
                                        )}
                                        {job.status === 'executing' && job.jobId && (
                                            <Link href={route('admin.queue.release', job.jobId)} method="post" as="button" preserveScroll className="text-xs text-muted-foreground hover:text-foreground hover:underline">
                                                Release
                                            </Link>
                                        )}
                                        {job.status === 'failed' && job.uuid && (
                                            <>
                                                <Link href={route('admin.queue.retry', job.uuid)} method="post" as="button" preserveScroll className="text-xs text-muted-foreground hover:text-foreground hover:underline">
                                                    Retry
                                                </Link>
                                                <Link href={route('admin.queue.delete', job.uuid)} method="post" as="button" preserveScroll className="text-xs text-destructive hover:underline">
                                                    Delete
                                                </Link>
                                            </>
                                        )}
                                    </div>
                                </div>
                                <div className="flex flex-wrap gap-x-3 text-xs text-muted-foreground">
                                    <span>{job.queue}</span>
                                    {job.attempts !== undefined && <span>attempts: {job.attempts}</span>}
                                    <span>{job.timestampLabel}</span>
                                </div>
                                <JobMediaLink media={job.media} />
                                {job.exception && (
                                    <details className="mt-1">
                                        <summary className="flex cursor-pointer list-none items-center gap-2 text-xs text-muted-foreground [&::-webkit-details-marker]:hidden">
                                            Show error
                                        </summary>
                                        <div className="flex items-center px-4 pb-1 pt-2">
                                            <button
                                                type="button"
                                                className="flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
                                                onClick={() => navigator.clipboard.writeText(job.exception ?? '')}
                                            >
                                                <Copy className="h-3 w-3" />
                                                Copy
                                            </button>
                                        </div>
                                        <pre className="whitespace-pre-wrap break-all px-4 pb-3 text-xs text-muted-foreground">{job.exception}</pre>
                                    </details>
                                )}
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
