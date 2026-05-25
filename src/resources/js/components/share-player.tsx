import { type ShareEpisode } from '@/types';
import { useRef, useState } from 'react';

interface SharePlayerProps {
    episode: ShareEpisode | null;
}

export default function SharePlayer({ episode }: SharePlayerProps) {
    const [error, setError] = useState<string | null>(null);
    const audioRef = useRef<HTMLAudioElement>(null);

    if (!episode) {
        return (
            <div className="rounded-lg border bg-muted/50 p-6 text-center">
                <p className="text-sm text-muted-foreground">Select an episode to play</p>
            </div>
        );
    }

    return (
        <div className="rounded-lg border bg-card p-4">
            <h3 className="mb-3 text-base font-semibold">{episode.title}</h3>
            {error ? (
                <p className="py-4 text-center text-sm text-red-500">{error}</p>
            ) : (
                <audio
                    ref={audioRef}
                    src={episode.media_url}
                    className="w-full"
                    controls
                    preload="metadata"
                    onError={() => setError('Audio loading failed')}
                    onCanPlay={() => setError(null)}
                />
            )}
        </div>
    );
}
