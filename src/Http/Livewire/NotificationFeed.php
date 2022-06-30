<?php

namespace Webbingbrasil\FilamentNotification\Http\Livewire;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Webbingbrasil\FilamentNotification\Actions\ButtonAction;
use Webbingbrasil\FilamentNotification\Concerns\HasActions;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;

class NotificationFeed extends Component implements Forms\Contracts\HasForms
{
    use WithPagination;
    use HasActions;
    use Forms\Concerns\InteractsWithForms;

    protected Collection $feed;
    public int $totalUnread = 0;

    public function boot()
    {
        $this->refresh();
    }

    public function refresh()
    {
        $this->hydrateNotificationFeed();
        $this->prepareActions();
    }

    public function hydrateNotificationFeed()
    {
        $notifications = Auth::user()->getFeedNotification();

        $this->feed = $notifications;
        $this->totalUnread = $notifications->count();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        $this->refresh();
    }

    protected function getForms(): array
    {
        return [
            'mountedNotificationActionForm' => $this->makeForm()
                ->schema(($action = $this->getMountedNotificationAction()) ? $action->getFormSchema() : [])
                ->model($this->getMountedNotificationActionRecord() ?? DatabaseNotification::class)
                ->statePath('mountedNotificationActionData'),
        ];
    }

    protected function resolveNotificationRecord(?string $key): ?Model
    {
        return DatabaseNotification::find($key);
    }

    public function markAsRead(DatabaseNotification $notification)
    {
        $notification->markAsRead();
        $this->refresh();
    }

    protected function prepareActions(): void
    {
        foreach ($this->feed as $notification) {
            if (isset($this->cachedNotificationActions[$notification->type])) {
                continue;
            }
            $actions = [];
            if(method_exists($notification->type, 'notificationFeedActions')) {
                $actions = call_user_func([$notification->type, 'notificationFeedActions']);
            }
            $this->cacheNotificationActions($notification->type, $actions);
        }
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('filament-notification::feed', [
            'notifications' => $this->feed
        ]);
    }
}
