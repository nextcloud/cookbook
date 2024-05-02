<template>
    <div class="container">
        <div class="wrapper">
            <LoadingSkeleton :type="SkeletonType.Avatar" />
            <div class="content">
                <div class="first-line">
                    <LoadingSkeleton :type="SkeletonType.Heading" />
                </div>
                <div class="second-line">
                    <LoadingSkeleton
                        :type="SkeletonType.Text"
                        style="margin-bottom: 0"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import LoadingSkeleton from 'cookbook/components/Utilities/LoadingSkeleton/LoadingSkeleton.vue';
import SkeletonType from 'cookbook/components/Utilities/LoadingSkeleton/SkeletonType';
</script>

<style scoped lang="scss">
.container {
    position: relative;
    display: block;
    width: 100%;
    box-sizing: border-box;
    flex: 0 0 auto;
    justify-content: flex-start;
    padding: 8px 10px;
    border-radius: 32px;
    margin: 2px 0;
    cursor: pointer;
    list-style: none;
    transition: background-color var(--animation-quick) ease-in-out;
    .wrapper {
        display: flex;
        height: 48px;
        align-items: center;

        .content {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            justify-content: space-between;
            padding-left: 8px;

            .first-line {
                display: flex;
                overflow: hidden;
                width: 100%;
                flex-grow: 1;
                align-items: center;
                justify-content: space-between;
                margin: 0 auto 5px 0;
                white-space: nowrap;
            }

            .second-line {
                display: flex;
                flex-grow: 1;
                align-items: flex-start;
                justify-content: space-between;
                white-space: nowrap;
            }
        }
    }
}
</style>
