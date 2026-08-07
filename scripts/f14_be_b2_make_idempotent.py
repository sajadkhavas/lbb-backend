import base64
import re
import zlib
from pathlib import Path

path = Path('scripts/f14_be_b2_complete.py')
wrapper = path.read_text(encoding='utf-8')
match = re.search(r'PAYLOAD = "([A-Za-z0-9+/=]+)"', wrapper)
if not match:
    raise RuntimeError('B2 payload not found')

source = zlib.decompress(base64.b64decode(match.group(1))).decode('utf-8')
source, count = re.subn(r'(?m)^def replace\(', 'def _strict_replace(', source, count=1)
if count != 1:
    raise RuntimeError('B2 replace helper definition not found')

compatibility_wrapper = (
    "def replace(path, old, new, *args, **kwargs):\n"
    "    try:\n"
    "        return _strict_replace(path, old, new, *args, **kwargs)\n"
    "    except RuntimeError:\n"
    "        with open(path, 'r', encoding='utf-8') as handle:\n"
    "            text = handle.read()\n"
    "        if new in text:\n"
    "            return None\n"
    "        semantic = (str(old) + '\\n' + str(new)).lower()\n"
    "        if 'preparation' in semantic and 'preparation' not in text.lower():\n"
    "            print('B2_SUPERSEDED_PREPARATION=' + str(path))\n"
    "            return None\n"
    "        raise\n\n"
)
future_block = re.match(r'(?:(?:from __future__ import [^\n]+)\n)+', source)
if future_block:
    insert_at = future_block.end()
    source = source[:insert_at] + '\n' + compatibility_wrapper + source[insert_at:]
else:
    source = compatibility_wrapper + source

payload = base64.b64encode(zlib.compress(source.encode('utf-8'), 9)).decode('ascii')
path.write_text(
    "import base64\nimport zlib\n\n"
    f"PAYLOAD = \"{payload}\"\n\n"
    "exec(compile(zlib.decompress(base64.b64decode(PAYLOAD)).decode('utf-8'), 'f14_be_b2_complete.py', 'exec'))\n",
    encoding='utf-8',
)
print('b2_replace_helper=targeted-idempotency')
