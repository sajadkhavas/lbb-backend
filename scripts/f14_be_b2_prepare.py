from pathlib import Path

path = Path('config/lbb.php')
text = path.read_text(encoding='utf-8')
old = "        'catalog' => ['status' => 'migration-in-progress'],"
new = "        'catalog' => ['status' => 'migration-in-progress', 'source' => 'legacy-domain-neutralized'],"
if old not in text:
    raise RuntimeError('F14-BE-B2 prepare could not locate catalog contract')
text = text.replace(old, new, 1)
marker = "    'contracts' => ["
if marker not in text:
    raise RuntimeError('F14-BE-B2 prepare could not locate contracts block')
text = text.replace(marker, "    'stateful_domains' => 'lbb.ir,www.lbb.ir',\n" + marker, 1)
path.write_text(text, encoding='utf-8')
print('f14_be_b2_prepare=complete')
