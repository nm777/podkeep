<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Console\\\\Commands\\\\CleanupOrphanedMediaFilesCommand\\:\\:handle\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Console/Commands/CleanupOrphanedMediaFilesCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Console/Commands/RedownloadMedia.php',
];
$ignoreErrors[] = [
	'message' => '#^Class App\\\\Services\\\\MediaProcessing\\\\MediaRedownloader constructor invoked with 2 parameters, 3 required\\.$#',
	'identifier' => 'arguments.count',
	'count' => 1,
	'path' => __DIR__ . '/app/Console/Commands/RedownloadMedia.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Console\\\\Commands\\\\RedownloadMedia\\:\\:handle\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Console/Commands/RedownloadMedia.php',
];
$ignoreErrors[] = [
	'message' => '#^Relation \'mediaFile\' is not found in App\\\\Models\\\\LibraryItem model\\.$#',
	'identifier' => 'larastan.relationExistence',
	'count' => 2,
	'path' => __DIR__ . '/app/Console/Commands/RedownloadMedia.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method diffForHumans\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/Console/Commands/ResetStuckProcessing.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Console\\\\Commands\\\\ResetStuckProcessing\\:\\:handle\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Console/Commands/ResetStuckProcessing.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$previous of method Exception\\:\\:__construct\\(\\) expects Throwable\\|null, App\\\\Exceptions\\\\Throwable\\|null given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/app/Exceptions/ApiException.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\$previous of method App\\\\Exceptions\\\\ApiException\\:\\:__construct\\(\\) has invalid type App\\\\Exceptions\\\\Throwable\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Exceptions/ApiException.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Exceptions\\\\ApiException\\:\\:\\$errorCode has no type specified\\.$#',
	'identifier' => 'missingType.property',
	'count' => 1,
	'path' => __DIR__ . '/app/Exceptions/ApiException.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Exceptions\\\\ApiException\\:\\:\\$errorType has no type specified\\.$#',
	'identifier' => 'missingType.property',
	'count' => 1,
	'path' => __DIR__ . '/app/Exceptions/ApiException.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\Feed\\:\\:\\$items\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\FeedController\\:\\:destroy\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\FeedController\\:\\:edit\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\FeedController\\:\\:index\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\FeedController\\:\\:store\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\FeedController\\:\\:syncFeedItems\\(\\) has parameter \\$items with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\FeedController\\:\\:update\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\|Illuminate\\\\Database\\\\Eloquent\\\\Collection\\<int, App\\\\Models\\\\LibraryItem\\>\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 2,
	'path' => __DIR__ . '/app/Http/Controllers/LibraryController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\LibraryController\\:\\:destroy\\(\\) has parameter \\$id with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/LibraryController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\LibraryController\\:\\:getSourceTypeAndUrl\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/LibraryController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\LibraryController\\:\\:redownload\\(\\) has parameter \\$id with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/LibraryController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\LibraryController\\:\\:retry\\(\\) has parameter \\$id with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/LibraryController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\LibraryController\\:\\:update\\(\\) has parameter \\$id with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/LibraryController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\MediaController\\:\\:show\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/MediaController.php',
];
$ignoreErrors[] = [
	'message' => '#^Relation \'items\' is not found in App\\\\Models\\\\Feed model\\.$#',
	'identifier' => 'larastan.relationExistence',
	'count' => 2,
	'path' => __DIR__ . '/app/Http/Controllers/MediaController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method isChronological\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/RssController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\RssController\\:\\:show\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/RssController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\RssController\\:\\:show\\(\\) has parameter \\$feed_slug with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/RssController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\RssController\\:\\:show\\(\\) has parameter \\$user_guid with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/RssController.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\Feed\\:\\:\\$items\\.$#',
	'identifier' => 'property.notFound',
	'count' => 2,
	'path' => __DIR__ . '/app/Http/Controllers/ShareController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method isChronological\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/ShareController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\ShareController\\:\\:show\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/ShareController.php',
];
$ignoreErrors[] = [
	'message' => '#^Relation \'items\' is not found in App\\\\Models\\\\Feed model\\.$#',
	'identifier' => 'larastan.relationExistence',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/ShareController.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/Web/UrlDuplicateCheckController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\Web\\\\UrlDuplicateCheckController\\:\\:check\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/Web/UrlDuplicateCheckController.php',
];
$ignoreErrors[] = [
	'message' => '#^Using nullsafe property access "\\?\\-\\>mediaFile" on left side of \\?\\? is unnecessary\\. Use \\-\\> instead\\.$#',
	'identifier' => 'nullsafe.neverNull',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Controllers/Web/UrlDuplicateCheckController.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$created_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$feed_id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$libraryItem\\.$#',
	'identifier' => 'property.notFound',
	'count' => 2,
	'path' => __DIR__ . '/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$library_item_id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$sequence\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$updated_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$cover_image_url\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$created_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$description\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$episode_order\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$is_public\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$items_count\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$slug\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$title\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$token\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$updated_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$user_guid\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$user_id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$website_url\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$created_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$description\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$duplicate_detected_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$is_duplicate\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 2,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$processing_completed_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$processing_error\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$processing_started_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$processing_status\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$processing_status_display\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$published_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$source_type\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$source_url\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$title\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$updated_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:hasCompleted\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:hasFailed\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:isPending\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:isProcessing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$created_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$duration\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$file_hash\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$filesize\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$mime_type\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$public_url\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$source_url\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$updated_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Jobs\\\\AddLibraryItemToFeedsJob\\:\\:__construct\\(\\) has parameter \\$feedIds with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Jobs/AddLibraryItemToFeedsJob.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Jobs/CleanupDuplicateLibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Relation \'libraryItems\' is not found in App\\\\Models\\\\MediaFile model\\.$#',
	'identifier' => 'larastan.relationExistence',
	'count' => 1,
	'path' => __DIR__ . '/app/Jobs/CleanupOrphanedMediaFiles.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Jobs/RedownloadMediaFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Class App\\\\Models\\\\Feed uses generic trait Illuminate\\\\Database\\\\Eloquent\\\\Factories\\\\HasFactory but does not specify its types\\: TFactory$#',
	'identifier' => 'missingType.generics',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/Feed.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\Feed\\:\\:items\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/Feed.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\Feed\\:\\:user\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/Feed.php',
];
$ignoreErrors[] = [
	'message' => '#^Class App\\\\Models\\\\FeedItem uses generic trait Illuminate\\\\Database\\\\Eloquent\\\\Factories\\\\HasFactory but does not specify its types\\: TFactory$#',
	'identifier' => 'missingType.generics',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/FeedItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\FeedItem\\:\\:feed\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/FeedItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\FeedItem\\:\\:libraryItem\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/FeedItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Array has 2 duplicate keys with value \'published_at\' \\(\'published_at\', \'published_at\'\\)\\.$#',
	'identifier' => 'array.duplicateKey',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getDisplayName\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method hasCompleted\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method hasFailed\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method isPending\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method isProcessing\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Class App\\\\Models\\\\LibraryItem uses generic trait Illuminate\\\\Database\\\\Eloquent\\\\Factories\\\\HasFactory but does not specify its types\\: TFactory$#',
	'identifier' => 'missingType.generics',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\LibraryItem\\:\\:feedItems\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\LibraryItem\\:\\:feeds\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\LibraryItem\\:\\:findByHashForUser\\(\\) should return static\\(App\\\\Models\\\\LibraryItem\\)\\|null but returns App\\\\Models\\\\LibraryItem\\|null\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\LibraryItem\\:\\:findBySourceUrlForUser\\(\\) should return static\\(App\\\\Models\\\\LibraryItem\\)\\|null but returns App\\\\Models\\\\LibraryItem\\|null\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\LibraryItem\\:\\:mediaFile\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\LibraryItem\\:\\:user\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Using nullsafe method call on non\\-nullable type string\\. Use \\-\\> instead\\.$#',
	'identifier' => 'nullsafe.neverNull',
	'count' => 5,
	'path' => __DIR__ . '/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Class App\\\\Models\\\\MediaFile uses generic trait Illuminate\\\\Database\\\\Eloquent\\\\Factories\\\\HasFactory but does not specify its types\\: TFactory$#',
	'identifier' => 'missingType.generics',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/MediaFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\MediaFile\\:\\:findByHash\\(\\) should return static\\(App\\\\Models\\\\MediaFile\\)\\|null but returns App\\\\Models\\\\MediaFile\\|null\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/MediaFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\MediaFile\\:\\:findBySourceUrl\\(\\) should return static\\(App\\\\Models\\\\MediaFile\\)\\|null but returns App\\\\Models\\\\MediaFile\\|null\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/MediaFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\MediaFile\\:\\:libraryItems\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/MediaFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\MediaFile\\:\\:user\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/MediaFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\User\\:\\:feeds\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\User\\:\\:libraryItems\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\=\\=\\= between \'approved\'\\|\'pending\'\\|\'rejected\' and App\\\\Enums\\\\ApprovalStatusType\\:\\:APPROVED will always evaluate to false\\.$#',
	'identifier' => 'identical.alwaysFalse',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\=\\=\\= between \'approved\'\\|\'pending\'\\|\'rejected\' and App\\\\Enums\\\\ApprovalStatusType\\:\\:PENDING will always evaluate to false\\.$#',
	'identifier' => 'identical.alwaysFalse',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\=\\=\\= between \'approved\'\\|\'pending\'\\|\'rejected\' and App\\\\Enums\\\\ApprovalStatusType\\:\\:REJECTED will always evaluate to false\\.$#',
	'identifier' => 'identical.alwaysFalse',
	'count' => 1,
	'path' => __DIR__ . '/app/Models/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Policies\\\\FeedPolicy\\:\\:delete\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Policies/FeedPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Policies\\\\FeedPolicy\\:\\:update\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/Policies/FeedPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Class App\\\\ProcessingStatusType not found\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/ProcessingStatusType.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/DuplicateDetectionService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\DuplicateDetectionService\\:\\:analyzeFileUpload\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/DuplicateDetectionService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\DuplicateDetectionService\\:\\:analyzeUrlSource\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/DuplicateDetectionService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaProcessingService\\:\\:handleProcessingError\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/MediaProcessingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaProcessingService\\:\\:processFromFile\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/MediaProcessingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaProcessingService\\:\\:processFromUrl\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/MediaProcessingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\!\\=\\= between \'completed\'\\|\'failed\'\\|\'pending\'\\|\'processing\' and App\\\\Enums\\\\ProcessingStatusType\\:\\:PROCESSING will always evaluate to true\\.$#',
	'identifier' => 'notIdentical.alwaysTrue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/MediaProcessingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/MediaRedownloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaRedownloader\\:\\:redownload\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/MediaRedownloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaStorageManager\\:\\:moveTempFile\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/MediaStorageManager.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaStorageManager\\:\\:storeFile\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/MediaStorageManager.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaValidator\\:\\:validate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/MediaValidator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:analyzeUrlDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:buildSuccessResponse\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleGlobalFileDuplicate\\(\\) has parameter \\$duplicateAnalysis with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleGlobalFileDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleGlobalUrlDuplicate\\(\\) has parameter \\$duplicateAnalysis with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleGlobalUrlDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleUserFileDuplicate\\(\\) has parameter \\$duplicateAnalysis with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleUserFileDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleUserMediaFileOnly\\(\\) has parameter \\$duplicateAnalysis with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleUserMediaFileOnly\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleUserUrlDuplicate\\(\\) has parameter \\$duplicateAnalysis with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleUserUrlDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:processFileDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:processUrlDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\LibraryItemFactory\\:\\:createFromValidated\\(\\) has parameter \\$validated with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/SourceProcessors/LibraryItemFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\LibraryItemFactory\\:\\:createFromValidatedWithMediaData\\(\\) has parameter \\$mediaFileData with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/SourceProcessors/LibraryItemFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\LibraryItemFactory\\:\\:createFromValidatedWithMediaData\\(\\) has parameter \\$validated with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/SourceProcessors/LibraryItemFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\LibraryItemFactory\\:\\:createFromValidatedWithMediaFile\\(\\) has parameter \\$mediaFile with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/SourceProcessors/LibraryItemFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\LibraryItemFactory\\:\\:createFromValidatedWithMediaFile\\(\\) has parameter \\$validated with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/SourceProcessors/LibraryItemFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\LibraryItemFactory\\:\\:dispatchFeedJob\\(\\) has parameter \\$validated with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/SourceProcessors/LibraryItemFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$value on string\\.$#',
	'identifier' => 'property.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/SourceProcessors/UrlSourceProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\UrlSourceProcessor\\:\\:process\\(\\) has parameter \\$validated with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/SourceProcessors/UrlSourceProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\UrlSourceProcessor\\:\\:process\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/SourceProcessors/UrlSourceProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Using nullsafe property access on non\\-nullable type string\\. Use \\-\\> instead\\.$#',
	'identifier' => 'nullsafe.neverNull',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/SourceProcessors/UrlSourceProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\YouTube\\\\YouTubeFileProcessor\\:\\:processFile\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/YouTube/YouTubeFileProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\YouTube\\\\YouTubeFileProcessor\\:\\:updateLibraryItemWithMetadata\\(\\) has parameter \\$metadata with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/YouTube/YouTubeFileProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\YouTube\\\\YouTubeMetadataExtractor\\:\\:extractMetadata\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/YouTube/YouTubeMetadataExtractor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\YouTube\\\\YouTubeProcessingService\\:\\:downloadAndProcess\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/YouTube/YouTubeProcessingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\YouTube\\\\YouTubeProcessingService\\:\\:processYouTubeUrl\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/YouTube/YouTubeProcessingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\YouTubeVideoInfoService\\:\\:getVideoInfo\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/Services/YouTubeVideoInfoService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Console\\\\Commands\\\\CleanupOrphanedMediaFilesCommand\\:\\:handle\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Console/Commands/CleanupOrphanedMediaFilesCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Console/Commands/RedownloadMedia.php',
];
$ignoreErrors[] = [
	'message' => '#^Class App\\\\Services\\\\MediaProcessing\\\\MediaRedownloader constructor invoked with 2 parameters, 3 required\\.$#',
	'identifier' => 'arguments.count',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Console/Commands/RedownloadMedia.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Console\\\\Commands\\\\RedownloadMedia\\:\\:handle\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Console/Commands/RedownloadMedia.php',
];
$ignoreErrors[] = [
	'message' => '#^Relation \'mediaFile\' is not found in App\\\\Models\\\\LibraryItem model\\.$#',
	'identifier' => 'larastan.relationExistence',
	'count' => 2,
	'path' => __DIR__ . '/app/app/Console/Commands/RedownloadMedia.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method diffForHumans\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Console/Commands/ResetStuckProcessing.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Console\\\\Commands\\\\ResetStuckProcessing\\:\\:handle\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Console/Commands/ResetStuckProcessing.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$previous of method Exception\\:\\:__construct\\(\\) expects Throwable\\|null, App\\\\Exceptions\\\\Throwable\\|null given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Exceptions/ApiException.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\$previous of method App\\\\Exceptions\\\\ApiException\\:\\:__construct\\(\\) has invalid type App\\\\Exceptions\\\\Throwable\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Exceptions/ApiException.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Exceptions\\\\ApiException\\:\\:\\$errorCode has no type specified\\.$#',
	'identifier' => 'missingType.property',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Exceptions/ApiException.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Exceptions\\\\ApiException\\:\\:\\$errorType has no type specified\\.$#',
	'identifier' => 'missingType.property',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Exceptions/ApiException.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\Feed\\:\\:\\$items\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\FeedController\\:\\:destroy\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\FeedController\\:\\:edit\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\FeedController\\:\\:index\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\FeedController\\:\\:store\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\FeedController\\:\\:syncFeedItems\\(\\) has parameter \\$items with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\FeedController\\:\\:update\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/FeedController.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\|Illuminate\\\\Database\\\\Eloquent\\\\Collection\\<int, App\\\\Models\\\\LibraryItem\\>\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 2,
	'path' => __DIR__ . '/app/app/Http/Controllers/LibraryController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\LibraryController\\:\\:destroy\\(\\) has parameter \\$id with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/LibraryController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\LibraryController\\:\\:getSourceTypeAndUrl\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/LibraryController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\LibraryController\\:\\:redownload\\(\\) has parameter \\$id with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/LibraryController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\LibraryController\\:\\:retry\\(\\) has parameter \\$id with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/LibraryController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\LibraryController\\:\\:update\\(\\) has parameter \\$id with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/LibraryController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\MediaController\\:\\:show\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/MediaController.php',
];
$ignoreErrors[] = [
	'message' => '#^Relation \'items\' is not found in App\\\\Models\\\\Feed model\\.$#',
	'identifier' => 'larastan.relationExistence',
	'count' => 2,
	'path' => __DIR__ . '/app/app/Http/Controllers/MediaController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\RssController\\:\\:show\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/RssController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\RssController\\:\\:show\\(\\) has parameter \\$feed_slug with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/RssController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\RssController\\:\\:show\\(\\) has parameter \\$user_guid with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/RssController.php',
];
$ignoreErrors[] = [
	'message' => '#^Relation \'items\' is not found in App\\\\Models\\\\Feed model\\.$#',
	'identifier' => 'larastan.relationExistence',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/RssController.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\Feed\\:\\:\\$items\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/ShareController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\ShareController\\:\\:show\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/ShareController.php',
];
$ignoreErrors[] = [
	'message' => '#^Relation \'items\' is not found in App\\\\Models\\\\Feed model\\.$#',
	'identifier' => 'larastan.relationExistence',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/ShareController.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/Web/UrlDuplicateCheckController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Http\\\\Controllers\\\\Web\\\\UrlDuplicateCheckController\\:\\:check\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/Web/UrlDuplicateCheckController.php',
];
$ignoreErrors[] = [
	'message' => '#^Using nullsafe property access "\\?\\-\\>mediaFile" on left side of \\?\\? is unnecessary\\. Use \\-\\> instead\\.$#',
	'identifier' => 'nullsafe.neverNull',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Controllers/Web/UrlDuplicateCheckController.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$created_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$feed_id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$libraryItem\\.$#',
	'identifier' => 'property.notFound',
	'count' => 2,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$library_item_id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$sequence\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedItemResource\\:\\:\\$updated_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$cover_image_url\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$created_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$description\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$is_public\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$items_count\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$slug\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$title\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$token\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$updated_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$user_guid\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$user_id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\FeedResource\\:\\:\\$website_url\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/FeedResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$created_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$description\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$duplicate_detected_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$is_duplicate\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 2,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$processing_completed_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$processing_error\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$processing_started_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$processing_status\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$processing_status_display\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$published_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$source_type\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$source_url\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$title\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:\\$updated_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:hasCompleted\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:hasFailed\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:isPending\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method App\\\\Http\\\\Resources\\\\LibraryItemResource\\:\\:isProcessing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/LibraryItemResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$created_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$duration\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$file_hash\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$filesize\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$id\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$mime_type\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$public_url\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$source_url\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Http\\\\Resources\\\\MediaFileResource\\:\\:\\$updated_at\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Http/Resources/MediaFileResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Jobs\\\\AddLibraryItemToFeedsJob\\:\\:__construct\\(\\) has parameter \\$feedIds with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Jobs/AddLibraryItemToFeedsJob.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Jobs/CleanupDuplicateLibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Relation \'libraryItems\' is not found in App\\\\Models\\\\MediaFile model\\.$#',
	'identifier' => 'larastan.relationExistence',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Jobs/CleanupOrphanedMediaFiles.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Jobs/RedownloadMediaFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Class App\\\\Models\\\\Feed uses generic trait Illuminate\\\\Database\\\\Eloquent\\\\Factories\\\\HasFactory but does not specify its types\\: TFactory$#',
	'identifier' => 'missingType.generics',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/Feed.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\Feed\\:\\:items\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/Feed.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\Feed\\:\\:user\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/Feed.php',
];
$ignoreErrors[] = [
	'message' => '#^Class App\\\\Models\\\\FeedItem uses generic trait Illuminate\\\\Database\\\\Eloquent\\\\Factories\\\\HasFactory but does not specify its types\\: TFactory$#',
	'identifier' => 'missingType.generics',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/FeedItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\FeedItem\\:\\:feed\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/FeedItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\FeedItem\\:\\:libraryItem\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/FeedItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Array has 2 duplicate keys with value \'published_at\' \\(\'published_at\', \'published_at\'\\)\\.$#',
	'identifier' => 'array.duplicateKey',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getDisplayName\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method hasCompleted\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method hasFailed\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method isPending\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method isProcessing\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Class App\\\\Models\\\\LibraryItem uses generic trait Illuminate\\\\Database\\\\Eloquent\\\\Factories\\\\HasFactory but does not specify its types\\: TFactory$#',
	'identifier' => 'missingType.generics',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\LibraryItem\\:\\:feedItems\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\LibraryItem\\:\\:feeds\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\LibraryItem\\:\\:findByHashForUser\\(\\) should return static\\(App\\\\Models\\\\LibraryItem\\)\\|null but returns App\\\\Models\\\\LibraryItem\\|null\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\LibraryItem\\:\\:findBySourceUrlForUser\\(\\) should return static\\(App\\\\Models\\\\LibraryItem\\)\\|null but returns App\\\\Models\\\\LibraryItem\\|null\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\LibraryItem\\:\\:mediaFile\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\LibraryItem\\:\\:user\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Using nullsafe method call on non\\-nullable type string\\. Use \\-\\> instead\\.$#',
	'identifier' => 'nullsafe.neverNull',
	'count' => 5,
	'path' => __DIR__ . '/app/app/Models/LibraryItem.php',
];
$ignoreErrors[] = [
	'message' => '#^Class App\\\\Models\\\\MediaFile uses generic trait Illuminate\\\\Database\\\\Eloquent\\\\Factories\\\\HasFactory but does not specify its types\\: TFactory$#',
	'identifier' => 'missingType.generics',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/MediaFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\MediaFile\\:\\:findByHash\\(\\) should return static\\(App\\\\Models\\\\MediaFile\\)\\|null but returns App\\\\Models\\\\MediaFile\\|null\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/MediaFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\MediaFile\\:\\:findBySourceUrl\\(\\) should return static\\(App\\\\Models\\\\MediaFile\\)\\|null but returns App\\\\Models\\\\MediaFile\\|null\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/MediaFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\MediaFile\\:\\:libraryItems\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/MediaFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\MediaFile\\:\\:user\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/MediaFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\User\\:\\:feeds\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Models\\\\User\\:\\:libraryItems\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\=\\=\\= between \'approved\'\\|\'pending\'\\|\'rejected\' and App\\\\Enums\\\\ApprovalStatusType\\:\\:APPROVED will always evaluate to false\\.$#',
	'identifier' => 'identical.alwaysFalse',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\=\\=\\= between \'approved\'\\|\'pending\'\\|\'rejected\' and App\\\\Enums\\\\ApprovalStatusType\\:\\:PENDING will always evaluate to false\\.$#',
	'identifier' => 'identical.alwaysFalse',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\=\\=\\= between \'approved\'\\|\'pending\'\\|\'rejected\' and App\\\\Enums\\\\ApprovalStatusType\\:\\:REJECTED will always evaluate to false\\.$#',
	'identifier' => 'identical.alwaysFalse',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Models/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Policies\\\\FeedPolicy\\:\\:delete\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Policies/FeedPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Policies\\\\FeedPolicy\\:\\:update\\(\\) has no return type specified\\.$#',
	'identifier' => 'missingType.return',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Policies/FeedPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Class App\\\\ProcessingStatusType not found\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/ProcessingStatusType.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/DuplicateDetectionService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\DuplicateDetectionService\\:\\:analyzeFileUpload\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/DuplicateDetectionService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\DuplicateDetectionService\\:\\:analyzeUrlSource\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/DuplicateDetectionService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaProcessingService\\:\\:handleProcessingError\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/MediaProcessingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaProcessingService\\:\\:processFromFile\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/MediaProcessingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaProcessingService\\:\\:processFromUrl\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/MediaProcessingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\!\\=\\= between \'completed\'\\|\'failed\'\\|\'pending\'\\|\'processing\' and App\\\\Enums\\\\ProcessingStatusType\\:\\:PROCESSING will always evaluate to true\\.$#',
	'identifier' => 'notIdentical.alwaysTrue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/MediaProcessingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/MediaRedownloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaRedownloader\\:\\:redownload\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/MediaRedownloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaStorageManager\\:\\:moveTempFile\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/MediaStorageManager.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaStorageManager\\:\\:storeFile\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/MediaStorageManager.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\MediaValidator\\:\\:validate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/MediaValidator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:analyzeUrlDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:buildSuccessResponse\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleGlobalFileDuplicate\\(\\) has parameter \\$duplicateAnalysis with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleGlobalFileDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleGlobalUrlDuplicate\\(\\) has parameter \\$duplicateAnalysis with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleGlobalUrlDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleUserFileDuplicate\\(\\) has parameter \\$duplicateAnalysis with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleUserFileDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleUserMediaFileOnly\\(\\) has parameter \\$duplicateAnalysis with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleUserMediaFileOnly\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleUserUrlDuplicate\\(\\) has parameter \\$duplicateAnalysis with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:handleUserUrlDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:processFileDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor\\:\\:processUrlDuplicate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/MediaProcessing/UnifiedDuplicateProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\LibraryItemFactory\\:\\:createFromValidated\\(\\) has parameter \\$validated with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/SourceProcessors/LibraryItemFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\LibraryItemFactory\\:\\:createFromValidatedWithMediaData\\(\\) has parameter \\$mediaFileData with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/SourceProcessors/LibraryItemFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\LibraryItemFactory\\:\\:createFromValidatedWithMediaData\\(\\) has parameter \\$validated with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/SourceProcessors/LibraryItemFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\LibraryItemFactory\\:\\:createFromValidatedWithMediaFile\\(\\) has parameter \\$mediaFile with no type specified\\.$#',
	'identifier' => 'missingType.parameter',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/SourceProcessors/LibraryItemFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\LibraryItemFactory\\:\\:createFromValidatedWithMediaFile\\(\\) has parameter \\$validated with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/SourceProcessors/LibraryItemFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\LibraryItemFactory\\:\\:dispatchFeedJob\\(\\) has parameter \\$validated with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/SourceProcessors/LibraryItemFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$value on string\\.$#',
	'identifier' => 'property.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/SourceProcessors/UrlSourceProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\UrlSourceProcessor\\:\\:process\\(\\) has parameter \\$validated with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/SourceProcessors/UrlSourceProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\SourceProcessors\\\\UrlSourceProcessor\\:\\:process\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/SourceProcessors/UrlSourceProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Using nullsafe property access on non\\-nullable type string\\. Use \\-\\> instead\\.$#',
	'identifier' => 'nullsafe.neverNull',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/SourceProcessors/UrlSourceProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\YouTube\\\\YouTubeFileProcessor\\:\\:processFile\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/YouTube/YouTubeFileProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\YouTube\\\\YouTubeFileProcessor\\:\\:updateLibraryItemWithMetadata\\(\\) has parameter \\$metadata with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/YouTube/YouTubeFileProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\YouTube\\\\YouTubeMetadataExtractor\\:\\:extractMetadata\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/YouTube/YouTubeMetadataExtractor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\YouTube\\\\YouTubeProcessingService\\:\\:downloadAndProcess\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/YouTube/YouTubeProcessingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\YouTube\\\\YouTubeProcessingService\\:\\:processYouTubeUrl\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/YouTube/YouTubeProcessingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Services\\\\YouTubeVideoInfoService\\:\\:getVideoInfo\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/app/app/Services/YouTubeVideoInfoService.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/AccessControlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:postJson\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/AccessControlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/AddLibraryItemToFeedsJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseCount\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/AddLibraryItemToFeedsJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/AddLibraryItemToFeedsJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/AddLibraryItemToFeedsJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property Pest\\\\Mixins\\\\Expectation\\<mixed\\>\\:\\:\\$not\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Api/V1/ApiKeyManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 7,
	'path' => __DIR__ . '/tests/Feature/Api/V1/ApiKeyManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/Api/V1/ApiKeyManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/Api/V1/ApiKeyManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:withHeader\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Api/V1/ApiKeyManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:getJson\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Api/V1/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:withHeader\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 6,
	'path' => __DIR__ . '/tests/Feature/Api/V1/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/Api/V1/FeedControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Api/V1/FeedControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:postJson\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Api/V1/FeedControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:withHeader\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 11,
	'path' => __DIR__ . '/tests/Feature/Api/V1/FeedControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TKey in call to function collect$#',
	'identifier' => 'argument.templateType',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Api/V1/FeedControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TValue in call to function collect$#',
	'identifier' => 'argument.templateType',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Api/V1/FeedControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/Api/V1/FeedItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/Api/V1/FeedItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:withHeader\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 10,
	'path' => __DIR__ . '/tests/Feature/Api/V1/FeedItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/Feature/Api/V1/LibraryItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Api/V1/LibraryItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:withHeader\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 12,
	'path' => __DIR__ . '/tests/Feature/Api/V1/LibraryItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TKey in call to function collect$#',
	'identifier' => 'argument.templateType',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Api/V1/LibraryItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TValue in call to function collect$#',
	'identifier' => 'argument.templateType',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Api/V1/LibraryItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:withHeader\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 7,
	'path' => __DIR__ . '/tests/Feature/Api/V1/MediaProcessingControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/Feature/ApiResourceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Auth/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertAuthenticated\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Auth/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertGuest\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/Auth/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Auth/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:post\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/Auth/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/Auth/EmailVerificationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/Auth/PasswordConfirmationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/Auth/PasswordResetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:post\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/Auth/PasswordResetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Auth/RegistrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertGuest\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Auth/RegistrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Auth/RegistrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:post\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Auth/RegistrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/Feature/CleanupDuplicateLibraryItemTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/DashboardTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/DashboardTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$expectedHash\\.$#',
	'identifier' => 'property.notFound',
	'count' => 7,
	'path' => __DIR__ . '/tests/Feature/DuplicateDetectionHashOptimizationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$filePath\\.$#',
	'identifier' => 'property.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/Feature/DuplicateDetectionHashOptimizationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 7,
	'path' => __DIR__ . '/tests/Feature/DuplicateDetectionHashOptimizationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/DuplicateDetectionHashOptimizationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/Feature/EpisodeOrderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/EpisodeOrderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/EpisodeOrderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:withHeader\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/EpisodeOrderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/ExampleTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/ExceptionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/ExceptionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:getJson\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/ExceptionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/FactoryDefinitionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/FeedEditPaginationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 11,
	'path' => __DIR__ . '/tests/Feature/FeedEditTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/FeedEditTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/FeedEditTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/FeedIdsOwnershipTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\Feed\\:\\:\\$items\\.$#',
	'identifier' => 'property.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/FeedItemClearTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/FeedItemClearTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TValue in call to function expect$#',
	'identifier' => 'argument.templateType',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/FeedItemClearTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$feed\\.$#',
	'identifier' => 'property.notFound',
	'count' => 11,
	'path' => __DIR__ . '/tests/Feature/FeedItemSyncTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$items\\.$#',
	'identifier' => 'property.notFound',
	'count' => 21,
	'path' => __DIR__ . '/tests/Feature/FeedItemSyncTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/FeedItemSyncTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 8,
	'path' => __DIR__ . '/tests/Feature/FeedItemSyncTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/FeedItemSyncTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 9,
	'path' => __DIR__ . '/tests/Feature/FeedManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/FeedManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/FeedManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:delete\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/FeedManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/FeedManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:post\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/FeedManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/FeedTokenLengthTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$duplicateProcessor of class App\\\\Services\\\\SourceProcessors\\\\FileUploadProcessor constructor expects App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/FileUploadProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/LibraryDuplicateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/LibraryDuplicateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/LibraryDuplicateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/LibraryItemFactoryFeedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/LibraryItemFactoryFeedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$factory\\.$#',
	'identifier' => 'property.notFound',
	'count' => 10,
	'path' => __DIR__ . '/tests/Feature/LibraryItemFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 13,
	'path' => __DIR__ . '/tests/Feature/LibraryItemFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/LibraryItemFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/LibraryItemFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/LibraryUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 11,
	'path' => __DIR__ . '/tests/Feature/LibraryUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/Feature/LibraryUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/LibraryUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/LibraryUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant FAILED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/LibraryUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 12,
	'path' => __DIR__ . '/tests/Feature/LibraryUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/Feature/LibraryUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TValue in call to function expect$#',
	'identifier' => 'argument.templateType',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/LibraryUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 7,
	'path' => __DIR__ . '/tests/Feature/MediaFileIdPersistenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/MediaFileIdPersistenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant PENDING on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/MediaFileIdPersistenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/MediaFileUserAccessTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:artisan\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/Feature/MediaRedownloadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$value on string\\.$#',
	'identifier' => 'property.nonObject',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/MediaRedownloadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TValue in call to function expect$#',
	'identifier' => 'argument.templateType',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/MediaRedownloadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/ProcessingStatusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant FAILED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/ProcessingStatusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant PENDING on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/ProcessingStatusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant PROCESSING on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/ProcessingStatusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/ProcessingStatusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/PublishedAtTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method format\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/PublishedAtTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TValue in call to function expect$#',
	'identifier' => 'argument.templateType',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/PublishedAtTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/RssCdataTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 10,
	'path' => __DIR__ . '/tests/Feature/RssEnclosureUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 9,
	'path' => __DIR__ . '/tests/Feature/RssFeedAccessTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/Feature/RssFeedAccessTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/Feature/RssFeedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/RssMalformedXmlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/SessionInvalidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertGuest\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/SessionInvalidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/Settings/PasswordUpdateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/Feature/Settings/ProfileUpdateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertGuest\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/Settings/ProfileUpdateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 26,
	'path' => __DIR__ . '/tests/Feature/ShareControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 9,
	'path' => __DIR__ . '/tests/Feature/ShareControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$mediaProcessingService\\.$#',
	'identifier' => 'property.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/UnifiedDuplicateProcessorIntegrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$processor\\.$#',
	'identifier' => 'property.notFound',
	'count' => 8,
	'path' => __DIR__ . '/tests/Feature/UnifiedDuplicateProcessorIntegrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 19,
	'path' => __DIR__ . '/tests/Feature/UnifiedDuplicateProcessorIntegrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:mock\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/UnifiedDuplicateProcessorIntegrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 8,
	'path' => __DIR__ . '/tests/Feature/UnifiedSourceProcessorEdgeCasesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/UnifiedSourceProcessorEdgeCasesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/UnifiedSourceProcessorEdgeCasesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$processor\\.$#',
	'identifier' => 'property.notFound',
	'count' => 9,
	'path' => __DIR__ . '/tests/Feature/UnifiedSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 6,
	'path' => __DIR__ . '/tests/Feature/UnifiedSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/UnifiedSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/UploadToFeedWorkflowTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/UrlDuplicateCheckIntegrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 6,
	'path' => __DIR__ . '/tests/Feature/UrlSourceProcessorConsolidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Mockery\\\\ExpectationInterface\\|Mockery\\\\HigherOrderMessage\\:\\:once\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/UrlSourceProcessorConsolidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/UrlSourceProcessorConsolidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$duplicateProcessor of class App\\\\Services\\\\SourceProcessors\\\\UrlSourceProcessor constructor expects App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/UrlSourceProcessorConsolidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/UrlSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Mockery\\\\ExpectationInterface\\|Mockery\\\\HigherOrderMessage\\:\\:once\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/Feature/UrlSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/UrlSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$strategy of class App\\\\Services\\\\SourceProcessors\\\\UrlSourceProcessor constructor expects App\\\\Services\\\\SourceProcessors\\\\SourceStrategyInterface, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/UrlSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$duplicateProcessor of class App\\\\Services\\\\SourceProcessors\\\\UrlSourceProcessor constructor expects App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/UrlSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 6,
	'path' => __DIR__ . '/tests/Feature/UserApprovalTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertAuthenticatedAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/UserApprovalTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/UserApprovalTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertGuest\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/UserApprovalTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:post\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/UserApprovalTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/UserManagementMiddlewareTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/UserManagementMiddlewareTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant PENDING on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/YouTubeDuplicateBypassTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Mockery\\\\ExpectationInterface\\|Mockery\\\\HigherOrderMessage\\:\\:once\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/YouTubeDuplicateBypassTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\$duplicateProcessor of class App\\\\Services\\\\YouTube\\\\YouTubeProcessingService constructor expects App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/YouTubeDuplicateBypassTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant FAILED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Mockery\\\\ExpectationInterface\\|Mockery\\\\HigherOrderMessage\\:\\:once\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$downloader of class App\\\\Services\\\\YouTube\\\\YouTubeProcessingService constructor expects App\\\\Services\\\\YouTube\\\\YouTubeDownloader, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$processingService of method App\\\\Jobs\\\\ProcessYouTubeAudio\\:\\:handle\\(\\) expects App\\\\Services\\\\YouTube\\\\YouTubeProcessingService, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$metadataExtractor of class App\\\\Services\\\\YouTube\\\\YouTubeProcessingService constructor expects App\\\\Services\\\\YouTube\\\\YouTubeMetadataExtractor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$fileProcessor of class App\\\\Services\\\\YouTube\\\\YouTubeProcessingService constructor expects App\\\\Services\\\\YouTube\\\\YouTubeFileProcessor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#4 \\$duplicateProcessor of class App\\\\Services\\\\YouTube\\\\YouTubeProcessingService constructor expects App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Feature/YouTubeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/Feature/YouTubeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Feature/YouTubeWorkflowTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/Feature/YouTubeWorkflowTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Undefined variable\\: \\$this$#',
	'identifier' => 'variable.undefined',
	'count' => 1,
	'path' => __DIR__ . '/tests/Pest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$processor\\.$#',
	'identifier' => 'property.notFound',
	'count' => 12,
	'path' => __DIR__ . '/tests/Unit/UnifiedDuplicateProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 20,
	'path' => __DIR__ . '/tests/Unit/UnifiedDuplicateProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/Unit/UnifiedDuplicateProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/AccessControlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:postJson\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/AccessControlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/AddLibraryItemToFeedsJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseCount\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/AddLibraryItemToFeedsJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/AddLibraryItemToFeedsJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/AddLibraryItemToFeedsJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property Pest\\\\Mixins\\\\Expectation\\<mixed\\>\\:\\:\\$not\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/ApiKeyManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 7,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/ApiKeyManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/ApiKeyManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/ApiKeyManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:withHeader\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/ApiKeyManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:getJson\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:withHeader\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 6,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/FeedControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/FeedControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:postJson\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/FeedControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:withHeader\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 11,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/FeedControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TKey in call to function collect$#',
	'identifier' => 'argument.templateType',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/FeedControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TValue in call to function collect$#',
	'identifier' => 'argument.templateType',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/FeedControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/FeedItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/FeedItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:withHeader\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 10,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/FeedItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/LibraryItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/LibraryItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:withHeader\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 12,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/LibraryItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TKey in call to function collect$#',
	'identifier' => 'argument.templateType',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/LibraryItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TValue in call to function collect$#',
	'identifier' => 'argument.templateType',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/LibraryItemControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:withHeader\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 7,
	'path' => __DIR__ . '/tests/tests/Feature/Api/V1/MediaProcessingControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/tests/Feature/ApiResourceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Auth/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertAuthenticated\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Auth/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertGuest\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/Auth/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Auth/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:post\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/Auth/AuthenticationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/Auth/EmailVerificationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/Auth/PasswordConfirmationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/Auth/PasswordResetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:post\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/Auth/PasswordResetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Auth/RegistrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertGuest\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Auth/RegistrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Auth/RegistrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:post\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Auth/RegistrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/tests/Feature/CleanupDuplicateLibraryItemTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/DashboardTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/DashboardTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$expectedHash\\.$#',
	'identifier' => 'property.notFound',
	'count' => 7,
	'path' => __DIR__ . '/tests/tests/Feature/DuplicateDetectionHashOptimizationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$filePath\\.$#',
	'identifier' => 'property.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/tests/Feature/DuplicateDetectionHashOptimizationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 7,
	'path' => __DIR__ . '/tests/tests/Feature/DuplicateDetectionHashOptimizationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/DuplicateDetectionHashOptimizationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/ExampleTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/ExceptionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/ExceptionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:getJson\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/ExceptionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/FactoryDefinitionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/FeedEditPaginationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 11,
	'path' => __DIR__ . '/tests/tests/Feature/FeedEditTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/FeedEditTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/FeedEditTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/FeedIdsOwnershipTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\Feed\\:\\:\\$items\\.$#',
	'identifier' => 'property.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/FeedItemClearTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/FeedItemClearTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TValue in call to function expect$#',
	'identifier' => 'argument.templateType',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/FeedItemClearTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$feed\\.$#',
	'identifier' => 'property.notFound',
	'count' => 11,
	'path' => __DIR__ . '/tests/tests/Feature/FeedItemSyncTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$items\\.$#',
	'identifier' => 'property.notFound',
	'count' => 21,
	'path' => __DIR__ . '/tests/tests/Feature/FeedItemSyncTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/FeedItemSyncTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 8,
	'path' => __DIR__ . '/tests/tests/Feature/FeedItemSyncTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/FeedItemSyncTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 9,
	'path' => __DIR__ . '/tests/tests/Feature/FeedManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/FeedManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/FeedManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:delete\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/FeedManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/FeedManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:post\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/FeedManagementTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/FeedTokenLengthTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$duplicateProcessor of class App\\\\Services\\\\SourceProcessors\\\\FileUploadProcessor constructor expects App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/FileUploadProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryDuplicateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryDuplicateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseMissing\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryDuplicateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryItemFactoryFeedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryItemFactoryFeedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$factory\\.$#',
	'identifier' => 'property.notFound',
	'count' => 10,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryItemFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 13,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryItemFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryItemFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryItemFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 11,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant FAILED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 12,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TValue in call to function expect$#',
	'identifier' => 'argument.templateType',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/LibraryUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 7,
	'path' => __DIR__ . '/tests/tests/Feature/MediaFileIdPersistenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/MediaFileIdPersistenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant PENDING on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/MediaFileIdPersistenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/MediaFileUserAccessTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:artisan\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/tests/Feature/MediaRedownloadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$value on string\\.$#',
	'identifier' => 'property.nonObject',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/MediaRedownloadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TValue in call to function expect$#',
	'identifier' => 'argument.templateType',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/MediaRedownloadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/ProcessingStatusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant FAILED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/ProcessingStatusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant PENDING on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/ProcessingStatusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant PROCESSING on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/ProcessingStatusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/ProcessingStatusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/PublishedAtTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method format\\(\\) on string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/PublishedAtTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type TValue in call to function expect$#',
	'identifier' => 'argument.templateType',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/PublishedAtTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/RssCdataTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 10,
	'path' => __DIR__ . '/tests/tests/Feature/RssEnclosureUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 9,
	'path' => __DIR__ . '/tests/tests/Feature/RssFeedAccessTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/tests/Feature/RssFeedAccessTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/tests/Feature/RssFeedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/RssMalformedXmlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/SessionInvalidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertGuest\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/SessionInvalidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/Settings/PasswordUpdateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/tests/Feature/Settings/ProfileUpdateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertGuest\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/Settings/ProfileUpdateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 26,
	'path' => __DIR__ . '/tests/tests/Feature/ShareControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 9,
	'path' => __DIR__ . '/tests/tests/Feature/ShareControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$mediaProcessingService\\.$#',
	'identifier' => 'property.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/UnifiedDuplicateProcessorIntegrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$processor\\.$#',
	'identifier' => 'property.notFound',
	'count' => 8,
	'path' => __DIR__ . '/tests/tests/Feature/UnifiedDuplicateProcessorIntegrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 19,
	'path' => __DIR__ . '/tests/tests/Feature/UnifiedDuplicateProcessorIntegrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:mock\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/UnifiedDuplicateProcessorIntegrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 8,
	'path' => __DIR__ . '/tests/tests/Feature/UnifiedSourceProcessorEdgeCasesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/UnifiedSourceProcessorEdgeCasesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/UnifiedSourceProcessorEdgeCasesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$processor\\.$#',
	'identifier' => 'property.notFound',
	'count' => 9,
	'path' => __DIR__ . '/tests/tests/Feature/UnifiedSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 6,
	'path' => __DIR__ . '/tests/tests/Feature/UnifiedSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/UnifiedSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/UploadToFeedWorkflowTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/UrlDuplicateCheckIntegrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 6,
	'path' => __DIR__ . '/tests/tests/Feature/UrlSourceProcessorConsolidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Mockery\\\\ExpectationInterface\\|Mockery\\\\HigherOrderMessage\\:\\:once\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/UrlSourceProcessorConsolidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/UrlSourceProcessorConsolidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$duplicateProcessor of class App\\\\Services\\\\SourceProcessors\\\\UrlSourceProcessor constructor expects App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/UrlSourceProcessorConsolidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/UrlSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Mockery\\\\ExpectationInterface\\|Mockery\\\\HigherOrderMessage\\:\\:once\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/tests/Feature/UrlSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/UrlSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$strategy of class App\\\\Services\\\\SourceProcessors\\\\UrlSourceProcessor constructor expects App\\\\Services\\\\SourceProcessors\\\\SourceStrategyInterface, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/UrlSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$duplicateProcessor of class App\\\\Services\\\\SourceProcessors\\\\UrlSourceProcessor constructor expects App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/UrlSourceProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 6,
	'path' => __DIR__ . '/tests/tests/Feature/UserApprovalTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertAuthenticatedAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/UserApprovalTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/UserApprovalTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertGuest\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/UserApprovalTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:post\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/UserApprovalTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/UserManagementMiddlewareTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:get\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/UserManagementMiddlewareTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant PENDING on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeDuplicateBypassTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Mockery\\\\ExpectationInterface\\|Mockery\\\\HigherOrderMessage\\:\\:once\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeDuplicateBypassTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\$duplicateProcessor of class App\\\\Services\\\\YouTube\\\\YouTubeProcessingService constructor expects App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeDuplicateBypassTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant FAILED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Mockery\\\\ExpectationInterface\\|Mockery\\\\HigherOrderMessage\\:\\:once\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$downloader of class App\\\\Services\\\\YouTube\\\\YouTubeProcessingService constructor expects App\\\\Services\\\\YouTube\\\\YouTubeDownloader, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$processingService of method App\\\\Jobs\\\\ProcessYouTubeAudio\\:\\:handle\\(\\) expects App\\\\Services\\\\YouTube\\\\YouTubeProcessingService, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$metadataExtractor of class App\\\\Services\\\\YouTube\\\\YouTubeProcessingService constructor expects App\\\\Services\\\\YouTube\\\\YouTubeMetadataExtractor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$fileProcessor of class App\\\\Services\\\\YouTube\\\\YouTubeProcessingService constructor expects App\\\\Services\\\\YouTube\\\\YouTubeFileProcessor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#4 \\$duplicateProcessor of class App\\\\Services\\\\YouTube\\\\YouTubeProcessingService constructor expects App\\\\Services\\\\MediaProcessing\\\\UnifiedDuplicateProcessor, Mockery\\\\MockInterface given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeJobTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:actingAs\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\TestCase\\:\\:assertDatabaseHas\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 2,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property App\\\\Models\\\\LibraryItem\\:\\:\\$mediaFile\\.$#',
	'identifier' => 'property.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeWorkflowTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 3,
	'path' => __DIR__ . '/tests/tests/Feature/YouTubeWorkflowTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Undefined variable\\: \\$this$#',
	'identifier' => 'variable.undefined',
	'count' => 1,
	'path' => __DIR__ . '/tests/tests/Pest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$processor\\.$#',
	'identifier' => 'property.notFound',
	'count' => 12,
	'path' => __DIR__ . '/tests/tests/Unit/UnifiedDuplicateProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property PHPUnit\\\\Framework\\\\TestCase\\:\\:\\$user\\.$#',
	'identifier' => 'property.notFound',
	'count' => 20,
	'path' => __DIR__ . '/tests/tests/Unit/UnifiedDuplicateProcessorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant COMPLETED on an unknown class App\\\\ProcessingStatusType\\.$#',
	'identifier' => 'class.notFound',
	'count' => 4,
	'path' => __DIR__ . '/tests/tests/Unit/UnifiedDuplicateProcessorTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
