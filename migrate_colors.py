import os
import re

def migrate_colors(directory):
    # Regex to match .withValues(alpha: 0.X) or .withValues(alpha: 0.X, ...)
    # Supporting older Flutter versions that don't have .withValues
    pattern = re.compile(r'\.withValues\(alpha:\s*([\d\.]+)\)')
    
    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.endswith('.dart'):
                path = os.path.join(root, file)
                with open(path, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                new_content = pattern.sub(r'.withOpacity(\1)', content)
                
                if new_content != content:
                    with open(path, 'w', encoding='utf-8') as f:
                        f.write(new_content)
                    print(f"Migrated: {path}")

if __name__ == "__main__":
    migrate_colors('lib')
