<template>
    <picture
        class="lazy-img"
        style="min-height: 1rem"
        :data-alt="alt"
        :style="style"
    >
        <span v-if="isPreviewLoading" class="loading-indicator icon-loading" />
        <img
            class="low-resolution blurred"
            :class="{ 'preview-loaded': !isPreviewLoading }"
            :width="width ? width + 'px' : ''"
            :height="height ? height + 'px' : ''"
        />
        <img
            class="full-resolution"
            :class="{ 'image-loaded': !isLoading }"
            :width="width ? width + 'px' : ''"
            :height="height ? height + 'px' : ''"
        />
    </picture>
</template>

<script>
import lozad from "lozad"

export default {
    name: "LazyPicture",
    props: {
        alt: {
            type: String,
            default: null,
        },
        blurredPreviewSrc: {
            type: String,
            default: null,
        },
        lazySrc: {
            type: String,
            default: null,
        },
        width: {
            type: Number,
            default: null,
        },
        height: {
            type: Number,
            default: null,
        },
    },
    data() {
        return {
            isPreviewLoading: {
                type: Boolean,
                default: true,
            },
            isLoading: {
                type: Boolean,
                default: true,
            },
            isBlurred: {
                type: Boolean,
                default: true,
            },
        }
    },
    computed: {
        style() {
            const style = {}
            if (this.width) {
                style.width = `${this.width}px`
            }
            if (this.isLoading && this.height && !this.blurredPreviewSrc) {
                style.height = 0
                style.paddingTop = `${this.height}px`
            }
            return style
        },
    },
    mounted() {
        const $this = this

        // init lozad
        const observer = lozad(this.$el, {
            enableAutoReload: true,
            load(el) {
                const imgPlaceholder = el.querySelector(".low-resolution")
                const img = el.querySelector(".full-resolution")

                // callback for fully-loaded image event
                const onThumbnailFullyLoaded = () => {
                    img.removeEventListener("load", onThumbnailFullyLoaded)
                    el.removeChild(imgPlaceholder)
                    $this.isLoading = false
                }

                // callback for preview-image-loaded event
                const onThumbnailPreviewLoaded = () => {
                    imgPlaceholder.removeEventListener(
                        "load",
                        onThumbnailPreviewLoaded
                    )
                    img.addEventListener("load", onThumbnailFullyLoaded)
                    $this.$once("hook:destroyed", () => {
                        img.removeEventListener("load", onThumbnailFullyLoaded)
                    })
                    img.src = $this.lazySrc
                    $this.isPreviewLoading = false
                }

                imgPlaceholder.addEventListener(
                    "load",
                    onThumbnailPreviewLoaded
                )
                $this.$once("hook:destroyed", () => {
                    imgPlaceholder.removeEventListener(
                        "load",
                        onThumbnailPreviewLoaded
                    )
                })

                imgPlaceholder.src = $this.blurredPreviewSrc
            },
        })
        observer.observe()
    },
}
</script>

<style scoped>
.lazy-img {
    overflow: hidden;
    max-width: 100%;
    max-height: 100%;
    vertical-align: middle;
}

picture .loading-indicator {
    display: contents;
    align-content: center;
}

picture .blurred {
    filter: blur(0.5rem);
}

picture .low-resolution.preview-loaded {
    display: inline;
    animation: fadeIn 1s linear 0s;
}

picture .full-resolution {
    display: none;
}

picture .full-resolution.image-loaded {
    display: inline;
    animation: unblur 1s linear 0s;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes unblur {
    from {
        filter: blur(0.5rem);
    }
    to {
        filter: blur(0);
    }
}
</style>
