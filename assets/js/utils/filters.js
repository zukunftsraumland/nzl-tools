export const filterProjects = (projects, filters, term) => {

    let result = projects.filter(project =>
        project.id == term.toLowerCase() ||
        project.title.toLowerCase().includes(term.toLowerCase()) ||
        (translateProjectField(project, 'title', 'de') || '').toLowerCase().includes(term.toLowerCase()) ||
        (translateProjectField(project, 'title', 'fr') || '').toLowerCase().includes(term.toLowerCase()) ||
        (translateProjectField(project, 'title', 'it') || '').toLowerCase().includes(term.toLowerCase()) ||
        (project.keywords || '').toLowerCase().includes(term.toLowerCase()) ||
        (((project.translations || {}).fr || {}).title || '').toLowerCase().includes(term.toLowerCase()) ||
        (((project.translations || {}).it || {}).title || '').toLowerCase().includes(term.toLowerCase())
    );

    let startDates = filters.filter(filter => filter.type === 'startDate').map(filter => filter.value);

    if(startDates.length) {
        result = result.filter((project) => {
            return project.startDate ? startDates.indexOf(project.startDate.substr(0, 4)) !== -1 : false;
        });
    }

    let endDates = filters.filter(filter => filter.type === 'endDate').map(filter => filter.value);

    if(endDates.length) {
        result = result.filter((project) => {
            return project.endDate ? endDates.indexOf(project.endDate.substr(0, 4)) !== -1 : false;
        });
    }

    let topics = filters.filter(filter => filter.type === 'topic').map(filter => filter.value);

    if(topics.length) {
        result = result.filter((project) => {
            let projectTopics = project.topics.map(topic => topic.name);
            return projectTopics.filter(projectTopic => topics.indexOf(projectTopic) !== -1).length === topics.length;
        });
    }

    let programs = filters.filter(filter => filter.type === 'program').map(filter => filter.value);

    if(programs.length) {
        result = result.filter((project) => {
            let projectPrograms = project.programs.map(program => program.name);
            return !!projectPrograms.filter(projectProgram => programs.indexOf(projectProgram) !== -1).length;
        });
    }

    let instruments = filters.filter(filter => filter.type === 'instrument').map(filter => filter.value);

    if(instruments.length) {
        result = result.filter((project) => {
            let projectInstruments = project.instruments.map(instrument => instrument.name);
            return !!projectInstruments.filter(projectInstrument => instruments.indexOf(projectInstrument) !== -1).length;
        });
    }

    let states = filters.filter(filter => filter.type === 'state').map(filter => filter.value);

    if(states.length) {
        result = result.filter((project) => {
            let projectStates = project.states.map(state => state.name);
            return !!projectStates.filter(projectState => states.indexOf(projectState) !== -1).length;
        });
    }

    let geographicRegions = filters.filter(filter => filter.type === 'geographicRegion').map(filter => filter.value);

    if(geographicRegions.length) {
        result = result.filter((project) => {
            let projectGeographicRegions = project.geographicRegions.map(geographicRegion => geographicRegion.name);
            return !!projectGeographicRegions.filter(projectGeographicRegion => geographicRegions.indexOf(projectGeographicRegion) !== -1).length;
        });
    }

    result.sort((a, b) => {
        return a.id > b.id ? -1 : 1;
    });

    return result;
};

export const filterEvents = (events, filters, term) => {

    let result = events.filter(event =>
        event.id == term.toLowerCase() ||
        event.title.toLowerCase().includes(term.toLowerCase()) ||
        (translateProjectField(event, 'title', 'de') || '').toLowerCase().includes(term.toLowerCase()) ||
        (translateProjectField(event, 'title', 'fr') || '').toLowerCase().includes(term.toLowerCase()) ||
        (translateProjectField(event, 'title', 'it') || '').toLowerCase().includes(term.toLowerCase()) ||
        (((event.translations || {}).fr || {}).title || '').toLowerCase().includes(term.toLowerCase()) ||
        (((event.translations || {}).it || {}).title || '').toLowerCase().includes(term.toLowerCase())
    );

    let startDates = filters.filter(filter => filter.type === 'startDate').map(filter => filter.value);

    if(startDates.length) {
        result = result.filter((event) => {
            return event.startDate ? startDates.indexOf(event.startDate.substr(0, 4)) !== -1 : false;
        });
    }

    let endDates = filters.filter(filter => filter.type === 'endDate').map(filter => filter.value);

    if(endDates.length) {
        result = result.filter((event) => {
            return event.endDate ? endDates.indexOf(event.endDate.substr(0, 4)) !== -1 : false;
        });
    }

    let types = filters.filter(filter => filter.type === 'type').map(filter => filter.value);

    if(types.length) {
        result = result.filter((event) => {
            return types.indexOf(event.type) !== -1;
        });
    }

    let topics = filters.filter(filter => filter.type === 'topic').map(filter => filter.value);

    if(topics.length) {
        result = result.filter((event) => {
            let eventTopics = event.topics.map(topic => topic.name);
            return eventTopics.filter(eventTopic => topics.indexOf(eventTopic) !== -1).length === topics.length;
        });
    }

    let languages = filters.filter(filter => filter.type === 'language').map(filter => filter.value);

    if(languages.length) {
        result = result.filter((event) => {
            let eventLanguages = event.languages.map(language => language.name);
            return eventLanguages.filter(eventLanguage => languages.indexOf(eventLanguage) !== -1).length === languages.length;
        });
    }

    let locations = filters.filter(filter => filter.type === 'location').map(filter => filter.value);

    if(locations.length) {
        result = result.filter((event) => {
            let eventLocations = event.locations.map(location => location.name);
            return eventLocations.filter(eventLocation => locations.indexOf(eventLocation) !== -1).length === locations.length;
        });
    }

    let statuses = filters.filter(filter => filter.type === 'status').map(filter => filter.value);

    if(statuses.length) {
        statuses.forEach((status) => {
            result = result.filter((event) => {
                if(status === 'archive') {
                    return event.endDate && new Date(event.endDate) < new Date();
                }
                if(status === 'current') {
                    return !event.endDate || new Date(event.endDate) >= new Date();
                }
                return true;
            });
        });
    }

    result.sort((a, b) => {
        return a.id > b.id ? -1 : 1;
    });

    return result;
};

export const translateField = (object, field, locale) => {

    let result = object[field];

    if(!locale) {
        locale = 'de';
    }

    if(!object.translations) {
        return result;
    }

    if(locale === 'de') {
        if(result) {
            return result;
        }
        if(object.translations['fr'] && object.translations['fr'][field]) {
            return object.translations['fr'][field];
        }
        if(object.translations['fr'] && typeof object.translations['fr'] === 'string') {
            return object.translations['fr'];
        }
        if(object.translations['it'] && object.translations['it'][field]) {
            return object.translations['it'][field];
        }
        if(object.translations['it'] && typeof object.translations['it'] === 'string') {
            return object.translations['it'];
        }
        return result;
    }

    if(locale === 'fr') {
        if(object.translations['fr'] && object.translations['fr'][field]) {
            return object.translations['fr'][field];
        }
        if(object.translations['fr'] && typeof object.translations['fr'] === 'string') {
            return object.translations['fr'];
        }
        if(result) {
            return result;
        }
        if(object.translations['it'] && object.translations['it'][field]) {
            return object.translations['it'][field];
        }
        if(object.translations['it'] && typeof object.translations['it'] === 'string') {
            return object.translations['it'];
        }
        return result;
    }

    if(locale === 'it') {
        if(object.translations['it'] && object.translations['it'][field]) {
            return object.translations['it'][field];
        }
        if(object.translations['it'] && typeof object.translations['it'] === 'string') {
            return object.translations['it'];
        }
        if(object.translations['fr'] && object.translations['fr'][field]) {
            return object.translations['fr'][field];
        }
        if(object.translations['fr'] && typeof object.translations['fr'] === 'string') {
            return object.translations['fr'];
        }
        return result;
    }

};

export const translateProjectField = (project, field, locale) => {

    return translateField(project, field, locale);

};

export const translateEventField = (event, field, locale) => {

    return translateField(event, field, locale);

};

export const sleep = (ms) => {
    return new Promise(resolve => setTimeout(resolve, ms));
};