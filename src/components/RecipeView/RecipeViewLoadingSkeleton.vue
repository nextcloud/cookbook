<template>
    <div :class="delayedDisplay.isVisible.value ? '' : 'hidden'">
        <div class="relative w-full">
            <div class="w-full md:max-w-full self-md-stretch relative">
                <div class="image-container">
                    <LoadingSkeleton
                        :type="SkeletonType.Image"
                        class="w-full"
                        style="height: 100%"
                    ></LoadingSkeleton>
                </div>
            </div>
            <div class="title-container absolute">
                <LoadingSkeleton :type="SkeletonType.Heading" />
            </div>
        </div>

        <div class="header w-full relative">
            <div class="meta w-full md:max-w-full">
                <div class="details">
                    <!-- Keywords -->
                    <div class="section d-flex">
                        <LoadingSkeleton :type="SkeletonType.Chip" />
                        <LoadingSkeleton :type="SkeletonType.Chip" />
                        <LoadingSkeleton :type="SkeletonType.Chip" />
                    </div>

                    <!-- Description -->
                    <LoadingSkeleton :type="SkeletonType.Paragraph" />

                    <!-- Source -->
                    <p class="section">
                        <LoadingSkeleton :type="SkeletonType.Text" />
                    </p>
                </div>
                <div
                    class="flex flex-row flex-wrap gap-4 gap-x-lg-16 gap-y-lg-8 justify-center align-items-center mt-6"
                >
                    <!-- Yield -->
                    <div class="w-24">
                        <LoadingSkeleton
                            :type="SkeletonType.Heading"
                            class="mb-2"
                        />
                        <div class="d-flex flex-row">
                            <LoadingSkeleton
                                :type="SkeletonType.Image"
                                width="48px"
                                height="48px"
                            />
                        </div>
                    </div>

                    <!-- Timers -->
                    <div class="w-24">
                        <LoadingSkeleton
                            :type="SkeletonType.Heading"
                            class="mb-2"
                        />
                        <LoadingSkeleton :type="SkeletonType.Text" />
                    </div>
                    <div class="w-24">
                        <LoadingSkeleton
                            :type="SkeletonType.Heading"
                            class="mb-2"
                        />
                        <LoadingSkeleton :type="SkeletonType.Text" />
                    </div>
                    <div class="w-24">
                        <LoadingSkeleton
                            :type="SkeletonType.Heading"
                            class="mb-2"
                        />
                        <LoadingSkeleton :type="SkeletonType.Text" />
                    </div>
                </div>
            </div>

            <div class="supplies w-full md:max-w-full">
                <!-- Ingredients -->
                <section class="ingredients">
                    <LoadingSkeleton
                        :type="SkeletonType.Heading"
                        class="w-3/4 max-w-64"
                    />
                    <LoadingSkeleton :type="SkeletonType.ListItem" />
                    <LoadingSkeleton :type="SkeletonType.ListItem" />
                    <LoadingSkeleton :type="SkeletonType.ListItem" />
                    <LoadingSkeleton :type="SkeletonType.ListItem" />
                </section>

                <!-- Tools -->
                <section class="tools">
                    <LoadingSkeleton
                        :type="SkeletonType.Heading"
                        class="w-2/3 max-w-52"
                    />
                    <ul class="mb-4 pt-2.5 pl-2 d-flex flex-row">
                        <LoadingSkeleton :type="SkeletonType.Chip" />
                        <LoadingSkeleton :type="SkeletonType.Chip" />
                        <LoadingSkeleton :type="SkeletonType.Chip" />
                    </ul>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import LoadingSkeleton from 'cookbook/components/Utilities/LoadingSkeleton/LoadingSkeleton.vue';
import SkeletonType from 'cookbook/components/Utilities/LoadingSkeleton/SkeletonType';
import useDelayedDisplay from '../../composables/useDelayedDisplay';

const props = defineProps({
    /** Delay in milliseconds before the component is displayed */
    delay: {
        type: Number,
        default: 0,
    },
});

const delayedDisplay = useDelayedDisplay(props.delay);
</script>

<script>
export default {
    name: 'RecipeViewLoadingSkeleton',
};
</script>

<style scoped>
.title-container {
    bottom: 15px;
    left: 0;
    display: flex;
    width: 60%;
    padding: 0.25rem 0.6rem 0.3rem;
}

.image-container {
    position: relative;
    overflow: hidden;
    width: 100%;
    height: 33vh;
    margin: 0;

    img.inline-img {
        position: absolute;
        top: 50%;
        max-width: 100%;
        cursor: pointer;
        transform: translateY(-50%);
    }

    .image-previews {
        position: absolute;
        right: 15px;
        bottom: 15px;
        display: flex;
        gap: 10px;

        /* Move images closer to the edge if last one is partially faded out */
        &:has(:last-child.faded) {
            margin-right: -8px;

            /* Fade last image if there are more images than can be shown */
            img.preview-image.faded {
                mask-image: linear-gradient(
                    to right,
                    rgba(0, 0, 0, 1),
                    rgba(0, 0, 0, 1) 25%,
                    rgba(0, 0, 0, 0.5) 50%,
                    rgba(0, 0, 0, 0) 75%
                );
            }
        }

        img.preview-image {
            width: 40px;
            height: 40px;
            cursor: pointer;
            object-fit: cover;
        }
    }
}

.header {
    display: grid;

    padding: 1rem;
    gap: 0.75rem;
    grid-template-areas:
        'meta'
        'supplies';

    grid-template-columns: 100%;
    grid-template-rows: auto;

    @media (min-width: 768px) {
        grid-template-areas: 'supplies meta';
        grid-template-columns: minmax(200px, 1fr) minmax(300px, 2fr);
    }

    .meta {
        padding: 0 1em;

        grid-area: meta;

        .details {
            .section {
                padding: 0;
                margin: 0.75em 0;
            }
        }
    }
}
</style>
