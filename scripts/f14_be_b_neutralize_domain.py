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


def remove_schema_callback_block(source: str, table: str, require: str | None = None) -> str:
    pattern = re.compile(
        rf"\n?[ \t]*Schema::(?:create|table)\(\s*['\"]{re.escape(table)}['\"]\s*,\s*"
        rf"function\s*\([^)]*\)\s*\{{.*?\n[ \t]*\}}\);\n?",
        flags=re.S,
    )

    def replace(match: re.Match[str]) -> str:
        block = match.group(0)
        if require is not None and require not in block:
            return block
        return "\n"

    return pattern.sub(replace, source)


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

# Page and test class names are pluralized and are not covered by the model map above.
page_class_map = [
    ('CreateBakeryCategory', 'CreateCategory'),
    ('EditBakeryCategory', 'EditCategory'),
    ('ListBakeryCategories', 'ListCategories'),
    ('CreateBakeryProduct', 'CreateProduct'),
    ('EditBakeryProduct', 'EditProduct'),
    ('ListBakeryProducts', 'ListProducts'),
    ('ManageBakeryContentPages', 'ManageContentPages'),
    ('ManageBakeryFaqs', 'ManageFaqs'),
    ('ManageBakeryGalleryItems', 'ManageGalleryItems'),
    ('ManageBakeryPosts', 'ManagePosts'),
    ('BakeryCatalogApiTest', 'CatalogApiTest'),
    ('BakeryCatalogFilamentTest', 'CatalogFilamentTest'),
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

index_map = [
    ('bakery_products_listing_index', 'products_listing_index'),
    ('bakery_variant_product_name_unique', 'variant_product_name_unique'),
    ('bakery_variants_listing_index', 'variants_listing_index'),
]

# Any migration already creating a neutral destination table belongs to the
# deleted legacy domain. The audited catalog migration still uses bakery_*
# names at this point, so it is preserved and becomes the single owner of the
# neutral tables after the explicit replacements below.
neutral_tables = [new for _, new in table_map]
for migration in (root / 'database/migrations').glob('*.php'):
    try:
        source = migration.read_text()
    except UnicodeDecodeError:
        continue
    creates_neutral_table = any(
        re.search(
            rf"Schema::create\(\s*['\"]{re.escape(table)}['\"]",
            source,
        )
        for table in neutral_tables
    )
    if creates_neutral_table:
        remove(migration)

# Two inherited content phases can both own message_templates. Keep the newest
# migration as the canonical owner and remove only the duplicate callback block
# from older migrations, preserving their other content tables.
message_template_migrations: list[tuple[Path, str]] = []
for migration in sorted((root / 'database/migrations').glob('*.php')):
    source = migration.read_text()
    if (
        re.search(r"Schema::create\(\s*['\"]message_templates['\"]", source)
        or 'message_templates_stage_channel_unique' in source
    ):
        message_template_migrations.append((migration, source))

if len(message_template_migrations) > 1:
    canonical_path, _ = message_template_migrations[-1]
    for migration, source in message_template_migrations[:-1]:
        updated = remove_schema_callback_block(source, 'message_templates')
        updated = remove_schema_callback_block(
            updated,
            'message_templates',
            require='message_templates_stage_channel_unique',
        )
        if updated == source:
            raise RuntimeError(
                f'Could not remove duplicate message_templates schema from {migration}; '
                f'canonical owner is {canonical_path}'
            )
        migration.write_text(updated)

# Update active source contents. Imported reference material remains immutable.
active_roots = [
    'app',
    'bootstrap',
    'config',
    'database',
    'routes',
    'tests',
]

content_replacements = class_map + page_class_map + table_map + index_map
for active_root in active_roots:
    base = root / active_root
    if not base.exists():
        continue
    for path in base.rglob('*'):
        if not path.is_file() or path.name == 'database.sqlite':
            continue
        try:
            text = path.read_text()
        except UnicodeDecodeError:
            continue
        updated = text
        for old, new in content_replacements:
            updated = updated.replace(old, new)
        updated = updated.replace('bakery.catalog.product.', 'catalog.product.')
        updated = updated.replace("['bakery-catalog']", "['catalog']")
        updated = updated.replace("'bakery-catalog'", "'catalog'")
        updated = updated.replace('bakery-', 'catalog-')
        updated = updated.replace('bakery_', 'catalog_')
        updated = updated.replace('bakery.', 'catalog.')
        updated = updated.replace('bakery', 'catalog')
        updated = updated.replace('محصول بیکری', 'محصول')
        updated = updated.replace('محصولات بیکری', 'محصولات')
        updated = updated.replace('فروشگاه وینیمی', 'فروشگاه LBB')
        updated = updated.replace('پیشنهاد وینیمی', 'پیشنهاد LBB')
        updated = updated.replace('وینیمی', 'LBB')
        updated = updated.replace('بیکری', 'فروشگاه')
        if updated != text:
            path.write_text(updated)

# Rename class/resource/migration paths after contents are updated.
path_replacements = content_replacements + [
    ('create_bakery_catalog_tables', 'create_catalog_tables'),
]
for path in sorted(root.rglob('*'), key=lambda item: len(item.parts), reverse=True):
    if not path.exists():
        continue
    normalized = str(path).replace('\\', '/')
    if normalized.startswith('docs/reference/') or normalized.startswith('scripts/reference/'):
        continue
    target_name = path.name
    for old, new in path_replacements:
        target_name = target_name.replace(old, new)
    target_name = target_name.replace('bakery', 'catalog')
    if target_name == path.name:
        continue
    target = path.with_name(target_name)
    if target.exists():
        raise RuntimeError(f'Cannot rename {path} to existing path {target}')
    path.rename(target)

# Generated SQLite state must never be committed or scanned as source identity.
remove(root / 'database/database.sqlite')

# Strengthen the active-domain audit for the neutralized baseline, while
# excluding generated binary state from source scans.
audit_path = root / 'scripts/audit-lbb-foundation.php'
if audit_path.exists():
    audit = audit_path.read_text()
    audit = audit.replace(
        "    'win'.'imi',\n];",
        "    'win'.'imi',\n    'Bak'.'ery',\n    'BAK'.'ERY',\n    'bak'.'ery',\n];",
    )
    audit = audit.replace(
        "        if (! $file->isFile()) {\n            continue;\n        }",
        "        if (! $file->isFile() || $file->getFilename() === 'database.sqlite') {\n            continue;\n        }",
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
- Removed legacy migrations that competed for the neutral catalog table names.
- Assigned duplicated `message_templates` schema ownership to the newest migration.
- Replaced bakery cache namespaces, index names and inherited storefront labels in active runtime code.
- Renamed nested Filament page classes and test files that used plural Bakery names.
- Excluded generated SQLite state from identity auditing and commits.
- Extended the foundation audit to reject active Bakery identity references.

## Remaining in B2.2

- Remove ingredients, allergens, shelf-life and cooling requirements.
- Remove weight-as-variant identity and chilled delivery behavior.
- Re-run migrations, tests and domain audits before closing F14-BE-B.
'''
status_path.write_text(status)
