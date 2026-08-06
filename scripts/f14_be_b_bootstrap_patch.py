from pathlib import Path
import re

root = Path('.')

provider_path = root / 'app/Providers/AppServiceProvider.php'
provider = provider_path.read_text()
provider = provider.replace('use App\\Models\\Product;\n', '')
provider = provider.replace('use App\\Observers\\ProductObserver;\n', '')
provider = provider.replace('        Product::observe(ProductObserver::class);\n\n', '')
provider = re.sub(
    r"\n        \$phase18 = config\('phase18', \[\]\);\n"
    r"        if \(is_array\(\$phase18\).*?\n        \}\n",
    '\n',
    provider,
    flags=re.S,
)
provider_path.write_text(provider)

for path in [
    root / 'app/Observers/ProductObserver.php',
    root / 'config/phase18.php',
]:
    if path.exists():
        path.unlink()
