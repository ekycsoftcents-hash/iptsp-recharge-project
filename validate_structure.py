from pathlib import Path

root = Path(__file__).parent
php_files = sorted(root.rglob('*.php'))
errors = []
for path in php_files:
    text = path.read_text(encoding='utf-8')
    if text.count('{') != text.count('}'):
        errors.append(f'{path}: unbalanced braces')
    if text.count('(') != text.count(')'):
        errors.append(f'{path}: unbalanced parentheses')
    if not text.lstrip().startswith('<?php') and path.suffix == '.php':
        errors.append(f'{path}: missing PHP opening tag')

required = [
    root / 'database/migrations/2026_01_01_000001_create_tenants_table.php',
    root / 'database/migrations/2026_01_01_000004_create_subscriptions_table.php',
    root / 'database/migrations/2026_01_01_000008_create_payments_recharges_logs_tables.php',
    root / 'app/Services/Payments/PipraPayGateway.php',
]
for path in required:
    if not path.exists():
        errors.append(f'missing required file: {path}')

if errors:
    print('\n'.join(errors))
    raise SystemExit(1)

print(f'Validated {len(php_files)} PHP files and required architecture files.')
