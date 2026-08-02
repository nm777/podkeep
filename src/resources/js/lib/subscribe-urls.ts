import { type Feed } from '@/types';

export function getFullRssUrl(feed: Feed): string {
    const baseUrl = `/rss/${feed.user_guid}/${feed.slug}`;
    if (!feed.is_public && feed.token) {
        return `${baseUrl}?token=${feed.token}`;
    }
    return baseUrl;
}

export function getAbsoluteRssUrl(feed: Feed): string {
    return window.location.origin + getFullRssUrl(feed);
}

export function getApplePodcastsUrlForRssUrl(rssUrl: string): string {
    return `podcast://${rssUrl.replace('https://', '').replace('http://', '')}`;
}

export function getGooglePodcastsUrlForRssUrl(rssUrl: string): string {
    return `https://podcasts.google.com/subscribe?url=${encodeURIComponent(rssUrl)}`;
}

export function getApplePodcastsUrl(feed: Feed): string {
    return getApplePodcastsUrlForRssUrl(getAbsoluteRssUrl(feed));
}

export function getGooglePodcastsUrl(feed: Feed): string {
    return getGooglePodcastsUrlForRssUrl(getAbsoluteRssUrl(feed));
}

export function getShareUrl(feed: Feed): string {
    const baseUrl = `/share/${feed.user_guid}/${feed.slug}`;
    if (!feed.is_public && feed.token) {
        return `${baseUrl}?token=${feed.token}`;
    }
    return baseUrl;
}
