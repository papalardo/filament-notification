<div
    x-data="{
    isOpen: false,
}"
    class="relative"
>
    <span wire:poll.10000ms="refresh"></span>
    <button
        x-on:click="isOpen = ! isOpen"
        @class([
            'flex items-center justify-center w-10 h-10 '
        ])
    >
        <x-heroicon-o-bell class="h-5 -mr-1 align-text-top  @if($this->totalUnread > 0) animate-swing @endif origin-top" />
        @if($this->totalUnread > 0)
            <sup class="inline-flex items-center justify-center p-1 text-xs leading-none text-white bg-danger-600 rounded-full w-5 h-5">
                {{ $this->totalUnread }}
            </sup>
        @endif
    </button>

    <div
        x-show="isOpen"
        x-on:click.away="isOpen = false"
        x-transition:enter="transition"
        x-transition:enter-start="-translate-y-1 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="-translate-y-1 opacity-0"
        x-cloak
        @class([
            'absolute z-10 right-0 rtl:right-auto rtl:left-0 scr shadow-xl bg-white top-full max-h-[70vh]',
            'w-[20rem] overflow-y-scroll rounded-lg scrollbar-hide',
            'dark:border-gray-600 dark:bg-gray-700' => config('filament.dark_mode'),
        ])
    >
        @if($notifications->count() > 0)
            <div class="px-2 pt-2">
                <x-filament-notification::button
                    wire:click="markAllAsRead"
                    :color="config('filament-notification.buttons.markAllRead.color', 'primary')"
                    :outlined="config('filament-notification.buttons.markAllRead.outlined', false)"
                    :icon="config('filament-notification.buttons.markAllRead.icon', 'filament-notification::icon-check-all')"
                    :size="config('filament-notification.buttons.markAllRead.size', 'sm')"
                    class="w-full"
                >
                    {{ trans('filament-notification::component.buttons.markAllRead') }}
                </x-filament-notification::button>
            </div>
            <ul @class([
            'py-1 px-2 divide-y divide-gray-200 rounded-xl',
            'dark:border-gray-600 dark:bg-gray-700' => config('filament.dark_mode'),
        ])>
            @foreach($notifications as $notification)
                <li @class([
                    'relative py-2 px-1 space-y-2',
                    $notification->read() ? 'opacity-50' : '',
                ])>
                    <div class="flex justify-between">
                        <div class="flex items-center w-full text-sm font-medium">
                            @if($icon = match (Arr::get($notification->data, 'level')) {
                            'info' => 'heroicon-o-information-circle',
                            'warning' => 'heroicon-o-exclamation-circle',
                            'error' => 'heroicon-o-x-circle',
                            'success' => 'heroicon-o-check-circle',
                            default => null,
                        })
                                <x-dynamic-component
                                    :component="$icon"
                                    class="mr-2 -ml-1 rtl:ml-2 rtl:-mr-1 w-6 h-6 text-gray-500"
                                ></x-dynamic-component>
                            @endif
                            {{ Arr::get($notification->data, 'title') }}
                        </div>
                        <x-heroicon-o-x
                            class="w-4 h-4 cursor-pointer"
                            wire:click="markAsRead('{{ $notification->id }}')"
                            wire:loading.hide="markAsRead('{{ $notification->id }}')"
                        >
                        </x-heroicon-o-x>
                    </div>
                    <small class="text-sm font-normal">{{ Arr::get($notification->data, 'message') }}</small>

                    @if($actions = $this->getCachedNotificationActions($notification->type))
                        <x-filament-notification::actions
                            :actions="$this->getCachedNotificationActions($notification->type)"
                            :record="$notification"
                            class=""
                        />
                    @endif
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-center w-full py-3 text-sm font-medium">
                Sem notificações
            </div>
        @endif
    </div>

    <form wire:submit.prevent="callMountedNotificationAction">
        @php
            $action = $this->getMountedNotificationAction();
        @endphp

        <x-tables::modal :id="\Illuminate\Support\Str::of(static::class)->replace('\\', '\\\\') . '-action'" :width="$action?->getModalWidth()" display-classes="block">
            @if ($action)
                @if ($action->isModalCentered())
                    <x-slot name="heading">
                        {{ $action->getModalHeading() }}
                    </x-slot>

                    @if ($subheading = $action->getModalSubheading())
                        <x-slot name="subheading">
                            {{ $subheading }}
                        </x-slot>
                    @endif
                @else
                    <x-slot name="header">
                        <x-tables::modal.heading>
                            {{ $action->getModalHeading() }}
                        </x-tables::modal.heading>
                    </x-slot>
                @endif

                @if ($action->hasFormSchema())
                    {{ $this->getMountedNotificationActionForm() }}
                @endif

                <x-slot name="footer">
                    <x-tables::modal.actions :full-width="$action->isModalCentered()">
                        @foreach ($action->getModalActions() as $modalAction)
                            {{ $modalAction }}
                        @endforeach
                    </x-tables::modal.actions>
                </x-slot>
            @endif
        </x-tables::modal>
    </form>
</div>
