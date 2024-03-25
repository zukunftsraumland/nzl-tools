export default {
    install: (app, options) => {
        app.config.globalProperties.$env = {

            INSTANCE_NAME: process.env.INSTANCE_NAME,
            INSTANCE_ID: process.env.INSTANCE_ID,

            HOST: process.env.HOST,
            FRONTEND_HOST: process.env.FRONTEND_HOST,

            MAPBOX_API_TOKEN: process.env.MAPBOX_API_TOKEN,
            MAPBOX_STYLE_URL: process.env.MAPBOX_STYLE_URL,

            THEME_COLOR_1: process.env.THEME_COLOR_1,
            THEME_COLOR_2: process.env.THEME_COLOR_2,
            THEME_COLOR_3: process.env.THEME_COLOR_3,
            THEME_COLOR_4: process.env.THEME_COLOR_4,
            THEME_LOGO: process.env.THEME_LOGO ? require('../../'+process.env.THEME_LOGO) : null,
            THEME_ICON: process.env.THEME_ICON ? require('../../'+process.env.THEME_ICON) : null,

            PLUGIN_ENABLE_INBOX: process.env.PLUGIN_ENABLE_INBOX === 'true',
            PLUGIN_ENABLE_PROJECTS: process.env.PLUGIN_ENABLE_PROJECTS === 'true',
            PLUGIN_ENABLE_PROJECT_COLLECTIONS: process.env.PLUGIN_ENABLE_PROJECT_COLLECTIONS === 'true',
            PLUGIN_ENABLE_EVENTS: process.env.PLUGIN_ENABLE_EVENTS === 'true',
            PLUGIN_ENABLE_EVENT_COLLECTIONS: process.env.PLUGIN_ENABLE_EVENT_COLLECTIONS === 'true',
            PLUGIN_ENABLE_INTERACTIVE_GRAPHICS: process.env.PLUGIN_ENABLE_INTERACTIVE_GRAPHICS === 'true',
            PLUGIN_ENABLE_FINANCIAL_SUPPORTS: process.env.PLUGIN_ENABLE_FINANCIAL_SUPPORTS === 'true',
            PLUGIN_ENABLE_EDUCATIONS: process.env.PLUGIN_ENABLE_EDUCATIONS === 'true',
            PLUGIN_ENABLE_JOBS: process.env.PLUGIN_ENABLE_JOBS === 'true',
            PLUGIN_ENABLE_CONTACTS: process.env.PLUGIN_ENABLE_CONTACTS === 'true',
            PLUGIN_ENABLE_CONTACT_GROUPS: process.env.PLUGIN_ENABLE_CONTACT_GROUPS === 'true',
            PLUGIN_ENABLE_REGIONS: process.env.PLUGIN_ENABLE_REGIONS === 'true',
            PLUGIN_ENABLE_POSTS: process.env.PLUGIN_ENABLE_POSTS === 'true',

            PROJECTS_ENABLE_COUNTRIES: process.env.PROJECTS_ENABLE_COUNTRIES === 'true',
            PROJECTS_ENABLE_PROGRAMS: process.env.PROJECTS_ENABLE_PROGRAMS === 'true',
            PROJECTS_ENABLE_INSTRUMENTS: process.env.PROJECTS_ENABLE_INSTRUMENTS === 'true',
            PROJECTS_ENABLE_GEOGRAPHIC_REGIONS: process.env.PROJECTS_ENABLE_GEOGRAPHIC_REGIONS === 'true',
            PROJECTS_ENABLE_STATES: process.env.PROJECTS_ENABLE_STATES === 'true',
            PROJECTS_ENABLE_TOPICS: process.env.PROJECTS_ENABLE_TOPICS === 'true',
            PROJECTS_ENABLE_BUSINESS_SECTORS: process.env.PROJECTS_ENABLE_BUSINESS_SECTORS === 'true',
            PROJECTS_ENABLE_PROJECT_COSTS: process.env.PROJECTS_ENABLE_PROJECT_COSTS === 'true',
            PROJECTS_ENABLE_FINANCING: process.env.PROJECTS_ENABLE_FINANCING === 'true',
            PROJECTS_ENABLE_START_DATE: process.env.PROJECTS_ENABLE_START_DATE === 'true',
            PROJECTS_ENABLE_END_DATE: process.env.PROJECTS_ENABLE_END_DATE === 'true',
            PROJECTS_ENABLE_LINKS: process.env.PROJECTS_ENABLE_LINKS === 'true',
            PROJECTS_ENABLE_IMAGES: process.env.PROJECTS_ENABLE_IMAGES === 'true',
            PROJECTS_ENABLE_FILES: process.env.PROJECTS_ENABLE_FILES === 'true',
            PROJECTS_ENABLE_VIDEOS: process.env.PROJECTS_ENABLE_VIDEOS === 'true',
            PROJECTS_ENABLE_CONTACTS: process.env.PROJECTS_ENABLE_CONTACTS === 'true',

        };
    }
}