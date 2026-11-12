<?php

namespace App\Livewire;

use Livewire\Component;

class MobileNavigation extends Component
{
    public ?string $activeMenu = null;

    public function toggleMenu(string $menu): void
    {
        if (! in_array($menu, ['project', 'perangkat', 'case', 'more'], true)) {
            return;
        }

        $this->activeMenu = $this->activeMenu === $menu ? null : $menu;
    }

    public function closeMenu(): void
    {
        $this->activeMenu = null;
    }

    public function render()
    {
        return view('livewire.mobile-navigation');
    }
}
