<?php

use PHPUnit\Framework\Assert;

test('create feed form defaults and submits append feed type', function () {
    $form = file_get_contents(dirname(__DIR__, 2).'/resources/js/components/create-feed-form.tsx');

    Assert::assertStringContainsString("feed_type: 'static' | 'append';", $form);
    Assert::assertStringContainsString("feed_type: 'append',", $form);
});
