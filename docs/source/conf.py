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

# Pygments styling
# Use a readable style for normal (light) mode and explicit dark style for
# systems/browsers that prefer dark color schemes. The Shibuya theme does not
# understand a html_theme_options key named 'pygments_style_dark'; instead Sphinx
# expects a top-level "pygments_dark_style" variable. Previously we attempted
# to stuff this into html_theme_options which produced a warning:
#   unsupported theme option 'pygments_style_dark'
# Defining pygments_dark_style at top level removes that warning.
pygments_style = "dracula"          # Style for light mode (already dark-friendly)
pygments_dark_style = "native"      # Explicit dark-mode style (fallback if theme switches)

# Keep html_theme_options defined (extendable without dark style misuse)
try:  # pragma: no cover - defensive
    html_theme_options = html_theme_options  # type: ignore  # preserve if predefined
except NameError:  # noqa: F821
    html_theme_options = {}

# -- Options for EPUB output
epub_show_urls = 'footnote'

html_logo = "../../src/images/project-logo.svg"


