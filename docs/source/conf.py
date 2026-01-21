# Configuration file for the Sphinx documentation builder.

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

html_theme = "alabaster"

# Pygments styling
pygments_style = "default"

html_theme_options = {
    'github_user': 'VitexSoftware',
    'github_repo': 'MultiFlexi',
    'github_button': True,
    'github_type': 'star',
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


