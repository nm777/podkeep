import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { getApplePodcastsUrl, getGooglePodcastsUrl } from '@/lib/subscribe-urls';
import { type Feed } from '@/types';
import { Link } from '@inertiajs/react';
import { Copy, Edit, Eye, EyeOff, FileAudio, Share, Smartphone, Trash2 } from 'lucide-react';

interface FeedCardProps {
    feed: Feed;
    onCopyUrl: (feed: Feed) => void;
    onCopyShareUrl: (feed: Feed) => void;
    onDelete: (feedId: number) => void;
}

export default function FeedCard({ feed, onCopyUrl, onCopyShareUrl, onDelete }: FeedCardProps) {
    return (
        <div className="px-4 py-3">
            <p className="font-medium md:truncate">{feed.title}</p>
            <div className="mt-1 flex items-center gap-2">
                <span className="text-xs text-muted-foreground">{feed.items_count ?? 0} items</span>
                <span
                    className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium ${
                        feed.is_public
                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                            : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400'
                    }`}
                >
                    {feed.is_public ? <Eye className="h-3 w-3" /> : <EyeOff className="h-3 w-3" />}
                    {feed.is_public ? 'Public' : 'Private'}
                </span>
                <div className="ml-auto flex items-center gap-1">
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button variant="ghost" size="icon" className="h-8 w-8" asChild>
                                <a href={getApplePodcastsUrl(feed)} target="_blank" rel="noopener noreferrer">
                                    <FileAudio className="h-4 w-4" />
                                </a>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Apple Podcasts</TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button variant="ghost" size="icon" className="h-8 w-8" asChild>
                                <a href={getGooglePodcastsUrl(feed)} target="_blank" rel="noopener noreferrer">
                                    <Smartphone className="h-4 w-4" />
                                </a>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Google Podcasts</TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => onCopyShareUrl(feed)}>
                                <Share className="h-4 w-4" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Copy Share Link</TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => onCopyUrl(feed)}>
                                <Copy className="h-4 w-4" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Copy RSS URL</TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button variant="ghost" size="icon" className="h-8 w-8" asChild>
                                <Link href={route('feeds.edit', feed.id)}>
                                    <Edit className="h-4 w-4" />
                                </Link>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Edit</TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                className="h-8 w-8 text-destructive hover:text-destructive"
                                onClick={() => onDelete(feed.id)}
                            >
                                <Trash2 className="h-4 w-4" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Delete</TooltipContent>
                    </Tooltip>
                </div>
            </div>
        </div>
    );
}
