<?php

namespace Webbingbrasil\FilamentNotification\Contracts;

interface FeedNotificationContract
{
    public function getFeedNotification(): \Illuminate\Support\Collection;
}