# vim: set tabstop=8 softtabstop=8 noexpandtab:
.PHONY: help
help: ## 📋 Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: docs
docs: ## 📚 Build Sphinx HTML documentation
	@if [ -x .venv/bin/python ]; then \
		.venv/bin/python -m sphinx -b html docs/source docs/_build/html; \
	else \
		python -m sphinx -b html docs/source docs/_build/html; \
	fi

debs: ## 📦 Build debian packages
	debuild -i -us -uc -b

reset: ## 🔄 Reset local branch to origin
	git fetch origin
	git reset --hard origin/$(git rev-parse --abbrev-ref HEAD)

