import { formatDuration } from '@/lib/format';
import { type ShareEpisode } from '@/types';
import { Play } from 'lucide-react';

interface ShareEpisodeListProps {
    episodes: ShareEpisode[];
    activeEpisodeId?: number | null;
    onSelect: (episode: ShareEpisode) => void;
}

function EpisodeMeta({ episode }: { episode: ShareEpisode }) {
    return (
        <div className="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
            {episode.published_at && <span>{episode.published_at}</span>}
            {episode.duration != null && episode.duration > 0 && <span>{formatDuration(episode.duration)}</span>}
        </div>
    );
}

function EpisodeRow({ episode, isActive, onSelect }: { episode: ShareEpisode; isActive: boolean; onSelect: () => void }) {
    return (
        <button
            onClick={onSelect}
            className={`flex w-full items-start gap-3 p-4 text-left transition-colors hover:bg-muted/50 ${isActive ? 'bg-muted/50' : ''}`}
        >
            <div className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-foreground/10">
                <Play className="h-4 w-4" />
            </div>
            <div className="min-w-0 flex-1">
                <p className="truncate font-medium">{episode.title}</p>
                <EpisodeMeta episode={episode} />
                {episode.description && <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">{episode.description}</p>}
            </div>
        </button>
    );
}

export default function ShareEpisodeList({ episodes, activeEpisodeId, onSelect }: ShareEpisodeListProps) {
    if (episodes.length === 0) {
        return (
            <div className="rounded-lg border bg-muted/50 p-8 text-center">
                <p className="text-muted-foreground">No episodes available</p>
            </div>
        );
    }

    return (
        <div className="divide-y rounded-lg border">
            {episodes.map((episode) => (
                <EpisodeRow key={episode.id} episode={episode} isActive={activeEpisodeId === episode.id} onSelect={() => onSelect(episode)} />
            ))}
        </div>
    );
}
