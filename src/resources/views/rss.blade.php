<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:psc="http://podlove.org/simple-chapters"
    xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ $feed->title }}</title>
        <description><![CDATA[{!! str_replace(']]>', ']]]]><![CDATA[>', $feed->description) !!}]]></description>
        <link>{{ $feed->website_url ?? route('share.show', ['user_guid' => $feed->user_guid, 'feed_slug' => $feed->slug]) }}</link>
        <image>
            <url>{{ $feed->cover_image_url ?? asset('logo.svg') }}</url>
            <title>{{ $feed->title }}</title>
            <link>{{ route('rss.show', ['user_guid' => $feed->user_guid, 'feed_slug' =>
            $feed->slug]) }}</link>
        </image>
        <language>en-us</language>
        <atom:link
            href="{{ route('rss.show', ['user_guid' => $feed->user_guid, 'feed_slug' => $feed->slug]) }}"
            rel="self" type="application/rss+xml" />
        @php($episodeIndex = 0)
        @foreach ($feed->items as $item)
            @if($item->libraryItem->mediaFile)
        <item>
            <title>{{ $item->libraryItem->title }}</title>
            <description><![CDATA[{!! $feed->feed_type->isAppend() && $item->libraryItem->display_date ? '[' . $item->libraryItem->display_date->format('M j, Y') . '] ' : '' !!}{!! str_replace(']]>', ']]]]><![CDATA[>', $item->libraryItem->description) !!}]]></description>
            <pubDate>{{ ($feed->feed_type->isStatic()
                ? $feed->created_at->copy()->addMinutes($item->sequence)
                : $item->created_at)->toRfc822String() }}</pubDate>
            <guid isPermaLink="false">{{ $item->id }}</guid>
            <enclosure url="{{ $item->libraryItem->mediaFile->rss_url }}{{ $feed->is_public ? '' : '?feed_token=' . $feed->token }}"
                length="{{ $item->libraryItem->mediaFile->filesize }}"
                type="{{ $item->libraryItem->mediaFile->mime_type }}" />
            @if($item->libraryItem->mediaFile->chapters->isNotEmpty())
            <psc:chapters version="1.2">@foreach($item->libraryItem->mediaFile->chapters as $chapter)
                <psc:chapter start="{{ $chapter->formattedStart() }}" title="{{ $chapter->title }}" />@endforeach
            </psc:chapters>
            @endif
        </item>
            @php($episodeIndex++)
            @endif
        @endforeach
    </channel>
</rss>
