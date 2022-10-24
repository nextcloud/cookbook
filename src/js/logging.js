// TODO: Switch to vuejs3-logger when we switch to Vue 3
import VueLogger from 'vuejs-logger'

export default function setupLogging(Vue) {
    Vue.use(VueLogger, {
        isEnabled: true,
        logLevel: 'debug',
        stringifyArguments: false,
        showLogLevel: true,
        showMethodName: true,
        separator: '|',
        showConsoleColors: true
    })
}
