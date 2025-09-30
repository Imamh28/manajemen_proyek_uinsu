<?php
function role_dir(string $roleId): string
{
    return match ($roleId) {
        'RL001' => 'admin',
        'RL002' => 'projek_manajer',
        'RL003' => 'mandor',
        default => 'mandor', // fallback aman
    };
}
