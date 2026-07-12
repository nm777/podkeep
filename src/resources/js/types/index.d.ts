import type { PageProps } from '@inertiajs/core';
import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface MediaFile {
    id: number;
    public_url?: string;
    file_hash: string;
    mime_type: string;
    filesize: number;
    duration?: number;
    source_url?: string;
    created_at: string;
    updated_at: string;
}

export interface LibraryItem {
    id: number;
    user_id: number;
    media_file_id?: number;
    title: string;
    description?: string;
    source_type: string;
    source_url?: string;
    is_duplicate: boolean;
    duplicate_detected_at?: string;
    processing_status: string;
    processing_started_at?: string;
    processing_completed_at?: string;
    processing_error?: string;
    published_at?: string;
    display_date?: string;
    created_at: string;
    updated_at: string;
    media_file?: MediaFile | null;
    feeds?: Feed[];
}

export interface FeedItem {
    id: number;
    feed_id: number;
    library_item_id: number;
    sequence: number;
    library_item: LibraryItem;
}

export interface Feed {
    id: number;
    title: string;
    description?: string;
    website_url?: string;
    is_public: boolean;
    feed_type: 'static' | 'append';
    slug: string;
    user_guid: string;
    token?: string;
    items_count?: number;
    items?: FeedItem[];
    created_at: string;
    updated_at: string;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    feeds: Feed[];
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    is_admin: boolean;
    approval_status: 'pending' | 'approved' | 'rejected';
    created_at: string;
    updated_at: string;
}

export interface ShareFeed {
    title: string;
    description: string | null;
    cover_image_url: string | null;
}

export interface ShareEpisode {
    id: number;
    title: string;
    description: string | null;
    published_at: string | null;
    duration: number | null;
    media_url: string;
}

export interface SharePageProps {
    feed: ShareFeed;
    episodes: ShareEpisode[];
    rssUrl: string;
    isPublic: boolean;
}
