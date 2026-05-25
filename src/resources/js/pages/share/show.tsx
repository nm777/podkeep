import ShareEpisodeList from '@/components/share-episode-list';
import SharePlayer from '@/components/share-player';
import { type ShareEpisode, type SharePageProps } from '@/types';
import { useState } from 'react';

function CopyRssButton({ rssUrl }: { rssUrl: string }) {
    const [copied, setCopied] = useState(false);

    const handleCopy = () => {
        navigator.clipboard.writeText(rssUrl).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    };

    return (
        <button
            type="button"
            className="inline-flex items-center rounded-md border px-3 py-1.5 text-sm font-medium transition-colors hover:bg-muted"
            onClick={handleCopy}
        >
            {copied ? 'Copied!' : 'Copy RSS URL'}
        </button>
    );
}

function FeedHeader({ feed }: { feed: SharePageProps['feed'] }) {
    return (
        <div className="mb-8">
            {feed.cover_image_url && (
                <img src={feed.cover_image_url} alt={feed.title} className="mb-4 h-32 w-32 rounded-lg object-cover" />
            )}
            <h1 className="text-2xl font-bold">{feed.title}</h1>
            {feed.description && <p className="mt-2 text-muted-foreground">{feed.description}</p>}
        </div>
    );
}

export default function ShareShow({ feed, episodes, rssUrl }: SharePageProps & { rssUrl: string }) {
    const firstEpisode = episodes.length > 0 ? episodes[0] : null;
    const [activeEpisode, setActiveEpisode] = useState<ShareEpisode | null>(firstEpisode);

    return (
        <div className="flex min-h-screen flex-col bg-background">
            <header className="border-b">
                <div className="mx-auto flex max-w-3xl items-center px-4 py-4">
                    <span className="text-lg font-semibold">PodKeep</span>
                </div>
            </header>

            <main className="mx-auto w-full max-w-3xl px-4 py-8">
                <FeedHeader feed={feed} />

                <div className="mb-6">
                    <SharePlayer episode={activeEpisode} />
                </div>

                <div className="mb-4 flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Episodes</h2>
                    <CopyRssButton rssUrl={rssUrl} />
                </div>

                <ShareEpisodeList
                    episodes={episodes}
                    activeEpisodeId={activeEpisode?.id ?? null}
                    onSelect={setActiveEpisode}
                />
            </main>
        </div>
    );
}
