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
    display: block;
    box-sizing: border-box;
    position: relative;
    flex: 0 0 auto;
    justify-content: flex-start;
    padding: 8px 10px;
    border-radius: 32px;
    margin: 2px 0;
    width: 100%;
    cursor: pointer;
    transition: background-color var(--animation-quick) ease-in-out;
    list-style: none;
    .wrapper {
        display: flex;
        align-items: center;
        height: 48px;

        .content {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            justify-content: space-between;
            padding-left: 8px;

            .first-line {
                display: flex;
                flex-grow: 1;
                width: 100%;
                align-items: center;
                justify-content: space-between;
                white-space: nowrap;
                margin: 0 auto 5px 0;
                overflow: hidden;
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
