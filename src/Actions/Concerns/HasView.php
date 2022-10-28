<?php

namespace Webbingbrasil\FilamentNotification\Actions\Concerns;

trait HasView
{
    protected string $view;

    public function customView(string $view): static
    {
        $this->view = $view;

        return $this;
    }

    public function getView(): string
    {
        return $this->view;
    }
}
