from __future__ import annotations

import re
import shutil
from pathlib import Path

root = Path('.')


def remove(path: Path) -> None:
    if path.is_dir():
        shutil.rmtree(path)
    elif path.exists() or path.is_symlink():
        path.unlink()


# These API resources belonged only to the deleted ToolMaster controllers/models.
for legacy_resource in [
    'app/Http/Resources/BlogPostResource.php',
    'app/Http/Resources/BrandResource.php',
    'app/Http/Resources/CategoryResource.php',
    'app/Http/Resources/ProductResource.php',
    'app/Http/Resources/SubcategoryResource.php',
]:
    remove(root / legacy_resource)

# City landing pages were a Winimi-specific SEO domain and have no verified LBB requirement.
for path in sorted(root.rglob('*'), key=lambda item: len(item.parts), reverse=True):
    normalized = str(path).replace('\\', '/')
    if (
        'BakeryCityPage' in normalized
        or 'bakery_city_pages' in normalized
        or normalized.endswith('/CityPage.php')
        or '/CityPageResource' in normalized
    ):
        remove(path)

controller_path = root / 'app/Http/Controllers/Api/StoreContentController.php'
if controller_path.exists():
    controller = controller_path.read_text()
    controller = controller.replace('use App\\Models\\BakeryCityPage;\n', '')
    controller = re.sub(
        r"\n    public function city\(string \$slug\): JsonResponse\n    \{.*?\n    \}\n\n    private function postSummary",
        '\n    private function postSummary',
        controller,
        flags=re.S,
    )
    controller_path.write_text(controller)

class_map = [
    ('BakeryProductVariant', 'ProductVariant'),
    ('BakeryVariantResource', 'ProductVariantResource'),
    ('BakeryGalleryItem', 'GalleryItem'),
    ('BakeryContentPage', 'ContentPage'),
    ('BakeryCategory', 'Category'),
    ('BakeryProduct', 'Product'),
    ('BakeryFaq', 'Faq'),
    ('BakeryPost', 'Post'),
]

table_map = [
    ('bakery_product_variants', 'product_variants'),
    ('bakery_gallery_items', 'gallery_items'),
    ('bakery_content_pages', 'content_pages'),
    ('bakery_categories', 'categories'),
    ('bakery_products', 'products'),
    ('bakery_faqs', 'faqs'),
    ('bakery_posts', 'posts'),
]

# Update active source contents. Imported reference material remains immutable.
active_roots = [
    'app',
    'bootstrap',
    'config',
    'database',
    'routes',
    'tests',
]

for active_root in active_roots:
    base = root / active_root
    if not base.exists():
        continue
    for path in base.rglob('*'):
        if not path.is_file():
            continue
        try:
            text = path.read_text()
        except UnicodeDecodeError:
            continue
        updated = text
        for old, new in class_map:
            updated = updated.replace(old, new)
        for old, new in table_map:
            updated = updated.replace(old, new)
        updated = updated.replace('bakery.catalog.product.', 'catalog.product.')
        updated = updated.replace("['bakery-catalog']", "['catalog']")
        updated = updated.replace("'bakery-catalog'", "'catalog'")
        updated = updated.replace('محصول بیکری', 'محصول')
        updated = updated.replace('بیکری', 'فروشگاه')
        if updated != text:
            path.write_text(updated)

# Rename class/resource/migration paths after contents are updated.
path_replacements = class_map + table_map
for path in sorted(root.rglob('*'), key=lambda item: len(item.parts), reverse=True):
    if not path.exists():
        continue
    normalized = str(path).replace('\\', '/')
    if normalized.startswith('docs/reference/') or normalized.startswith('scripts/reference/'):
        continue
    target_name = path.name
    for old, new in path_replacements:
        target_name = target_name.replace(old, new)
    if target_name == path.name:
        continue
    target = path.with_name(target_name)
    if target.exists():
        raise RuntimeError(f'Cannot rename {path} to existing path {target}')
    path.rename(target)

# Strengthen the active-domain audit for the neutralized baseline.
audit_path = root / 'scripts/audit-lbb-foundation.php'
if audit_path.exists():
    audit = audit_path.read_text()
    audit = audit.replace(
        "    'win'.'imi',\n];",
        "    'win'.'imi',\n    'Bak'.'ery',\n    'BAK'.'ERY',\n    'bak'.'ery',\n];",
    )
    audit_path.write_text(audit)

status_path = root / 'docs/F14_BE_B_CLEANUP_STATUS.md'
status = status_path.read_text() if status_path.exists() else '# F14-BE-B Cleanup Status\n'
status += '''

## Completed in B2.1

- Renamed active catalog/content models, resources, policies, factories and tests from bakery-specific names to neutral commerce names.
- Renamed active database tables from `bakery_*` to neutral table names.
- Removed the city SEO page model, endpoint, resource and migration.
- Removed stale generic API resources left by the deleted ToolMaster controllers.
- Replaced bakery cache namespaces and Persian bakery labels in active runtime code.
- Extended the foundation audit to reject active Bakery identity references.

## Remaining in B2.2

- Remove ingredients, allergens, shelf-life and cooling requirements.
- Remove weight-as-variant identity and chilled delivery behavior.
- Re-run migrations, tests and domain audits before closing F14-BE-B.
'''
status_path.write_text(status)
