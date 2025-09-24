# Configuration file for the Sphinx documentation builder.

# -- Project information

project = 'MultiFlexi'
copyright = '2024, VitexSoftware'
author = 'Vitex'

release = '0.1'
version = '0.1.0'

# -- General configuration

extensions = [
    'sphinx.ext.duration',
    'sphinx.ext.doctest',
    'sphinx.ext.autodoc',
    'sphinx.ext.autosummary',
    'sphinx.ext.intersphinx',
]

intersphinx_mapping = {
    'python': ('https://docs.python.org/3/', None),
    'sphinx': ('https://www.sphinx-doc.org/en/master/', None),
}
intersphinx_disabled_domains = ['std']

templates_path = ['_templates']

# -- Options for HTML output

html_theme = "shibuya"

# Workaround for shibuya theme missing pygments_style_dark attribute on RTD build
# Define explicit pygments styles to avoid AttributeError in shibuya._patch._fix_builder_highlighter
pygments_style = "dracula"  # Light mode style
try:
    # Some newer Sphinx versions support this option directly
    html_theme_options = html_theme_options  # type: ignore  # Preserve existing if defined elsewhere
except NameError:  # noqa: F821
    html_theme_options = {}

# Provide a dark style key to satisfy theme expectations if accessed
html_theme_options.setdefault('pygments_style_dark', 'native')

# -- Options for EPUB output
epub_show_urls = 'footnote'

html_logo = "../../src/images/project-logo.svg"


