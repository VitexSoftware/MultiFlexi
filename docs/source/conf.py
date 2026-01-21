# Configuration file for the Sphinx documentation builder.

# Monkey patch shibuya to use available pygments styles
import shibuya._pygments
# Override shibuya's default styles with available ones
shibuya._pygments.ShibuyaPygmentsBridge.light_style_name = "default"
shibuya._pygments.ShibuyaPygmentsBridge.dark_style_name = "github-dark"

# -- Project information

project = 'MultiFlexi'
copyright = '2024-2025, VitexSoftware'
author = 'Vitex'

release = '1.29.0'
version = '1.29.0'

# -- General configuration

extensions = [
    'sphinx.ext.duration',
    'sphinx.ext.doctest',
    'sphinx.ext.autodoc',
    'sphinx.ext.autosummary',
    'sphinx.ext.intersphinx',
    'sphinx.ext.todo',
    'sphinx.ext.ifconfig',
    'sphinx.ext.viewcode',
]

# Intersphinx configuration for linking to external docs
intersphinx_mapping = {
    'python': ('https://docs.python.org/3/', None),
    'sphinx': ('https://www.sphinx-doc.org/en/master/', None),
    'php': ('https://www.php.net/manual/en/', None),
}
intersphinx_disabled_domains = ['std']

# TODO extension configuration
todo_include_todos = True
todo_emit_warnings = True

templates_path = ['_templates']

# Source file suffixes
source_suffix = '.rst'

# The master toctree document
master_doc = 'index'

# Exclude patterns for build
exclude_patterns = []

# -- Options for HTML output

html_theme = "shibuya"

# Pygments styling - use valid styles for light and dark modes
pygments_style = "default"
pygments_dark_style = "github-dark"  # Use available github-dark instead of github-dark-default

html_theme_options = {
    "github_url": "https://github.com/VitexSoftware/MultiFlexi",
    "dark_code": True,
    "nav_links": [
        {
            "title": "GitHub",
            "url": "https://github.com/VitexSoftware/MultiFlexi",
        },
    ],
}

# Show source links
html_show_sourcelink = False
html_show_sphinx = True
html_show_copyright = True

# -- Options for EPUB output
epub_show_urls = 'footnote'

html_logo = "../../src/images/project-logo.svg"

# HTML context for version info
html_context = {
    'display_github': True,
    'github_user': 'VitexSoftware',
    'github_repo': 'MultiFlexi',
    'github_version': 'main',
    'conf_py_path': '/docs/source/',
}


