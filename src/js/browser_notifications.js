import { showSimpleAlertModal } from "cookbook/js/modals"

export const requestPermission = async ({ justification }) => {

    // Exit early if browser doesn't support notifications
    if (!("Notification" in window)) {
        return
    }

    // Exit early if already granted
    if (Notification.permission === 'granted') {
        return
    }

    await showSimpleAlertModal(justification)
    await Notification.requestPermission()
}

export const notify = async (title, options) => {

    // Exit early if browser doesn't support notifications
    if (!("Notification" in window)) {
        return
    }

    // Try one more time to get permission (maybe the caller forgot to call
    // requestPermission first)
    // If it's still not granted, exit early
    if (Notification.permission !== 'granted' && (await Notification.requestPermission() !== 'granted')) {
        return
    }

    const notification = new Notification(title, options)
    notification.addEventListener("error", (error) => {
        // eslint-disable-next-line no-console
        console.error("Error showing notification:", error)
    })
}
