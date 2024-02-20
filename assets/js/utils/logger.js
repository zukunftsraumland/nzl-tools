import api from '../api';

export const track = async (category = null, action = null, value = null) => {
    try {
        await api.logs.pixel({
            category,
            action,
            value: (typeof value === 'string') ? value : JSON.stringify(value)
        });
        return true;
    } catch (e) {
        return false;
    }
};

export const trackDevice = () => {
    return track(
        'Device Stats',
        null,
        {
            referrer: document.referrer,
            userAgent: window.navigator.userAgent,
            windowWidth: window.innerWidth,
            windowHeight: window.innerHeight,
            language: window.navigator.languages && window.navigator.languages[0] || window.navigator.language,
        }
    );
};

export const trackPageView = (href = null, title = null) => {
    return track(
        'Page View',
        (window.location.pathname + '' + window.location.search).substring(0, 64),
        {
            title: (title || document.title || ''),
            href: (href || window.location.href || ''),
        },
    );
};