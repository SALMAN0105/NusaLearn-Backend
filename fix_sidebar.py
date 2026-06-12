import os
import re

files_to_fix = [
    'resources/views/administrator/conversions/index.blade.php',
    'resources/views/administrator/dashboard.blade.php',
    'resources/views/administrator/languages.blade.php',
    'resources/views/administrator/materials.blade.php',
    'resources/views/administrator/questions.blade.php',
    'resources/views/administrator/regions.blade.php',
    'resources/views/administrator/schools.blade.php',
    'resources/views/administrator/teachers.blade.php',
    'resources/views/administrator/users.blade.php'
]

def fix_file(filepath):
    if not os.path.exists(filepath):
        print(f'File not found: {filepath}')
        return
        
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
        
    nav_match = re.search(r'<nav class="flex-1 overflow-y-auto.*?</nav>', content, flags=re.DOTALL)
    if not nav_match:
        print(f'Nav block not found in {filepath}')
        return
        
    nav_content = nav_match.group(0)
    
    def replace_a(m):
        route = m.group(1)
        if not route.startswith('administrator.'):
            return m.group(0)
            
        active_class = 'nav-active flex items-center gap-3 px-3 py-2.5 border-2 rounded-xl font-bold text-sm transition-all group'
        inactive_class = 'flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-p-lt dark:hover:bg-p-dark/40 hover:text-p dark:hover:text-white border-2 border-transparent hover:border-black dark:hover:border-p-dark rounded-xl transition-all font-semibold text-sm group'
        
        route_pattern = route
        if route.endswith('.index'):
            route_pattern = route.replace('.index', '.*')
            
        new_a = f'<a href="{{{{ route(\'{route}\') }}}}" class="{{{{ request()->routeIs(\'{route_pattern}\') || request()->routeIs(\'{route}\') ? \'{active_class}\' : \'{inactive_class}\' }}}}">'
        return new_a

    new_nav = re.sub(r'<a href="{{\s*route\(\'([^\']+)\'\)\s*}}"[^>]+>', replace_a, nav_content)
    
    new_content = content.replace(nav_content, new_nav)
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print(f'Fixed {filepath}')

for f in files_to_fix:
    fix_file(f)
