
BUNDLER=bundler
HOST=127.0.0.1

PLANTUML_SRC=$(shell find -name \*.puml ! -path ./_site/\*)
PLANTUML_DST_SVG=${PLANTUML_SRC:%.puml=%.svg}

.PHONY: all
all: help

.PHONY: help watch build install build-uml-svg

help:
	@echo "Make tragets supported:"
	@echo "  help    - Show this help screen"
	@echo "  build   - Build the HTML pages locally"
	@echo "  watch   - Keep building the pages locally and serve on local port for writing"
	@echo "  install - Install the ruby packages using bundler"

build:
	${BUNDLER} exec jekyll build

watch:
	${BUNDLER} exec jekyll serve --host ${HOST}

install:
	${BUNDLER} install

build-uml-svg:
ifeq ($(strip ${PLANTUML_SRC}),)
	@echo 'No plantuml files were found. Skipping.'
else
	${MAKE} ${PLANTUML_DST_SVG}
endif

%.svg: %.puml
	plantuml -tsvg $<

%.png: %.puml
	plantuml -tpng $<
