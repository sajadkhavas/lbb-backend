import json
from pathlib import Path

composer_path = Path('composer.json')
composer = json.loads(composer_path.read_text())

laravel_extra = composer.setdefault('extra', {}).setdefault('laravel', {})
dont_discover = laravel_extra.setdefault('dont-discover', [])
for package in [
    'z3d0x/filament-fabricator',
    'pboivin/filament-peek',
]:
    if package not in dont_discover:
        dont_discover.append(package)

composer_path.write_text(json.dumps(composer, ensure_ascii=False, indent=4) + '\n')
