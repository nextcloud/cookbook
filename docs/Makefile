
BUNDLER=bundler

.PHONY: all
all: help

.PHONY: help watch build install

help:
	@echo "Make tragets supported:"
	@echo "  help    - Show this help screen"
	@echo "  build   - Build the HTML pages locally"
	@echo "  watch   - Keep building the pages locally and serve on local port for writing"
	@echo "  install - Install the ruby packages using bundler"

build:
	${BUNDLER} exec jekyll build

watch:
	${BUNDLER} exec jekyll serve

install:
	${BUNDLER} install

